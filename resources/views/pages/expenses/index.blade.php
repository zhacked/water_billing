@extends('layouts.master')

@section('subtitle', 'Expenses')
@section('content_header_title', 'Expenses')
@section('content_header_subtitle', 'dashboard')

@section('content_body')
    <div >
        <x-layouts.add-button route="expenses.create" label="New Expenses" />
        <x-table 
            :headers="['Name', 'Description', 'Amount', 'Date', 'User']" 
            :rows="$expenses"
            :displayFields="['name', 'description', 'amount', 'date','user_name']"
            showIndex="true"
            hideId="true"
            editRoute="expenses.edit"
            deleteRoute="expenses.destroy"
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