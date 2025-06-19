@extends('layouts.master')

@section('subtitle', 'Dashboard')
@section('content_header_title', 'Financial Dashboard')
@section('content_header_subtitle', '')

@section('content_body')

{{-- Group Filter --}}
{{--  <div class="row mb-3">
    <div class="col-md-3">
        <div class="form-group">
            <label>Select Group</label>
            <select class="form-control" id="groupSelect">
                <option value="all">All Groups</option>
                <option value="A">Group A</option>
                <option value="B">Group B</option>
                <option value="C">Group C</option>
            </select>
        </div>
    </div>
</div>  --}}

{{-- Top Financial Summary Cards --}}
<div class="row" id="dashboard-cards">
    <div class="col-md-4 group group1 group2">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>₱{{ $monthlyExpenses[0]->total_expenses ?? 0 }}</h3>
                <p>Expenses</p>
            </div>
            <div class="icon">
                <i class="fas fa-money-bill-wave"></i>
            </div>
        </div>
    </div>

    <div class="col-md-4 group group1 group3">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>₱{{ $monthlyIncome[0]->total_income ?? 0 }}</h3>
                <p>Collected</p>
            </div>
            <div class="icon">
                <i class="fas fa-piggy-bank"></i>
            </div>
        </div>
    </div>

    <div class="col-md-4 group group2 group3">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>₱{{ number_format($netProfit, 2) }}</h3>
                <p>Profit</p>
            </div>
            <div class="icon">
                <i class="fas fa-chart-line"></i>
            </div>
        </div>
    </div>
</div>

{{-- Additional Stats Cards --}}
<div class="row mt-3">
    <div class="col-md-3">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $monthlyPaidClients[0]->total_clients ?? 0 }}</h3>
                <p>Paid Customers</p>
            </div>
            <div class="icon">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $monthlyUnPaidClients[0]->total_clients ?? 0 }}</h3>
                <p>Unpaid Customers</p>
            </div>
            <div class="icon">
                <i class="fas fa-exclamation-circle"></i>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $totalClient }}</h3>
                <p>Total Clients</p>
            </div>
            <div class="icon">
                <i class="fas fa-users"></i>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="small-box bg-secondary">
            <div class="inner">
                <h3>{{ $totalStaff }}</h3>
                <p>Total Staff</p>
            </div>
            <div class="icon">
                <i class="fas fa-user-tie"></i>
            </div>
        </div>
    </div>

    {{-- Client Transactions Table --}}
    <div class="card mt-4 col-md-12">
        <div class="card card-outline card-primary mt-4">
            <div class="card-header">
                <h3 class="card-title">Client Transactions</h3>
            </div>

            <div class="card-body p-0">
                <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs" id="custom-tabs" role="tablist">
                        @php $activeTab = true; @endphp
                        @foreach($groupedTransactions as $groupName => $data)
                            <li class="nav-item">
                                <a class="nav-link @if($activeTab) active @endif" 
                                id="{{ Str::slug($groupName) }}-tab" 
                                data-toggle="tab" 
                                href="#{{ Str::slug($groupName) }}" 
                                role="tab" 
                                aria-controls="{{ Str::slug($groupName) }}" 
                                aria-selected="{{ $activeTab ? 'true' : 'false' }}">
                                    {{ $groupName }}
                                </a>
                            </li>
                            @php $activeTab = false; @endphp
                        @endforeach
                    </ul>

                    <div class="tab-content p-3">
                        @php $activeTab = true; @endphp
                        @forelse($groupedTransactions as $groupName => $data)
                            <div class="tab-pane fade @if($activeTab) show active @endif" 
                                id="{{ Str::slug($groupName) }}" 
                                role="tabpanel" 
                                aria-labelledby="{{ Str::slug($groupName) }}-tab">

                                {{-- Table Component --}}
                                <x-table 
                                    :headers="['Client Name', 'Consumption', 'Amount', 'Due Date', 'Bill Amount', 'Status']" 
                                    :rows="$data['transactions']"
                                    :displayFields="['name','consumption', 'amount','billing_date','formatted_amount_due', 'is_paid']"
                                    showIndex="true"
                                    hideId="true"
                                />

                                <div class="mt-3">
                                    <strong>Total Collectibles ({{ $groupName }}): ₱{{ number_format($data['total_due'], 2) }}</strong>
                                </div>
                            </div>
                            @php $activeTab = false; @endphp
                        @empty
                            <h1>No Record Found</h1>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>
    </div>


    
    {{-- Monthly Income Report Bar Graph --}}
    <div class="card mt-4 col-md-12">
        <div class="card-header">
            <h3 class="card-title">Monthly Income Report</h3>
        </div>
        <div class="card-body">
            <canvas id="incomeBarChart" height="100"></canvas>
        </div>
    </div>
</div>

@stop

@push('css')
    <style>
        .small-box {
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        .small-box:hover {
            transform: scale(1.05);
        }
    </style>
@endpush

@push('js')
    <script>
        document.getElementById('groupSelect').addEventListener('change', function () {
            const selectedGroup = this.value;
            const cards = document.querySelectorAll('#dashboard-cards .group');

            cards.forEach(card => {
                if (selectedGroup === 'all' || card.classList.contains(selectedGroup)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const monthlyIncomeData = @json($monthlyIncomeReport->pluck('total_income'));
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const barCtx = document.getElementById('incomeBarChart').getContext('2d');

        // fallback to fill missing months with 0
        const incomeData = Array.from({ length: 12 }, (_, i) => monthlyIncomeData[i] ?? 0);
        const totalIncome = incomeData
        .map(val => Number(val) || 0) // ensure all values are numeric
        .reduce((acc, val) => acc + val, 0)
        .toFixed(2);

        const incomeBarChart = new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: [
                    'January', 'February', 'March', 'April', 'May', 'June',
                    'July', 'August', 'September', 'October', 'November', 'December'
                ],
                datasets: [{
                    label: `Income (₱${parseFloat(totalIncome).toLocaleString(undefined, {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    })})`,
                    data: incomeData,
                    backgroundColor: 'rgba(40, 167, 69, 0.7)',
                    borderColor: 'rgba(40, 167, 69, 1)',
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `₱${context.parsed.y.toLocaleString()}`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₱' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    </script>

@endpush
