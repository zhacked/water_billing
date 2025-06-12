@extends('layouts.master')
{{-- Customize layout sections --}}
@section('subtitle', 'Dashboard')
@section('content_header_title', 'Dashboard')
@section('content_header_subtitle', 'Welcome')
{{-- Content body: main page content --}}
@section('content_body')
    <p>Code Shotcut, Welcome to this beautiful admin panel.</p>
@stop
{{-- Push extra CSS --}}
@push('css')
    {{-- Add here extra stylesheets --}}
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
@endpush
{{-- Push extra scripts --}}
@push('js')
    <script> console.log("Hi, We are using the Laravel-AdminLTE package!"); </script>
@endpush