<?php

namespace App\Http\Controllers;

use App\Models\group;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $groupQuery = Group::query();

        if (!empty($search)) {
            $groupQuery->where('name', 'like', "%{$search}%");
        }

        $groups = $groupQuery->latest()->paginate(10)->appends(['search' => $search]);

        return view("pages.group.index", compact("groups", "search"));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view("pages.group.form");
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string'
        ]);

        group::create($validated);

        return redirect()->route('groups.index')->with('success', 'Group created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(group $group)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {

        $groups = group::findOrFail($id);
        return view('pages.group.form', compact('groups'));
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, group $group)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string'
        ]);

        $group->update($validated);

        return redirect()->route('groups.index')->with('success', 'Group updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(group $group)
    {
        //
    }
}
