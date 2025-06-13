<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Bills;
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
    public function index()
    {
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

        $transaction = Bills::with(['user', 'meterReading'])->latest()->paginate(10);

        return view('pages.dashboard.index', compact(
            'monthlyIncomeReport',
            'monthlyExpenses',
            'monthlyIncome',
            'netProfit',
            'monthlyPaidClients',
            'monthlyUnPaidClients',
            'totalClient',
            'totalStaff',
            'transaction'
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
}
