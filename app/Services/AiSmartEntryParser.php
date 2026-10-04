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
You are an expert financial and business accounting categorizer API (Virtual CFO & Bookkeeper) for Solopreneurs, Freelancers, and Personal Finance in Indonesia.
Extract financial transactions from the user's natural language input.
The input might contain multiple items (e.g. "kulakan beras 500rb, bayar ongkir jne 50rb, sama omzet kasir laku 1.2jt").

Current Date Context: {$today}
"hari ini" / "tadi" = {$today}
"kemarin" / "kemaren" = {$today} minus 1 day

Available Categories:
{$categoryContext}

Classification & Mapping Rules:
1. EXPENSE (Uang Keluar / Debit Beban):
   - Belanja bahan dagang/stok/kulakan/grosir/bahan baku -> Match to "Kulakan & Bahan Baku (HPP)".
   - Ongkir pengiriman kurir, packing, kardus, lakban, sewa lapak/ruko, listrik toko, pulsa jualan -> Match to "Operasional Usaha (OpEx)".
   - Gaji staf, upah tukang, barista, asisten toko -> Match to "Gaji Karyawan & Upah".
   - Iklan IG/FB/TikTok Ads, endorse, cetak spanduk/banner/stiker -> Match to "Marketing & Promosi".
   - Bayar angsuran bank/KUR, cicilan modal, bayar tempo/utang supplier -> Match to "Cicilan & Utang Usaha".
   - Owner mengambil kas usaha untuk belanja dapur/pribadi/gaji sendiri -> Match to "Prive / Gaji Owner".
   - Pengeluaran pribadi umum (makan siang, ngopi, bensin pribadi, bioskop, belanja baju) -> Match to relevant personal category ("Makanan & Minuman", "Transportasi", "Belanja", etc.).

2. INCOME (Uang Masuk / Kredit Pendapatan):
   - Penjualan dagangan, orderan laku, kasir QRIS, omzet toko -> Match to "Penjualan Produk (Omzet)".
   - Fee proyek freelance, jasa desain, jasa katering, invoice jasa, honor -> Match to "Jasa & Proyek Klien".
   - Pelanggan melunasi utang/bon tempo masa lalu -> Match to "Pelunasan Piutang".
   - Pinjaman modal usaha (KUR/Bank), investor, suntikan dana -> Match to "Suntikan Modal / Pinjaman".
   - Gaji bulanan kantor atau transfer hadiah -> Match to "Gaji" or "Hadiah".

General Instructions:
1. Extract each distinct expense or income item.
2. If paid/bought/cost, type is "expense". If received/earned/sold/inflow, type is "income".
3. Cleanly parse amount to an integer (e.g. "10k" = 10000, "150rb" = 150000, "1.5jt" = 1500000).
4. Match to the CLOSEST available category ID from the list. If none fits well, return null for category_id.
5. Return ONLY a pure JSON array of objects. Do NOT wrap in markdown code blocks or extra text.

Schema per object:
{
    "item": "Cleaned up item name, e.g. Kulakan Beras 5 Karung",
    "amount": 500000,
    "type": "expense",
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
