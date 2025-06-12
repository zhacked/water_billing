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

                @foreach($displayFields as $field)
                    <td>
                       @if($field === 'status')
                            <span class="badge {{ $row->status === 'active' ? 'bg-success' : 'bg-danger' }}">
                                {{ ucfirst($row->status) }}
                            </span>
                        @elseif($field === 'is_paid')
                            {{--  {{ $row->is_paid ?  'bg-green-500' : 'bg-red-500' }}  --}}
                            <span class="badge  inline-block px-3 py-1 rounded-full text-white text-sm font-semibold 
                                {{ $row->is_paid ? 'bg-success' : 'bg-danger' }}">
                                {{ $row->is_paid ? 'Paid' : 'Not Paid' }}
                            </span>
                        @else
                            {{ data_get($row, $field) }}
                        @endif
                    </td>
                @endforeach
                

                @if($readingRoute || $historyRoute || $paymentRoute)
                   
                    <td>
                        @if($readingRoute )
                            <x-layouts.action-icon-button 
                                        href="{{ route($readingRoute, $row->id) }}"
                                        title="Read meter"
                                        icon="front"
                                        color="primary"
                            />
                        @endif
                        @if($historyRoute)
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
                                data-amount="{{ $row->amount_due}}"
                                data-user="{{ $row->user_id}}"
                                data-id="{{ $row->id}}"
                                data-url="{{ $paymentRoute }}"
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



    <div class="clearfix">
        {{ $rows->links('vendor.pagination.bootstrap-5') }}
    </div>


    {{-- SweetAlert2 --}}
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
    </script>

    <script>
        document.addEventListener('click', function (event) {
            const button = event.target.closest('.payment-button');

            if (button) {
                showPaymentModal(button);
            }
        });

        function showPaymentModal(button) {
            const name = button.dataset.name;
            const amount = button.dataset.amount;
            const url = button.dataset.url;
            const user_id = button.dataset.user;
            const id = button.dataset.id;
            const formattedAmount = new Intl.NumberFormat('en-PH', {
                style: 'currency',
                currency: 'PHP'
            }).format(amount);

            Swal.fire({
                title: `Payment for ${name}`,
                html: `<p>Amount Due: <strong>${formattedAmount}</strong></p>`,
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: 'Proceed to Pay',
            }).then(result => {
                if (result.isConfirmed) {
                    fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            name: name,
                            amount: amount,
                            user_id: user_id,
                            id: id
                        })
                    })
                    .then(data => {
    
                        if (data.status = 200) {
                            Swal.fire('Success', 'Payment has been processed!', 'success');
                        } else {
                            Swal.fire('Oops!', data.message || 'Something went wrong.', 'error');
                        }
                    })
                
                }
            });
        }

        </script>
