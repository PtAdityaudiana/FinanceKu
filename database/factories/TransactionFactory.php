<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(['income', 'expense']);

        return [
            'category_id' => Category::factory()->state(['type' => $type]),
            'type' => $type,
            'amount' => fake()->randomFloat(2, 5000, 500000),
            'note' => fake()->optional()->sentence(3),
            'transaction_date' => now(),
        ];
    }
}
