@extends('layouts.master')

@section('subtitle', 'Staff')
@section('content_header_title', 'Staff')
@section('content_header_subtitle', '')

@section('content_body')

        <x-layouts.add-button route="staff.create" label="Add User" />
        <x-table 
            :headers="['Name', 'Email', 'Contact Number', 'Address','Group' ,'Status']" 
            :rows="$staffs"
            :displayFields="['name', 'email', 'contact_number','address', 'group.name','status']"
            showIndex="true"
            hideId="true"
            editRoute="staff.edit"
            deleteRoute="staff.destroy"
            editStatus="staff.toggleStaffStatus"
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