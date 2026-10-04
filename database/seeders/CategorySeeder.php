<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $expenseCategories = [
            // Kategori Bisnis & Operasional UMKM
            ['name' => 'Kulakan & Bahan Baku (HPP)', 'icon' => 'package', 'color' => '#EA580C', 'bucket' => 'need'],
            ['name' => 'Operasional Usaha (OpEx)', 'icon' => 'cog', 'color' => '#2563EB', 'bucket' => 'need'],
            ['name' => 'Gaji Karyawan & Upah', 'icon' => 'users', 'color' => '#059669', 'bucket' => 'need'],
            ['name' => 'Marketing & Promosi', 'icon' => 'megaphone', 'color' => '#D97706', 'bucket' => 'want'],
            ['name' => 'Cicilan & Utang Usaha', 'icon' => 'credit-card', 'color' => '#DC2626', 'bucket' => 'need'],
            ['name' => 'Prive / Gaji Owner', 'icon' => 'wallet', 'color' => '#7C3AED', 'bucket' => 'want'],

            // Kategori Pengeluaran Pribadi
            ['name' => 'Makanan & Minuman', 'icon' => 'utensils', 'color' => '#EF4444', 'bucket' => 'need'],
            ['name' => 'Transportasi', 'icon' => 'car', 'color' => '#F97316', 'bucket' => 'need'],
            ['name' => 'Belanja', 'icon' => 'shopping-bag', 'color' => '#EC4899', 'bucket' => 'want'],
            ['name' => 'Tagihan & Utilitas', 'icon' => 'receipt', 'color' => '#3B82F6', 'bucket' => 'need'],
            ['name' => 'Kesehatan', 'icon' => 'heart-pulse', 'color' => '#10B981', 'bucket' => 'need'],
            ['name' => 'Hiburan', 'icon' => 'film', 'color' => '#8B5CF6', 'bucket' => 'want'],
            ['name' => 'Pendidikan', 'icon' => 'graduation-cap', 'color' => '#06B6D4', 'bucket' => 'need'],
            ['name' => 'Kecantikan & Perawatan', 'icon' => 'sparkles', 'color' => '#F43F5E', 'bucket' => 'want'],
            ['name' => 'Olahraga', 'icon' => 'dumbbell', 'color' => '#14B8A6', 'bucket' => 'want'],
            ['name' => 'Rumah Tangga', 'icon' => 'home', 'color' => '#6366F1', 'bucket' => 'need'],
            ['name' => 'Sosial & Hadiah', 'icon' => 'gift', 'color' => '#A855F7', 'bucket' => 'want'],
            ['name' => 'Lain-lain', 'icon' => 'dots-horizontal', 'color' => '#64748B', 'bucket' => 'want'],
        ];

        foreach ($expenseCategories as $cat) {
            Category::updateOrCreate(
                ['name' => $cat['name'], 'type' => 'expense', 'user_id' => null],
                [
                    'icon' => $cat['icon'],
                    'color' => $cat['color'],
                    'bucket' => $cat['bucket'],
                    'is_default' => true,
                ]
            );
        }

        $incomeCategories = [
            // Kategori Pemasukan Bisnis & Usaha
            ['name' => 'Penjualan Produk (Omzet)', 'icon' => 'store', 'color' => '#16A34A'],
            ['name' => 'Jasa & Proyek Klien', 'icon' => 'briefcase', 'color' => '#2563EB'],
            ['name' => 'Pelunasan Piutang', 'icon' => 'hand-coins', 'color' => '#0D9488'],
            ['name' => 'Suntikan Modal / Pinjaman', 'icon' => 'landmark', 'color' => '#9333EA'],

            // Kategori Pemasukan Pribadi & Lainnya
            ['name' => 'Gaji', 'icon' => 'briefcase', 'color' => '#22C55E'],
            ['name' => 'Freelance', 'icon' => 'laptop', 'color' => '#3B82F6'],
            ['name' => 'Bisnis', 'icon' => 'store', 'color' => '#F59E0B'],
            ['name' => 'Investasi', 'icon' => 'trending-up', 'color' => '#8B5CF6'],
            ['name' => 'Hadiah', 'icon' => 'gift', 'color' => '#EC4899'],
            ['name' => 'Pinjaman Kembali', 'icon' => 'hand-coins', 'color' => '#06B6D4'],
            ['name' => 'Lain-lain', 'icon' => 'dots-horizontal', 'color' => '#64748B'],
        ];

        foreach ($incomeCategories as $cat) {
            Category::updateOrCreate(
                ['name' => $cat['name'], 'type' => 'income', 'user_id' => null],
                [
                    'icon' => $cat['icon'],
                    'color' => $cat['color'],
                    'bucket' => null,
                    'is_default' => true,
                ]
            );
        }
    }
}
