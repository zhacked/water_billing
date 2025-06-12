@extends('layouts.master')

@section('subtitle', 'Meter Reading')
@section('content_header_title', 'Meter Reading')
@section('content_header_subtitle', '')

@section('content_body')
    <div >
        <x-table 
            :headers="['Name', 'Meter Number', 'Address', 'Contact']" 
            :rows="$customers"
            :displayFields="['name', 'meter_number', 'address', 'contact_number']"
            showIndex="true"
            hideId="true"
            readingRoute="reading.meter"
            historyRoute="transaction.history"
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