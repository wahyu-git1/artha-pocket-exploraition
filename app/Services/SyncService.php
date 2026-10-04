<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Expense;
use App\Models\GoalDeposit;
use App\Models\Income;
use App\Models\IncomeReceipt;
use App\Models\SavingsGoal;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SyncService
{
    /**
     * Process incoming batch changes from mobile client.
     */
    public function push(User $user, array $changes, ?string $clientTime = null): array
    {
        $serverTime = Carbon::now()->toIso8601String();
        $results = [];
        $appliedCount = 0;
        $conflictCount = 0;

        foreach ($changes as $change) {
            $entity = $change['entity'] ?? 'expense';
            $op = strtolower($change['op'] ?? 'create');
            $clientId = $change['client_id'] ?? (string) Str::uuid();
            $clientUpdatedAt = isset($change['updated_at']) ? Carbon::parse($change['updated_at']) : Carbon::now();
            $data = $change['data'] ?? [];

            try {
                $itemResult = DB::transaction(function () use ($user, $entity, $op, $clientId, $clientUpdatedAt, $data) {
                    return match ($entity) {
                        'expense' => $this->syncExpense($user, $op, $clientId, $clientUpdatedAt, $data),
                        'income' => $this->syncIncome($user, $op, $clientId, $clientUpdatedAt, $data),
                        'income_receipt' => $this->syncIncomeReceipt($user, $op, $clientId, $clientUpdatedAt, $data),
                        'savings_goal' => $this->syncSavingsGoal($user, $op, $clientId, $clientUpdatedAt, $data),
                        'goal_deposit' => $this->syncGoalDeposit($user, $op, $clientId, $clientUpdatedAt, $data),
                        default => ['status' => 'rejected', 'error' => "Entity '{$entity}' not supported for sync."],
                    };
                });

                if (($itemResult['status'] ?? '') === 'applied') {
                    $appliedCount++;
                } elseif (($itemResult['status'] ?? '') === 'conflict') {
                    $conflictCount++;
                }

                $results[] = array_merge(['client_id' => $clientId], $itemResult);
            } catch (\Throwable $e) {
                $results[] = [
                    'client_id' => $clientId,
                    'status' => 'rejected',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return [
            'server_time' => $serverTime,
            'applied_count' => $appliedCount,
            'conflict_count' => $conflictCount,
            'results' => $results,
        ];
    }

    /**
     * Retrieve all changes occurred on server since a given timestamp.
     */
    public function pull(User $user, ?string $since = null, ?string $cursor = null, int $limit = 200): array
    {
        $serverTime = Carbon::now()->toIso8601String();
        
        $sinceDate = Carbon::create(2000, 1, 1);
        if ($since) {
            $cleanSince = str_replace(' ', '+', $since);
            try {
                $sinceDate = Carbon::parse($cleanSince);
            } catch (\Throwable $e) {
                $sinceDate = Carbon::create(2000, 1, 1);
            }
        }
        $changes = [];

        // 1. Pull Expenses (including soft deleted)
        $expenses = Expense::withTrashed()
            ->where('user_id', $user->id)
            ->where('updated_at', '>', $sinceDate)
            ->orderBy('updated_at')
            ->limit($limit)
            ->get();

        foreach ($expenses as $expense) {
            $changes[] = [
                'entity' => 'expense',
                'op' => $expense->deleted_at ? 'delete' : 'upsert',
                'data' => $expense->deleted_at ? ['id' => $expense->id, 'client_id' => $expense->client_id] : $expense->toArray(),
                'updated_at' => $expense->updated_at?->toIso8601String(),
            ];
        }

        // 2. Pull Incomes
        $incomes = Income::withTrashed()
            ->where('user_id', $user->id)
            ->where('updated_at', '>', $sinceDate)
            ->orderBy('updated_at')
            ->limit($limit)
            ->get();

        foreach ($incomes as $income) {
            $changes[] = [
                'entity' => 'income',
                'op' => $income->deleted_at ? 'delete' : 'upsert',
                'data' => $income->deleted_at ? ['id' => $income->id] : $income->toArray(),
                'updated_at' => $income->updated_at?->toIso8601String(),
            ];
        }

        // 3. Pull Savings Goals
        $goals = SavingsGoal::withTrashed()
            ->where('user_id', $user->id)
            ->where('updated_at', '>', $sinceDate)
            ->orderBy('updated_at')
            ->limit($limit)
            ->get();

        foreach ($goals as $goal) {
            $changes[] = [
                'entity' => 'savings_goal',
                'op' => $goal->deleted_at ? 'delete' : 'upsert',
                'data' => $goal->deleted_at ? ['id' => $goal->id] : $goal->toArray(),
                'updated_at' => $goal->updated_at?->toIso8601String(),
            ];
        }

        // 4. Pull Custom Categories
        $categories = Category::withTrashed()
            ->where(function ($q) use ($user) {
                $q->whereNull('user_id')->orWhere('user_id', $user->id);
            })
            ->where('updated_at', '>', $sinceDate)
            ->orderBy('updated_at')
            ->limit($limit)
            ->get();

        foreach ($categories as $category) {
            $changes[] = [
                'entity' => 'category',
                'op' => $category->deleted_at ? 'delete' : 'upsert',
                'data' => $category->deleted_at ? ['id' => $category->id] : $category->toArray(),
                'updated_at' => $category->updated_at?->toIso8601String(),
            ];
        }

        return [
            'server_time' => $serverTime,
            'has_more' => false,
            'next_cursor' => null,
            'changes' => $changes,
        ];
    }

    private function syncExpense(User $user, string $op, string $clientId, Carbon $clientUpdatedAt, array $data): array
    {
        $existing = Expense::withTrashed()
            ->where('user_id', $user->id)
            ->where('client_id', $clientId)
            ->first();

        if ($op === 'delete') {
            if ($existing) {
                $existing->delete();
                return ['status' => 'applied', 'server_id' => $existing->id];
            }
            if (!empty($data['id'])) {
                $byServerId = Expense::where('user_id', $user->id)->where('id', $data['id'])->first();
                if ($byServerId) {
                    $byServerId->delete();
                    return ['status' => 'applied', 'server_id' => $byServerId->id];
                }
            }
            return ['status' => 'applied', 'server_id' => null];
        }

        // Create or update
        if ($existing) {
            // Last-Write-Wins check
            if ($existing->updated_at && $existing->updated_at->isAfter($clientUpdatedAt)) {
                return [
                    'status' => 'conflict',
                    'server_id' => $existing->id,
                    'message' => 'Server has newer data (Last-Write-Wins)',
                ];
            }

            $existing->update([
                'income_id' => $data['income_id'] ?? $existing->income_id,
                'category_id' => $data['category_id'] ?? $existing->category_id,
                'item' => $data['item'] ?? $existing->item,
                'amount' => $data['amount'] ?? $existing->amount,
                'spent_at' => $data['spent_at'] ?? $existing->spent_at,
                'note' => $data['note'] ?? $existing->note,
                'source' => 'sync',
            ]);

            return ['status' => 'applied', 'server_id' => $existing->id];
        }

        // New record
        $expense = Expense::create([
            'id' => $data['id'] ?? (string) Str::uuid(),
            'user_id' => $user->id,
            'income_id' => $data['income_id'] ?? $user->primary_income_id ?? Income::where('user_id', $user->id)->value('id'),
            'category_id' => $data['category_id'] ?? Category::where('type', 'expense')->value('id'),
            'item' => $data['item'] ?? 'Pengeluaran',
            'amount' => $data['amount'] ?? 0,
            'spent_at' => $data['spent_at'] ?? Carbon::now()->toDateString(),
            'note' => $data['note'] ?? null,
            'source' => 'sync',
            'client_id' => $clientId,
        ]);

        return ['status' => 'applied', 'server_id' => $expense->id];
    }

    private function syncIncome(User $user, string $op, string $clientId, Carbon $clientUpdatedAt, array $data): array
    {
        if ($op === 'delete' && !empty($data['id'])) {
            $income = Income::where('user_id', $user->id)->where('id', $data['id'])->first();
            if ($income) {
                $income->delete();
            }
            return ['status' => 'applied', 'server_id' => $data['id']];
        }

        $id = $data['id'] ?? (string) Str::uuid();
        $income = Income::updateOrCreate(
            ['user_id' => $user->id, 'id' => $id],
            [
                'name' => $data['name'] ?? 'Dompet Utama',
                'default_amount' => $data['default_amount'] ?? 0,
                'frequency' => $data['frequency'] ?? 'monthly',
                'is_primary' => $data['is_primary'] ?? false,
                'is_active' => $data['is_active'] ?? true,
            ]
        );

        return ['status' => 'applied', 'server_id' => $income->id];
    }

    private function syncIncomeReceipt(User $user, string $op, string $clientId, Carbon $clientUpdatedAt, array $data): array
    {
        $existing = IncomeReceipt::where('user_id', $user->id)->where('client_id', $clientId)->first();

        if ($op === 'delete') {
            if ($existing) {
                $existing->delete();
            }
            return ['status' => 'applied', 'server_id' => $existing?->id];
        }

        if ($existing) {
            $existing->update([
                'amount' => $data['amount'] ?? $existing->amount,
                'received_at' => $data['received_at'] ?? $existing->received_at,
                'note' => $data['note'] ?? $existing->note,
            ]);
            return ['status' => 'applied', 'server_id' => $existing->id];
        }

        $receipt = IncomeReceipt::create([
            'id' => (string) Str::uuid(),
            'user_id' => $user->id,
            'income_id' => $data['income_id'],
            'amount' => $data['amount'] ?? 0,
            'received_at' => $data['received_at'] ?? Carbon::now()->toDateString(),
            'note' => $data['note'] ?? null,
            'client_id' => $clientId,
        ]);

        return ['status' => 'applied', 'server_id' => $receipt->id];
    }

    private function syncSavingsGoal(User $user, string $op, string $clientId, Carbon $clientUpdatedAt, array $data): array
    {
        $id = $data['id'] ?? (string) Str::uuid();
        if ($op === 'delete') {
            $goal = SavingsGoal::where('user_id', $user->id)->where('id', $id)->first();
            if ($goal) {
                $goal->delete();
            }
            return ['status' => 'applied', 'server_id' => $id];
        }

        $goal = SavingsGoal::updateOrCreate(
            ['user_id' => $user->id, 'id' => $id],
            [
                'name' => $data['name'] ?? 'Target Tabungan',
                'price' => $data['price'] ?? 0,
                'saved_amount' => $data['saved_amount'] ?? 0,
                'target_date' => $data['target_date'] ?? Carbon::now()->addMonths(6)->toDateString(),
                'status' => $data['status'] ?? 'active',
            ]
        );

        return ['status' => 'applied', 'server_id' => $goal->id];
    }

    private function syncGoalDeposit(User $user, string $op, string $clientId, Carbon $clientUpdatedAt, array $data): array
    {
        $existing = GoalDeposit::where('user_id', $user->id)->where('client_id', $clientId)->first();

        if ($op === 'delete' && $existing) {
            $existing->delete();
            return ['status' => 'applied', 'server_id' => $existing->id];
        }

        if ($existing) {
            return ['status' => 'applied', 'server_id' => $existing->id];
        }

        $deposit = GoalDeposit::create([
            'id' => (string) Str::uuid(),
            'user_id' => $user->id,
            'goal_id' => $data['goal_id'],
            'income_id' => $data['income_id'],
            'amount' => $data['amount'],
            'deposited_at' => $data['deposited_at'] ?? Carbon::now()->toDateString(),
            'note' => $data['note'] ?? null,
            'client_id' => $clientId,
        ]);

        // Increment saved_amount on the goal
        $goal = SavingsGoal::where('user_id', $user->id)->where('id', $data['goal_id'])->first();
        if ($goal) {
            $goal->increment('saved_amount', $data['amount']);
            if ($goal->saved_amount >= $goal->price) {
                $goal->update(['status' => 'completed', 'completed_at' => Carbon::now()]);
            }
        }

        return ['status' => 'applied', 'server_id' => $deposit->id];
    }
}
