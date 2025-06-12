@extends('layouts.master')

@section('subtitle', 'Meter Reading')
@section('content_header_title', 'Meter Reading')
@section('content_header_subtitle', 'home')

@section('content_body')
    <div >
        <x-layouts.add-button route="meter.create" label="New Reading" />
        <x-table 
            :headers="['Name', 'Reading Date', 'Previous Reading', 'Current Reading']" 
            :rows="$meters"
            :displayFields="['name', 'formatted_reading_date', 'previous_reading', 'current_reading']"
            showIndex="true"
            hideId="true"
            editRoute="meter.edit"
            deleteRoute="meter.destroy"
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