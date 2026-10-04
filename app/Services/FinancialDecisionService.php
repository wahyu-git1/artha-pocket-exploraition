<?php

namespace App\Services;

use App\Models\AllocationPlan;
use App\Models\CategoryBucketMapping;
use App\Models\EmergencyFund;
use App\Models\Expense;
use App\Models\IncomeReceipt;
use App\Models\Investment;
use App\Models\SavingsGoal;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FinancialDecisionService
{
    public function analyze(string $userId, string $from, string $to): array
    {
        // 1. Ambil data pengeluaran dan pemasukan pada rentang waktu
        $totalIncome = (int) IncomeReceipt::where('user_id', $userId)
            ->whereBetween('received_at', [$from, $to])
            ->sum('amount');

        $expenses = Expense::where('user_id', $userId)
            ->whereBetween('spent_at', [$from, $to])
            ->with('category')
            ->get();

        $totalExpense = (int) $expenses->sum('amount');
        $netCashFlow = $totalIncome - $totalExpense;

        // Breakdown per kategori
        $categoryBreakdown = [];
        foreach ($expenses as $e) {
            $catName = $e->category->name ?? 'Lain-lain';
            if (!isset($categoryBreakdown[$catName])) {
                $categoryBreakdown[$catName] = 0;
            }
            $categoryBreakdown[$catName] += $e->amount;
        }
        arsort($categoryBreakdown);
        $topCategory = !empty($categoryBreakdown) ? array_key_first($categoryBreakdown) : 'Tidak ada';
        $topCategoryAmount = !empty($categoryBreakdown) ? reset($categoryBreakdown) : 0;

        // 2. Ambil Alokasi Anggaran (Plan vs Realized)
        $month = substr($from, 0, 7);
        $allocationPlan = AllocationPlan::where('user_id', $userId)
            ->where('month', $month)
            ->with('items')
            ->first();

        $mappings = CategoryBucketMapping::where('user_id', $userId)->pluck('bucket', 'category_id')->toArray();

        $bucketSpent = ['need' => 0, 'want' => 0, 'saving' => 0, 'investment' => 0];
        foreach ($expenses as $e) {
            $bucket = $mappings[$e->category_id] ?? ($e->category->bucket ?? 'need');
            if (isset($bucketSpent[$bucket])) {
                $bucketSpent[$bucket] += $e->amount;
            } else {
                $bucketSpent['need'] += $e->amount;
            }
        }

        $bucketPercentages = [];
        foreach ($bucketSpent as $b => $amt) {
            $bucketPercentages[$b] = $totalExpense > 0 ? round(($amt / $totalExpense) * 100, 1) : 0;
        }

        // 3. Ambil Dana Darurat
        $emergencyFund = EmergencyFund::where('user_id', $userId)->first();
        $efSaved = $emergencyFund ? (int) $emergencyFund->saved_amount : 0;
        $efTarget = $emergencyFund ? (int) $emergencyFund->target_amount : 0;
        $efMultiplier = $emergencyFund ? (int) $emergencyFund->multiplier : 6;
        $monthlyExpenseEstimate = $totalExpense > 0 ? $totalExpense : 3000000;
        $efMonthsCovered = $monthlyExpenseEstimate > 0 ? round($efSaved / $monthlyExpenseEstimate, 1) : 0;

        // 4. Ambil Target Tabungan
        $savingsGoals = SavingsGoal::where('user_id', $userId)
            ->where('status', 'active')
            ->get();

        $totalMonthlyGoalCommitment = 0;
        $goalDetails = [];
        foreach ($savingsGoals as $g) {
            $monthlyNeeded = $g->monthly_amount ?: ($g->price > 0 ? ceil($g->price / 12) : 0);
            $totalMonthlyGoalCommitment += $monthlyNeeded;
            $goalDetails[] = [
                'name' => $g->name,
                'target_price' => (int) $g->price,
                'saved' => (int) $g->saved_amount,
                'monthly_needed' => (int) $monthlyNeeded,
                'target_date' => $g->target_date ? $g->target_date->format('Y-m-d') : null,
                'progress_percent' => $g->price > 0 ? round(($g->saved_amount / $g->price) * 100, 1) : 0
            ];
        }

        // 5. Ambil Investasi
        $investments = Investment::where('user_id', $userId)->get();
        $totalInvested = (int) $investments->sum('amount');

        // Kemas konteks data untuk AI
        $contextData = [
            'period' => ['from' => $from, 'to' => $to],
            'income' => $totalIncome,
            'expense' => $totalExpense,
            'net_cash_flow' => $netCashFlow,
            'top_category' => ['name' => $topCategory, 'amount' => $topCategoryAmount],
            'categories' => array_slice($categoryBreakdown, 0, 5, true),
            'allocation_actual_percent' => $bucketPercentages,
            'emergency_fund' => [
                'saved' => $efSaved,
                'target' => $efTarget,
                'multiplier_target' => $efMultiplier,
                'months_covered_real' => $efMonthsCovered
            ],
            'savings_goals' => [
                'count' => count($savingsGoals),
                'total_monthly_commitment' => $totalMonthlyGoalCommitment,
                'goals' => $goalDetails
            ],
            'investments' => [
                'total_portfolio' => $totalInvested,
                'count' => count($investments)
            ]
        ];

        // Coba analisis menggunakan AI Gemini
        $aiResult = $this->callGeminiAdvisor($contextData);
        if ($aiResult) {
            return array_merge($contextData, ['decision_analysis' => $aiResult]);
        }

        // Fallback cerdas berbasis perhitungan deterministik
        $fallbackResult = $this->generateDeterministicAnalysis($contextData);
        return array_merge($contextData, ['decision_analysis' => $fallbackResult]);
    }

    private function callGeminiAdvisor(array $context): ?array
    {
        $apiKey = config('services.gemini.api_key');
        if (empty($apiKey)) {
            return null;
        }

        $jsonContext = json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $prompt = <<<PROMPT
Anda adalah Financial Advisor AI (Perencana Keuangan Ahli) kelas dunia untuk aplikasi finansial "CatatDuit".
Tugas Anda adalah membaca data keuangan aktual pengguna pada rentang waktu tertentu, lalu menyimpulkan KEPUTUSAN FINANSIAL apa yang harus diambil, serta menjabarkan IMPACT / DAMPAK KE DEPANNYA terhadap 4 Pilar Finansial pengguna:
1. Alokasi Anggaran (Need / Want / Saving / Investment)
2. Dana Darurat (Ketahanan finansial)
3. Target Tabungan (Pencapaian deadline impian)
4. Investasi (Pertumbuhan kekayaan jangka panjang)

Gunakan nada bicara yang profesional, empatik, berbasis data, dan to the point dalam Bahasa Indonesia.

Berikut Data Pengguna:
{$jsonContext}

Wajib berikan respon dalam format JSON murni tanpa markdown (tanpa ```json) dengan struktur persis seperti berikut:
{
  "health_score": 75,
  "health_status": "Sehat / Perlu Waspada / Kritis",
  "primary_conclusion": "Kesimpulan tajam 2-3 kalimat mengenai keputusan belanja dan arus kas periode ini.",
  "pillars": {
    "allocation": {
      "status": "good / warning / danger",
      "headline": "Judul ringkas pos alokasi",
      "explanation": "Penjelasan mengapa pos ini over/under budget dan kebocorannya",
      "future_impact": "Dampak ke pos lain jika kebiasaan ini terus berulang"
    },
    "emergency_fund": {
      "status": "good / warning / danger",
      "headline": "Judul daya tahan darurat",
      "explanation": "Penjelasan daya tahan riil dalam bulan terhadap pengeluaran saat ini",
      "future_impact": "Risiko keamanan jika terjadi hal tak terduga"
    },
    "savings_goals": {
      "status": "good / warning / danger",
      "headline": "Judul status target impian",
      "explanation": "Penjelasan apakah surplus mencukupi setoran bulanan target",
      "future_impact": "Proyeksi apakah target tercapai tepat waktu atau mundur berapa bulan"
    },
    "investment": {
      "status": "good / warning / info",
      "headline": "Judul potensi akumulasi portofolio",
      "explanation": "Penjelasan peluang pertumbuhan modal dari sisa kas",
      "future_impact": "Proyeksi nilai aset dalam 3-5 tahun jika surplus diinvestasikan secara disiplin"
    }
  },
  "action_plan": [
    {
      "priority": "Tinggi",
      "action": "Tindakan konkret 1 yang harus dilakukan minggu ini",
      "target_pillar": "allocation / emergency_fund / savings_goals / investment"
    },
    {
      "priority": "Sedang",
      "action": "Tindakan konkret 2",
      "target_pillar": "savings_goals"
    },
    {
      "priority": "Sedang",
      "action": "Tindakan konkret 3",
      "target_pillar": "emergency_fund"
    }
  ]
}
PROMPT;

        try {
            $response = Http::timeout(25)->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.2,
                    'responseMimeType' => 'application/json',
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
                $text = trim($text);
                if (str_starts_with($text, '```json')) {
                    $text = str_replace(['```json', '```'], '', $text);
                    $text = trim($text);
                }
                $decoded = json_decode($text, true);
                if (is_array($decoded) && isset($decoded['health_score'])) {
                    return $decoded;
                }
            }
        } catch (\Exception $e) {
            Log::warning('Gemini Financial Advisor fallback: ' . $e->getMessage());
        }

        return null;
    }

    private function generateDeterministicAnalysis(array $context): array
    {
        $income = $context['income'];
        $expense = $context['expense'];
        $net = $context['net_cash_flow'];
        $efCovered = $context['emergency_fund']['months_covered_real'];
        $savingsCommitment = $context['savings_goals']['total_monthly_commitment'];
        $topCatName = $context['top_category']['name'];
        $topCatAmt = $context['top_category']['amount'];

        $wantsPct = $context['allocation_actual_percent']['want'] ?? 0;
        $needsPct = $context['allocation_actual_percent']['need'] ?? 0;

        // Hitung Skor Kesehatan Finansial
        $score = 50;
        if ($net > 0) $score += 20;
        if ($net < 0) $score -= 25;
        if ($efCovered >= 6) $score += 20;
        elseif ($efCovered >= 3) $score += 10;
        else $score -= 10;
        if ($wantsPct <= 30) $score += 10;
        else $score -= 10;

        $score = max(15, min(95, $score));

        $status = $score >= 75 ? 'Sehat (On Track)' : ($score >= 50 ? 'Perlu Waspada' : 'Kritis (Defisit)');

        // Narasi Kesimpulan Utama
        if ($net < 0) {
            $primaryConclusion = "Arus kas mengalami defisit sebesar Rp" . number_format(abs($net), 0, ',', '.') . " karena pengeluaran melebihi pemasukan, didominasi oleh kategori {$topCatName}. Anda perlu mengerem belanja non-pokok agar tidak menguras tabungan.";
        } else {
            $primaryConclusion = "Arus kas surplus sebesar Rp" . number_format($net, 0, ',', '.') . ". Namun pengeluaran terbesar ada pada kategori {$topCatName} (Rp" . number_format($topCatAmt, 0, ',', '.') . ") yang perlu dijaga agar alokasi masa depan tidak terganggu.";
        }

        // Analisis Alokasi
        $allocStatus = $wantsPct > 35 ? 'warning' : 'good';
        $allocHeadline = $wantsPct > 35 ? "Pos Keinginan Melebihi Batas ({$wantsPct}%)" : "Alokasi Pos Kebutuhan Terkendali";
        $allocExplanation = $wantsPct > 35 
            ? "Pengeluaran gaya hidup menyerap {$wantsPct}% dari total anggaran, melampaui standar ideal 30%."
            : "Rasio belanja kebutuhan pokok dan keinginan masih dalam batas aman anggaran 50/30/20.";
        $allocImpact = $wantsPct > 35 
            ? "Membatasi potensi dana yang seharusnya bisa disalurkan ke Dana Darurat atau Investasi."
            : "Menjaga stabilitas arus kas bulanan tetap konsisten.";

        // Analisis Dana Darurat
        $efStatus = $efCovered >= 6 ? 'good' : ($efCovered >= 3 ? 'warning' : 'danger');
        $efHeadline = "Ketahanan Darurat: {$efCovered} Bulan";
        $efExplanation = $efCovered < 3 
            ? "Saldo dana darurat saat ini hanya sanggup menanggung {$efCovered} bulan pengeluaran riil."
            : "Cadangan likuid Anda sanggup memproteksi hidup selama {$efCovered} bulan jika terjadi kehilangan pemasukan.";
        $efImpact = $efCovered < 3 
            ? "Risiko finansial tinggi. Kebutuhan mendesak berpotensi memicu utang konsumtif jika tidak segera ditambah."
            : "Memberikan ketenangan pikiran dan fondasi kuat sebelum memperbesar porsi investasi.";

        // Analisis Target Tabungan
        $goalDeficit = $savingsCommitment - max(0, $net);
        $goalsStatus = $goalDeficit > 0 ? 'warning' : 'good';
        $goalsHeadline = $goalDeficit > 0 ? "Target Tabungan Berisiko Tertunda" : "Target Tabungan Tepat Waktu";
        $goalsExplanation = $goalDeficit > 0
            ? "Kewajiban setoran impian Anda adalah Rp" . number_format($savingsCommitment, 0, ',', '.') . "/bln, sementara sisa surplus saat ini hanya Rp" . number_format(max(0, $net), 0, ',', '.') . "."
            : "Surplus kas mencukupi untuk memenuhi target komitmen setoran bulanan.";
        $goalsImpact = $goalDeficit > 0
            ? "Bila pola belanja ini berlanjut, deadline target tabungan berpotensi mundur 1 hingga 3 bulan."
            : "Target impian diproyeksikan dapat tercapai tepat pada waktu yang direncanakan.";

        // Analisis Investasi
        $projectedWealth = max(0, $net) > 0 ? round(($net * 36) * 1.15) : 15000000;
        $invHeadline = "Peluang Akumulasi Rp" . number_format($projectedWealth, 0, ',', '.') . " dalam 3 Tahun";
        $invExplanation = "Konsistensi menyisihkan sisa surplus ke instrumen pasar modal/emas akan mengakumulasi pertumbuhan majemuk.";
        $invImpact = "Membantu melawan inflasi dan membangun kebebasan finansial jangka panjang.";

        return [
            'health_score' => $score,
            'health_status' => $status,
            'primary_conclusion' => $primaryConclusion,
            'pillars' => [
                'allocation' => [
                    'status' => $allocStatus,
                    'headline' => $allocHeadline,
                    'explanation' => $allocExplanation,
                    'future_impact' => $allocImpact
                ],
                'emergency_fund' => [
                    'status' => $efStatus,
                    'headline' => $efHeadline,
                    'explanation' => $efExplanation,
                    'future_impact' => $efImpact
                ],
                'savings_goals' => [
                    'status' => $goalsStatus,
                    'headline' => $goalsHeadline,
                    'explanation' => $goalsExplanation,
                    'future_impact' => $goalsImpact
                ],
                'investment' => [
                    'status' => 'info',
                    'headline' => $invHeadline,
                    'explanation' => $invExplanation,
                    'future_impact' => $invImpact
                ]
            ],
            'action_plan' => [
                [
                    'priority' => 'Tinggi',
                    'action' => "Kendalikan pengeluaran pada kategori '{$topCatName}' untuk mengamankan minimal 20% surplus.",
                    'target_pillar' => 'allocation'
                ],
                [
                    'priority' => 'Tinggi',
                    'action' => "Prioritaskan alokasi surplus ke Dana Darurat hingga mencapai minimal 3-6 bulan pengeluaran.",
                    'target_pillar' => 'emergency_fund'
                ],
                [
                    'priority' => 'Sedang',
                    'action' => "Setor komitmen bulanan target tabungan aktif secara otomatis setiap awal gajian.",
                    'target_pillar' => 'savings_goals'
                ]
            ]
        ];
    }
}
