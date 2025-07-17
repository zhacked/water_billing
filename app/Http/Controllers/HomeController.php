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

        // TRANSACTIONS QUERY
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

        // No pagination yet, we group first
        $allTransactions = $transactionQuery->latest()->get();

        // Group by group name
        $grouped = $allTransactions->groupBy(function ($bill) {
            return optional($bill->user->group)->name ?? 'No Group';
        });

        // Transform grouped data with totals
        $groupedTransactions = $grouped->map(function ($groupBills) {
            return [
                'transactions' => $groupBills,
                'total_due' => $groupBills->sum('amount_due'),
            ];
        });



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
            'groupedTransactions'
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

        $transactionQuery = Bills::with(['user.group', 'meterReading', 'payments']);

        // Filter by group
        if (!empty($groupId)) {
            $transactionQuery->whereHas('user.group', function ($q) use ($groupId) {
                $q->where('id', $groupId);
            });
        }

        // Filter by date range
        if (!empty($fromDate) && !empty($toDate)) {
            $transactionQuery->whereBetween('billing_date', [
                Carbon::parse($fromDate)->startOfDay(),
                Carbon::parse($toDate)->endOfDay(),
            ]);
        } elseif (!empty($fromDate)) {
            $transactionQuery->whereDate('billing_date', '>=', Carbon::parse($fromDate)->startOfDay());
        } elseif (!empty($toDate)) {
            $transactionQuery->whereDate('billing_date', '<=', Carbon::parse($toDate)->endOfDay());
        }

        // Execute query
        $allTransactions = $transactionQuery->latest()->get();

        // Group transactions by group name
        $grouped = $allTransactions->groupBy(function ($bill) {
            return optional($bill->user->group)->name ?? 'No Group';
        });

        // Prepare for display
        $groupedTransactions = $grouped->map(function ($groupBills) {
            return [
                'transactions' => $groupBills,
                'total_due' => $groupBills->sum('amount_due'),
            ];
        });

        // Export to CSV
        if ($request->input('export') === 'csv') {
            $selectedGroup = Group::find($groupId);
            $filename = 'billing_report_' . now()->format('Ymd_His') . '.csv';

            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
            ];

            $callback = function () use ($selectedGroup, $groupedTransactions, $fromDate, $toDate) {
                $handle = fopen('php://output', 'w');

                // Custom header
                fputcsv($handle, ['LGU PANTUKAN WATER WORKS']);
                fputcsv($handle, ['CASHIER BILLING REPORT']);
                fputcsv($handle, [
                    'BILLING PERIOD: ' .
                        ($fromDate ? Carbon::parse($fromDate)->format('F j, Y') : 'N/A') .
                        ' - ' .
                        ($toDate ? Carbon::parse($toDate)->format('F j, Y') : 'N/A')
                ]);
                fputcsv($handle, ['BILLING TYPE: ' . ($selectedGroup?->name ?? 'ALL')]);
                fputcsv($handle, []); // spacing row

                // Table header matching Excel format
                fputcsv($handle, [
                    'ACCOUNT ID',
                    'CUSTOMER NAME',
                    'CONSUMPTION (m³)',
                    'BILL REF NO.',
                    'BILLING PERIOD',
                    'PENALTY',
                    'TOTAL AMOUNT DUE',
                    'PAYMENT STATUS',
                    'REFERENCE NUMBER',
                    'OR DATE'
                ]);

                $hasData = false;

                foreach ($groupedTransactions as $group => $data) {
                    foreach ($data['transactions'] as $transaction) {
                        fputcsv($handle, [
                            $transaction->user->account_id ?? 'N/A',
                            $transaction->user->name ?? 'N/A',
                            $transaction->consumption ?? 0,
                            $transaction->bill_ref ?? 'N/A',
                            $transaction->billing_date ? Carbon::parse($transaction->billing_date)->format('F Y') : 'N/A',
                            number_format($transaction->penalty, 2),
                            number_format($transaction->amount_due, 2),
                            $transaction->is_paid ? 'Paid' : 'Not Paid',
                            optional($transaction->payments)->reference_number ?? 'N/A',
                            optional($transaction->payments)->or_date
                                ? Carbon::parse($transaction->payments->or_date)->format('F j, Y')
                                : 'N/A',
                        ]);
                        $hasData = true;
                    }
                }

                if (!$hasData) {
                    fputcsv($handle, ['No records found for the given filters.']);
                }

                fclose($handle);
            };

            return response()->stream($callback, 200, $headers);
        }

        // Load groups for filter dropdown
        $groups = Group::all();

        return view('pages.report.index', compact('groups', 'groupedTransactions'));
    }
}
