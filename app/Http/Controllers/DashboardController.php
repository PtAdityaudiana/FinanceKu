<?php

namespace App\Http\Controllers;

use App\Services\FinanceSummaryService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly FinanceSummaryService $summaryService,
    ) {}

    /**
     * Display the mobile-first personal finance summary dashboard.
     */
    public function index(Request $request): View
    {
        $summary = $this->summaryService->getDashboardSummary($request->user());

        return view('dashboard', [
            'user' => $request->user(),
            'netBalance' => $summary['net_balance'],
            'comparison' => $summary['comparison'],
            'recentTransactions' => $summary['recent_transactions'],
            'visibleStreak' => $summary['visible_streak'],
        ]);
    }
}
