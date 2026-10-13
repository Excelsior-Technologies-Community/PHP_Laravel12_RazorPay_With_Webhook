<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Razorpay Payment Studio & Live Webhook Radar</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Bootstrap 5.3 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Razorpay Checkout & Chart.js -->
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- SweetAlert2 for rich alerts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            background: #f4f6fb;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }

        .stat-card {
            border: none;
            border-radius: 16px;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08) !important;
        }

        .table-container {
            background: #fff;
            border-radius: 16px;
            padding: 24px;
        }

        .chart-box {
            height: 300px;
        }

        .chart-box-large {
            height: 320px;
        }

        .chip-btn {
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .chip-btn.active, .chip-btn:hover {
            background-color: #0d6efd;
            color: #fff !important;
            border-color: #0d6efd;
        }

        .badge-subtle-success { background: #d1e7dd; color: #0f5132; }
        .badge-subtle-danger { background: #f8d7da; color: #842029; }
        .badge-subtle-warning { background: #fff3cd; color: #664d03; }
        .badge-subtle-info { background: #cff4fc; color: #055160; }
        .badge-subtle-purple { background: #e2d9f3; color: #432874; }

        .code-pre {
            background: #1e1e2f;
            color: #50fa7b;
            padding: 12px;
            border-radius: 8px;
            max-height: 250px;
            overflow-y: auto;
            font-size: 0.82rem;
        }
    </style>
</head>

<body>

    <!-- Header Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2 fw-bold" href="{{ url('/') }}">
                <span class="badge bg-primary p-2 fs-6">⚡</span> Razorpay Studio & Webhook Radar
            </a>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-light btn-sm" onclick="document.getElementById('webhook-section').scrollIntoView({behavior: 'smooth'})">
                    <i class="bi bi-broadcast"></i> Webhook Radar
                </button>
                <button type="button" class="btn btn-primary fw-bold d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#dynamicCheckoutModal">
                    <i class="bi bi-credit-card"></i> Pay Now (Dynamic Studio)
                </button>
            </div>
        </div>
    </nav>

    <div class="container py-4">

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <!-- Quick Stats Matrix -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-2">
                <div class="card stat-card bg-primary text-white shadow-sm">
                    <div class="card-body text-center p-3">
                        <small class="text-white-50 text-uppercase fw-bold">Total</small>
                        <h3 class="fw-bold mb-0 mt-1">{{ $totalPayments }}</h3>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-2">
                <div class="card stat-card bg-success text-white shadow-sm">
                    <div class="card-body text-center p-3">
                        <small class="text-white-50 text-uppercase fw-bold">Success</small>
                        <h3 class="fw-bold mb-0 mt-1">{{ $successPayments }}</h3>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-2">
                <div class="card stat-card bg-danger text-white shadow-sm">
                    <div class="card-body text-center p-3">
                        <small class="text-white-50 text-uppercase fw-bold">Failed</small>
                        <h3 class="fw-bold mb-0 mt-1">{{ $failedPayments }}</h3>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-2">
                <div class="card stat-card bg-warning text-dark shadow-sm">
                    <div class="card-body text-center p-3">
                        <small class="text-dark-50 text-uppercase fw-bold">Pending</small>
                        <h3 class="fw-bold mb-0 mt-1">{{ $pendingPayments }}</h3>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-2">
                <div class="card stat-card bg-secondary text-white shadow-sm">
                    <div class="card-body text-center p-3">
                        <small class="text-white-50 text-uppercase fw-bold">Refunded</small>
                        <h3 class="fw-bold mb-0 mt-1">{{ $refundedPayments }}</h3>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-2">
                <div class="card stat-card bg-dark text-white shadow-sm">
                    <div class="card-body text-center p-3">
                        <small class="text-white-50 text-uppercase fw-bold">Revenue</small>
                        <h4 class="fw-bold text-warning mb-0 mt-1">₹{{ number_format($totalRevenue, 0) }}</h4>
                        @if($totalRefunded > 0)
                        <small class="text-danger-50" style="font-size:0.75rem;">(₹{{ number_format($totalRefunded, 0) }} Ref)</small>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart.js Analytics Grid -->
        <div class="row g-3 mb-4">
            <div class="col-lg-6">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-header bg-white py-3 fw-bold border-0 d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-graph-up text-primary me-2"></i> Monthly Revenue Trend</span>
                        <span class="badge bg-light text-dark">₹ INR</span>
                    </div>
                    <div class="card-body chart-box">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-header bg-white py-3 fw-bold border-0 d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-pie-chart text-success me-2"></i> Payment Status Ratio</span>
                        <span class="badge bg-light text-dark">Live Metrics</span>
                    </div>
                    <div class="card-body chart-box">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment History Table with Filters & 1-Click Refund -->
        <div class="table-container shadow-sm mb-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                <h5 class="fw-bold mb-0">
                    <i class="bi bi-receipt-cutoff text-primary me-2"></i> Payment Records & Refund Management
                    <span class="badge bg-primary rounded-pill ms-2">{{ $payments->total() }}</span>
                </h5>

                <button class="btn btn-outline-primary btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#dynamicCheckoutModal">
                    <i class="bi bi-plus-circle me-1"></i> New Payment
                </button>
            </div>

            <!-- Filter & Search Form -->
            <form method="GET" action="{{ url('/') }}" class="row g-2 mb-4">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                            placeholder="Search Order, Payment, Name, Email...">
                    </div>
                </div>

                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>Success</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="refunded" {{ request('status') == 'refunded' ? 'selected' : '' }}>Refunded</option>
                        <option value="partially_refunded" {{ request('status') == 'partially_refunded' ? 'selected' : '' }}>Partially Refunded</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <select name="sort" class="form-select">
                        <option value="">Latest First</option>
                        <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Oldest First</option>
                        <option value="amount_asc" {{ request('sort') == 'amount_asc' ? 'selected' : '' }}>Amount: Low to High</option>
                        <option value="amount_desc" {{ request('sort') == 'amount_desc' ? 'selected' : '' }}>Amount: High to Low</option>
                    </select>
                </div>

                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-primary w-100 fw-semibold">Filter</button>
                    <a href="{{ url('/') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle border">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Order & Customer</th>
                            <th>Gateway IDs</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Refund Matrix</th>
                            <th>Date</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                        <tr>
                            <td class="text-muted fw-bold">{{ $payments->firstItem() + $loop->index }}</td>
                            <td>
                                <div class="fw-bold">{{ $payment->order_id }}</div>
                                @if($payment->customer_name || $payment->customer_email)
                                <small class="text-muted d-block">
                                    <i class="bi bi-person"></i> {{ $payment->customer_name ?? 'Guest' }}
                                    @if($payment->customer_email) &bull; {{ $payment->customer_email }} @endif
                                </small>
                                @endif
                                @if($payment->notes)
                                <small class="badge bg-light text-secondary border mt-1">{{ Str::limit($payment->notes, 30) }}</small>
                                @endif
                            </td>
                            <td>
                                <div><small class="text-muted">Pay ID:</small> <code>{{ $payment->payment_id ?? 'None' }}</code></div>
                                @if($payment->refund_id)
                                <div><small class="text-muted">Ref ID:</small> <code class="text-danger">{{ $payment->refund_id }}</code></div>
                                @endif
                                @if($payment->failure_reason)
                                <small class="text-danger d-block mt-1"><i class="bi bi-x-circle"></i> {{ Str::limit($payment->failure_reason, 40) }}</small>
                                @endif
                            </td>
                            <td>
                                <div class="fs-6 fw-bold">₹{{ number_format($payment->amount, 2) }}</div>
                                @if($payment->refunded_amount > 0)
                                <small class="text-danger fw-semibold d-block">-₹{{ number_format($payment->refunded_amount, 2) }} Ref</small>
                                @endif
                            </td>
                            <td>
                                @if($payment->status === 'success')
                                    <span class="badge bg-success"><i class="bi bi-check-circle"></i> Success</span>
                                @elseif($payment->status === 'refunded')
                                    <span class="badge bg-danger"><i class="bi bi-arrow-counterclockwise"></i> Refunded</span>
                                @elseif($payment->status === 'partially_refunded')
                                    <span class="badge bg-warning text-dark"><i class="bi bi-pie-chart"></i> Partial Refund</span>
                                @elseif($payment->status === 'failed')
                                    <span class="badge bg-danger"><i class="bi bi-x-circle"></i> Failed</span>
                                @else
                                    <span class="badge bg-secondary"><i class="bi bi-hourglass-split"></i> Pending</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $remainingBalance = $payment->amount - ($payment->refunded_amount ?? 0);
                                @endphp
                                @if($payment->status === 'success' || $payment->status === 'partially_refunded')
                                    @if($remainingBalance > 0)
                                    <button class="btn btn-outline-danger btn-sm fw-semibold refund-btn"
                                        data-id="{{ $payment->id }}"
                                        data-order="{{ $payment->order_id }}"
                                        data-amount="{{ $payment->amount }}"
                                        data-remaining="{{ $remainingBalance }}">
                                        <i class="bi bi-arrow-counterclockwise"></i> Refund
                                    </button>
                                    @else
                                    <span class="badge badge-subtle-success">Fully Refunded</span>
                                    @endif
                                @else
                                    <span class="text-muted small">N/A</span>
                                @endif
                            </td>
                            <td class="text-nowrap small text-muted">
                                {{ $payment->created_at->format('d M Y') }}<br>
                                {{ $payment->created_at->format('h:i A') }}
                            </td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('payment.receipt', $payment->id) }}" class="btn btn-success btn-sm" title="PDF Receipt">
                                    <i class="bi bi-file-earmark-pdf"></i>
                                </a>

                                <form action="{{ route('payments.destroy', $payment->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-outline-secondary btn-sm" onclick="return confirm('Delete this transaction record?')" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-2 text-muted"></i>
                                No payment transactions found matching the filter criteria.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <small class="text-muted">Showing {{ $payments->firstItem() ?? 0 }} to {{ $payments->lastItem() ?? 0 }} of {{ $payments->total() }} records</small>
                <div>
                    {{ $payments->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>

        <!-- Live Webhook Audit Radar Section -->
        <div id="webhook-section" class="card shadow-sm border-0 rounded-4 mb-5">
            <div class="card-header bg-dark text-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h5 class="fw-bold mb-0 d-flex align-items-center gap-2">
                        <span class="spinner-grow spinner-grow-sm text-success" role="status"></span>
                        Live Webhook Audit Radar & Event Logger
                    </h5>
                    <small class="text-light-50">Monitoring Razorpay Webhook Callbacks (payment.captured, payment.failed, refund.processed)</small>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-info btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#simulateWebhookModal">
                        <i class="bi bi-play-circle"></i> Simulate Webhook
                    </button>
                    @if(count($webhookLogs) > 0)
                    <form action="{{ route('webhook.clear') }}" method="POST" class="d-inline">
                        @csrf
                        <button class="btn btn-outline-danger btn-sm" onclick="return confirm('Clear all webhook audit logs?')">
                            <i class="bi bi-trash3"></i> Clear Logs
                        </button>
                    </form>
                    @endif
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Timestamp</th>
                                <th>Event Type</th>
                                <th>Status</th>
                                <th>Associated IDs</th>
                                <th>Payload Preview</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($webhookLogs as $log)
                            <tr>
                                <td class="text-nowrap small">
                                    <strong>{{ $log->created_at->format('d M, h:i:s A') }}</strong>
                                    <div class="text-muted" style="font-size:0.75rem;">{{ $log->created_at->diffForHumans() }}</div>
                                </td>
                                <td>
                                    @if(str_contains($log->event, 'captured') || str_contains($log->event, 'paid'))
                                        <span class="badge bg-success"><i class="bi bi-check-circle"></i> {{ $log->event }}</span>
                                    @elseif(str_contains($log->event, 'failed'))
                                        <span class="badge bg-danger"><i class="bi bi-x-octagon"></i> {{ $log->event }}</span>
                                    @elseif(str_contains($log->event, 'refund'))
                                        <span class="badge bg-warning text-dark"><i class="bi bi-arrow-repeat"></i> {{ $log->event }}</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $log->event }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($log->status === 'verified')
                                        <span class="badge badge-subtle-success"><i class="bi bi-shield-check"></i> Verified Signature</span>
                                    @elseif($log->status === 'simulated')
                                        <span class="badge badge-subtle-info"><i class="bi bi-cpu"></i> Radar Simulated</span>
                                    @else
                                        <span class="badge badge-subtle-warning">{{ $log->status }}</span>
                                    @endif
                                </td>
                                <td class="small">
                                    @if($log->order_id)
                                    <div><span class="text-muted">Order:</span> <code>{{ $log->order_id }}</code></div>
                                    @endif
                                    @if($log->payment_id)
                                    <div><span class="text-muted">Payment:</span> <code>{{ $log->payment_id }}</code></div>
                                    @endif
                                </td>
                                <td>
                                    <button class="btn btn-outline-dark btn-sm py-0 px-2 view-payload-btn" 
                                        data-payload="{{ json_encode($log->payload, JSON_PRETTY_PRINT) }}"
                                        data-headers="{{ json_encode($log->headers, JSON_PRETTY_PRINT) }}">
                                        <i class="bi bi-code-slash"></i> View JSON
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    <i class="bi bi-broadcast fs-3 d-block mb-1 text-muted"></i>
                                    No webhook events recorded yet. Configure your Razorpay webhook URL to <code>{{ url('/razorpay-webhook') }}</code> or use the <strong>Simulate Webhook</strong> tool above.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-light py-2 text-muted small d-flex justify-content-between align-items-center">
                <span>Webhook Endpoint: <code>{{ url('/razorpay-webhook') }}</code></span>
                <span>Secret Configured: <strong>{{ env('RAZORPAY_WEBHOOK_SECRET') ? 'Yes (Active)' : 'Not Set (Set in .env)' }}</strong></span>
            </div>
        </div>

    </div>

    <!-- MODAL 1: Dynamic Checkout Studio -->
    <div class="modal fade" id="dynamicCheckoutModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-credit-card-2-front me-2"></i> Dynamic Checkout Studio
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="dynamicCheckoutForm">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Select or Enter Amount (INR)</label>
                            
                            <!-- Quick Chips -->
                            <div class="d-flex flex-wrap gap-2 mb-2">
                                <button type="button" class="btn btn-outline-secondary btn-sm chip-btn" onclick="selectChip(100)">₹100</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm chip-btn active" onclick="selectChip(500)">₹500</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm chip-btn" onclick="selectChip(1000)">₹1,000</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm chip-btn" onclick="selectChip(2500)">₹2,500</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm chip-btn" onclick="selectChip(5000)">₹5,000</button>
                            </div>

                            <div class="input-group input-group-lg">
                                <span class="input-group-text fw-bold">₹</span>
                                <input type="number" id="payAmountInput" name="amount" class="form-control fw-bold" value="500" min="1" step="1" required>
                            </div>
                        </div>

                        <hr class="my-3">

                        <h6 class="fw-bold mb-3 text-secondary"><i class="bi bi-person-lines-fill me-1"></i> Customer Information (Autofill for Gateway)</h6>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Customer Full Name</label>
                            <input type="text" id="custNameInput" class="form-control" placeholder="e.g. Rahul Sharma" value="Rahul Sharma">
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Email Address</label>
                                <input type="email" id="custEmailInput" class="form-control" placeholder="name@example.com" value="rahul@example.com">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Phone Number</label>
                                <input type="tel" id="custPhoneInput" class="form-control" placeholder="9876543210" value="9876543210">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Order Note / Purpose</label>
                            <input type="text" id="custNotesInput" class="form-control" placeholder="e.g. Web Development Service Fee" value="Service Invoice Payment">
                        </div>

                        <button type="submit" id="submitPayBtn" class="btn btn-primary btn-lg w-100 fw-bold shadow-sm mt-2">
                            <i class="bi bi-lock-fill me-1"></i> Proceed to Pay with Razorpay
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL 2: 1-Click Refund Engine Modal -->
    <div class="modal fade" id="refundModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-danger text-white border-0">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-arrow-counterclockwise me-2"></i> Razorpay Refund Engine
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="refundForm">
                        <input type="hidden" id="refundPaymentId">

                        <div class="alert alert-light border mb-3">
                            <div class="small text-muted">Order ID: <strong id="modalRefundOrder" class="text-dark"></strong></div>
                            <div class="small text-muted">Original Amount: <strong id="modalRefundAmount" class="text-dark"></strong></div>
                            <div class="small text-muted">Available to Refund: <strong id="modalRefundRemaining" class="text-success"></strong></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Refund Type</label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="refund_type" id="refundTypeFull" value="full" checked onchange="toggleRefundAmountInput()">
                                    <label class="form-check-label fw-semibold" for="refundTypeFull">
                                        Full Refund
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="refund_type" id="refundTypePartial" value="partial" onchange="toggleRefundAmountInput()">
                                    <label class="form-check-label fw-semibold" for="refundTypePartial">
                                        Partial Refund
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3" id="partialAmountContainer" style="display: none;">
                            <label class="form-label fw-bold">Partial Refund Amount (₹)</label>
                            <input type="number" id="refundCustomAmount" class="form-control" min="1" step="1" placeholder="Enter amount">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Refund Reason</label>
                            <input type="text" id="refundReasonInput" class="form-control" placeholder="e.g. Customer cancelled order / defective product" value="Customer request">
                        </div>

                        <button type="submit" id="confirmRefundBtn" class="btn btn-danger btn-lg w-100 fw-bold shadow-sm">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Process Refund Now
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL 3: View JSON Payload Modal -->
    <div class="modal fade" id="payloadModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-dark text-white border-0">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-filetype-json me-2"></i> Webhook Event Payload
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-3">
                    <h6 class="fw-bold text-muted small text-uppercase">Headers</h6>
                    <pre id="modalHeadersView" class="code-pre mb-3"></pre>
                    <h6 class="fw-bold text-muted small text-uppercase">JSON Payload</h6>
                    <pre id="modalPayloadView" class="code-pre mb-0"></pre>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL 4: Simulate Webhook Modal -->
    <div class="modal fade" id="simulateWebhookModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-info text-white border-0">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-broadcast-pin me-2"></i> Simulate Razorpay Webhook
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('webhook.simulate') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Select Webhook Event</label>
                            <select name="event" id="simulateEventSelect" class="form-select" onchange="toggleSimulateFailureReason()">
                                <option value="payment.captured">payment.captured (Payment Success)</option>
                                <option value="payment.failed">payment.failed (Payment Failure)</option>
                                <option value="refund.processed">refund.processed (Refund Completed)</option>
                            </select>
                        </div>

                        <div class="mb-3" id="simulateFailureReasonGroup" style="display: none;">
                            <label class="form-label fw-bold">Failure Reason</label>
                            <select name="failure_reason" class="form-select">
                                <option value="Insufficient funds in bank account">Insufficient funds in bank account</option>
                                <option value="Payment was dropped by user / Cancelled OTP">Payment was dropped by user / Cancelled OTP</option>
                                <option value="Card network communication timed out">Card network communication timed out</option>
                                <option value="Bank gateway server unavailable">Bank gateway server unavailable</option>
                            </select>
                        </div>

                        <p class="small text-muted mb-0">
                            Simulates an incoming Razorpay webhook call to test signature verification, event logging, and status updates on your radar.
                        </p>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-info text-white fw-bold">Dispatch Simulated Event</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- SCRIPTS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Chart.js Config -->
    <script>
        new Chart(document.getElementById('revenueChart'), {
            type: 'bar',
            data: {
                labels: @json($monthlyRevenue->pluck('month')),
                datasets: [{
                    label: 'Revenue (₹)',
                    data: @json($monthlyRevenue->pluck('revenue')),
                    backgroundColor: 'rgba(13, 110, 253, 0.75)',
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        new Chart(document.getElementById('statusChart'), {
            type: 'doughnut',
            data: {
                labels: Object.keys(@json($statusData)),
                datasets: [{
                    data: Object.values(@json($statusData)),
                    backgroundColor: [
                        '#198754', // Success
                        '#dc3545', // Failed
                        '#ffc107', // Pending
                        '#6c757d'  // Refunded
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    </script>

    <!-- App Logic Scripts -->
    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        // Quick Amount Chips
        function selectChip(amount) {
            document.querySelectorAll('.chip-btn').forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
            document.getElementById('payAmountInput').value = amount;
        }

        // Dynamic Checkout Submission
        document.getElementById('dynamicCheckoutForm').onsubmit = function(e) {
            e.preventDefault();

            const submitBtn = document.getElementById('submitPayBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Initializing Razorpay...';

            const payload = {
                amount: document.getElementById('payAmountInput').value,
                customer_name: document.getElementById('custNameInput').value,
                customer_email: document.getElementById('custEmailInput').value,
                customer_contact: document.getElementById('custPhoneInput').value,
                notes: document.getElementById('custNotesInput').value,
            };

            fetch("{{ route('payments.create-order') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrfToken
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(order => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-lock-fill me-1"></i> Proceed to Pay with Razorpay';

                if (!order.success && order.message) {
                    Swal.fire('Error', order.message, 'error');
                    return;
                }

                // Hide Modal
                bootstrap.Modal.getInstance(document.getElementById('dynamicCheckoutModal')).hide();

                // Open Razorpay Standard Checkout
                var options = {
                    key: "{{ config('services.razorpay.key') }}",
                    amount: order.amount,
                    currency: order.currency,
                    order_id: order.id,
                    name: "Razorpay Studio",
                    description: order.notes || "Order Checkout",
                    prefill: {
                        name: order.customer_name || "",
                        email: order.customer_email || "",
                        contact: order.customer_contact || ""
                    },
                    theme: {
                        color: "#0d6efd"
                    },
                    handler: function(response) {
                        Swal.fire({
                            title: 'Verifying Payment...',
                            text: 'Please wait while we confirm your payment with the server.',
                            allowOutsideClick: false,
                            didOpen: () => { Swal.showLoading(); }
                        });

                        fetch("{{ route('payments.success') }}", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": csrfToken
                            },
                            body: JSON.stringify({
                                razorpay_payment_id: response.razorpay_payment_id,
                                razorpay_order_id: order.id
                            })
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Payment Successful!',
                                    text: 'Your payment ID is: ' + response.razorpay_payment_id,
                                    timer: 2000,
                                    showConfirmButton: false
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire('Error', data.message || 'Payment update failed', 'error');
                            }
                        })
                        .catch(err => {
                            console.error(err);
                            Swal.fire('Network Error', 'Payment was successful at gateway but server verification failed.', 'warning');
                        });
                    }
                };

                let rzp = new Razorpay(options);
                rzp.open();
            })
            .catch(err => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-lock-fill me-1"></i> Proceed to Pay with Razorpay';
                console.error(err);
                Swal.fire('Error', 'Failed to initialize payment gateway order.', 'error');
            });
        };

        // 1-Click Refund Engine Handlers
        document.querySelectorAll('.refund-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const order = this.getAttribute('data-order');
                const amount = this.getAttribute('data-amount');
                const remaining = this.getAttribute('data-remaining');

                document.getElementById('refundPaymentId').value = id;
                document.getElementById('modalRefundOrder').innerText = order;
                document.getElementById('modalRefundAmount').innerText = '₹' + amount;
                document.getElementById('modalRefundRemaining').innerText = '₹' + remaining;
                document.getElementById('refundCustomAmount').max = remaining;
                document.getElementById('refundCustomAmount').value = remaining;

                // Reset radios
                document.getElementById('refundTypeFull').checked = true;
                toggleRefundAmountInput();

                new bootstrap.Modal(document.getElementById('refundModal')).show();
            });
        });

        function toggleRefundAmountInput() {
            const isPartial = document.getElementById('refundTypePartial').checked;
            document.getElementById('partialAmountContainer').style.display = isPartial ? 'block' : 'none';
        }

        document.getElementById('refundForm').onsubmit = function(e) {
            e.preventDefault();
            const id = document.getElementById('refundPaymentId').value;
            const refundType = document.querySelector('input[name="refund_type"]:checked').value;
            const refundAmount = document.getElementById('refundCustomAmount').value;
            const reason = document.getElementById('refundReasonInput').value;

            const confirmBtn = document.getElementById('confirmRefundBtn');
            confirmBtn.disabled = true;
            confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Processing Refund...';

            fetch(`/payments/${id}/refund`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    refund_type: refundType,
                    refund_amount: refundAmount,
                    reason: reason
                })
            })
            .then(res => res.json())
            .then(data => {
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = '<i class="bi bi-arrow-counterclockwise me-1"></i> Process Refund Now';

                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('refundModal')).hide();
                    Swal.fire('Refund Initiated', data.message, 'success').then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Refund Failed', data.message, 'error');
                }
            })
            .catch(err => {
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = '<i class="bi bi-arrow-counterclockwise me-1"></i> Process Refund Now';
                Swal.fire('Error', 'Unable to reach refund gateway.', 'error');
            });
        };

        // Webhook Payload Preview
        document.querySelectorAll('.view-payload-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const payload = this.getAttribute('data-payload');
                const headers = this.getAttribute('data-headers');

                document.getElementById('modalHeadersView').innerText = headers || 'No headers captured';
                document.getElementById('modalPayloadView').innerText = payload || '{}';

                new bootstrap.Modal(document.getElementById('payloadModal')).show();
            });
        });

        // Simulate Webhook failure toggle
        function toggleSimulateFailureReason() {
            const val = document.getElementById('simulateEventSelect').value;
            document.getElementById('simulateFailureReasonGroup').style.display = (val === 'payment.failed') ? 'block' : 'none';
        }
    </script>

</body>

</html>