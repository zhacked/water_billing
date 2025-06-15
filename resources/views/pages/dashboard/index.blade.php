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
                <p>Capital</p>
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
        <div class="card-header">
            <h3 class="card-title">Client Transactions</h3>
        </div>

        <div class="card-body table-responsive p-0">
            <x-table 
                :headers="['Previous Reading', 'Present Reading', 'Consumption', 'Amount', 'Date', 'Bill Amount','Status' ]" 
                :rows="$transaction"
                :displayFields="['previous_reading', 'current_reading', 'consumption', 'amount','billing_date','formatted_amount_due', 'is_paid']"
                showIndex="true"
                hideId="true"
            />
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

        const incomeBarChart = new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: [
                    'January', 'February', 'March', 'April', 'May', 'June',
                    'July', 'August', 'September', 'October', 'November', 'December'
                ],
                datasets: [{
                    label: 'Monthly Income (₱)',
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
