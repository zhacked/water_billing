<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Bills;
use App\Models\MeterReading;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\SemaphoreSmsService;

class MeterReadingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $meters = MeterReading::with('user')->latest()->paginate(10);
        return view("pages.meter_reading.index", compact("meters"));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $customers = User::with('category')->clients()->latest()->get();

        return view("pages.meter_reading.form", compact('customers'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request,  SemaphoreSmsService $smsService)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'previous_reading' => 'required|numeric|min:0',
                'current_reading' => 'required|numeric|min:0|gte:previous_reading',
                'amount' => 'required|numeric|min:0',
            ]);

            // Check if this user already has meter readings
            $latestReading = MeterReading::where('user_id', $validated['user_id'])
                ->latest('reading_date')
                ->first();

            // If no previous reading exists, override 'previous_reading' to 0
            if (!$latestReading) {
                $validated['previous_reading'] = 0;
            } else {
                // Optional: Update validation to force correct sequence
                if ($validated['previous_reading'] != $latestReading->current_reading) {
                    return back()->withErrors([
                        'previous_reading' => 'Previous reading must match the last recorded current reading (' . $latestReading->current_reading . ').'
                    ])->withInput();
                }
            }

            $consumption =  $validated['current_reading'] - $validated['previous_reading'];
            $amount_due = $consumption * $validated['amount'];

            // Store the new meter reading
            $validated['reading_date'] = today();
            $meter = MeterReading::create($validated);

            do {
                $billRef = 'B1' . now()->timestamp . mt_rand(10, 99);
            } while (Bills::where('bill_ref', $billRef)->exists());


            $bill = Bills::create([
                'user_id' => $validated['user_id'],
                'meter_reading_id' => $meter->id,
                'bill_ref' =>  $billRef,
                'billing_date' => \Carbon\Carbon::now()->addDays(30)->format('Y-m-d'),
                'consumption' =>  $consumption,
                'amount_due' =>    $amount_due,
                'due_date' => \Carbon\Carbon::now()->addDays(30)->format('Y-m-d'),
                'penalty' => 0,
                'is_paid' => false
            ]);

            $user = User::where('id', $validated['user_id'])->first();
            $message = "Hi {$user->name}, your first water meter reading has been recorded.\n" .
                "First Reading: " .  $validated['current_reading'] . " m³" .  "\n" .
                "Amount Due: PHP " . number_format($amount_due, 2) . "\n" .
                "Due Date: " . \Carbon\Carbon::parse($bill->due_date)->format('M d, Y');

            $smsService->sendSms($user->contact_number, $message);

            return redirect()->route('billing.index')->with('success', 'Meter reading recorded successfully.');
        } catch (\Exception $e) {
            Log::error('Exception caught: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $meter = MeterReading::findOrFail($id);
        $customers = User::clients()->latest()->get();
        return view("pages.meter_reading.form", compact('customers', 'meter'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SemaphoreSmsService $smsService)
    {
        try {

            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'previous_reading' => 'required|numeric|min:0',
                'current_reading' => 'required|numeric|min:0|gte:previous_reading',
                'amount' => 'required|numeric|min:0',
            ]);

            $validated['reading_date'] = now()->timezone('Asia/Manila')->format('Y-m-d');

            // Fetch unpaid bills
            $unpaidBills = Bills::where('user_id', $validated['user_id'])
                ->where('is_paid', false)
                ->orderByDesc('due_date')
                ->get();

            $unpaidCount = $unpaidBills->count();

            // Apply 10% penalty to the most recent unpaid bill
            if ($unpaidCount > 0) {
                $latestUnpaidBill = $unpaidBills->first();
                $latestUnpaidBill->penalty = $latestUnpaidBill->amount_due * 0.10;
                $latestUnpaidBill->save();
            }


            $status = match (true) {
                $unpaidCount >= 3 => 'inactive',
                $unpaidCount === 2 => 'for reconnection',
                default => null,
            };

            if ($status) {
                User::where('id', $validated['user_id'])->update(['status' => $status]);
            }

            $consumption = $validated['current_reading'] - $validated['previous_reading'];
            $amountDue = $consumption * $validated['amount'];

            // Generate unique bill reference
            do {
                $billRef = 'B1' . now()->timestamp . mt_rand(10, 99);
            } while (Bills::where('bill_ref', $billRef)->exists());

            // Create meter reading
            $meter = MeterReading::create([
                'user_id' => $validated['user_id'],
                'previous_reading' => $validated['previous_reading'],
                'current_reading' => $validated['current_reading'],
                'reading_date' => $validated['reading_date'],
                'amount' => $validated['amount'],
            ]);

            $dueDate = now()->addDays(30)->timezone('Asia/Manila')->format('Y-m-d');

            // Create new bill
            $bill = Bills::create([
                'user_id' => $validated['user_id'],
                'meter_reading_id' => $meter->id,
                'bill_ref' =>  $billRef,
                'billing_date' => $dueDate,
                'consumption' => $consumption,
                'amount_due' => $amountDue,
                'due_date' => $dueDate,
                'penalty' => 0,
                'is_paid' => false
            ]);

            // Notify user
            $user = User::find($validated['user_id']);
            $message = "Hi {$user->name}, your water meter reading has been recorded.\n" .
                "Previous Reading: {$validated['previous_reading']} m³\n" .
                "Current Reading: {$validated['current_reading']} m³\n" .
                "Consumption: {$consumption} m³\n" .
                "Amount Due: PHP " . number_format($amountDue, 2) . "\n" .
                "Due Date: " . \Carbon\Carbon::parse($bill->due_date)->format('M d, Y');

            $smsService->sendSms($user->contact_number, $message);

            return redirect()->route('billing.index')->with('success', 'Meter reading updated successfully.');
        } catch (\Exception $e) {
            Log::error('Exception caught: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Something went wrong while updating the meter reading.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MeterReading $meter)
    {
        $meter->delete();
        return redirect()->route('meter.index')->with('success', 'Meter reading deleted successfully.');
    }


    public function readingMeter($id)
    {
        $meter = MeterReading::where('user_id', $id)->latest()->first();
        $customer = User::with('category')->clients()->where('id', $id)->first();
        $totals = Bills::where('user_id', $id)
                        ->where('is_paid', 0)
                        ->selectRaw('SUM(amount_due) as total_due, SUM(penalty) as total_penalty')
                        ->first();

        $billAmount = $totals->total_due + $totals->total_penalty;
        return view("pages.billing.form", compact('customer', 'meter', 'billAmount'));
    }
}
