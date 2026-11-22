<?php

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests cannot access transaction endpoints', function (): void {
    $transaction = Transaction::factory()->create();

    $this->get(route('transactions.index'))->assertRedirect(route('login'));
    $this->post(route('transactions.store'), [])->assertRedirect(route('login'));
    $this->get(route('transactions.edit', $transaction))->assertRedirect(route('login'));
    $this->put(route('transactions.update', $transaction), [])->assertRedirect(route('login'));
    $this->delete(route('transactions.destroy', $transaction))->assertRedirect(route('login'));
});

test('authenticated layout includes bottom navigation, quick add fab, and category options', function (): void {
    $user = User::factory()->create();
    $expenseCategory = Category::factory()->expense()->create(['name' => 'Makan & Minum']);
    $incomeCategory = Category::factory()->income()->create(['name' => 'Gaji']);

    $this->actingAs($user)
        ->get(route('home'))
        ->assertOk()
        ->assertSee('max-w-md')
        ->assertSee('open-quick-add')
        ->assertSee('inputmode="numeric"', false)
        ->assertSee($expenseCategory->name)
        ->assertSee($incomeCategory->name)
        ->assertSee(route('transactions.index'));
});

test('authenticated user can store expense and income transactions', function (): void {
    $user = User::factory()->create();
    $expenseCategory = Category::factory()->expense()->create();
    $incomeCategory = Category::factory()->income()->create();

    $this->actingAs($user)
        ->from(route('home'))
        ->post(route('transactions.store'), [
            'type' => 'expense',
            'category_id' => $expenseCategory->id,
            'amount' => '25000',
            'note' => 'Kopi pagi',
            'transaction_date' => '2026-11-22T08:30',
        ])
        ->assertRedirect(route('home'))
        ->assertSessionHas('success', 'Transaksi berhasil dicatat.');

    $this->actingAs($user)
        ->from(route('transactions.index'))
        ->post(route('transactions.store'), [
            'type' => 'income',
            'category_id' => $incomeCategory->id,
            'amount' => '5000000',
            'note' => null,
            'transaction_date' => '2026-11-22T09:00',
        ])
        ->assertRedirect(route('transactions.index'))
        ->assertSessionHas('success', 'Transaksi berhasil dicatat.');

    $this->assertDatabaseHas('transactions', [
        'category_id' => $expenseCategory->id,
        'type' => 'expense',
        'amount' => 25000.00,
        'note' => 'Kopi pagi',
    ]);

    $this->assertDatabaseHas('transactions', [
        'category_id' => $incomeCategory->id,
        'type' => 'income',
        'amount' => 5000000.00,
        'note' => null,
    ]);
});

test('storing transaction validates amount and matching category type', function (): void {
    $user = User::factory()->create();
    $incomeCategory = Category::factory()->income()->create();

    $this->actingAs($user)
        ->from(route('transactions.index'))
        ->post(route('transactions.store'), [
            'type' => 'expense',
            'category_id' => $incomeCategory->id,
            'amount' => '0',
            'transaction_date' => '',
        ])
        ->assertRedirect(route('transactions.index'))
        ->assertSessionHasErrors(['category_id', 'amount', 'transaction_date']);

    expect(Transaction::count())->toBe(0);
});

test('transaction history groups transactions by date with daily subtotals', function (): void {
    Carbon::setTestNow(Carbon::parse('2026-11-22 15:00:00'));

    try {
        $user = User::factory()->create();
        $expenseCategory = Category::factory()->expense()->create(['name' => 'Makan & Minum']);
        $incomeCategory = Category::factory()->income()->create(['name' => 'Gaji']);

        Transaction::factory()->create([
            'category_id' => $expenseCategory->id,
            'type' => 'expense',
            'amount' => 30000,
            'note' => 'Sarapan',
            'transaction_date' => '2026-11-22 07:30:00',
        ]);
        Transaction::factory()->create([
            'category_id' => $incomeCategory->id,
            'type' => 'income',
            'amount' => 150000,
            'note' => 'Refund',
            'transaction_date' => '2026-11-22 12:00:00',
        ]);
        Transaction::factory()->create([
            'category_id' => $expenseCategory->id,
            'type' => 'expense',
            'amount' => 45000,
            'note' => 'Makan malam kemarin',
            'transaction_date' => '2026-11-21 19:00:00',
        ]);
        Transaction::factory()->create([
            'category_id' => $expenseCategory->id,
            'type' => 'expense',
            'amount' => 120000,
            'note' => 'Belanja dua hari lalu',
            'transaction_date' => '2026-11-20 14:00:00',
        ]);

        $response = $this->actingAs($user)->get(route('transactions.index'));

        $response->assertOk()
            ->assertSeeInOrder(['Hari Ini', 'Kemarin', '20 Nov 2026'])
            ->assertSee('+Rp 150.000')
            ->assertSee('-Rp 30.000')
            ->assertSee('-Rp 45.000')
            ->assertSee('-Rp 120.000');
    } finally {
        Carbon::setTestNow();
    }
});

test('transaction history supports type, category, and date range filters', function (): void {
    $user = User::factory()->create();
    $food = Category::factory()->expense()->create(['name' => 'Makan & Minum']);
    $transport = Category::factory()->expense()->create(['name' => 'Transportasi']);
    $salary = Category::factory()->income()->create(['name' => 'Gaji']);

    Transaction::factory()->create([
        'category_id' => $food->id,
        'type' => 'expense',
        'amount' => 25000,
        'note' => 'Bakso',
        'transaction_date' => '2026-11-20 12:00:00',
    ]);
    Transaction::factory()->create([
        'category_id' => $transport->id,
        'type' => 'expense',
        'amount' => 15000,
        'note' => 'Ojek',
        'transaction_date' => '2026-11-21 08:00:00',
    ]);
    Transaction::factory()->create([
        'category_id' => $salary->id,
        'type' => 'income',
        'amount' => 4000000,
        'note' => 'Gaji Bulanan',
        'transaction_date' => '2026-11-22 09:00:00',
    ]);

    $this->actingAs($user)
        ->get(route('transactions.index', ['type' => 'income']))
        ->assertOk()
        ->assertSee('Gaji Bulanan')
        ->assertDontSee('Bakso')
        ->assertDontSee('Ojek');

    $this->actingAs($user)
        ->get(route('transactions.index', ['category_id' => $food->id]))
        ->assertOk()
        ->assertSee('Bakso')
        ->assertDontSee('Ojek')
        ->assertDontSee('Gaji Bulanan');

    $this->actingAs($user)
        ->get(route('transactions.index', [
            'start_date' => '2026-11-21',
            'end_date' => '2026-11-21',
        ]))
        ->assertOk()
        ->assertSee('Ojek')
        ->assertDontSee('Bakso')
        ->assertDontSee('Gaji Bulanan');
});

test('authenticated user can view edit page and update a transaction', function (): void {
    $user = User::factory()->create();
    $oldCategory = Category::factory()->expense()->create(['name' => 'Makan & Minum']);
    $newCategory = Category::factory()->income()->create(['name' => 'Bonus']);

    $transaction = Transaction::factory()->create([
        'category_id' => $oldCategory->id,
        'type' => 'expense',
        'amount' => 50000,
        'note' => 'Catatan awal',
        'transaction_date' => '2026-11-22 10:00:00',
    ]);

    $this->actingAs($user)
        ->get(route('transactions.edit', $transaction))
        ->assertOk()
        ->assertSee('Edit Transaksi')
        ->assertSee('Catatan awal');

    $this->actingAs($user)
        ->put(route('transactions.update', $transaction), [
            'redirect_to' => 'index',
            'type' => 'income',
            'category_id' => $newCategory->id,
            'amount' => '250000',
            'note' => 'Bonus proyek',
            'transaction_date' => '2026-11-22T11:15',
        ])
        ->assertRedirect(route('transactions.index'))
        ->assertSessionHas('success', 'Transaksi berhasil diperbarui.');

    $transaction->refresh();

    expect($transaction->type)->toBe('income')
        ->and($transaction->category_id)->toBe($newCategory->id)
        ->and($transaction->amount)->toBe('250000.00')
        ->and($transaction->note)->toBe('Bonus proyek')
        ->and($transaction->transaction_date->format('Y-m-d H:i'))->toBe('2026-11-22 11:15');
});

test('authenticated user can delete a transaction', function (): void {
    $user = User::factory()->create();
    $transaction = Transaction::factory()->create();

    $this->actingAs($user)
        ->from(route('transactions.index'))
        ->delete(route('transactions.destroy', $transaction))
        ->assertRedirect(route('transactions.index'))
        ->assertSessionHas('success', 'Transaksi berhasil dihapus.');

    $this->assertDatabaseMissing('transactions', [
        'id' => $transaction->id,
    ]);
});
