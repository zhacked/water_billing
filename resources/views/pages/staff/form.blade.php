@extends('layouts.master')

@section('subtitle', isset($staff) ? 'Edit staff' : 'Add New staff')
@section('content_header_title', isset($staff) ? 'Update staff' : 'Create staff')

@section('content_body')
<div class="row">
    <div class="col-md-12  mt-4">

        {{-- Back Button --}}
        <a href="{{ route('staff.index') }}" class="btn btn-secondary mb-3">
            <i class="fas fa-arrow-left"></i> Back
        </a>

        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">{{ isset($staff) ? 'Edit' : 'New' }} staff Information</h3>
            </div>

            <form 
                method="POST" 
                action="{{ isset($staff) ? route('staff.update', $staff->id) : route('staff.store') }}"
            >
                @csrf
                @if(isset($staff))
                    @method('PUT')
                @endif

                <div class="card-body">

                    <x-form.input 
                        label="Full Name" 
                        name="name" 
                        type="text" 
                        placeholder="Enter full name" 
                        :value="old('name', $staff->name ?? '')"
                    />

                    <x-form.input 
                        label="Email Address" 
                        name="email" 
                        type="email" 
                        placeholder="Enter email" 
                        :value="old('email', $staff->email ?? '')"
                    />

                    <x-form.input 
                        label="Address" 
                        name="address" 
                        type="text" 
                        placeholder="Enter address" 
                        :value="old('address', $staff->address ?? '')"
                    />

                    <x-form.input 
                        label="Contact Number" 
                        name="contact_number" 
                        type="text" 
                        placeholder="Enter phone number"
                        :value="old('contact_number', $staff->contact_number ?? '')"
                    />

                    <x-form.select 
                        label="Group name :  {{ isset($staff)  ?  $staff?->group->name : 'No group chosen'}}" 
                        name="group_id" 
                        :options="$groups->pluck('name', 'id')->toArray()" 
                        :selected="old('group_id', isset($group) ? $group->id : '')" 
                    />

                </div>

                <div class="card-footer text-right">
                    <x-form.submit-button 
                        :label="isset($staff) ? 'Update' : 'Submit'" 
                        class="btn btn-success" 
                    />
                </div>
            </form>
        </div>
    </div>
</div>
@stop
