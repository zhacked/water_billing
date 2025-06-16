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
        $customers = User::clients()->latest()->get();
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

            $bill = Bills::create([
                'user_id' => $validated['user_id'],
                'meter_reading_id' => $meter->id,
                'billing_date' => \Carbon\Carbon::now()->addDays(30)->format('Y-m-d'),
                'consumption' =>  $consumption,
                'amount_due' =>    $amount_due,
                'due_date' => \Carbon\Carbon::now()->addDays(30)->format('Y-m-d'),
                'penalty' => 0,
                'is_paid' => false
            ]);

            $user = User::find($validated['user_id'])->first();
            $message = "Hi {$user->name}, your water meter reading has been recorded.\n" .
                "Consumption: {$consumption} m³\n" .
                "Amount Due: PHP " . number_format($amount_due, 2) . "\n" .
                "Due Date: " . \Carbon\Carbon::parse($bill->due_date)->format('M d, Y');

            try {
                $smsService->sendSms($user->contact_number, $message);
            } catch (\Exception $e) {
                Log::error("Failed to send SMS: " . $e->getMessage());
            }


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
    public function update(Request $request,  SemaphoreSmsService $smsService)
    {

        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'previous_reading' => 'required|numeric|min:0',
                'current_reading' => 'required|numeric|min:0|gte:previous_reading',
                'amount' => 'required|numeric|min:0',
            ]);



            // Update the meter reading
            $validated['reading_date'] = today();
            $latestMeterUser = MeterReading::where('user_id', $validated['user_id'])
                ->latest('reading_date')
                ->first();

            $previousReading = $latestMeterUser?->current_reading ?? 0;

            // We're treating current_reading input as a delta/increment
            $finalCurrentReading = $previousReading + $validated['current_reading'];

            $consumption = $validated['current_reading']; // the actual increment
            $amountDue = $consumption * $validated['amount'];

            $meter = MeterReading::create([
                'user_id' => $validated['user_id'],
                'previous_reading' => $previousReading,
                'current_reading' => $finalCurrentReading,
                'reading_date' => today(),
                'amount' => $validated['amount'],
            ]);

            $bill = Bills::create([
                'user_id' => $validated['user_id'],
                'meter_reading_id' => $meter->id,
                'billing_date' => \Carbon\Carbon::now()->addDays(30)->format('Y-m-d'),
                'consumption' =>  $consumption,
                'amount_due' =>  $amountDue,
                'due_date' => \Carbon\Carbon::now()->addDays(30)->format('Y-m-d'),
                'penalty' => 0,
                'is_paid' => false
            ]);


            $user = User::where('id', $validated['user_id'])->first();
            $message = "Hi {$user->name}, your water meter reading has been recorded.\n" .
                "Consumption: {$consumption} m³\n" .
                "Amount Due: PHP " . number_format($amountDue, 2) . "\n" .
                "Due Date: " . \Carbon\Carbon::parse($bill->due_date)->format('M d, Y');


            $smsService->sendSms($user->contact_number, $message);

            return redirect()->route('billing.index')->with('success', 'Meter reading updated successfully.');
        } catch (\Exception $e) {
            Log::error('Exception caught: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
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
        $customer = User::clients()->where('id', $id)->first();
        return view("pages.billing.form", compact('customer', 'meter'));
    }
}
