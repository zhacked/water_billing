<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Bills;
use App\Models\group;
use App\Models\Payment;
use App\Models\Expenses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        // Monthly financial reports
        $monthlyExpenses = $this->getMonthlySum(Expenses::class, 'amount', 'total_expenses');
        $monthlyIncome = $this->getMonthlySum(Payment::class, 'amount_paid', 'total_income');
        $monthlyIncomeReport = $this->getMonthlyIncomeReport();

        $totalExpenses = $monthlyIncomeReport->sum('total_expenses');
        $totalIncome = $monthlyIncomeReport->sum('total_income');
        $netProfit = $totalIncome - $totalExpenses;

        $monthlyPaidClients = $this->getMonthlyClientsByPaymentStatus(true);
        $monthlyUnPaidClients = $this->getMonthlyClientsByPaymentStatus(false);
        $totalClient = User::clients()->count();
        $totalStaff = User::staffs()->count();

        // Transactions query - only unpaid bills
        $transactionQuery = Bills::with(['user.group', 'meterReading'])
            ->where('is_paid', 0);

        //  Apply search filter if present
        if (!empty($search)) {
            $transactionQuery->whereHas('user', function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhereHas('group', function ($groupQuery) use ($search) {
                        $groupQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Get all (unpaid) transactions
        $allTransactions = $transactionQuery->latest()->get();

        // Group by user group name
        $grouped = $allTransactions->groupBy(function ($bill) {
            return optional($bill->user?->group)->name ?? 'No Group';
        });

        // Map grouped transactions with total due
        $groupedTransactions = $grouped->map(function ($groupBills) {
            return [
                'transactions' => $groupBills->map(function ($bill) {
                    // include user status to use in filter/view
                    $bill->user_status = optional($bill->user)->status ?? null;
                    return $bill;
                }),
                'total_due' => $groupBills->sum('amount_due'),
            ];
        });
        $reconnectionClients = User::clients()
            ->where('status', 'for reconnection')
            ->select('users.*')
            ->addSelect([
                'total_balance' => DB::table('bills')
                    ->selectRaw('COALESCE(SUM(amount_due + penalty), 0)')
                    ->whereColumn('user_id', 'users.id')
            ])
            ->paginate(10);

        return view('pages.dashboard.index', compact(
            'monthlyIncomeReport',
            'monthlyExpenses',
            'monthlyIncome',
            'netProfit',
            'monthlyPaidClients',
            'monthlyUnPaidClients',
            'totalClient',
            'totalStaff',
            'search',
            'groupedTransactions',
            'reconnectionClients'
        ));
    }


    private function getMonthlySum($model, $column, $alias)
    {
        return $model::select(
            DB::raw("MONTH(created_at) as month"),
            DB::raw(value: "SUM($column) as $alias")
        )
            ->groupBy(DB::raw("MONTH(created_at)"))
            ->orderBy('month')
            ->get();
    }

    private function getMonthlyClientsByPaymentStatus(bool $isPaid)
    {
        return Bills::select(
            DB::raw("MONTH(created_at) as month"),
            DB::raw("COUNT(DISTINCT user_id) as total_clients")
        )
            ->where('is_paid', $isPaid)
            ->groupBy(DB::raw("MONTH(created_at)"))
            ->orderBy('month')
            ->get();
    }

    private function getMonthlyIncomeReport()
    {
        $monthlyIncome = $this->getMonthlySum(Payment::class, 'amount_paid', 'total_income');
        $monthlyExpenses = $this->getMonthlySum(Expenses::class, 'amount', 'total_expenses');

        $report = collect();

        // merge by month
        foreach (range(1, 12) as $month) {
            $income = $monthlyIncome->firstWhere('month', $month)?->total_income ?? 0;
            $expense = $monthlyExpenses->firstWhere('month', $month)?->total_expenses ?? 0;

            $report->push([
                'month' => $month,
                'total_income' => $income,
                'total_expenses' => $expense,
                'net_profit' => $income - $expense,
            ]);
        }

        return $report;
    }

    public function record(Request $request)
    {
        $search = $request->input('search');
        $groupId = $request->input('group_id');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        // Filter users who have bills
        $usersQuery = User::Clients()->with(['group', 'bills' => function ($q) use ($fromDate, $toDate) {
            // Filter bills by date range
            if (!empty($fromDate) && !empty($toDate)) {
                $q->whereBetween('billing_date', [
                    Carbon::parse($fromDate)->startOfDay(),
                    Carbon::parse($toDate)->endOfDay(),
                ]);
            } elseif (!empty($fromDate)) {
                $q->whereDate('billing_date', '>=', Carbon::parse($fromDate)->startOfDay());
            } elseif (!empty($toDate)) {
                $q->whereDate('billing_date', '<=', Carbon::parse($toDate)->endOfDay());
            }

            // Get only the latest bill
            $q->latest('billing_date')->limit(1);
        }, 'bills.payments']);

        // Filter by group
        if (!empty($groupId)) {
            $usersQuery->whereHas('group', function ($q) use ($groupId) {
                $q->where('id', $groupId);
            });
        }

        $users = $usersQuery->get();

        // Prepare data per user
        $latestTransactions = $users->map(function ($user) {
            $bill = $user->bills->first(); // only latest bill loaded
            return [
                'account_id' => $user->account_id ?? 'N/A',
                'name' => $user->name ?? 'N/A',
                'consumption' => $bill->consumption ?? 0,
                'previous_reading' => $bill->previous_reading ?? 'N/A',
                'current_reading' => $bill->current_reading ?? 'N/A',
                'penalty' => number_format($bill->penalty ?? 0, 2),
                'amount_due' => number_format($bill->amount_due ?? 0, 2),
                'grand_total_due' => number_format(($bill->amount_due ?? 0) + ($bill->penalty ?? 0), 2),
                'group_name' => optional($user->group)->name ?? 'No Group',
            ];
        })->filter(); // Remove nulls

        // Export to CSV if requested
        if ($request->input('export') === 'csv') {
            $selectedGroup = Group::find($groupId);
            $filename = 'billing_report_' . now()->format('Ymd_His') . '.csv';

            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
            ];

            $callback = function () use ($latestTransactions, $fromDate, $toDate, $selectedGroup) {
                $handle = fopen('php://output', 'w');

                // Header
                fputcsv($handle, ['LGU PANTUKAN WATER WORKS']);
                fputcsv($handle, ['CASHIER BILLING REPORT']);
                fputcsv($handle, ['BILLING PERIOD: ' . ($fromDate ? Carbon::parse($fromDate)->format('F j, Y') : 'N/A') . ' - ' . ($toDate ? Carbon::parse($toDate)->format('F j, Y') : 'N/A')]);
                fputcsv($handle, ['BILLING TYPE: ' . ($selectedGroup?->name ?? 'ALL')]);
                fputcsv($handle, []);

                // Table header
                fputcsv($handle, [
                    'ACCOUNT ID',
                    'CUSTOMER NAME',
                    'CONSUMPTION (m³)',
                    'PREVIOUS READING',
                    'CURRENT READING',
                    'PENALTY',
                    'TOTAL AMOUNT DUE',
                    'GRAND TOTAL DUE',
                ]);

                if ($latestTransactions->isEmpty()) {
                    fputcsv($handle, ['No records found for the given filters.']);
                } else {
                    foreach ($latestTransactions as $tx) {
                        fputcsv($handle, [
                            $tx['account_id'],
                            $tx['name'],
                            $tx['consumption'],
                            $tx['previous_reading'],
                            $tx['current_reading'],
                            $tx['penalty'],
                            $tx['amount_due'],
                            $tx['grand_total_due'],
                        ]);
                    }
                }

                fclose($handle);
            };

            return response()->stream($callback, 200, $headers);
        }
        $groups = Group::all();

        $transactionQuery = Bills::with(['user.group', 'meterReading'])->where('is_paid', 0);

        if (!empty($search)) {
            $transactionQuery->where(function ($query) use ($search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhereHas('group', function ($g) use ($search) {
                            $g->where('name', 'like', "%{$search}%");
                        });
                });
            });
        }
        $allTransactions = $transactionQuery->latest()->get();

        // Group by group name
        $grouped = $allTransactions->groupBy(function ($bill) {
            return optional($bill?->user?->group)->name ?? 'No Group';
        });
        // Load groups for dropdown
        $groupedTransactions = $grouped->map(function ($groupBills) {
            return [
                'transactions' => $groupBills,
                'total_due' => $groupBills->sum('amount_due'),
            ];
        });

        return view('pages.report.index', compact('groups', 'latestTransactions', 'groupedTransactions'));
    }
}
