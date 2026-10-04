<?php

namespace App\Services;

use App\Models\Category;
use App\Models\CategoryRule;
use App\Models\UserCategoryPreference;
use Carbon\Carbon;
use Illuminate\Support\Str;

class SmartEntryParser
{
    protected array $expenseKeywords = ['beli', 'bayar', 'jajan', 'keluar', 'kulak', 'kulakan', 'ongkir', 'prive', 'cicil', 'angsuran', 'sewa'];
    protected array $incomeKeywords = ['gajian', 'terima', 'dapat', 'masuk', 'laku', 'omzet', 'jual', 'penjualan', 'cair', 'piutang', 'closing'];
    protected array $dateKeywords = [
        'kemarin' => '-1 day',
        'tadi pagi' => 'today',
        'hari ini' => 'today',
        'kemaren' => '-1 day'
    ];
    protected array $splitKeywords = ['sama', 'dan', 'terus', ','];

    /**
     * @return array{items: array, needs_review: bool}
     * @throws \Exception
     */
    public function parse(string $text, string $userId): array
    {
        $text = strtolower(trim($text));
        
        // Handle multi-items
        $parts = $this->splitText($text);
        
        $results = [];
        $needsReviewGlobal = false;
        
        foreach ($parts as $part) {
            $parsedItem = $this->parseSingleItem($part, $userId);
            if ($parsedItem) {
                if ($parsedItem['confidence_score'] < 0.7) {
                    $needsReviewGlobal = true;
                    $parsedItem['needs_review'] = true;
                }
                $results[] = $parsedItem;
            }
        }

        if (empty($results)) {
            throw new \Exception('PARSE_NO_AMOUNT');
        }

        return [
            'items' => $results,
            'needs_review' => $needsReviewGlobal
        ];
    }

    protected function splitText(string $text): array
    {
        $pattern = '/\b(' . implode('|', $this->splitKeywords) . ')\b/';
        $parts = preg_split($pattern, $text);
        return array_filter(array_map('trim', $parts));
    }

    protected function parseSingleItem(string $text, string $userId): ?array
    {
        // Extract Amount
        $amountData = $this->extractAmount($text);
        if (!$amountData['amount']) {
            return null; // Not an item if no amount
        }
        $amount = $amountData['amount'];
        $text = str_replace($amountData['matched'], '', $text); // Remove matched amount from text

        // Extract Date
        $dateData = $this->extractDate($text);
        $date = $dateData['date'];
        if ($dateData['matched']) {
            $text = str_replace($dateData['matched'], '', $text);
        }

        // Extract Type
        $typeData = $this->extractType($text);
        $type = $typeData['type'];
        if ($typeData['matched']) {
            $text = str_replace($typeData['matched'], '', $text);
        }

        // Clean up item name
        $item = trim(preg_replace('/\s+/', ' ', $text));
        if (empty($item)) {
            $item = 'Tidak diketahui';
        }

        // Categorize
        $categoryData = $this->categorize($item, $userId, $type);
        
        // Calculate confidence
        $confidence = 0.5; // Base confidence
        if ($categoryData['source'] === 'preference') $confidence += 0.4;
        if ($categoryData['source'] === 'rule') $confidence += 0.3;
        if (!empty($item) && $item !== 'Tidak diketahui') $confidence += 0.1;
        
        if ($confidence > 1.0) $confidence = 1.0;

        return [
            'item' => Str::title($item),
            'amount' => $amount,
            'type' => $type,
            'spent_at' => $date,
            'category_id' => $categoryData['category_id'] ?? null,
            'category_name' => $categoryData['category_name'] ?? null,
            'suggestions' => $confidence < 0.7 ? $this->getSuggestions($type) : [],
            'confidence_score' => round($confidence, 2)
        ];
    }

    protected function extractAmount(string $text): array
    {
        // Remove Rp and dots first to simplify
        $textNorm = preg_replace('/rp\.?\s?/i', '', $text);
        
        // Match patterns like 10k, 10rb, 10 ribu, 1.5jt, 1,5jt, 10000
        $pattern = '/(\d+(?:[\.,]\d+)?)\s*(k|rb|ribu|jt|juta|m|milyar|)\b/i';
        
        if (preg_match($pattern, $textNorm, $matches, PREG_OFFSET_CAPTURE)) {
            $numberStr = str_replace(',', '.', $matches[1][0]);
            $number = (float) $numberStr;
            $suffix = strtolower($matches[2][0]);
            
            $multiplier = 1;
            if (in_array($suffix, ['k', 'rb', 'ribu'])) $multiplier = 1000;
            if (in_array($suffix, ['jt', 'juta'])) $multiplier = 1000000;
            if (in_array($suffix, ['m', 'milyar'])) $multiplier = 1000000000;
            
            // Re-find the exact original match to remove it
            preg_match($pattern, $text, $origMatches);
            
            return [
                'amount' => (int) ($number * $multiplier),
                'matched' => $origMatches[0] ?? $matches[0][0]
            ];
        }
        
        // Try strict digits
        if (preg_match('/\b(\d{3,})\b/', $text, $matches)) {
             return [
                'amount' => (int) $matches[1],
                'matched' => $matches[1]
             ];
        }
        
        return ['amount' => 0, 'matched' => ''];
    }

    protected function extractDate(string $text): array
    {
        foreach ($this->dateKeywords as $keyword => $modifier) {
            if (str_contains($text, $keyword)) {
                return [
                    'date' => date('Y-m-d', strtotime($modifier)),
                    'matched' => $keyword
                ];
            }
        }
        return [
            'date' => date('Y-m-d'),
            'matched' => ''
        ];
    }

    protected function extractType(string $text): array
    {
        foreach ($this->expenseKeywords as $kw) {
            if (preg_match('/\b' . $kw . '\b/i', $text, $matches)) {
                return ['type' => 'expense', 'matched' => $matches[0]];
            }
        }
        foreach ($this->incomeKeywords as $kw) {
            if (preg_match('/\b' . $kw . '\b/i', $text, $matches)) {
                return ['type' => 'income', 'matched' => $matches[0]];
            }
        }
        return ['type' => 'expense', 'matched' => '']; // default expense
    }

    protected function categorize(string $item, string $userId, string $type): array
    {
        $words = explode(' ', $item);
        
        // 1. Check user preferences
        foreach ($words as $word) {
            if (strlen($word) < 3) continue;
            $pref = UserCategoryPreference::where('user_id', $userId)
                        ->where('keyword', 'like', "%{$word}%")
                        ->with('category')
                        ->first();
            if ($pref && $pref->category->type === $type) {
                return ['category_id' => $pref->category_id, 'category_name' => $pref->category->name, 'source' => 'preference'];
            }
        }

        // 2. Check rules (exact match first, then partial match)
        foreach ($words as $word) {
            if (strlen($word) < 3) continue;
            
            $rule = CategoryRule::where('keyword', $word)
                    ->whereHas('category', fn($q) => $q->where('type', $type))
                    ->with('category')
                    ->orderByDesc('priority')
                    ->first();

            if (!$rule) {
                $rule = CategoryRule::where('keyword', 'like', "%{$word}%")
                        ->whereHas('category', fn($q) => $q->where('type', $type))
                        ->with('category')
                        ->orderByDesc('priority')
                        ->first();
            }

            if ($rule && $rule->category->type === $type) {
                return ['category_id' => $rule->category_id, 'category_name' => $rule->category->name, 'source' => 'rule'];
            }
        }

        return ['category_id' => null, 'category_name' => null, 'source' => 'none'];
    }

    protected function getSuggestions(string $type): array
    {
        return Category::where(function($q) {
                $q->whereNull('user_id');
            })
            ->where('type', $type)
            ->limit(3)
            ->get(['id', 'name'])
            ->toArray();
    }
}
