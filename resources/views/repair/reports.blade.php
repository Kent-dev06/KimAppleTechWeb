<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Service reports | Kim Apple Tech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/repair-ui.css') }}" rel="stylesheet">
    @vite('resources/js/app.js')
</head>
<body class="repair-app">
<nav class="navbar navbar-dark"><div class="container">
    <a class="navbar-brand" href="/repair">Kim Apple Tech <span class="opacity-75">&middot; Service reports</span></a>
    <div class="d-flex gap-2"><x-notification-bell /><a class="btn btn-outline-light btn-sm" href="/repair">Dashboard</a></div>
</div></nav>
<main class="container py-4">
    <div class="mb-4"><span class="text-primary fw-bold text-uppercase small">Staff workspace</span><h1 class="h2 mt-1 mb-2">Service reports</h1><p class="text-secondary mb-0">Appointment dates and completed smartphone repairs for the selected period.</p></div>
    <form method="get" class="card card-body mb-4"><div class="row g-3 align-items-end">
        <div class="col-sm-4"><label class="form-label" for="report_from">From</label><input id="report_from" class="form-control" type="date" name="from" value="{{ request('from') }}"></div>
        <div class="col-sm-4"><label class="form-label" for="report_to">To</label><input id="report_to" class="form-control" type="date" name="to" value="{{ request('to') }}"></div>
        <div class="col-sm-4"><button class="btn btn-primary">Run report</button> <a class="btn btn-outline-secondary" href="/repair/reports">Clear</a></div>
    </div></form>
    @if($errors->any())<div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>@endif
    <section class="card card-body mb-4"><div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3"><h2 class="h5 mb-0">Appointments</h2><span class="badge text-bg-light border text-dark">{{ $appointments->total() }} {{ \Illuminate\Support\Str::plural('record', $appointments->total()) }}</span></div><div class="table-responsive"><table class="table table-sm align-middle"><thead><tr><th>Date</th><th>Customer</th><th>Smartphone</th><th>Preferred slot</th><th>Confirmed schedule</th><th>Status</th></tr></thead><tbody>
        @forelse($appointments as $a)
            @php($confirmedDate = $a->confirmed_date ?? $a->preferred_date)
            @php($confirmedStart = $a->confirmed_start_time ?: $a->preferred_start_time)
            @php($confirmedEnd = $a->confirmed_end_time ?: $a->preferred_end_time)
            <tr><td>{{ $a->preferred_date->format('Y-m-d') }}</td><td>{{ $a->customer->first_name }} {{ $a->customer->last_name }}</td><td>{{ $a->device->brand }} {{ $a->device->model }}</td><td>{{ substr($a->preferred_start_time,0,5) }}–{{ substr($a->preferred_end_time,0,5) }}</td><td>{{ $confirmedDate?->format('Y-m-d') }}<br>{{ substr($confirmedStart,0,5) }}–{{ substr($confirmedEnd,0,5) }}</td><td><x-status-badge :status="$a->status" /></td></tr>
        @empty<tr><td colspan="6" class="text-center text-secondary py-4">No appointments match this date range.</td></tr>@endforelse
    </tbody></table></div>{{ $appointments->links() }}</section>
    <section class="card card-body"><div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3"><h2 class="h5 mb-0">Completed repairs</h2><span class="badge text-bg-light border text-dark">{{ $repairs->total() }} {{ \Illuminate\Support\Str::plural('record', $repairs->total()) }}</span></div><div class="table-responsive"><table class="table table-sm align-middle"><thead><tr><th>Completed</th><th>Customer</th><th>Smartphone</th><th>Diagnosis</th><th>Cost</th></tr></thead><tbody>
        @forelse($repairs as $repair)<tr><td>{{ $repair->date_completed?->format('Y-m-d') }}</td><td>{{ $repair->appointment->customer->first_name }} {{ $repair->appointment->customer->last_name }}</td><td>{{ $repair->device->brand }} {{ $repair->device->model }}</td><td>{{ $repair->diagnosis }}</td><td>{{ number_format($repair->cost_estimate,2) }}</td></tr>@empty<tr><td colspan="5" class="text-center text-secondary py-4">No completed repairs match this date range.</td></tr>@endforelse
    </tbody></table></div>{{ $repairs->links() }}</section>
</main>
<script>window.addEventListener('pageshow', event => { if (event.persisted) fetch('/repair/session-check', { headers: { Accept: 'application/json' }, cache: 'no-store' }).then(response => { if (!response.ok) window.location.replace('/login'); }).catch(() => window.location.replace('/login')); });</script>
</body>
</html>
