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
    public function index(Request $request)
    {
        $groupId = Auth::user()->group_id;
        $search = $request->input('search');

        $customerQuery = User::with('bills')->clients();

        // Filter by staff's group_id if set
        if (!is_null($groupId) && $groupId != 0) {
            $customerQuery->where('group_id', $groupId);
        }

        // Search by name or meter number
        if (!empty($search)) {
            $customerQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('meter_number', 'like', "%{$search}%");
            });
        }

        $customers = $customerQuery->latest()->paginate(10)->appends(['search' => $search]);

        return view("pages.billing.index", compact("customers", "search"));
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
