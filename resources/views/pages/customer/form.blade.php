@extends('layouts.master')

@section('subtitle', isset($customer) ? 'Edit customer' : 'Add New customer')
@section('content_header_title', isset($customer) ? 'Update customer' : 'Create customer')

@section('content_body')
<div class="row">
    <div class="col-md-12  mt-4">

        {{-- Back Button --}}
        <a href="{{ route('customer.index') }}" class="btn btn-secondary mb-3">
            <i class="fas fa-arrow-left"></i> Back
        </a>

        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">{{ isset($customer) ? 'Edit' : 'New' }} customer Information</h3>
            </div>

            <form 
                method="POST" 
                action="{{ isset($customer) ? route('customer.update', $customer->id) : route('customer.store') }}"
            >
                @csrf
                @if(isset($customer))
                    @method('PUT')
                @endif

                <div class="card-body">
                    <x-form.input 
                        label="Client ID"  
                        name="account_id"  
                        type="text" 
                        :value="old('account_id', $customer->account_id ?? '')"
                        readonly
                    />

                    <x-form.input 
                        label="Full Name" 
                        name="name" 
                        type="text" 
                        placeholder="Enter full name" 
                        :value="old('name', $customer->name ?? '')"
                    />

                    <x-form.input 
                        label="Email Address" 
                        name="email" 
                        type="email" 
                        placeholder="Enter email" 
                        :value="old('email', $customer->email ?? '')"
                    />

                    <x-form.input 
                        label="Address" 
                        name="address" 
                        type="text" 
                        placeholder="Enter address" 
                        :value="old('address', $customer->address ?? '')"
                    />

                    <x-form.input 
                        label="Contact Number" 
                        name="contact_number" 
                        type="text" 
                        placeholder="Enter phone number"
                        :value="old('contact_number', $customer->contact_number ?? '')"
                    />

                      <x-form.input 
                        label="Meter Number" 
                        name="meter_number" 
                        type="text" 
                        placeholder="Enter Meter Number"
                        :value="old('meter_number', $customer->meter_number ?? '')"
                    />

                    <x-form.select 
                        label="Group name :  {{ isset($customer)  ?  $customer?->group?->name : 'No group chosen'}}" 
                        name="group_id" 
                        :options="$groups->pluck('name', 'id')->toArray()" 
                        :selected="old('group_id', isset($group) ? $group->id : '')" 
                    />

                    <x-form.select 
                        label="Category Type :  {{ isset($customer)  ?  $customer?->category?->name : 'No group chosen'}}"
                        name="category_id"
                        :options="$category->toArray()"
                        :selected="old('category_id', isset($category) ? '' : '')" 
                    />


                </div>

                <div class="card-footer text-right">
                    <x-form.submit-button 
                        :label="isset($customer) ? 'Update' : 'Submit'" 
                        class="btn btn-success" 
                    />
                </div>
            </form>
        </div>
    </div>
</div>
@stop
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const input = document.querySelector('input[name="account_id"]');
            if (input && !input.value) {
                const uniqueId = 'A' + new Date().getFullYear().toString().slice(2) + Math.floor(1000000000 + Math.random() * 9000000000);
                input.value = uniqueId;
            }
        });
    </script>