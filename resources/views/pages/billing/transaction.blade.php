@extends('layouts.master')

@section('subtitle', 'Trasaction History')
@section('content_header_title', 'Trasaction History')
@section('content_header_subtitle', '')

@section('content_body')
    <div class="card" >
        <div class="card-header">
            <strong>Client Name : </strong> {{ $customer->name }} | <strong>Meter Number: </strong> {{  $customer->meter_number}} | <strong class="text-red">Next Payment Date =  </strong>  {{ \Carbon\Carbon::parse($bill->first()->due_date)->format('F d, Y') }}
        </div>
        <div class="card-body">
        <x-table 
            :headers="['Previous Reading', 'Present Reading', 'Consumption', 'Amount', 'Date', 'Bill Amount','Status' ]" 
            :rows="$bill"
            :displayFields="['previous_reading', 'current_reading', 'consumption', 'amount','billing_date','formatted_amount_due', 'is_paid']"
            showIndex="true"
            hideId="true"
            paymentRoute="{{ route('payment.store') }}"
        />
        </div>
        <div class="card-footer text-right">
            <h1>Total Bill: ₱{{ number_format($totalUnpaid, 2) }}</h1>
        </div>

    </div>
@stop

@push('css')
    <style>
        [x-cloak] { display: none !important; }
    </style>
@endpush

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            @if(session('success'))
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: @json(session('success')),
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
            @endif
        });
    </script>
@endpush