<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Bills;
use App\Models\Payment;
use Illuminate\Http\Request;
use App\Models\ReconnectionHistory;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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

        $bill = Bills::findOrFail($request->id);
        $bill->is_paid = true;
        $bill->update();

        Payment::create([
            'user_id' => $request->user_id,
            'bill_id' => $request->id,
            'amount_paid' => $request->reconnection_fee,
            'payment_type' => 'Cash',
            'reference_number' => $request->reference_number,
            'payment_date' => \Carbon\Carbon::now(),
        ]);

        return redirect()->back()->with('success', 'Payment created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Payment $payment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function Payment($id)
    {
        $customer = User::clients()->where('id', $id)->first();
        $payment = Payment::where('user_id', $id)->latest()->get();

        return view('pages.payment.form', compact('customer', 'payment'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Payment $payment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Payment $payment)
    {
        //
    }

    public function Reconnect(Request $request, $id)
    {
        $payment = ReconnectionHistory::create([
            'user_id' => $id,
            'amount_due' => $request->reconnection_fee,
            'payment_type' => 'cash',
            'reference_number' => $request->reference_number,
        ]);

        if ($payment) {
            User::where('id', $id)->update([
                "status" => "active",
            ]);
        }

        return redirect()->back()->with('success', 'Reconnected successfully.');
    }
}
