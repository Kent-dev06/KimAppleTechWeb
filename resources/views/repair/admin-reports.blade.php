<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin reports | Kim Apple Tech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/repair-ui.css') }}" rel="stylesheet">
    @vite('resources/js/app.js')
</head>
<body class="repair-app">
<nav class="navbar navbar-dark"><div class="container"><a class="navbar-brand" href="/repair">Kim Apple Tech <span class="opacity-75">&middot; Analytics</span></a><div class="d-flex gap-2"><x-notification-bell /><a class="btn btn-outline-light btn-sm" href="{{ route('repair.admin') }}">Accounts</a><a class="btn btn-outline-light btn-sm" href="/repair">Dashboard</a></div></div></nav>
<main class="container py-4">
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    <div class="mb-4"><div class="small text-primary fw-bold text-uppercase">Administration</div><h1 class="h2 mt-1">Repair reports</h1><p class="text-secondary mb-0">Completed smartphone repairs, estimated revenue, and customer totals.</p></div>
    <form method="get" class="card card-body mb-4"><div class="row g-3 align-items-end"><div class="col-sm-4"><label class="form-label" for="report_from">From</label><input id="report_from" class="form-control" type="date" name="from" value="{{ request('from') }}"></div><div class="col-sm-4"><label class="form-label" for="report_to">To</label><input id="report_to" class="form-control" type="date" name="to" value="{{ request('to') }}"></div><div class="col-sm-4"><button class="btn btn-primary">Apply date range</button> <a class="btn btn-outline-secondary" href="{{ route('repair.admin.reports') }}">Clear</a></div></div></form>
    <div class="row g-3 mb-4">@foreach(['Customers'=>$stats['customers'],'Completed repairs'=>$stats['completed_repairs'],'Estimated revenue'=>'PHP '.number_format($stats['revenue_estimate'],2)] as $label=>$value)<div class="col-md-4"><div class="card card-body h-100"><span class="text-secondary">{{ $label }}</span><strong class="fs-3">{{ $value }}</strong></div></div>@endforeach</div>
    <section class="card card-body"><h2 class="h5">Completed repairs</h2><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Completed</th><th>Customer</th><th>Smartphone</th><th>Diagnosis</th><th>Estimate</th><th>Status</th></tr></thead><tbody>
        @forelse($completedRepairs as $repair)<tr><td>{{ $repair->date_completed?->format('M d, Y') ?? '—' }}</td><td>{{ $repair->appointment?->customer?->first_name }} {{ $repair->appointment?->customer?->last_name }}</td><td>{{ $repair->device->brand }} {{ $repair->device->model }}</td><td>{{ $repair->diagnosis }}</td><td>PHP {{ number_format($repair->cost_estimate,2) }}</td><td><x-status-badge :status="$repair->repair_status" /></td></tr>@empty<tr><td colspan="6" class="text-center text-secondary py-4">No completed repairs in this date range.</td></tr>@endforelse
    </tbody></table></div></section>
</main>
<script>window.addEventListener('pageshow', event => { if (event.persisted) fetch('/repair/session-check', { headers: { Accept: 'application/json' }, cache: 'no-store' }).then(response => { if (!response.ok) window.location.replace('/login'); }).catch(() => window.location.replace('/login')); });</script>
</body>
</html>
