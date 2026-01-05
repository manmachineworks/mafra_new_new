@extends('backend.layouts.app')

@section('content')
    @if (auth()->user()->can('smtp_settings') && env('MAIL_USERNAME') == null && env('MAIL_PASSWORD') == null)
        <div class="alert alert-info d-flex align-items-center">
            {{ translate('Please Configure SMTP Setting to work all email sending functionality') }},
            <a class="alert-link ml-2" href="{{ route('smtp_settings.index') }}">{{ translate('Configure Now') }}</a>
        </div>
    @endif

    @can('admin_dashboard')
        @php
            $recent_orders = $recent_orders ?? collect();
            $latest_customers = $latest_customers ?? collect();
            $low_stock_products = $low_stock_products ?? collect();
            $today_orders = $today_orders ?? 0;
            $cancelled_orders = $cancelled_orders ?? 0;
        @endphp

        <div class="dashboard-skin">
            <!-- Page intro -->
            <div class="card shadow-sm border-0 intro-card mb-3">
                <div class="card-body d-flex flex-wrap justify-content-between align-items-center">
                    <div class="mb-2 mb-md-0">
                        <p class="eyebrow text-uppercase mb-1">{{ translate('Control Room') }}</p>
                        <h4 class="mb-1 text-dark fw-700">{{ translate('Admin Overview') }}</h4>
                        <small class="text-muted">{{ translate('Watch orders, customers, and inventory in one glance.') }}</small>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="pill pill-success mr-2">{{ translate('Live') }}</div>
                        <div class="pill pill-light">{{ now()->format('l, M d') }}</div>
                    </div>
                </div>
            </div>

            <!-- KPI Cards -->
            <div class="row gutters-16 mb-3">
                @php
                    $kpis = [
                        ['label' => translate('Total Orders'), 'value' => $total_order ?? 0, 'icon' => 'las la-shopping-bag'],
                        ['label' => translate('Total Revenue'), 'value' => single_price($total_sale ?? 0), 'icon' => 'las la-rupee-sign'],
                        ['label' => translate("Today's Orders"), 'value' => $today_orders, 'icon' => 'las la-bolt'],
                        ['label' => translate('Pending / Cancelled'), 'value' => ($total_pending_order ?? 0) . ' / ' . $cancelled_orders, 'icon' => 'las la-hourglass-half'],
                    ];
                @endphp
                @foreach ($kpis as $kpi)
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="card shadow-sm border-0 h-100 kpi-card">
                            <div class="card-body d-flex align-items-center">
                                <div class="kpi-icon mr-3">
                                    <i class="{{ $kpi['icon'] }}"></i>
                                </div>
                                <div>
                                    <div class="kpi-value">{{ $kpi['value'] }}</div>
                                    <div class="kpi-label">{{ $kpi['label'] }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Secondary health signals -->
            <div class="row gutters-16 mb-3">
                <div class="col-lg-8 mb-3">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body d-flex flex-wrap align-items-center">
                            <div class="pulse-dot mr-3"></div>
                            <div>
                                <div class="fw-600 text-dark">{{ translate('Operations pulse') }}</div>
                                <small class="text-muted d-block">{{ translate('Tracking fulfillment and cancellations in near real-time.') }}</small>
                            </div>
                            <div class="ml-auto d-flex flex-wrap">
                                <span class="pill pill-light mr-2 mb-2">{{ translate('Pending') }}: {{ $total_pending_order ?? 0 }}</span>
                                <span class="pill pill-light mr-2 mb-2">{{ translate('Cancelled') }}: {{ $cancelled_orders }}</span>
                                <span class="pill pill-ghost mb-2">{{ translate("Today's Orders") }}: {{ $today_orders }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-3">
                    <div class="card shadow-sm border-0 h-100 kpi-card">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <div class="kpi-icon mr-3">
                                    <i class="las la-user-friends"></i>
                                </div>
                                <div>
                                    <div class="kpi-value" id="live-active-users-count">--</div>
                                    <div class="kpi-label">{{ translate('Live Active Users') }}</div>
                                </div>
                            </div>
                            <span class="live-status" id="live-active-users-status">{{ translate('Updated live') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick actions -->
            <div class="row gutters-16 mb-3">
                <div class="col-12 d-flex flex-wrap">
                    <a href="{{ route('all_orders.index') }}" class="btn btn-action mr-2 mb-2">{{ translate('View Orders') }}</a>
                    <a href="{{ route('products.create') }}" class="btn btn-action mr-2 mb-2">{{ translate('Add Product') }}</a>
                    @if (Route::has('reports.index'))
                        <a href="{{ route('reports.index') }}" class="btn btn-action mb-2">{{ translate('View Reports') }}</a>
                    @endif
                </div>
            </div>

            <!-- Insights -->
            <div class="row gutters-16">
                <div class="col-xl-6 mb-3">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-1 text-dark fw-700">{{ translate('Recent Orders') }}</h5>
                                <small class="text-muted">{{ translate('Latest activity from customers') }}</small>
                            </div>
                            <a href="{{ route('all_orders.index') }}" class="text-primary">{{ translate('View all') }}</a>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table mb-0">
                                    <thead class="text-muted">
                                        <tr>
                                            <th>{{ translate('Order') }}</th>
                                            <th>{{ translate('Customer') }}</th>
                                            <th>{{ translate('Total') }}</th>
                                            <th>{{ translate('Status') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($recent_orders as $order)
                                            <tr>
                                                <td class="fw-600 text-dark">#{{ $order->code ?? '--' }}</td>
                                                <td>{{ optional($order->user)->name ?? translate('Guest') }}</td>
                                                <td>{{ single_price($order->grand_total ?? 0) }}</td>
                                                <td>
                                                    @php
                                                        $status = $order->delivery_status ?? 'pending';
                                                        $statusClass = match ($status) {
                                                            'pending' => 'badge-warning',
                                                            'cancelled', 'canceled' => 'badge-danger',
                                                            'delivered' => 'badge-success',
                                                            default => 'badge-secondary',
                                                        };
                                                    @endphp
                                                    <span class="badge badge-inline {{ $statusClass }}">{{ translate(ucwords(str_replace('_', ' ', $status))) }}</span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-4">{{ translate('No recent orders available') }}</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 mb-3">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white border-0">
                            <h5 class="mb-1 text-dark fw-700">{{ translate('Latest Customers') }}</h5>
                            <small class="text-muted">{{ translate('Newly joined customers') }}</small>
                        </div>
                        <div class="card-body">
                            @forelse ($latest_customers as $customer)
                                <div class="d-flex align-items-center mb-3">
                                    <span class="avatar avatar-sm mr-2">
                                        <img src="{{ uploaded_asset($customer->avatar_original) }}" class="img-fit" onerror="this.onerror=null;this.src='{{ static_asset('assets/img/avatar-place.png') }}';">
                                    </span>
                                    <div>
                                        <div class="fw-600 text-dark">{{ $customer->name }}</div>
                                        <small class="text-muted">{{ $customer->email }}</small>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center text-muted py-3">{{ translate('No customers found') }}</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 mb-3">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white border-0">
                            <h5 class="mb-1 text-dark fw-700">{{ translate('Low Stock Products') }}</h5>
                            <small class="text-muted">{{ translate('Keep inventory healthy') }}</small>
                        </div>
                        <div class="card-body">
                            @forelse ($low_stock_products as $product)
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="text-dark text-truncate mr-2">{{ $product->getTranslation('name') }}</div>
                                    <span class="badge badge-danger badge-inline">{{ max($product->stocks?->sum('qty') ?? $product->current_stock ?? 0, 0) }}</span>
                                </div>
                            @empty
                                <div class="text-center text-muted py-3">{{ translate('All stock levels look good') }}</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts -->
            <div class="row gutters-16">
                <div class="col-lg-8 mb-3">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-1 text-dark fw-700">{{ translate('Orders & Revenue Trend') }}</h5>
                                <small class="text-muted">{{ translate('Weekly / Monthly overview') }}</small>
                            </div>
                        </div>
                        <div class="card-body">
                            <canvas id="graph-3" class="w-100" height="170"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mb-3">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-1 text-dark fw-700">{{ translate('Payment Method Split') }}</h5>
                                <small class="text-muted">{{ translate('Prepaid vs COD') }}</small>
                            </div>
                        </div>
                        <div class="card-body d-flex align-items-center justify-content-center">
                            <canvas id="graph-2" height="210"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endcan
@endsection

@push('css')
<style>
    .dashboard-skin { background:linear-gradient(120deg, #fff7f7 0%, #ffffff 30%, #f8fafc 100%); padding:4px; }
    .intro-card { background:linear-gradient(135deg, #ffffff 0%, #fff4f4 100%); }
    .eyebrow { letter-spacing: .2em; font-size:10px; font-weight:700; color:#c70a0a; }
    .pill { padding:6px 12px; border-radius:999px; font-size:12px; font-weight:600; display:inline-flex; align-items:center; }
    .pill-success { background:rgba(22,163,74,0.1); color:#15803d; }
    .pill-light { background:#f3f4f6; color:#374151; }
    .pill-ghost { background:transparent; color:#111827; border:1px dashed #e5e7eb; }
    .pulse-dot { width:12px; height:12px; border-radius:50%; background:#16a34a; box-shadow:0 0 0 10px rgba(22,163,74,0.08); animation:pulse 2s ease-in-out infinite; }
    .kpi-card { transition:all .2s ease; border:1px solid #f1f5f9; }
    .kpi-card:hover { transform:translateY(-2px); box-shadow:0 12px 30px rgba(0,0,0,0.08) !important; }
    .kpi-icon { width:44px; height:44px; border-radius:12px; background:rgba(199,10,10,0.08); color:#c70a0a; display:flex; align-items:center; justify-content:center; font-size:20px; }
    .kpi-value { font-size:22px; font-weight:700; color:#111827; }
    .kpi-label { font-size:12px; color:#6b7280; }
    .btn-action { background:#c70a0a; color:#fff; border-radius:12px; padding:10px 16px; box-shadow:0 10px 20px rgba(199,10,10,0.15); }
    .btn-action:hover { color:#fff; opacity:0.9; }
    .card-header h5 { color:#212121; }
    .live-status { font-size:11px; color:#16a34a; display:flex; align-items:center; gap:6px; white-space:nowrap; }
    .live-status::before { content:''; width:8px; height:8px; border-radius:50%; background:#16a34a; box-shadow:0 0 0 6px rgba(22,163,74,0.12); animation:pulse 1.8s ease-in-out infinite; }
    @keyframes pulse { 0%{transform:scale(1);} 50%{transform:scale(1.25); opacity:.7;} 100%{transform:scale(1);} }
</style>
@endpush

@section('script')
    @include('backend.dashboard.dashboard_js')

    <script type="text/javascript">
        AIZ.plugins.chart('#graph-3', {
            type: 'line',
            data: {
                labels: [
                    @foreach ($sales_stat as $month => $row)
                        "{{ $month }}",
                    @endforeach
                ],
                datasets: [{
                    fill: false,
                    borderColor: '#c70a0a',
                    backgroundColor: 'rgba(199,10,10,0.12)',
                    label: "{{ translate('Yearly Sales') }}",
                    data: [
                        @foreach ($sales_stat as $row)
                            {{ $row[0]->total }},
                        @endforeach
                    ],
                    tension: 0.3,
                    pointRadius: 3
                }]
            },
            options: {
                plugins: { legend: { position: 'bottom' } },
                scales: {
                    x: { grid: { display: false } },
                    y: { grid: { color: '#f3f4f6' } }
                }
            }
        });

        AIZ.plugins.chart('#graph-2', {
            type: 'doughnut',
            data: {
                labels: [
                    @foreach ($payment_type_wise_inhouse_sale as $row)
                        "{{ ucwords(str_replace('_', ' ', $row->payment_type)) }}",
                    @endforeach
                ],
                datasets: [{
                    label: 'Total Sales',
                    data: [
                        @foreach ($payment_type_wise_inhouse_sale as $row)
                            {{ $row->total_amount }},
                        @endforeach
                    ],
                    backgroundColor: ['#c70a0a', '#212121', '#f5b4b4'],
                    hoverOffset: 4
                }]
            },
            options: {
                plugins: { legend: { position: 'bottom', labels: { usePointStyle: true } } }
            }
        });

        (function () {
            const counterEl = document.getElementById('live-active-users-count');
            const statusEl = document.getElementById('live-active-users-status');
            if (!counterEl) return;

            const endpoint = "{{ route('admin.active-users.count') }}";
            const refreshMs = 10000;
            let abortCtrl;

            const setStatusError = () => {
                if (!statusEl) return;
                statusEl.classList.add('text-danger');
                statusEl.textContent = "{{ translate('Retrying...') }}";
            };

            const setStatusOk = () => {
                if (!statusEl) return;
                statusEl.classList.remove('text-danger');
                statusEl.textContent = "{{ translate('Updated live') }}";
            };

            const fetchCount = () => {
                if (abortCtrl) abortCtrl.abort();
                abortCtrl = new AbortController();

                fetch(endpoint, {
                    method: 'GET',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    signal: abortCtrl.signal
                })
                    .then((res) => res.ok ? res.json() : Promise.reject())
                    .then((data) => {
                        const count = typeof data.count === 'number' ? data.count : 0;
                        counterEl.textContent = count;
                        setStatusOk();
                    })
                    .catch(() => {
                        setStatusError();
                    });
            };

            fetchCount();
            setInterval(fetchCount, refreshMs);
        })();
    </script>
@endsection
