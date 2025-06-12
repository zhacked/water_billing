@extends('layouts.master')

@section('subtitle', 'Customer')
@section('content_header_title', 'Customer')
@section('content_header_subtitle', '')

@section('content_body')
    <div >
        <x-layouts.add-button route="customer.create" label="Add User" />
        <x-table 
            :headers="['Name', 'Email', 'Contact Number','Meter Number', 'Address', 'Status']" 
            :rows="$customers"
            :displayFields="['name', 'email', 'contact_number','meter_number','address', 'status']"
            showIndex="true"
            hideId="true"
            editRoute="customer.edit"
            deleteRoute="customer.destroy"
            editStatus="customer.toggleStatus"
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