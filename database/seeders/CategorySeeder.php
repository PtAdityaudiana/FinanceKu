<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            // Expense categories
            [
                'name' => 'Makan & Minum',
                'type' => 'expense',
                'icon' => 'utensils',
                'color' => 'bg-orange-100 text-orange-600',
            ],
            [
                'name' => 'Transportasi',
                'type' => 'expense',
                'icon' => 'car',
                'color' => 'bg-sky-100 text-sky-600',
            ],
            [
                'name' => 'Belanja',
                'type' => 'expense',
                'icon' => 'shopping-bag',
                'color' => 'bg-pink-100 text-pink-600',
            ],
            [
                'name' => 'Tagihan',
                'type' => 'expense',
                'icon' => 'receipt',
                'color' => 'bg-rose-100 text-rose-600',
            ],
            [
                'name' => 'Lainnya',
                'type' => 'expense',
                'icon' => 'ellipsis-horizontal',
                'color' => 'bg-slate-100 text-slate-600',
            ],

            // Income categories
            [
                'name' => 'Gaji',
                'type' => 'income',
                'icon' => 'banknotes',
                'color' => 'bg-emerald-100 text-emerald-600',
            ],
            [
                'name' => 'Bonus',
                'type' => 'income',
                'icon' => 'gift',
                'color' => 'bg-amber-100 text-amber-600',
            ],
            [
                'name' => 'Freelance',
                'type' => 'income',
                'icon' => 'briefcase',
                'color' => 'bg-indigo-100 text-indigo-600',
            ],
            [
                'name' => 'Lainnya',
                'type' => 'income',
                'icon' => 'plus-circle',
                'color' => 'bg-teal-100 text-teal-600',
            ],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                [
                    'name' => $category['name'],
                    'type' => $category['type'],
                ],
                [
                    'icon' => $category['icon'],
                    'color' => $category['color'],
                ],
            );
        }
    }
}
