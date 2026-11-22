<?php

namespace App\Http\Controllers;

use App\Http\Requests\Transaction\StoreTransactionRequest;
use App\Http\Requests\Transaction\UpdateTransactionRequest;
use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class TransactionController extends Controller
{
    /**
     * Indonesian short month names mapped by month number.
     *
     * @var array<int, string>
     */
    private const array INDONESIAN_MONTHS = [
        1 => 'Jan',
        2 => 'Feb',
        3 => 'Mar',
        4 => 'Apr',
        5 => 'Mei',
        6 => 'Jun',
        7 => 'Jul',
        8 => 'Agu',
        9 => 'Sep',
        10 => 'Okt',
        11 => 'Nov',
        12 => 'Des',
    ];

    /**
     * Display the transaction history grouped by date with filters.
     */
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'type' => ['nullable', 'in:all,income,expense'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
        ]);

        $filters = [
            'type' => $validated['type'] ?? 'all',
            'category_id' => isset($validated['category_id']) ? (int) $validated['category_id'] : null,
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
        ];

        $query = Transaction::query()
            ->with('category')
            ->when(
                in_array($filters['type'], ['income', 'expense'], true),
                fn ($q) => $q->where('type', $filters['type']),
            )
            ->when(
                $filters['category_id'] !== null,
                fn ($q) => $q->where('category_id', $filters['category_id']),
            )
            ->when(
                $filters['start_date'] !== null,
                fn ($q) => $q->where('transaction_date', '>=', Carbon::parse($filters['start_date'])->startOfDay()),
            )
            ->when(
                $filters['end_date'] !== null,
                fn ($q) => $q->where('transaction_date', '<=', Carbon::parse($filters['end_date'])->endOfDay()),
            )
            ->orderByDesc('transaction_date')
            ->orderByDesc('id');

        $transactions = $query->get();
        $groupedTransactions = $this->groupTransactionsByDate($transactions);

        $categories = Category::query()
            ->orderBy('type')
            ->orderBy('id')
            ->get(['id', 'name', 'type', 'icon', 'color']);

        $hasActiveFilters = $filters['type'] !== 'all'
            || $filters['category_id'] !== null
            || $filters['start_date'] !== null
            || $filters['end_date'] !== null;

        return view('transactions.index', [
            'groupedTransactions' => $groupedTransactions,
            'categories' => $categories,
            'filters' => $filters,
            'hasActiveFilters' => $hasActiveFilters,
            'totalCount' => $transactions->count(),
        ]);
    }

    /**
     * Store a newly created transaction in storage.
     */
    public function store(StoreTransactionRequest $request): RedirectResponse
    {
        Transaction::create($request->validated());

        return back()->with('success', 'Transaksi berhasil dicatat.');
    }

    /**
     * Show the form for editing the specified transaction.
     */
    public function edit(Transaction $transaction): View
    {
        $categories = Category::query()
            ->orderBy('id')
            ->get(['id', 'name', 'type', 'icon', 'color']);

        return view('transactions.edit', [
            'transaction' => $transaction->load('category'),
            'categories' => $categories,
        ]);
    }

    /**
     * Update the specified transaction in storage.
     */
    public function update(UpdateTransactionRequest $request, Transaction $transaction): RedirectResponse
    {
        $transaction->update($request->validated());

        if ($request->input('redirect_to') === 'index') {
            return redirect()
                ->route('transactions.index')
                ->with('success', 'Transaksi berhasil diperbarui.');
        }

        return back()->with('success', 'Transaksi berhasil diperbarui.');
    }

    /**
     * Remove the specified transaction from storage.
     */
    public function destroy(Transaction $transaction): RedirectResponse
    {
        $transaction->delete();

        return back()->with('success', 'Transaksi berhasil dihapus.');
    }

    /**
     * Group transactions by date and compute daily income/expense subtotals.
     *
     * @param  Collection<int, Transaction>  $transactions
     * @return Collection<int, array{
     *     date: string,
     *     label: string,
     *     total_income: float,
     *     total_expense: float,
     *     transactions: Collection<int, Transaction>
     * }>
     */
    private function groupTransactionsByDate(Collection $transactions): Collection
    {
        return $transactions
            ->groupBy(fn (Transaction $transaction): string => $transaction->transaction_date->toDateString())
            ->map(function (Collection $items, string $date): array {
                $carbonDate = $items->first()->transaction_date;

                return [
                    'date' => $date,
                    'label' => $this->formatDateGroupLabel($carbonDate),
                    'total_income' => (float) $items->where('type', 'income')->sum('amount'),
                    'total_expense' => (float) $items->where('type', 'expense')->sum('amount'),
                    'transactions' => $items->values(),
                ];
            })
            ->values();
    }

    /**
     * Format a date into a human-friendly Indonesian group label.
     */
    private function formatDateGroupLabel(CarbonInterface $date): string
    {
        if ($date->isToday()) {
            return 'Hari Ini';
        }

        if ($date->isYesterday()) {
            return 'Kemarin';
        }

        $month = self::INDONESIAN_MONTHS[$date->month] ?? $date->format('M');

        return sprintf('%d %s %d', $date->day, $month, $date->year);
    }
}
