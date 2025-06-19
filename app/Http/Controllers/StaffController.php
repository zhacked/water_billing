<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\group;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;

class StaffController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $staffs = User::staffs()
            ->with('group')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('meter_number', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->appends(['search' => $search]); // keeps query string in pagination

        return view("pages.staff.index", compact("staffs", "search"));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $groups = group::latest()->get();
        return view("pages.staff.form", compact("groups"));
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
            'group_id' => 'required',
        ]);



        $employee = User::create([
            ...$validated,
            'role' => 'staff',
            'status' => 'active',
            'password' => Hash::make('password123'),
        ]);


        // Assign the 'staff' role using Spatie
        $employee->assignRole('staff');

        return redirect()->route('staff.index')->with('success', 'Employee created successfully.');
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $staff = User::findOrFail($id);
        $groups = group::latest()->get();
        return view('pages.staff.form', compact('staff', 'groups'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $staff = User::findOrFail($id); // Grab the record

            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', Rule::unique('users')->ignore($staff->id)],
                'address' => ['required', 'string'],
                'contact_number' => ['required', 'string', 'max:15'],
                'status' => ['nullable', 'in:active,inactive'],
            ]);

            $validated['group_id'] = $request->group_id;
            $staff->update($validated);

            return redirect()
                ->route('staff.index')
                ->with('success', 'staff updated successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->validator)->withInput();
        } catch (\Throwable $e) {
            Log::error("Error updating staff #$id: " . $e->getMessage(), [
                'trace' => $e->getTrace(),
            ]);

            return back()->with('error', 'Something went wrong while updating the staff. Try again.');
        }
    }

    public function toggleStaffStatus($id)
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
        //
    }
}
