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
            // --- KATEGORI BISNIS & UMKM (EXPENSE) ---
            'Kulakan & Bahan Baku (HPP)' => [
                'kulakan', 'grosir', 'bahan baku', 'stok', 'supplier', 'beli kain', 'beras katering', 'daging katering', 'belanja modal', 'kulak'
            ],
            'Operasional Usaha (OpEx)' => [
                'ongkir', 'packing', 'kardus', 'bubble wrap', 'lakban', 'sewa lapak', 'sewa ruko', 'listrik toko', 'kuota jualan', 'plastik',
                'jne', 'jnt', 'sicepat', 'anteraja', 'tiki', 'pos', 'lalamove', 'deliveree', 'kirim paket'
            ],
            'Gaji Karyawan & Upah' => [
                'gaji karyawan', 'upah tukang', 'gaji staf', 'gaji barista', 'upah harian', 'bonus karyawan', 'gaji admin'
            ],
            'Marketing & Promosi' => [
                'iklan', 'ads', 'endorse', 'spanduk', 'banner', 'brosur', 'promosi', 'instagram ads', 'tiktok ads'
            ],
            'Cicilan & Utang Usaha' => [
                'kur', 'utang', 'angsuran kur', 'cicilan bank', 'utang supplier', 'tempo supplier', 'bayar utang usaha', 'cicilan modal', 'angsuran'
            ],
            'Prive / Gaji Owner' => [
                'prive', 'gaji owner', 'ambil kas pribadi', 'tarik modal', 'keperluan pribadi toko'
            ],

            // --- KATEGORI PRIBADI (EXPENSE) ---
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

            // --- KATEGORI BISNIS & PRIBADI (INCOME) ---
            'Penjualan Produk (Omzet)' => [
                'omzet', 'penjualan', 'laku', 'qris', 'kasir', 'pesanan', 'orderan', 'laris', 'dagangan'
            ],
            'Jasa & Proyek Klien' => [
                'fee proyek', 'dp proyek', 'jasa desain', 'invoice', 'jasa katering', 'honor', 'klien bayar'
            ],
            'Pelunasan Piutang' => [
                'piutang', 'pelunasan bon', 'bayar tempo', 'utang lunas', 'bon lunas', 'bayar utang kemarin'
            ],
            'Suntikan Modal / Pinjaman' => [
                'pinjaman kur', 'suntikan modal', 'investasi modal', 'modal usaha', 'cair kur', 'investor'
            ],
            'Gaji' => [
                'gajian', 'gaji bulanan', 'salary', 'payroll', 'upah bulanan'
            ],
        ];

        foreach ($rules as $categoryName => $keywords) {
            $category = Category::where('name', $categoryName)
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
