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
                                    'for disconnection' => 'bg-warning text-white',
                                    'inactive' => 'bg-secondary',
                                    default => 'bg-secondary',
                                };
                            @endphp
                            <div class="d-flex flex-column">
                                <span class="badge {{ $badgeClass }}">
                                    {{ ucfirst($status) }}
                                </span>

                                @if($status != 'active')
                                    <button 
                                        class="btn btn-sm btn-outline-success mt-1 settle-button"
                                        data-name="{{ $row->name }}"
                                        data-amount="{{ $row->amount_due }}"
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
                        @else
                            {{ data_get($row, key: $field) }}
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

                @if($editRoute || $deleteRoute || $editStatus)
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

                        @if($editRoute)
                            <x-layouts.action-icon-button 
                                href="{{ route($editRoute, $row->id) }}"
                                title="Edit"
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
            const amount = button.dataset.amount;
            const user_id = button.dataset.user;
            const id = button.dataset.id;

            // Fix URL here to include the actual id
            const url = `/reconnect/${id}`;

            Swal.fire({
                title: `Payment for ${name}`,
                html: `
                    <input id="refNumber" class="swal2-input mb-2" placeholder="Enter Reference Number">
                    <input id="reconnectionFee" class="swal2-input" type="number" step="0.01" min="0" placeholder="Enter Reconnection Fee (PHP)">
                `,
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: 'Proceed to Pay',
                preConfirm: () => {
                    const refNumber = document.getElementById('refNumber').value.trim();
                    const reconnectionFee = parseFloat(document.getElementById('reconnectionFee').value.trim());

                    if (!refNumber) {
                        Swal.showValidationMessage('Reference number is required');
                        return false;
                    }

                    if (isNaN(reconnectionFee) || reconnectionFee < 0) {
                        Swal.showValidationMessage('Reconnection fee must be a valid non-negative number');
                        return false;
                    }

                    return { refNumber, reconnectionFee };
                }
            }).then(result => {
                if (result.isConfirmed) {
                    const { refNumber, reconnectionFee } = result.value;

                    fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            name: name,
                            user_id: user_id,
                            id: id,
                            reference_number: refNumber,
                            reconnection_fee: reconnectionFee
                        })
                    })
                    .then(response => {
                        if (response.ok) {
                            Swal.fire('Success', 'Payment has been processed!', 'success').then(() => {
                                location.reload();
                            });
                        } else {
                            return response.json().then(data => {
                                throw new Error(data.message || 'Unknown error');
                            });
                        }
                    })
                    .catch(err => {
                        Swal.fire('Error', err.message || 'Something went wrong.', 'error');
                    });
                }
            });
        }

    </script>

