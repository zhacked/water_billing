@extends('layouts.master')

@section('subtitle', 'category')
@section('content_header_title', 'category')
@section('content_header_subtitle', 'dashboard')

@section('content_body')
    <div >
        <x-layouts.add-button route="category.create" label="New category" />
        <x-table 
            :headers="['Name', 'Amount']" 
            :rows="$category"
            :displayFields="['name', 'amount']"
            showIndex="true"
            hideId="true"
            editRoute="category.edit"
            deleteRoute="category.destroy"
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