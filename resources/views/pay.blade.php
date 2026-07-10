<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Razorpay Dashboard</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>

    <style>
        body {
            background: #f5f7fb
        }

        .stat-card {
            border: none;
            border-radius: 15px
        }

        .table-container {
            background: #fff;
            border-radius: 15px;
            padding: 20px
        }
    </style>
</head>

<body>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="container py-4">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Razorpay Payment Dashboard</h2>
            <button id="payBtn" class="btn btn-primary btn-lg">
                💳 Pay
            </button>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-2">
                <div class="card bg-primary text-white shadow">
                    <div class="card-body text-center">
                        <h6>Total</h6>
                        <h3>{{ $totalPayments }}</h3>
                    </div>
                </div>
            </div>

            <div class="col-md-2">
                <div class="card bg-success text-white shadow">
                    <div class="card-body text-center">
                        <h6>Success</h6>
                        <h3>{{ $successPayments }}</h3>
                    </div>
                </div>
            </div>

            <div class="col-md-2">
                <div class="card bg-danger text-white shadow">
                    <div class="card-body text-center">
                        <h6>Failed</h6>
                        <h3>{{ $failedPayments }}</h3>
                    </div>
                </div>
            </div>

            <div class="col-md-2">
                <div class="card bg-warning shadow">
                    <div class="card-body text-center">
                        <h6>Pending</h6>
                        <h3>{{ $pendingPayments }}</h3>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card bg-dark text-white shadow">
                    <div class="card-body text-center">
                        <h6>Total Revenue</h6>
                        <h3>₹{{ number_format($totalRevenue, 2) }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-container shadow-sm">

            <form method="GET" action="{{ url('/') }}" class="row g-2 mb-4">
                <div class="col-md-4">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                        placeholder="Search Order ID or Payment ID">
                </div>

                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>Success</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <select name="sort" class="form-select">
                        <option value="">Latest</option>
                        <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Oldest</option>
                        <option value="amount_asc" {{ request('sort') == 'amount_asc' ? 'selected' : '' }}>Amount ASC
                        </option>
                        <option value="amount_desc" {{ request('sort') == 'amount_desc' ? 'selected' : '' }}>Amount DESC
                        </option>
                    </select>
                </div>

                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-success w-100">
                        Filter
                    </button>

                    <a href="{{ url('/') }}" class="btn btn-secondary">
                        Reset
                    </a>
                </div>
            </form>

            <h5 class="mb-3">
                Payment History
                <span class="badge bg-primary">
                    {{ $payments->total() }}
                </span>
            </h5>

            <div class="table-responsive">

                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Order ID</th>
                            <th>Payment ID</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                                            <tr>
                                                <td>{{ $payments->firstItem() + $loop->index }}</td>
                                                <td>{{ $payment->order_id }}</td>
                                                <td>{{ $payment->payment_id ?? '-' }}</td>
                                                <td>₹{{ $payment->amount }}</td>
                                                <td>
                                                    <span
                                                        class="badge bg-{{
                            $payment->status == 'success' ? 'success' :
                            ($payment->status == 'failed' ? 'danger' : 'warning')
                                                                                                                                                                                                                                                }}">
                                                        {{ ucfirst($payment->status) }}
                                                    </span>
                                                </td>
                                                <td>{{ $payment->created_at->format('d M Y h:i A') }}</td>
                                                <td>
                                                    <form action="{{ route('payments.destroy', $payment->id) }}" method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="btn btn-danger btn-sm"
                                                            onclick="return confirm('Delete this payment?')">
                                                            Delete
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">No payments found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-4">
                @if ($payments->lastPage() > 1)
                    <div class="d-flex justify-content-center mt-4">
                        <nav>
                            <ul class="pagination">

                                @for ($i = 1; $i <= $payments->lastPage(); $i++)
                                    <li class="page-item {{ $payments->currentPage() == $i ? 'active' : '' }}">
                                        <a class="page-link" href="{{ $payments->url($i) }}">
                                            {{ $i }}
                                        </a>
                                    </li>
                                @endfor

                            </ul>
                        </nav>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        document.getElementById('payBtn').onclick = function () {

            fetch('/create-order', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
                .then(res => res.json())
                .then(order => {

                    var options = {
                        key: "{{ config('services.razorpay.key') }}",
                        amount: order.amount,
                        currency: "INR",
                        order_id: order.id,
                        name: "Laravel Razorpay",
                        description: "Payment",
                        handler: function (response) {

                            fetch('/payment-success', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                },
                                body: JSON.stringify(response)
                            }).then(() => location.reload());
                        }
                    };

                    var rzp = new Razorpay(options);
                    rzp.open();
                });
        };
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>