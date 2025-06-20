@extends('layouts.master')

@section('subtitle', isset($category) ? 'Edit category' : 'Add New category')
@section('content_header_title', isset($category) ? 'Update category' : 'Create category')

@section('content_body')
<div class="row">
    <div class="col-md-12 mt-4">

        {{-- Back Button --}}
        <a href="{{ route('category.index') }}" class="btn btn-secondary mb-3">
            <i class="fas fa-arrow-left"></i> Back
        </a>

        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">{{ isset($category) ? 'Edit' : 'New' }} category Reading</h3>
            </div>

            <form 
                method="POST" 
                action="{{ isset($category) ? route('category.update', $category->id) : route('category.store') }}"
            >
                @csrf
                @if(isset($category))
                    @method('PUT')
                @endif

                <div class="card-body">

                    <x-form.input 
                        label="Name" 
                        name="name" 
                        type="text" 
                        placeholder="Enter category name" 
                        :value="old('name', $category->name ?? '')" 
                        required 
                    />

                    <x-form.input 
                        label="Amount " 
                        name="amount" 
                        type="number" 
                        placeholder="Enter amount" 
                        :value="old('amount', $category->amount ?? '')" 
                        required 
                    />
                </div>

                <div class="card-footer text-right">
                    <x-form.submit-button 
                        :label="isset($category) ? 'Update' : 'Submit'" 
                        class="btn btn-success" 
                    />
                </div>
            </form>
        </div>
    </div>
</div>
@stop
