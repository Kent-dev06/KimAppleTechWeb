<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="icon" type="image/png" href="{{ asset('images/kim-apple-tech-logo.png') }}">
    <meta name="theme-color" content="#14243b">
    <title>Sign in | Kim Apple Tech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/repair-ui.css') }}" rel="stylesheet">
</head>
<body class="repair-app">
<main class="repair-login">
    <section class="repair-login-card row g-0">
        <aside class="col-md-5 repair-login-brand d-flex flex-column justify-content-between">
            <div>
                <img class="repair-login-logo mb-4" src="{{ asset('images/kim-apple-tech-logo.png') }}" alt="Kim Apple Tech logo">
                <p class="small text-uppercase fw-bold text-info mb-2">Kim Apple Tech <span class="text-white-50">&middot; Davao City</span></p>
                <h2 class="display-6 fw-bold">Your phone,<br>back in good hands.</h2>
                <p class="text-white-50 mt-3 mb-0">Sign in to manage appointments and follow your smartphone repair.</p>
            </div>
            <div class="d-none d-md-flex align-items-center gap-2 small text-white-50 mt-5"><span class="rounded-circle bg-success" style="width:8px;height:8px"></span> Smartphone repair service and support</div>
        </aside>
        <div class="col-md-7 repair-login-form align-self-center">
            <p class="text-primary text-uppercase fw-bold small mb-2">Customer, clerk &amp; admin portal</p>
            <h1 class="h2 mb-2">Welcome back</h1>
            <p class="text-secondary mb-4">Enter your account details to continue.</p>
            @if($errors->any())<div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>@endif
            <form method="post" action="/login" autocomplete="on">@csrf
                <div class="mb-3"><label class="form-label" for="email">Email address</label><input class="form-control" id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" placeholder="you@example.com" required autofocus></div>
                <div class="mb-4"><label class="form-label" for="password">Password</label><input class="form-control" id="password" name="password" type="password" autocomplete="current-password" placeholder="Enter your password" required></div>
                <button class="btn btn-primary w-100" type="submit">Sign in</button>
            </form>
            <p class="text-secondary text-center mt-4 mb-0">New to Kim Apple Tech? <a class="fw-semibold text-decoration-none" href="/register">Create customer account</a></p>
            <p class="text-center small text-secondary mt-4 mb-0">&copy; {{ date('Y') }} Kim Apple Tech</p>
        </div>
    </section>
</main>
</body>
</html>
