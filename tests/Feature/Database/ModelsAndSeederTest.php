<?php

use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Carbon\CarbonInterface;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

test('database schema has all required tables and columns', function (): void {
    expect(Schema::hasColumns('users', [
        'id',
        'name',
        'email',
        'password',
        'current_streak',
        'last_active_date',
    ]))->toBeTrue();

    expect(Schema::hasColumns('categories', [
        'id',
        'name',
        'type',
        'icon',
        'color',
        'created_at',
        'updated_at',
    ]))->toBeTrue();

    expect(Schema::hasColumns('transactions', [
        'id',
        'category_id',
        'type',
        'amount',
        'note',
        'transaction_date',
        'created_at',
        'updated_at',
    ]))->toBeTrue();

    expect(Schema::hasColumns('budgets', [
        'id',
        'period_type',
        'amount',
        'is_dynamic',
        'start_date',
        'end_date',
        'created_at',
        'updated_at',
    ]))->toBeTrue();
});

test('category and transaction relationships and casts work properly', function (): void {
    $category = Category::factory()->expense()->create([
        'name' => 'Makan & Minum',
    ]);

    $transaction = Transaction::factory()->create([
        'category_id' => $category->id,
        'type' => 'expense',
        'amount' => 45500.5,
        'note' => 'Makan siang',
        'transaction_date' => '2026-11-22 12:30:00',
    ]);

    expect($transaction->category->is($category))->toBeTrue()
        ->and($category->transactions)->toHaveCount(1)
        ->and($category->transactions->first()->is($transaction))->toBeTrue()
        ->and($transaction->amount)->toBe('45500.50')
        ->and($transaction->transaction_date)->toBeInstanceOf(CarbonInterface::class)
        ->and($transaction->transaction_date->format('Y-m-d H:i:s'))->toBe('2026-11-22 12:30:00');
});

test('budget casts amount, is_dynamic, start_date, and end_date properly', function (): void {
    $budget = Budget::factory()->create([
        'period_type' => 'monthly',
        'amount' => 3000000,
        'is_dynamic' => 1,
        'start_date' => '2026-11-01',
        'end_date' => '2026-11-30',
    ]);

    expect($budget->amount)->toBe('3000000.00')
        ->and($budget->is_dynamic)->toBeTrue()
        ->and($budget->start_date)->toBeInstanceOf(CarbonInterface::class)
        ->and($budget->end_date)->toBeInstanceOf(CarbonInterface::class)
        ->and($budget->start_date->toDateString())->toBe('2026-11-01')
        ->and($budget->end_date->toDateString())->toBe('2026-11-30');
});

test('user casts current_streak and last_active_date properly', function (): void {
    $user = User::factory()->create([
        'current_streak' => '5',
        'last_active_date' => '2026-11-22',
    ]);

    expect($user->current_streak)->toBe(5)
        ->and($user->last_active_date)->toBeInstanceOf(CarbonInterface::class)
        ->and($user->last_active_date->toDateString())->toBe('2026-11-22');
});

test('database seeder creates default user and initial categories idempotently', function (): void {
    $this->seed(DatabaseSeeder::class);
    $this->seed(DatabaseSeeder::class);

    expect(User::count())->toBe(1);

    $user = User::where('email', 'admin@financeku.test')->first();

    expect($user)->not->toBeNull()
        ->and(Hash::check('password', $user->password))->toBeTrue()
        ->and($user->current_streak)->toBe(0)
        ->and($user->last_active_date)->toBeNull();

    $expenseCategories = Category::where('type', 'expense')->pluck('name')->all();
    $incomeCategories = Category::where('type', 'income')->pluck('name')->all();

    expect(Category::count())->toBe(9)
        ->and($expenseCategories)->toEqualCanonicalizing([
            'Makan & Minum',
            'Transportasi',
            'Belanja',
            'Tagihan',
            'Lainnya',
        ])
        ->and($incomeCategories)->toEqualCanonicalizing([
            'Gaji',
            'Bonus',
            'Freelance',
            'Lainnya',
        ]);
});
