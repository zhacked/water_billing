@extends('layouts.master')

@section('subtitle', 'groups')
@section('content_header_title', 'groups')
@section('content_header_subtitle', 'dashboard')

@section('content_body')
    <div >
        <x-layouts.add-button route="groups.create" label="New groups" />
        <x-table 
            :headers="['Name', 'Description']" 
            :rows="$groups"
            :displayFields="['name', 'description']"
            showIndex="true"
            hideId="true"
            editRoute="groups.edit"
            deleteRoute="groups.destroy"
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