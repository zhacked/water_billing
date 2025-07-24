<?php

namespace App\Http\Controllers;


use App\Models\User;
use App\Models\Expenses;
use Illuminate\Http\Request;


class OtherPaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(request $request)
    {
        $search = $request->input('search');

        $expenseQuery = Expenses::with('user')->where('type', 'customer');

        if (!empty($search)) {
            $expenseQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $Other = $expenseQuery->latest()->paginate(10)->appends(['search' => $search]);

        return view("pages.other_payment.index", compact("Other"));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $customers = User::clients()->latest()->get();
        return view("pages.other_payment.form", compact("customers"));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
         $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'date' => 'required|string|max:15',
            'amount' => 'required|numeric',
        ]);

        $validated['type'] = 'customer';

        Expenses::create($validated);

        return redirect()->route("client-other.index")->with('success', 'Payment created successfully.');
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request,$id)
    {
        // Validate the incoming request
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'date' => 'required|date',
            'amount' => 'required|numeric',
        ]);

        $validated['type'] = 'customer';

        // Find the expense or fail
        $expense = Expenses::findOrFail($id);

        // Update the expense with validated data
        $expense->update($validated);

        // Redirect back with success message
        return redirect()->route('client-other.index')->with('success', 'Expense updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $expense = Expenses::findOrFail($id);
        $expense->delete();
        return redirect()->route('client-other.index')->with('success', 'Payment deleted successfully.');
    }
}
