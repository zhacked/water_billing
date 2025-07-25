@props([
    'headers' => [],
    'rows',
    'displayFields' => [],
    'editRoute' => null,
    'deleteRoute' => null,
    'readingRoute' => null,
    'historyRoute' => null,
    'paymentRoute' => null,
    'editStatus' => null,
    'changeMeter' => null,
    'showIndex' => false,
])

<form method="GET" class="mb-3">
    <div class="input-group" style="max-width: 400px;">
        <input 
            type="text" 
            name="search" 
            value="{{ request('search') }}" 
            class="form-control" 
            placeholder="Search name or meter number..."
        >
        <div class="input-group-append">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i>
            </button>
        </div>
    </div>
</form>

<table class="table table-bordered w-full">
    <thead>
        <tr>
            @if($showIndex)
                <th>#</th>
            @endif

            @foreach($headers as $header)
                <th>{{ $header }}</th>
            @endforeach

            @if($editRoute || $deleteRoute || $editStatus)
                <th>Actions</th>
            @endif
        </tr>
    </thead>

    <tbody>
        @foreach($rows as $index => $row)
            <tr>
                @if($showIndex)
                    <td>{{ $rows instanceof \Illuminate\Pagination\LengthAwarePaginator ? ($rows->firstItem() + $index) : $index + 1 }}</td>
                @endif

                @forelse ($displayFields as $field)
                    <td>
                @if($field === 'status')
                    @php
                        $status = $row->status;
                        $badgeClass = match($status) {
                            'active' => 'bg-success',
                            'for reconnection' => 'bg-warning text-white',
                            'inactive' => 'bg-secondary',
                            default => 'bg-secondary',
                        };
                    @endphp
                    <div class="d-flex flex-column">
                        <span class="badge {{ $badgeClass }}">
                            {{ ucfirst($status == 'for reconnection' ? 'For Disconnection' : $status) }}
                        </span>

                        @if($status != 'active')
                            @php
                                $isTransactionPage = request()->is('client/transaction/*');
                                $amount = $isTransactionPage ? $row->amount_due : $row->total_unpaid_bill;
                            @endphp

                            <button 
                                class="btn btn-sm btn-outline-success mt-1 settle-button"
                                data-name="{{ $row->name }}"
                                data-amount="{{ $amount }}"
                                data-user="{{ $row->account_id }}"
                                data-id="{{ $row->id }}"
                                data-url="{{ $paymentRoute }}"
                            >
                                Reconnect 🧩
                            </button>
                        @endif
                    </div>

                @elseif($field === 'is_paid')
                    <span class="badge inline-block px-3 py-1 rounded-full text-white text-sm font-semibold 
                        {{ $row->is_paid ? 'bg-success' : 'bg-danger' }}">
                        {{ $row->is_paid ? 'Paid' : 'Not Paid' }}
                    </span>

                @elseif($field === 'total_unpaid_bill')
                    ₱{{ number_format(data_get($row, $field), 2) }}

                @else
                    {{ data_get($row, $field) }}
                @endif
            </td>
                    
                @empty
                    <td>
                        <p>No Record Found</p>
                    </td>
                @endforelse

                @if($readingRoute || $historyRoute || $paymentRoute)
                    <td>
                        @if ($readingRoute && (auth()->user()->role === 'plumber' || auth()->user()->role === 'admin'))
                            @php
                                $latestBill = $row->bills->sortByDesc('billing_date')->first();
                            @endphp
                            {{--  @if (optional( $latestBill?->billing_date )->isToday() && $latestBill != null  )  --}}
                                <x-layouts.action-icon-button 
                                    href="{{ route($readingRoute, $row->id) }}"
                                    title="Read meter"
                                    icon="front"
                                    color="primary"
                                />
                            {{--  @endif  --}}
                        @endif

                        @if($historyRoute && (auth()->user()->role === 'cashier' || auth()->user()->role === 'admin'))
                            <x-layouts.action-icon-button 
                                href="{{ route($historyRoute, $row->id) }}"
                                title="History"
                                icon="eye"
                                color="primary"
                            />
                        @endif

                        @if($paymentRoute && !$row->is_paid )
                            <x-layouts.action-icon-button 
                                href="javascript:void(0);"
                                class="payment-button"
                                title="Payment"
                                icon="cash-coin"
                                color="primary"
                                data-name="{{ $row->user->name }}"
                                data-amount="{{ $row->amount_due }}"
                                data-user="{{ $row->user_id }}"
                                data-id="{{ $row->id }}"
                            />
                        @endif
                    </td>
                @endif

                @if($editRoute || $deleteRoute || $editStatus || $changeMeter)
                    <td>
                        @if($editStatus)
                            <x-layouts.action-icon-button 
                                :form="[
                                    'action' => route($editStatus, $row->id),
                                    'method' => 'POST',
                                    'spoof' => 'PATCH',
                                    'class' => 'status-form'
                                ]"
                                type="button"
                                :color="$row->status === 'active' ? 'warning' : 'success'"
                                :icon="$row->status === 'active' ? 'slash-circle' : 'check-circle'"
                                :title="ucfirst($row->status === 'active' ? 'Deactivate' : 'Activate')"
                                class="status-button"
                            />
                        @endif

                        @php
                            $unpaidBills = $row->bills->where('is_paid', false);
                            $currentBill = $unpaidBills->sum(function($bill) {
                                return $bill->amount_due + ($bill->penalty ?? 0);
                            });

                            $prevBill = $row->bills->sortByDesc('billing_date')->first();
                        @endphp

                        @if($changeMeter)
                            <x-layouts.action-icon-button 
                                href="javascript:void(0);"
                                class="change-meter-button"
                                icon="fa fa-reply-all"
                                color="success"
                                data-name="{{ $row->name }}"
                                data-prev-amount="{{ $prevBill?->amount_due ?? 0 }}"
                                data-prev-date="{{ $prevBill?->billing_date ? \Carbon\Carbon::parse($prevBill->billing_date)->format('M d, Y') : 'N/A' }}"
                                data-current-bill="{{ number_format($currentBill, 2) }}"
                                data-url="{{ route($changeMeter, $row->id) }}"
                                data-method="PATCH"
                            />
                        @endif



                        @if($editRoute)
                            <x-layouts.action-icon-button 
                                href="{{ route($editRoute, $row->id) }}"
                                icon="pencil"
                                color="primary"
                            />
                        @endif

                        @if($deleteRoute)
                            <x-layouts.action-icon-button 
                                :form="[
                                    'action' => route($deleteRoute, $row->id),
                                    'method' => 'POST',
                                    'spoof' => 'DELETE',
                                    'class' => 'delete-form'
                                ]"
                                type="button"
                                color="danger"
                                icon="trash"
                                title="Delete"
                                class="delete-button"
                            />
                        @endif
                    </td>
                @endif
            </tr>
        @endforeach
    </tbody>
</table>


@if ($rows instanceof \Illuminate\Pagination\LengthAwarePaginator || $rows instanceof \Illuminate\Pagination\Paginator)
    <div class="card-footer clearfix">
        <div class="float-left">
            Showing {{ $rows->firstItem() }} to {{ $rows->lastItem() }} of {{ $rows->total() }} entries
        </div>
        <div class="float-right">
            {{ $rows->appends(request()->query())->links('pagination::bootstrap-4') }}
        </div>
    </div>
@endif
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Delete confirmation
        document.querySelectorAll('.delete-button').forEach(button => {
            button.addEventListener('click', function () {
                const form = this.closest('form');

                Swal.fire({
                    title: 'Delete this item?',
                    text: "This action cannot be undone.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel',
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });

        // Status toggle confirmation
        document.querySelectorAll('.status-button').forEach(button => {
            button.addEventListener('click', function () {
                const form = this.closest('form');

                Swal.fire({
                    title: 'Change user status?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, change it',
                    cancelButtonText: 'Cancel',
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });

        // Handle payment buttons (both original and "Settle Now")
        document.addEventListener('click', function (event) {
            const button = event.target.closest('.payment-button') || event.target.closest('.settle-button');

            if (button) {
                showPaymentModal(button);
            }
        });

        function showPaymentModal(button) {
            const name = button.dataset.name;
            const amount = parseFloat(button.dataset.amount);
            const userId = button.dataset.user;
            const billId = button.dataset.id;
            const path = window.location.pathname;

            const isClientTransaction = path.startsWith('/client/transaction/');
            const isCustomerPath = path.startsWith('/customer');
            const url = isClientTransaction ? '/payment' : `/reconnect/${billId}`;

            const fixedReconnectionFee = isCustomerPath ? 1500 : 0;
            const totalAmount = amount + fixedReconnectionFee;

            const referenceInput = `<input id="refNumber" class="swal2-input mb-2" placeholder="Enter Reference Number">`;

            const amountDetails = `
                <div id="amountText" class="swal2-html-container" style="font-weight: bold; text-align: left;">
                    ${!isClientTransaction ? `<p>Amount: ₱${amount.toFixed(2)}</p>` : ''}
                    ${isCustomerPath ? `<p>Reconnection Fee: ₱${fixedReconnectionFee.toFixed(2)}</p>` : ''}
                    <p>Total Amount to Settle: ₱${totalAmount.toFixed(2)}</p>
                </div>`;

            Swal.fire({
                title: `Payment for ${name}`,
                html: referenceInput + amountDetails,
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: 'Proceed to Pay',
                preConfirm: () => {
                    const refNumber = document.getElementById('refNumber')?.value.trim();
                    if (!refNumber) {
                        Swal.showValidationMessage('Reference number is required');
                        return false;
                    }

                    const reconnectionFee = isCustomerPath ? totalAmount : amount;

                    return { refNumber, reconnectionFee };
                }
            }).then(result => {
                if (!result.isConfirmed) return;

                const { refNumber, reconnectionFee } = result.value;

                fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        name,
                        user_id: userId,
                        id: billId,
                        reference_number: refNumber,
                        reconnection_fee: reconnectionFee
                    })
                })
                .then(() => {
                    
                    Swal.fire('Success', 'Payment has been processed!', 'success')
                        .then(() => location.reload());
                })
                .catch(err => {
                    console.log(err);
                    Swal.fire('Error', err.message || 'Something went wrong.', 'error');
                });
            });
        }


    </script>
    <script>
        document.querySelectorAll('.change-meter-button').forEach(button => {
            button.addEventListener('click', async function () {
                const name = this.dataset.name || 'N/A';
                const prevAmount = parseFloat(this.dataset.prevAmount || 0).toFixed(2);
                const prevDate = this.dataset.prevDate || 'N/A';
                const currentBill = this.dataset.currentBill || '0.00';
                const url = this.dataset.url;

                Swal.fire({
                    title: `Change Meter for ${name}?`,
                    html: `
                        <div style="text-align: left; font-size: 1rem;">
                            <h4>Your Meter Reading will be reset to Zero</h4>
                            <p><strong>Last Bill:</strong></p>
                            <ul style="margin-left: 1rem;">
                                <li><strong>Amount:</strong> ₱${prevAmount}</li>
                                <li><strong>Date:</strong> ${prevDate}</li>
                            </ul>
                            <hr>
                            <p><strong>Total Unpaid Bills:</strong> <span style="color:red;">₱${currentBill}</span></p>
                            <hr>
                            <label for="newMeterNumber"><strong>New Meter Number:</strong></label>
                            <input type="text" id="newMeterNumber" class="swal2-input" placeholder="Enter new meter number" required>
                        </div>
                    `,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, change it',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true,
                    preConfirm: () => {
                        const meterNumber = document.getElementById('newMeterNumber').value.trim();
                        if (!meterNumber) {
                            Swal.showValidationMessage('⚠️ Please enter the new meter number.');
                            return false;
                        }
                        return meterNumber;
                    }
                }).then(result => {
                    if (result.isConfirmed) {
                        const meterNumber = result.value;

                        fetch(url, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({
                                meterNumber
                            })
                        })
                        .then(data => {
                            Swal.fire('✅ Success!', 'Meter number updated successfully.', 'success');
                        })
                        .catch(err => {
                            console.error(err);
                            Swal.fire('❌ Error', 'Something went wrong while updating.', 'error');
                        });
                    }
                });
            });
        });
        </script>
