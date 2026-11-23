<?php

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests are redirected from dashboard routes to login', function (): void {
    $this->get('/')->assertRedirect(route('login'));
    $this->get('/dashboard')->assertRedirect(route('login'));
});

test('dashboard route redirects authenticated user to home dashboard', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertRedirect(route('home'));
});

test('dashboard displays net balance, today summary, recent five transactions, and quick add fab', function (): void {
    Carbon::setTestNow(Carbon::parse('2026-11-22 14:00:00'));

    try {
        $user = User::factory()->create(['name' => 'Budi']);
        $incomeCategory = Category::factory()->income()->create(['name' => 'Gaji']);
        $expenseCategory = Category::factory()->expense()->create(['name' => 'Makan & Minum']);

        Transaction::factory()->create([
            'category_id' => $incomeCategory->id,
            'type' => 'income',
            'amount' => 1000000,
            'note' => 'Transaksi paling lama',
            'transaction_date' => '2026-11-20 09:00:00',
        ]);

        foreach (range(1, 5) as $index) {
            Transaction::factory()->create([
                'category_id' => $expenseCategory->id,
                'type' => 'expense',
                'amount' => 20000,
                'note' => "Transaksi terbaru #{$index}",
                'transaction_date' => Carbon::parse('2026-11-22 10:00:00')->addMinutes($index),
            ]);
        }

        // Net balance: 1.000.000 - (5 * 20.000) = 900.000
        $this->actingAs($user)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Halo, Budi')
            ->assertSee('Rp 900.000')
            ->assertSee('-Rp 100.000')
            ->assertSee('Lihat Semua')
            ->assertSee(route('transactions.index'))
            ->assertSee('open-quick-add')
            ->assertSee('Transaksi terbaru #5')
            ->assertSee('Transaksi terbaru #1')
            ->assertDontSee('Transaksi paling lama');
    } finally {
        Carbon::setTestNow();
    }
});

test('dashboard formats negative net balance cleanly', function (): void {
    $user = User::factory()->create();
    $expenseCategory = Category::factory()->expense()->create();

    Transaction::factory()->create([
        'category_id' => $expenseCategory->id,
        'type' => 'expense',
        'amount' => 50000,
        'transaction_date' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('home'))
        ->assertOk()
        ->assertSee('-Rp 50.000');
});

test('dashboard shows saving, overspending, and equal daily expense comparison status', function (): void {
    Carbon::setTestNow(Carbon::parse('2026-11-22 14:00:00'));

    try {
        $user = User::factory()->create();
        $expenseCategory = Category::factory()->expense()->create();
        $incomeCategory = Category::factory()->income()->create();

        // Yesterday expense: 50.000, Yesterday income: 200.000
        Transaction::factory()->create([
            'category_id' => $expenseCategory->id,
            'type' => 'expense',
            'amount' => 50000,
            'transaction_date' => '2026-11-21 12:00:00',
        ]);
        Transaction::factory()->create([
            'category_id' => $incomeCategory->id,
            'type' => 'income',
            'amount' => 200000,
            'transaction_date' => '2026-11-21 09:00:00',
        ]);

        // Case 1: Saving (today 35.000 < yesterday 50.000 => Lebih hemat Rp15.000 dari kemarin)
        $todayTx = Transaction::factory()->create([
            'category_id' => $expenseCategory->id,
            'type' => 'expense',
            'amount' => 35000,
            'transaction_date' => '2026-11-22 10:00:00',
        ]);

        $this->actingAs($user)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Lebih hemat Rp15.000 dari kemarin');

        // Case 2: Equal (today 50.000 == yesterday 50.000)
        $todayTx->update(['amount' => 50000]);

        $this->actingAs($user)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Sama dengan kemarin');

        // Case 3: Overspending (today 60.000 > yesterday 50.000 => Lebih boros Rp10.000 dari kemarin)
        $todayTx->update(['amount' => 60000]);

        $this->actingAs($user)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Lebih boros Rp10.000 dari kemarin');
    } finally {
        Carbon::setTestNow();
    }
});

test('storing transactions updates user daily streak accurately', function (): void {
    Carbon::setTestNow(Carbon::parse('2026-11-22 10:00:00'));

    try {
        $expenseCategory = Category::factory()->expense()->create();

        // 1. First ever transaction -> streak = 1
        $user = User::factory()->create([
            'current_streak' => 0,
            'last_active_date' => null,
        ]);

        $this->actingAs($user)->post(route('transactions.store'), [
            'type' => 'expense',
            'category_id' => $expenseCategory->id,
            'amount' => '15000',
            'transaction_date' => '2026-11-22T10:00',
        ])->assertRedirect();

        $user->refresh();
        expect($user->current_streak)->toBe(1)
            ->and($user->last_active_date->toDateString())->toBe('2026-11-22');

        // 2. Another transaction on the same day -> streak remains 1
        $this->actingAs($user)->post(route('transactions.store'), [
            'type' => 'expense',
            'category_id' => $expenseCategory->id,
            'amount' => '10000',
            'transaction_date' => '2026-11-22T11:00',
        ])->assertRedirect();

        $user->refresh();
        expect($user->current_streak)->toBe(1);

        // 3. Last active was yesterday -> increments streak
        $user->update([
            'current_streak' => 3,
            'last_active_date' => '2026-11-21',
        ]);

        $this->actingAs($user)->post(route('transactions.store'), [
            'type' => 'expense',
            'category_id' => $expenseCategory->id,
            'amount' => '20000',
            'transaction_date' => '2026-11-22T12:00',
        ])->assertRedirect();

        $user->refresh();
        expect($user->current_streak)->toBe(4)
            ->and($user->last_active_date->toDateString())->toBe('2026-11-22');

        // 4. Last active was 2+ days ago -> resets streak to 1
        $user->update([
            'current_streak' => 7,
            'last_active_date' => '2026-11-19',
        ]);

        $this->actingAs($user)->post(route('transactions.store'), [
            'type' => 'expense',
            'category_id' => $expenseCategory->id,
            'amount' => '25000',
            'transaction_date' => '2026-11-22T13:00',
        ])->assertRedirect();

        $user->refresh();
        expect($user->current_streak)->toBe(1)
            ->and($user->last_active_date->toDateString())->toBe('2026-11-22');
    } finally {
        Carbon::setTestNow();
    }
});

test('dashboard displays streak as zero if inactive for two or more days without mutating database', function (): void {
    Carbon::setTestNow(Carbon::parse('2026-11-22 10:00:00'));

    try {
        // Inactive since 2 days ago (20 Nov) -> displays 0 Hari, preserves DB current_streak = 5
        $staleUser = User::factory()->create([
            'current_streak' => 5,
            'last_active_date' => '2026-11-20',
        ]);

        $this->actingAs($staleUser)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('0 Hari');

        $staleUser->refresh();
        expect($staleUser->current_streak)->toBe(5)
            ->and($staleUser->last_active_date->toDateString())->toBe('2026-11-20');

        // Active yesterday (21 Nov) -> still displays 3 Hari today before logging
        $activeYesterdayUser = User::factory()->create([
            'current_streak' => 3,
            'last_active_date' => '2026-11-21',
        ]);

        $this->actingAs($activeYesterdayUser)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('3 Hari');
    } finally {
        Carbon::setTestNow();
    }
});
