@extends('layouts.master')

@section('subtitle', 'Customer')
@section('content_header_title', 'Customer')
@section('content_header_subtitle', '')

@section('content_body')
    <div style="display: flex; flex-wrap: wrap; gap: 20px; align-items: flex-start;">
            
        {{-- LEFT: Customer Details Card --}}
        <div style="flex: 1 1 30%;">
            <div class="card shadow rounded-lg p-4" style="background-color: #f9f9f9;">
                <h5 class="mb-4 font-bold text-lg">👤 Customer Details</h5>
                <p><strong>Name:</strong> {{ $clients->name }}</p>
                <p><strong>Meter Number:</strong> {{ $clients->meter_number }}</p>
                <p><strong>Consumption:</strong></p>
                <ul>
                    @forelse ($clients->bills as $bill)
                          <li>{{ $bill->consumption}} kW/h on ({{ \Carbon\Carbon::parse($bill->billing_date)->format('M d, Y') }}) = ₱{{ number_format($bill->amount_due, 2)}}</li>
                    @empty
                        <h5>All payment has been settled ! thankyou</h5>
                    @endforelse()
                      
                </ul>
                <p><strong>Penalty:</strong> ₱{{ number_format($totalPenalty, 2) ?? 0 }}</p>

                <p><strong>Need to Pay:</strong> ₱{{ number_format($totalUnpaid + $totalPenalty, 2) }}</p>
            </div>
        </div>

        {{-- RIGHT: Tables --}}
        <div style="flex: 1 1 65%; display: flex; flex-direction: column; gap: 20px;">
            <div class="card shadow rounded-lg p-4" style="background-color: #ffffff;">
                <h5 class="mb-3 font-semibold">📄 Transaction History</h5>
                <x-table 
                    :headers="[ 'Current Reading', 'Previous Reading', 'Current Reading', 'Consumption', 'status']" 
                    :rows="$bills"
                    :displayFields="['current_reading', 'previous_reading', 'current_reading','consumption','is_paid']"
                    showIndex="true"
                    hideId="true"
                />
            </div>
        </div>

        @if($clients->status === 'for disconnection')
        <div style="flex: 1 1 65%; display: flex; flex-direction: column; gap: 20px;">
            <div class="card shadow rounded-lg p-4" style="background-color: #f9f9f9;">
                    <p> Hello user , This is a disconnection notice  </p>
            </div>
        </div>
        @endif

    </div>
@stop

@push('css')
    <style>
        [x-cloak] { display: none !important; }
        .card {
            background: white;
            border-radius: 0.5rem;
            border: 1px solid #ddd;
        }
        .badge-success {
            background-color: #28a745;
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
        }
        .badge-secondary {
            background-color: #6c757d;
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
        }
    </style>
@endpush

@push('js')

@endpush
