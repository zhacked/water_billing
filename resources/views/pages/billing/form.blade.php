@extends('layouts.master')

@section('subtitle', 'Meter Reading')
@section('content_header_title', 'Meter Reading')
@section('content_header_subtitle', 'Meter Number : ' . $customer->meter_number .' | '.$customer->name  )


@section('content_body')
<div class="row">
    <div class="col-md-12 mt-4">

        {{-- Back Button --}}
        <a href="{{ route('billing.index') }}" class="btn btn-secondary mb-3">
            <i class="fas fa-arrow-left"></i> Back
        </a>

        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">{{ isset($meter) ? 'Reading' : 'New' }} Meter</h3>
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
                    <input type="hidden" name="user_id" value="{{ $customer->id }}">
                    {{-- Previous Reading --}}
                    <x-form.input 
                        label="Previous Reading" 
                        name="previous_reading" 
                        type="number" 
                        placeholder="Enter previous reading" 
                        :value="old('previous_reading', $meter->current_reading ?? 0)" 
                        readonly
                    />

                    {{-- Current Reading --}}
                    <x-form.input 
                        label="Current Reading" 
                        name="current_reading" 
                        type="number" 
                        placeholder="Enter current reading" 
                        value="" 
                    />

                    <x-form.input 
                        label="Price/ ML" 
                        name="amount" 
                        type="number" 
                        placeholder="Enter Amount per ML" 
                        value="" 
                    />
                </div>

                <div class="card-footer text-right">
                    <x-form.submit-button 
                        :label="isset($meter) ? 'Update Meter' : 'Submit'" 
                        class="btn btn-success" 
                    />
                </div>
            </form>
        </div>
    </div>
</div>
@stop
