@extends('layouts.master')

@section('subtitle', isset($meter) ? 'Edit meter' : 'Add New meter')
@section('content_header_title', isset($meter) ? 'Update meter' : 'Create meter')

@section('content_body')
<div class="row">
    <div class="col-md-12 mt-4">

        {{-- Back Button --}}
        <a href="{{ route('meter.index') }}" class="btn btn-secondary mb-3">
            <i class="fas fa-arrow-left"></i> Back
        </a>

        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">{{ isset($meter) ? 'Edit' : 'New' }} Meter Reading</h3>
            </div>

            <form 
                method="POST" 
                action="{{ isset($meter) ? route('meter.update', $meter->id) : route('meter.store') }}"
            >
                @csrf
                @if(isset($meter))
                    @method('PUT')
                @endif

                <div class="card-body">

                    {{-- User ID Dropdown --}}
                    @if(isset($meter))
                        <x-form.input 
                            label="User" 
                            name="user_name" 
                            :value="$meter->user->name" 
                            readonly 
                        />
                        <input type="hidden" name="user_id" value="{{ $meter->user_id }}">
                    @else
                        <x-form.select 
                            label="User" 
                            name="user_id" 
                            :options="$customers->pluck('name', 'id')->toArray()" 
                            :selected="old('user_id', '')" 
                            required 
                        />
                    @endif
                    {{-- Reading Date --}}
                    <x-form.input 
                        label="Reading Date" 
                        name="reading_date" 
                        type="date" 
                        :value="old('reading_date', $meter->reading_date ?? '')" 
                        required 
                    />

                    {{-- Previous Reading --}}
                    <x-form.input 
                        label="Previous Reading" 
                        name="previous_reading" 
                        type="number" 
                        placeholder="Enter previous reading" 
                        :value="old('previous_reading', $meter->previous_reading ?? '')" 
                        required 
                    />

                    {{-- Current Reading --}}
                    <x-form.input 
                        label="Current Reading" 
                        name="current_reading" 
                        type="number" 
                        placeholder="Enter current reading" 
                        :value="old('current_reading', $meter->current_reading ?? '')" 
                        required 
                    />
                </div>

                <div class="card-footer text-right">
                    <x-form.submit-button 
                        :label="isset($meter) ? 'Update' : 'Submit'" 
                        class="btn btn-success" 
                    />
                </div>
            </form>
        </div>
    </div>
</div>
@stop
