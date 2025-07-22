<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Bills;
use App\Models\group;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $customers = User::clients()
            ->with('group', 'bills')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('meter_number', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->appends(['search' => $search]); // Keeps query string in pagination
    
        return view("pages.customer.index", compact("customers", "search"));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $groups = group::latest()->get();
        $category = Category::pluck('name', 'id');
        return view("pages.customer.form", compact("groups", "category"));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'address' => 'required|string',
            'contact_number' => 'required|string|max:15',
            'meter_number' => 'required|numeric',
            'group_id' => 'required',
            'account_id' => 'required',
            'category_id' => 'required',
        ]);

        $employee = User::create([
            ...$validated,
            'status' => 'active',
            'password' => Hash::make('password123'),
        ]);

        // Assign the 'client' role using Spatie
        $employee->assignRole('client');

        return redirect()->route('customer.index')->with('success', 'Employee created successfully.');
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $customer = User::findOrFail($id);
        $groups = group::latest()->get();
        $category = Category::pluck('name', 'id');
        return view('pages.customer.form', compact('customer', 'groups', 'category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $customer = User::findOrFail($id); // Grab the record

            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', Rule::unique('users')->ignore($customer->id)],
                'address' => ['required', 'string'],
                'contact_number' => ['required', 'string', 'max:15'],
                'status' => ['nullable', 'in:active,inactive'], // optional but useful
                'meter_number' => 'required|numeric',
                'account_id' => 'required',
                'category_id' => 'required',
            ]);


            $validated['group_id'] = $request->group_id;
            $customer->update($validated);

            return redirect()
                ->route('customer.index')
                ->with('success', 'Customer updated successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->validator)->withInput();
        } catch (\Throwable $e) {
            Log::error("Error updating customer #$id: " . $e->getMessage(), [
                'trace' => $e->getTrace(),
            ]);

            return back()->with('error', 'Something went wrong while updating the customer. Try again.');
        }
    }

    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);
        $user->status = $user->status === 'active' ? 'inactive' : 'active';
        $user->save();

        return redirect()->back()->with('success', 'Status updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->back()->with('success', 'Client Deleted successfully!');
    }
}
