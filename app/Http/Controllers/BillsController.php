<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Bills;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BillsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $bills = Bills::all();
        $groupId = Auth::user()->group_id; // Assuming staff has a group_id field

        // Smart customer fetch based on group
        $customerQuery = User::clients(); // Assume scopeClients() returns only customers

        if ($groupId !== null && $groupId != 0) {
            $customerQuery->where('group_id', $groupId);
        }

        $customers = $customerQuery->latest()->paginate(10);



        return view("pages.billing.index", compact("customers"));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Bills $bills)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Bills $bills)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Bills $bills)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Bills $bills)
    {
        //
    }

    public function clientTransaction($id)
    {

        $customer = User::where('id', $id)->first();

        $bill = Bills::with(['user', 'meterReading'])->where('user_id', $id)->latest()->paginate(10);

        $totalUnpaid = Bills::where('user_id', $id)
            ->where('is_paid', false)
            ->sum('amount_due');
        return view("pages.billing.transaction", compact("bill", 'customer', 'totalUnpaid'));
    }
}
