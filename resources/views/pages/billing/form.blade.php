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
        @if ($customer->status == "for reconnection" || $customer->status == "inacive")
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg shadow mb-4 card card-primary">
                <h2 class="text-lg font-semibold card-header">Action Required</h2>
                <p class="text-sm ">
                    <h1>
                        This client is currently <strong class="{{ $customer->status == "for reconnection" ? "text-warning" : "text-danger" }}">{{ $customer->status }}</strong>.<br>
                        They need to settle an outstanding amount of 
                        <span class="font-bold text-red-800">
                            ₱{{ number_format($billAmountwithPenalty, 2) }}
                        </span>
                    </h1> 
                </p>
            </div>
        @else
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">{{ isset($meter) ? 'Reading' : 'New' }} Meter</h3>
            </div>

            <form 
                    method="POST" 
                    action="{{ isset($meter) ? route('meter.update', $meter->id) : route('meter.store') }}"
                    id="meterForm"
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
                        id="previous_reading"
                        type="number" 
                        placeholder="Enter previous reading" 
                        :value="old('previous_reading', $meter->current_reading ?? 0)" 
                        readonly
                    />

                    {{-- Current Reading --}}
                    <x-form.input 
                        label="Current Reading" 
                        name="current_reading" 
                        id="current_reading" 
                        type="number" 
                        placeholder="Enter current reading" 
                        value="" 
                    />

                    <x-form.input 
                        label="Price/ ML" 
                        name="amount" 
                        type="number" 
                        placeholder="{{  $customer->category?->amount ?? 'please choose a category'}}" 
                        value="{{  $customer->category?->amount ?? 'please choose a category'}}" 
                        readonly
                    />
                </div>
                
                <div class="card-footer text-right">
                    <x-form.submit-button 
                        label="Print Receipt" 
                        class="btn btn-success" 
                        onclick="previewAndSubmit(event)"
                    />

                </div>
            </form>
        </div>
        @endif
       
    </div>
</div>
<div id="receipt-preview" class="d-none">
    <h2>Water Billing Receipt</h2>
    <p><strong>Customer:</strong> {{ $customer->name }}</p>
    <p><strong>Meter Number:</strong> {{ $customer->meter_number }}</p>
    <p><strong>Previous Reading:</strong> <span id="r-prev"></span></p>
    <p><strong>Current Reading:</strong> <span id="r-current"></span></p>
    <p><strong>Price/ML:</strong> ₱<span id="r-price"></span></p>
    <p><strong>Consumption:</strong> <span id="r-consumed"></span> ML</p>
    <p><strong>Total:</strong> ₱<span id="r-total"></span></p>
</div>
@stop
@push('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs/qrcode.min.js"></script>
    <script>
    function previewAndSubmit(event) {
        event.preventDefault();

        Swal.fire({
            title: 'Print & Save?',
            text: 'Do you want to print this receipt and save the meter reading?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Print & Save',
            cancelButtonText: 'Cancel',
            reverseButtons: true
        }).then((result) => {
            if (!result.isConfirmed) return;

            let current = parseFloat(document.getElementById('current_reading').value);
            let prev = parseFloat(document.getElementById('previous_reading').value);
            const price = parseFloat(document.querySelector('[name="amount"]').value);
            const previousDue = parseFloat({{ $billAmount ?? 0 }});
            const penalty = parseFloat({{ $penalty ?? 0 }});

            const consumed = current - prev;

            if (isNaN(current) || isNaN(prev) || isNaN(price)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Input',
                    text: 'Please enter valid readings and price.'
                });
                return;
            }

            if (current <= prev) {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops!',
                    text: 'The current reading must be greater than previous reading.'
                });
                return;
            }

            const subtotal = consumed * price;
            const total = subtotal + previousDue + penalty;

            document.getElementById('meterForm').submit();

            const qrImageUrl = "{{ asset('images/pantukan_QR.png') }}";

            const receiptHtml = `
                <div style="font-family: 'Courier New', monospace; font-size: 12px; width: 240px; margin: 0 auto; padding: 10px; text-align: center;">
                    <p style="margin: 10px 0;">Scan to login online</p>
                    <img 
                        src="${qrImageUrl}" 
                        alt="QR Code" 
                        style="width: 120px; height: 120px; margin-top: 8px;"
                    />
                    <h2 style="font-size: 14px; margin: 0;">WATER BILLING RECEIPT</h2>
                    <p style="margin: 4px 0;">Pantukan Waterworks</p>
                    <p style="margin: 4px 0;">${new Date().toLocaleString()}</p>
                    <hr style="border-top: 1px dashed #000;" />

                    <div style="text-align: left;">
                        <p>Customer: <strong>{{ $customer->name }}</strong></p>
                        <p>Meter No: <strong>{{ $customer->meter_number }}</strong></p>
                        <p>Prev: ${prev}</p>
                        <p>Current: ${current}</p>
                        <p>Used: ${consumed} m³</p>
                        <p>Rate/ML: ₱${price.toFixed(2)}</p>
                        <p>Subtotal: ₱${subtotal.toFixed(2)}</p>
                        <p>Previous Dues: ₱${previousDue.toFixed(2)}</p>
                        <p>Penalty: ₱${penalty.toFixed(2)}</p>
                        <p>Due Date: {{ now('Asia/Manila')->addDays(30)->format('M d, Y') }}</p>
                        <hr style="border-top: 1px dashed #000;" />
                        <p style="font-weight: bold;">TOTAL: ₱${total.toFixed(2)}</p>
                    </div>

                    <p style="margin-top: 10px;">Thank you!</p>
                </div>
            `;

            const printWindow = window.open('', 'PrintWindow', 'width=320,height=600');
            printWindow.document.write(`
                <html>
                <head>
                    <title>Print Receipt</title>
                    <style>
                        body {
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            height: 100vh;
                            margin: 0;
                            background: #fff;
                        }
                    </style>
                </head>
                <body onload="window.print(); window.onafterprint = () => { window.opener.postMessage('print-done', '*'); window.close(); }">
                    ${receiptHtml}
                </body>
                </html>
            `);
            printWindow.document.close();
        });
    }
    </script>

@endpush
