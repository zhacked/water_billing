@extends('layouts.master')

@section('subtitle', isset($expenses) ? 'Edit expenses' : 'Add New expenses')
@section('content_header_title', isset($expenses) ? 'Update expenses' : 'Create expenses')

@section('content_body')
<div class="row">
    <div class="col-md-12 mt-4">

        {{-- Back Button --}}
        <a href="{{ route('client-other.index') }}" class="btn btn-secondary mb-3">
            <i class="fas fa-arrow-left"></i> Back
        </a>

        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">{{ isset($expenses) ? 'Edit' : 'New' }} expenses Reading</h3>
            </div>

            <form 
                id="expenseForm"
                method="POST" 
                action="{{ isset($expenses) ? route('client-other.update', $expenses->id) : route('client-other.store') }}"
            >
                @csrf
                @if(isset($expenses))
                    @method('PUT')
                @endif

                <div class="card-body">
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
                    <button type="button" onclick="previewAndSubmit(event)" class="btn btn-info mr-2">
                        <i class="fas fa-print"></i> Print & Save
                    </button>
                    <x-form.submit-button 
                        :label="isset($expenses) ? 'Update' : 'Submit'" 
                        class="btn btn-success d-none" 
                        id="realSubmitBtn"
                    />
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    function previewAndSubmit(event) {
        event.preventDefault();

        const name = document.querySelector('[name="name"]').value;
        const description = document.querySelector('[name="description"]').value;
        const amount = parseFloat(document.querySelector('[name="amount"]').value);
        const date = document.querySelector('[name="date"]').value;
        const user = document.querySelector('[name="user_name"]') 
                        ? document.querySelector('[name="user_name"]').value 
                        : document.querySelector('[name="user_id"] option:checked').text;

        if (!name || !description || isNaN(amount) || !date) {
            Swal.fire({
                icon: 'error',
                title: 'Missing Info',
                text: 'Please fill in all required fields correctly.'
            });
            return;
        }
        
        Swal.fire({
            title: 'Print & Save?',
            text: 'Do you want to print this receipt before saving?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Print & Save',
            cancelButtonText: 'Cancel',
            reverseButtons: true
        }).then((result) => {
            if (!result.isConfirmed) return;
            const qrImageUrl = "{{ asset('images/pantukan_QR.png') }}";
            const receiptHtml = `
                <div style="font-family: 'Courier New', monospace; font-size: 12px; width: 240px; padding: 10px; text-align: center;">
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
                        <h2 style="font-size: 14px; margin-bottom: 4px;">EXPENSE RECEIPT</h2>
                        <hr style="border-top: 1px dashed #000;" />
                        <p><strong>User:</strong> ${user}</p>
                        <p><strong>Date:</strong> ${date}</p>
                        <p><strong>Name:</strong> ${name}</p>
                        <p><strong>Description:</strong> ${description}</p>
                        <p><strong>Amount:</strong> ₱${amount.toFixed(2)}</p>
                        <hr style="border-top: 1px dashed #000;" />
                        <p style="margin-top: 10px;">Generated: ${new Date().toLocaleString()}</p>
                        <p>Thank you!</p>
                    </div>
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

            // Wait for print window to close before submitting
            {{--  window.addEventListener('message', function listener(e) {
                if (e.data === 'print-done') {
                    document.getElementById('realSubmitBtn').click();
                    window.removeEventListener('message', listener);
                }
            });  --}}
               document.getElementById('realSubmitBtn').click();
        });
    }
    </script>
@endpush
