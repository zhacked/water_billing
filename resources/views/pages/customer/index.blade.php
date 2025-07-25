@extends('layouts.master')

@section('subtitle', 'Customer')
@section('content_header_title', 'Customer')
@section('content_header_subtitle', '')

@section('content_body')

        <x-layouts.add-button route="customer.create" label="Add User" />
        <x-table 
            :headers="['Client ID','Name', 'Email', 'Contact Number','Meter Number', 'Address', 'Status', 'To Collect']" 
            :rows="$customers"
            :displayFields="['account_id','name', 'email', 'contact_number','meter_number','address', 'status', 'total_unpaid_bill']"
            showIndex="true"
            hideId="true"
            editRoute="customer.edit"
            deleteRoute="customer.destroy"
            editStatus="customer.toggleStatus"
            changeMeter="customer.changeMeter"
        />
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