<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;

class FinanceSummaryService
{
    /**
     * Build the full dashboard summary payload for the authenticated user.
     *
     * @return array{
     *     net_balance: float,
     *     comparison: array{
     *         today_income: float,
     *         today_expense: float,
     *         yesterday_income: float,
     *         yesterday_expense: float,
     *         expense_difference: float,
     *         expense_status: string,
     *         expense_status_text: string
     *     },
     *     recent_transactions: Collection<int, Transaction>,
     *     visible_streak: int
     * }
     */
    public function getDashboardSummary(User $user): array
    {
        return [
            'net_balance' => $this->getNetBalance(),
            'comparison' => $this->getDailyComparison(),
            'recent_transactions' => $this->getRecentTransactions(),
            'visible_streak' => $this->getVisibleStreak($user),
        ];
    }

    /**
     * Calculate total net balance: SUM(income) - SUM(expense) across all transactions.
     */
    public function getNetBalance(): float
    {
        $totals = Transaction::query()
            ->selectRaw("
                COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) as total_income,
                COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) as total_expense
            ")
            ->first();

        return (float) ($totals?->total_income ?? 0) - (float) ($totals?->total_expense ?? 0);
    }

    /**
     * Compare today's income and expense totals against yesterday.
     *
     * @return array{
     *     today_income: float,
     *     today_expense: float,
     *     yesterday_income: float,
     *     yesterday_expense: float,
     *     expense_difference: float,
     *     expense_status: string,
     *     expense_status_text: string
     * }
     */
    public function getDailyComparison(): array
    {
        $today = $this->getTotalsForDate(Carbon::today());
        $yesterday = $this->getTotalsForDate(Carbon::yesterday());

        $difference = $today['expense'] - $yesterday['expense'];
        $formattedAbsDiff = number_format(abs($difference), 0, ',', '.');

        if ($difference < 0) {
            $status = 'saving';
            $statusText = "Lebih hemat Rp{$formattedAbsDiff} dari kemarin";
        } elseif ($difference > 0) {
            $status = 'overspending';
            $statusText = "Lebih boros Rp{$formattedAbsDiff} dari kemarin";
        } else {
            $status = 'same';
            $statusText = 'Sama dengan kemarin';
        }

        return [
            'today_income' => $today['income'],
            'today_expense' => $today['expense'],
            'yesterday_income' => $yesterday['income'],
            'yesterday_expense' => $yesterday['expense'],
            'expense_difference' => $difference,
            'expense_status' => $status,
            'expense_status_text' => $statusText,
        ];
    }

    /**
     * Retrieve the latest transactions with their category relation for quick preview.
     *
     * @return Collection<int, Transaction>
     */
    public function getRecentTransactions(int $limit = 5): Collection
    {
        return Transaction::query()
            ->with('category')
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    /**
     * Determine the streak count to display without mutating stored user history.
     * Displays 0 if the user has not recorded any transaction since 2 or more days ago.
     */
    public function getVisibleStreak(User $user): int
    {
        if ($user->last_active_date === null || $user->current_streak <= 0) {
            return 0;
        }

        $lastActive = $user->last_active_date->copy()->startOfDay();
        $yesterday = Carbon::yesterday()->startOfDay();

        if ($lastActive->lt($yesterday)) {
            return 0;
        }

        return (int) $user->current_streak;
    }

    /**
     * Update the user's daily streak and last active date when a new transaction is stored.
     */
    public function recordDailyActivity(User $user): void
    {
        $today = Carbon::today();
        $lastActive = $user->last_active_date?->copy()->startOfDay();

        if ($lastActive !== null && $lastActive->equalTo($today)) {
            return;
        }

        if ($lastActive !== null && $lastActive->equalTo(Carbon::yesterday())) {
            $user->current_streak = $user->current_streak + 1;
        } else {
            $user->current_streak = 1;
        }

        $user->last_active_date = $today->toDateString();
        $user->save();
    }

    /**
     * Calculate income and expense totals for a specific calendar date.
     *
     * @return array{income: float, expense: float}
     */
    private function getTotalsForDate(CarbonInterface $date): array
    {
        $totals = Transaction::query()
            ->whereBetween('transaction_date', [
                $date->copy()->startOfDay(),
                $date->copy()->endOfDay(),
            ])
            ->selectRaw("
                COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) as total_income,
                COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) as total_expense
            ")
            ->first();

        return [
            'income' => (float) ($totals?->total_income ?? 0),
            'expense' => (float) ($totals?->total_expense ?? 0),
        ];
    }
}
