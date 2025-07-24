@extends('layouts.master')

@section('subtitle', 'Report')
@section('content_header_title', 'Report')
@section('content_header_subtitle', '')

@section('content_body')
    <p class="mb-3">Generate Client Record</p>

    {{-- Dropdown Form --}}
    <form method="GET" action="{{ route('record.index') }}" class="mb-4">
        <div class="form-row align-items-end">
            {{-- Group Filter --}}
            <div class="form-group mr-3">
                <label for="group_id">Select Group</label>
                <select name="group_id" id="group_id" class="form-control">
                    <option value="">-- Choose Group --</option>
                    @foreach ($groups as $group)
                        <option value="{{ $group->id }}" {{ request('group_id') == $group->id ? 'selected' : '' }}>
                            {{ $group->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- From Date --}}
            <div class="form-group mr-3">
                <label for="from_date">From</label>
                <input type="date" name="from_date" id="from_date" class="form-control"
                    value="{{ request('from_date') }}">
            </div>

            {{-- To Date --}}
            <div class="form-group mr-3">
                <label for="to_date">To</label>
                <input type="date" name="to_date" id="to_date" class="form-control"
                    value="{{ request('to_date') }}">
            </div>

            {{-- Submit --}}
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
            
            <div class="form-group ml-2">
                <button type="submit" name="export" value="csv" class="btn btn-success">
                    <i class="fas fa-file-csv"></i> Generate CSV
                </button>
            </div>
        </div>
    </form>


    {{-- Table of Transactions --}}
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Amount Due</th>
                    <th>Billing Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($groupedTransactions as $groupName => $groupData)
                    @foreach ($groupData['transactions'] as $transaction)
                        <tr>
                            <td>{{ $transaction->id }}</td>
                            <td>{{ $transaction->user->name ?? 'N/A' }}</td>
                            <td>₱{{ number_format($transaction->amount_due, 2) }}</td>
                            <td>{{ \Carbon\Carbon::parse($transaction->billing_date)->format('F j, Y') }}</td>
                            <td>
                                <span class="badge {{ $transaction->is_paid ? 'badge-success' : 'badge-danger' }}">
                                    {{ $transaction->is_paid ? 'Paid' : 'Not Paid' }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                @empty
                    <tr>
                        <td colspan="5" class="text-center">No records found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@stop

@push('css')
{{-- Your custom styles here --}}
@endpush

@push('js')
<script>
    console.log("Group filter working like a charm 🚀");
</script>
@endpush
