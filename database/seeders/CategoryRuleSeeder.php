<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\CategoryRule;
use Illuminate\Database\Seeder;

class CategoryRuleSeeder extends Seeder
{
    public function run(): void
    {
        $rules = [
            'Makanan & Minuman' => [
                'bakso', 'nasi', 'ayam', 'mie', 'mi', 'kopi', 'es teh', 'sate', 'soto',
                'burger', 'pizza', 'gofood', 'grabfood', 'shopeefood', 'cafe', 'resto',
                'warung', 'makan', 'sarapan', 'cemilan', 'roti', 'snack', 'minum'
            ],
            'Transportasi' => [
                'ojek', 'bensin', 'parkir', 'gojek', 'goride', 'gocar', 'grab',
                'pertalite', 'pertamax', 'spbu', 'tol', 'mrt', 'krl', 'busway',
                'kereta', 'tiket pesawat', 'travel', 'angkot'
            ],
            'Tagihan & Utilitas' => [
                'listrik', 'pln', 'pulsa', 'kuota', 'paket data', 'internet',
                'indihome', 'wifi', 'air', 'pdam', 'bpjs', 'netflix', 'spotify', 'youtube'
            ],
            'Belanja' => [
                'baju', 'celana', 'sepatu', 'shopee', 'tokopedia', 'lazada',
                'tiktok shop', 'supermarket', 'indomaret', 'alfamart', 'belanja'
            ],
            'Kesehatan' => [
                'obat', 'apotek', 'dokter', 'rumah sakit', 'klinik', 'vitamin', 'periksa'
            ],
            'Hiburan' => [
                'bioskop', 'game', 'steam', 'playstation', 'nonton', 'karaoke', 'liburan'
            ],
            'Pendidikan' => [
                'buku', 'kursus', 'ukt', 'spp', 'seminar', 'pelatihan'
            ],
            'Kecantikan & Perawatan' => [
                'skincare', 'salon', 'barbershop', 'potong rambut', 'makeup'
            ],
            'Olahraga' => [
                'gym', 'badminton', 'futsal', 'renang', 'sewa lapangan'
            ],
            'Rumah Tangga' => [
                'sabun', 'deterjen', 'gas', 'elpiji', 'sapu', 'galon'
            ],
        ];

        foreach ($rules as $categoryName => $keywords) {
            $category = Category::where('name', $categoryName)
                ->where('type', 'expense')
                ->whereNull('user_id')
                ->first();

            if (!$category) {
                continue;
            }

            foreach ($keywords as $index => $keyword) {
                CategoryRule::firstOrCreate(
                    [
                        'keyword' => strtolower($keyword),
                        'category_id' => $category->id,
                    ],
                    [
                        'priority' => 10,
                    ]
                );
            }
        }
    }
}
