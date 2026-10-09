<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Admin controls | Kim Apple Tech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/repair-ui.css') }}" rel="stylesheet">
</head>
<body class="repair-app">
<nav class="navbar navbar-dark"><div class="container"><a class="navbar-brand" href="/repair">Kim Apple Tech <span class="opacity-75">&middot; Admin</span></a><div class="d-flex gap-2"><a class="btn btn-outline-light btn-sm" href="{{ route('repair.admin.reports') }}">Reports</a><a class="btn btn-outline-light btn-sm" href="/repair">Dashboard</a></div></div></nav>
<main class="container py-4">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    <div class="mb-4"><div class="small text-primary fw-bold text-uppercase">Administration</div><h1 class="h2 mt-1">Accounts &amp; activity</h1><p class="text-secondary mb-0">Manage clerk and admin access and review recent system activity.</p></div>

    <section class="card card-body mb-4"><h2 class="h5">Create an account</h2><form method="post" action="/repair/admin/accounts" class="row g-3">@csrf
        <div class="col-md-3"><label class="form-label" for="account_name">Full name</label><input class="form-control" id="account_name" name="name" value="{{ old('name') }}" required></div>
        <div class="col-md-3"><label class="form-label" for="account_email">Email</label><input class="form-control" id="account_email" type="email" name="email" value="{{ old('email') }}" required></div>
        <div class="col-md-2"><label class="form-label" for="account_role">Role</label><select class="form-select" id="account_role" name="role" required><option value="clerk" @selected(old('role')==='clerk')>Clerk</option><option value="admin" @selected(old('role')==='admin')>Admin</option></select></div>
        <div class="col-md-2"><label class="form-label" for="account_password">Password</label><input class="form-control" id="account_password" type="password" name="password" minlength="8" required></div>
        <div class="col-md-2"><label class="form-label" for="account_password_confirmation">Confirm password</label><input class="form-control" id="account_password_confirmation" type="password" name="password_confirmation" minlength="8" required></div>
        <div class="col-12"><button class="btn btn-primary">Create account</button></div>
    </form></section>

    <section class="card card-body mb-4"><h2 class="h5">Clerk and admin accounts</h2><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Access</th><th>Actions</th></tr></thead><tbody>
        @forelse($accounts as $account)<tr><td>{{ $account->name }}</td><td>{{ $account->email }}</td><td><span class="badge {{ $account->role==='admin'?'text-bg-primary':'text-bg-info' }}">{{ ucfirst($account->role) }}</span></td><td><span class="badge {{ $account->is_active?'text-bg-success':'text-bg-secondary' }}">{{ $account->is_active?'Active':'Inactive' }}</span></td><td class="d-flex flex-wrap gap-2"><form method="post" action="/repair/admin/accounts/{{ $account->user_id }}/toggle">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-{{ $account->is_active?'warning':'success' }}">{{ $account->is_active?'Deactivate':'Activate' }}</button></form><form method="post" action="{{ route('repair.admin.records.destroy',['type'=>'user','id'=>$account->user_id]) }}" onsubmit="return confirm('Permanently delete this account?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Delete</button></form></td></tr>@empty<tr><td colspan="5" class="text-center text-secondary py-4">No clerk or admin accounts yet.</td></tr>@endforelse
    </tbody></table></div></section>

    <section class="card card-body"><div class="d-flex justify-content-between align-items-center mb-3"><div><h2 class="h5 mb-1">Recent activity</h2><p class="small text-secondary mb-0">Latest account and record actions.</p></div><span class="badge text-bg-light border text-dark">{{ $activityLogs->count() }} entries</span></div><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Time</th><th>User</th><th>Action</th><th>Record</th></tr></thead><tbody>
        @forelse($activityLogs as $entry)<tr><td>{{ $entry->created_at->format('M d, Y H:i') }}</td><td>{{ $entry->user?->name ?? 'Deleted account' }}</td><td>{{ $entry->action }}</td><td>{{ $entry->model }}</td></tr>@empty<tr><td colspan="4" class="text-center text-secondary py-4">No activity has been recorded yet.</td></tr>@endforelse
    </tbody></table></div></section>
</main>
</body>
</html>
