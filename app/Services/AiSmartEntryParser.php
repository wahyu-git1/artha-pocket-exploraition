<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AiSmartEntryParser
{
    /**
     * @return array{items: array, needs_review: bool}
     * @throws \Exception
     */
    public function parse(string $text, string $userId): array
    {
        $categories = Category::where('user_id', $userId)
            ->orWhereNull('user_id')
            ->get(['id', 'name', 'type']);
            
        $categoryContext = $categories->map(function ($cat) {
            return "- [{$cat->type}] {$cat->name} (ID: {$cat->id})";
        })->implode("\n");

        $today = Carbon::now()->format('Y-m-d');
        
        $prompt = <<<TEXT
You are an expert financial categorizer API.
Extract financial transactions from the user's natural language input.
The input might contain multiple items (e.g. "hari ini beli bakso 10k, kos 500k").

Current Date Context: {$today}
"hari ini" / "tadi" = {$today}
"kemarin" / "kemaren" = {$today} minus 1 day

Available Categories:
{$categoryContext}

Instructions:
1. Extract each distinct expense or income.
2. If it's something they paid/bought, type is "expense". If they received/got, type is "income".
3. Map the amount cleanly to an integer (e.g. "10k" = 10000, "1.5jt" = 1500000).
4. Match it to the CLOSEST available category ID. If none fits well, return null for category_id.
5. Return ONLY a pure JSON array of objects. Do not include markdown code block syntax (like ```json), just the array itself.

Schema per object:
{
    "item": "Cleaned up item name, e.g. Bakso",
    "amount": 10000,
    "type": "expense", // atau "income"
    "date": "YYYY-MM-DD",
    "category_id": "uuid-here or null",
    "category_name": "Name of the matched category or null",
    "confidence_score": 0.95
}

User Input: "{$text}"
TEXT;

        $apiKey = config('services.gemini.api_key');
        if (empty($apiKey)) {
            // Fallback to local parser if API key is not set
            return (new SmartEntryParser())->parse($text, $userId);
        }

        $response = Http::timeout(30)->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-3.5-flash-lite:generateContent?key={$apiKey}", [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.1,
                'responseMimeType' => 'application/json',
            ]
        ]);

        if (!$response->successful()) {
            throw new \Exception('Failed to communicate with AI model: ' . $response->status() . ' - ' . $response->body());
        }

        $responseData = $response->json();
        
        $jsonText = $responseData['candidates'][0]['content']['parts'][0]['text'] ?? '[]';
        $jsonText = trim($jsonText);
        
        // Safety clean up in case AI still returns markdown block
        if (str_starts_with($jsonText, '```json')) {
            $jsonText = str_replace('```json', '', $jsonText);
            $jsonText = str_replace('```', '', $jsonText);
            $jsonText = trim($jsonText);
        }

        $items = json_decode($jsonText, true);

        if (!is_array($items) || empty($items)) {
            throw new \Exception('PARSE_NO_AMOUNT');
        }

        $needsReviewGlobal = false;
        $results = [];

        foreach ($items as $item) {
            if (!isset($item['amount']) || $item['amount'] <= 0) continue;
            
            $spentAt = $item['spent_at'] ?? $item['date'] ?? Carbon::now()->toDateString();
            $item['spent_at'] = $spentAt;
            $item['date'] = $spentAt;

            if (isset($item['confidence_score']) && $item['confidence_score'] < 0.7) {
                $needsReviewGlobal = true;
                $item['needs_review'] = true;
            } else {
                $item['needs_review'] = false;
            }

            if (!isset($item['suggestions'])) {
                $item['suggestions'] = [];
            }
            
            $results[] = $item;
        }

        if (empty($results)) {
            throw new \Exception('PARSE_NO_AMOUNT');
        }

        return [
            'items' => $results,
            'needs_review' => $needsReviewGlobal
        ];
    }
}
