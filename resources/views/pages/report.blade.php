@extends('layouts.master')
{{-- Customize layout sections --}}
@section('subtitle', 'Posts')
@section('content_header_title', 'Posts')
@section('content_header_subtitle', '')

{{-- Content body: main page content --}}
@section('content_body')
    <p>Code Shortcut, Welcome to the Posts page.</p>
    
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Content</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                {{--  @foreach($posts as $post)  --}}
                <tr>
                    <td>1</td>
                    <td>2</td>
                    <td>3</td>
                    <td>4</td>
                    <td>
                        {{--  <a href="{{ route('posts.show', $post->id) }}" class="btn btn-info btn-sm">View</a>
                        <a href="{{ route('posts.edit', $post->id) }}" class="btn btn-primary btn-sm">Edit</a>
                        <form action="{{ route('posts.destroy', $post->id) }}" method="POST" style="display: inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                        </form>  --}}
                    </td>
                </tr>
                {{--  @endforeach  --}}
            </tbody>
        </table>
    </div>
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