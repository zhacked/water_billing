<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Bills;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\SemaphoreSmsService;

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

    public function sendSms(Request $request, SemaphoreSmsService $smsService)
    {
        $number = $request->input('number'); // e.g. 0917xxxxxxx
        $message = $request->input('message'); // your message

        if ($smsService->sendSms($number, $message)) {
            return response()->json(['status' => 'Message sent!']);
        }

        return response()->json(['status' => 'Failed to send'], 500);
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
