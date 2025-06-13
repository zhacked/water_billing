@extends('layouts.master')

@section('subtitle', isset($expenses) ? 'Edit expenses' : 'Add New expenses')
@section('content_header_title', isset($expenses) ? 'Update expenses' : 'Create expenses')

@section('content_body')
<div class="row">
    <div class="col-md-12 mt-4">

        {{-- Back Button --}}
        <a href="{{ route('expenses.index') }}" class="btn btn-secondary mb-3">
            <i class="fas fa-arrow-left"></i> Back
        </a>

        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">{{ isset($expenses) ? 'Edit' : 'New' }} expenses Reading</h3>
            </div>

            <form 
                method="POST" 
                action="{{ isset($expenses) ? route('expenses.update', $expenses->id) : route('expenses.store') }}"
            >
                @csrf
                @if(isset($expenses))
                    @method('PUT')
                @endif

                <div class="card-body">

                    {{-- User ID Dropdown --}}
                    @if(isset($expenses))
                        <x-form.input 
                            label="User" 
                            name="user_name" 
                            :value="$expenses->user->name" 
                            readonly 
                        />
                        <input type="hidden" name="user_id" value="{{ $expenses->user_id }}">
                    @else
                        <x-form.select 
                            label="User" 
                            name="user_id" 
                            :options="$customers->pluck('name', 'id')->toArray()" 
                            :selected="old('user_id', '')" 
                            required 
                        />
                    @endif

                    <x-form.input 
                        label="date Date" 
                        name="date" 
                        type="date" 
                        :value="old('date', $expenses->date ?? '')" 
                        required 
                    />


                    <x-form.input 
                        label="Name" 
                        name="name" 
                        type="text" 
                        placeholder="Enter expenses name" 
                        :value="old('name', $expenses->name ?? '')" 
                        required 
                    />

                    <x-form.input 
                        label="description" 
                        name="description" 
                        type="text" 
                        placeholder="Enter expenses description" 
                        :value="old('description', $expenses->description ?? '')" 
                        required 
                    />


                    <x-form.input 
                        label="Amount " 
                        name="amount" 
                        type="number" 
                        placeholder="Enter amount" 
                        :value="old('amount', $expenses->amount ?? '')" 
                        required 
                    />
                </div>

                <div class="card-footer text-right">
                    <x-form.submit-button 
                        :label="isset($expenses) ? 'Update' : 'Submit'" 
                        class="btn btn-success" 
                    />
                </div>
            </form>
        </div>
    </div>
</div>
@stop
