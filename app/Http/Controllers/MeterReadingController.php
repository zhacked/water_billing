<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Bills;
use App\Models\MeterReading;
use Illuminate\Http\Request;

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
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
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


            return redirect()->route('billing.index')->with('success', 'Meter reading recorded successfully.');
        } catch (\Exception $e) {
            dd($e->getMessage(), $e->getTraceAsString()); // dev mode, swap this out in prod
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
    public function update(Request $request, MeterReading $meter)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'previous_reading' => 'required|numeric|min:0',
                'current_reading' => 'required|numeric|min:0|gte:previous_reading',
                'amount' => 'required|numeric|min:0',
            ]);

            // Check if user_id is being updated
            if ($validated['user_id'] != $meter->user_id) {
                // Get the latest reading for the new user
                $latestReading = MeterReading::where('user_id', $validated['user_id'])
                    ->latest('reading_date')
                    ->first();

                // If no previous reading exists for the new user, override 'previous_reading' to 0
                if (!$latestReading) {
                    $validated['previous_reading'] = 0;
                } else {
                    // Check if previous reading matches the last recorded current reading for the new user
                    if ($validated['previous_reading'] != $latestReading->current_reading) {
                        return redirect()->route('billing.index')->with('error', 'Previous reading must match the last recorded current reading (' . $latestReading->current_reading . ')');
                    }
                }
            } else {
                // Get the reading before the one being updated
                $previousReading = MeterReading::where('user_id', $validated['user_id'])
                    ->where('reading_date', '<', $meter->reading_date)
                    ->latest('reading_date')
                    ->first();

                // Get the reading after the one being updated
                $nextReading = MeterReading::where('user_id', $validated['user_id'])
                    ->where('reading_date', '>', $meter->reading_date)
                    ->oldest('reading_date')
                    ->first();

                // If no previous reading exists and no next reading exists, allow any previous reading
                if (!$previousReading && !$nextReading) {
                    // do nothing
                } elseif (!$previousReading && $nextReading) {
                    // Check if current reading is being updated to a value less than the next reading
                    if ($nextReading->previous_reading > $validated['current_reading']) {
                        return back()->withErrors([
                            'current_reading' => 'Current reading must be less than or equal to the next recorded previous reading (' . $nextReading->previous_reading . ').'
                        ])->withInput();
                    }
                } else {
                    // Check if previous reading matches the last recorded current reading
                    if ($validated['previous_reading'] != $previousReading->current_reading) {
                        return back()->withErrors([
                            'previous_reading' => 'Previous reading must match the last recorded current reading (' . $previousReading->current_reading . ').'
                        ])->withInput();
                    }

                    // Check if current reading is being updated to a value less than the next reading
                    if ($nextReading) {
                        if ($nextReading->previous_reading > $validated['current_reading']) {
                            return back()->withErrors([
                                'current_reading' => 'Current reading must be less than or equal to the next recorded previous reading (' . $nextReading->previous_reading . ').'
                            ])->withInput();
                        }
                    }
                }
            }

            // Update the meter reading
            $validated['reading_date'] = today();
            $meter = MeterReading::create($validated);

            $consumption =  $validated['current_reading'] - $validated['previous_reading'];
            $amount_due = $consumption * $validated['amount'];

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

            return redirect()->route('billing.index')->with('success', 'Meter reading updated successfully.');
        } catch (\Exception $e) {
            dd($e->getMessage(), $e->getTraceAsString()); // dev mode, swap this out in prod
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
        $meter = MeterReading::where('user_id', $id)->first();
        $customer = User::clients()->where('id', $id)->first();
        return view("pages.billing.form", compact('customer', 'meter'));
    }
}
