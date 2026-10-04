<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ErrorCode;
use App\Models\UserCategoryPreference;
use App\Services\AiSmartEntryParser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SmartEntryController extends BaseController
{
    public function parse(Request $request, AiSmartEntryParser $parser): JsonResponse
    {
        $request->validate([
            'text' => 'required|string|max:200'
        ]);

        try {
            $result = $parser->parse($request->text, $request->user()->id);
            return $this->success($result);
        } catch (\Exception $e) {
            if ($e->getMessage() === 'PARSE_NO_AMOUNT') {
                return $this->error(
                    ErrorCode::PARSE_NO_AMOUNT,
                    'Nominal tidak ditemukan dalam teks.',
                    [],
                    422
                );
            }

            return $this->error(ErrorCode::SERVER_ERROR, 'Gagal memproses teks.');
        }
    }

    public function feedback(Request $request): JsonResponse
    {
        $request->validate([
            'keyword' => 'required|string|max:60',
            'category_id' => 'required|uuid|exists:categories,id'
        ]);

        $pref = UserCategoryPreference::firstOrNew([
            'user_id' => $request->user()->id,
            'keyword' => strtolower(trim($request->keyword)),
        ]);

        $pref->category_id = $request->category_id;
        $pref->hit_count = $pref->exists ? $pref->hit_count + 1 : 1;
        $pref->save();

        return $this->success([
            'id' => $pref->id,
            'keyword' => $pref->keyword,
            'category_id' => $pref->category_id,
            'hit_count' => $pref->hit_count
        ]);
    }
    public function submit(Request $request, \App\Services\IncomeBalanceService $balanceService): JsonResponse
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.type' => 'required|in:expense,income',
            'items.*.item' => 'required|string',
            'items.*.amount' => 'required|numeric',
            'items.*.date' => 'nullable|date',
            'items.*.category_id' => 'nullable|uuid'
        ]);

        $user = $request->user();
        $parsed = ['items' => $request->items];
        $savedItems = [];
        $affectedIncomeIds = [];

        \Illuminate\Support\Facades\DB::transaction(function () use ($request, $parsed, $user, &$savedItems, &$affectedIncomeIds) {
            foreach ($parsed['items'] as $item) {
                if ($item['type'] === 'expense') {
                    // Try to get primary income or the first income
                    $income = \App\Models\Income::where('user_id', $user->id)->first();
                    if (!$income) {
                        $income = \App\Models\Income::create([
                            'user_id' => $user->id,
                            'name' => 'Dompet Utama',
                            'frequency' => 'irregular',
                            'is_active' => true
                        ]);
                    }

                    if (!$item['category_id']) {
                        $cat = \App\Models\Category::firstOrCreate(
                            ['user_id' => $user->id, 'name' => 'Lain-lain', 'type' => 'expense'],
                            ['is_default' => true]
                        );
                        $item['category_id'] = $cat->id;
                    }

                    $expense = \App\Models\Expense::create([
                        'user_id' => $user->id,
                        'income_id' => $income->id,
                        'category_id' => $item['category_id'],
                        'amount' => $item['amount'],
                        'item' => $item['item'],
                        'spent_at' => $item['date'] ?? date('Y-m-d'),
                        'source' => 'smart_entry',
                        'raw_input' => $request->input('raw_input', $item['item']),
                        'confidence_score' => $item['confidence_score'] ?? null,
                    ]);
                    $savedItems[] = $expense;
                    $affectedIncomeIds[$income->id] = true;
                } else {
                    $income = \App\Models\Income::firstOrCreate(
                        ['user_id' => $user->id, 'name' => 'Pemasukan Otomatis'],
                        ['frequency' => 'irregular', 'is_active' => true]
                    );

                    $receipt = \App\Models\IncomeReceipt::create([
                        'user_id' => $user->id,
                        'income_id' => $income->id,
                        'category_id' => $item['category_id'],
                        'amount' => $item['amount'],
                        'note' => $item['item'],
                        'received_at' => $item['date'] ?? date('Y-m-d'),
                        'raw_input' => $request->input('raw_input', $item['item'])
                    ]);
                    $savedItems[] = $receipt;
                    $affectedIncomeIds[$income->id] = true;
                }
            }
        });

        $balances = [];
        foreach (array_keys($affectedIncomeIds) as $incomeId) {
            $balances[$incomeId] = $balanceService->calculate($incomeId);
        }

        return $this->success([
            'parsed_raw' => $parsed,
            'saved_count' => count($savedItems),
            'balances' => $balances
        ]);
    }
}
