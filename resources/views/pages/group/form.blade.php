@extends('layouts.master')

@section('subtitle', isset($groups) ? 'Edit groups' : 'Add New groups')
@section('content_header_title', isset($groups) ? 'Update groups' : 'Create groups')

@section('content_body')
<div class="row">
    <div class="col-md-12 mt-4">

        {{-- Back Button --}}
        <a href="{{ route('groups.index') }}" class="btn btn-secondary mb-3">
            <i class="fas fa-arrow-left"></i> Back
        </a>

        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">{{ isset($groups) ? 'Edit' : 'New' }} groups Reading</h3>
            </div>

            <form 
                method="POST" 
                action="{{ isset($groups) ? route('groups.update', $groups->id) : route('groups.store') }}"
            >
                @csrf
                @if(isset($groups))
                    @method('PUT')
                @endif

                <div class="card-body">
                    <x-form.input 
                        label="Name" 
                        name="name" 
                        type="text" 
                        placeholder="Enter groups name" 
                        :value="old('name', $groups->name ?? '')" 
                        required 
                    />

                    <x-form.input 
                        label="description" 
                        name="description" 
                        type="text" 
                        placeholder="Enter groups description" 
                        :value="old('description', $groups->description ?? '')" 
                        required 
                    />
                </div>

                <div class="card-footer text-right">
                    <x-form.submit-button 
                        :label="isset($groups) ? 'Update' : 'Submit'" 
                        class="btn btn-success" 
                    />
                </div>
            </form>
        </div>
    </div>
</div>
@stop
