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
                        value="{{  $customer->category->amount }}" 
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

                    const prev = document.querySelector('[name="previous_reading"]').value;
                    const current = document.querySelector('[name="current_reading"]').value;
                    const price = document.querySelector('[name="amount"]').value;
                    const consumed = current - prev;
                    const total = consumed * price;

                    if (current < prev) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops!',
                            text: 'The current reading must be greater than or equal to previous reading.'
                        });
                        return;
                    }

                    document.getElementById('meterForm').submit();
                    const receiptHtml = `
                        <div style="font-family: 'Courier New', monospace; font-size: 12px; width: 240px; margin: 0 auto; padding: 10px; text-align: center;">
                            <h2 style="font-size: 14px; margin: 0;">WATER BILLING RECEIPT</h2>
                            <p style="margin: 4px 0;">Company Name</p>
                            <p style="margin: 4px 0;">${new Date().toLocaleString()}</p>
                            <hr style="border-top: 1px dashed #000;" />

                            <div style="text-align: left;">
                                <p>Customer: <strong>{{ $customer->name }}</strong></p>
                                <p>Meter No: <strong>{{ $customer->meter_number }}</strong></p>
                                <p>Prev: ${prev}</p>
                                <p>Current: ${current}</p>
                                <p>Used: ${consumed} m³</p>
                                <p>Rate/ML: ₱${price}</p>
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

            // Listen for post-print message
            {{--  window.addEventListener('message', function(event) {
                if (event.data === 'print-done') {
                    document.getElementById('meterForm').submit();
                }
            });  --}}
            </script>
@endpush
