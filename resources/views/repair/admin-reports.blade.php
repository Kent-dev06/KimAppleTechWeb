<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="icon" type="image/png" href="{{ asset('images/kim-apple-tech-logo.png') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Analytics | Kim Apple Tech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/repair-ui.css') }}" rel="stylesheet">
    @vite('resources/js/app.js')
    <style>
        @media print {
            nav, .no-print, .notification-dropdown { display: none !important; }
            body, .repair-app { background: #fff !important; }
            main { max-width: 100% !important; padding: 0 !important; }
            .card { border: 1px solid #ddd !important; box-shadow: none !important; break-inside: avoid; }
        }
    </style>
</head>
<body class="repair-app">
<nav class="navbar navbar-dark"><div class="container"><a class="navbar-brand d-flex align-items-center gap-2" href="/repair"><img class="site-brand-logo" src="{{ asset('images/kim-apple-tech-logo.png') }}" alt="Kim Apple Tech logo"> <span>Kim Apple Tech <span class="opacity-75">&middot; Analytics</span></span></a><div class="d-flex gap-2"><x-notification-bell /><a class="btn btn-outline-light btn-sm" href="{{ route('repair.admin') }}">Accounts</a><a class="btn btn-outline-light btn-sm" href="/repair">Dashboard</a></div></div></nav>
<main class="container py-4">
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div><div class="small text-primary fw-bold text-uppercase">Administration</div><h1 class="h2 mt-1 mb-2">Repair analytics</h1><p class="text-secondary mb-0">Appointment status and completed repair totals for the selected period.</p></div>
        <div class="d-flex gap-2 no-print"><a class="btn btn-outline-primary" href="{{ route('repair.admin.reports.export', request()->only('from', 'to')) }}">Export CSV</a><button class="btn btn-primary" type="button" onclick="window.print()">Print</button></div>
    </div>
    <form method="get" class="card card-body mb-4 no-print"><div class="row g-3 align-items-end"><div class="col-sm-4"><label class="form-label" for="report_from">From</label><input id="report_from" class="form-control" type="date" name="from" value="{{ request('from') }}"></div><div class="col-sm-4"><label class="form-label" for="report_to">To</label><input id="report_to" class="form-control" type="date" name="to" value="{{ request('to') }}"></div><div class="col-sm-4"><button class="btn btn-primary">Apply date range</button> <a class="btn btn-outline-secondary" href="{{ route('repair.admin.reports') }}">Clear</a></div></div></form>
    <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-4 g-3 mb-4">
        @foreach(['Customers'=>$stats['customers'],'Appointments'=>$stats['appointments'],'Completed repairs'=>$stats['completed_repairs'],'Estimated revenue'=>'PHP '.number_format($stats['revenue_estimate'],2)] as $label=>$value)
            <div class="col"><div class="card card-body h-100"><span class="text-secondary">{{ $label }}</span><strong class="fs-3">{{ $value }}</strong></div></div>
        @endforeach
    </div>
    @if($stats['appointments'] === 0)
        <div class="alert alert-info" role="status">No appointments match this date range. Try widening the filter.</div>
    @else
        <section class="card card-body mb-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3"><div><h2 class="h5 mb-1">Appointment status breakdown</h2><p class="text-secondary small mb-0">{{ $stats['appointments'] }} {{ \Illuminate\Support\Str::plural('appointment', $stats['appointments']) }} in the selected date range</p></div></div>
            <div class="row row-cols-2 row-cols-md-3 row-cols-xl-6 g-3 mb-4">
                @foreach($statusCounts as $status=>$count)
                    <div class="col"><div class="border rounded-3 p-3 h-100"><div class="small text-secondary">{{ $status }}</div><div class="fs-4 fw-semibold">{{ $count }}</div><div class="small text-secondary">{{ \Illuminate\Support\Str::plural('appointment', $count) }}</div></div></div>
                @endforeach
            </div>
            <div style="height: 320px"><canvas id="appointmentStatusChart" role="img" aria-label="Appointment counts by status"></canvas></div>
        </section>
    @endif
</main>
@if($stats['appointments'] > 0)
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
    new Chart(document.getElementById('appointmentStatusChart'), {
        type: 'bar',
        data: {
            labels: @json($statusCounts->keys()),
            datasets: [{
                label: 'Appointments',
                data: @json($statusCounts->values()),
                backgroundColor: ['#f59e0b', '#0d6efd', '#6f42c1', '#198754', '#dc3545', '#6c757d'],
                borderRadius: 6,
            }],
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } },
    });
</script>
@endif
<script>window.addEventListener('pageshow', event => { if (event.persisted) fetch('/repair/session-check', { headers: { Accept: 'application/json' }, cache: 'no-store' }).then(response => { if (!response.ok) window.location.replace('/login'); }).catch(() => window.location.replace('/login')); });</script>
</body>
</html>
