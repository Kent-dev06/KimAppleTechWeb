<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="icon" type="image/png" href="{{ asset('images/kim-apple-tech-logo.png') }}">
    <meta name="theme-color" content="#101d35">
    <title>Create your account | Kim Apple Tech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/repair-ui.css') }}" rel="stylesheet">
    <style>
        :root{--ink:#15243b;--muted:#6b7890;--blue:#2864e8;--line:#e5eaf2}
        body{min-height:100vh;background:radial-gradient(ellipse at 12% 8%,#e9f1ff 0,transparent 34%),#f5f7fb;color:var(--ink);font-family:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",sans-serif}
        .register-shell{max-width:1120px}
        .register-card{border:1px solid rgba(223,230,241,.9);border-radius:24px;overflow:hidden;background:#fff;box-shadow:0 24px 70px rgba(26,48,86,.11)}
        .brand-panel{position:relative;overflow:hidden;min-height:100%;padding:48px;background:linear-gradient(145deg,#14213a 0%,#1d3963 65%,#24539b 100%);color:white}
        .brand-panel:before,.brand-panel:after{content:"";position:absolute;border:1px solid rgba(255,255,255,.10);border-radius:50%;pointer-events:none}
        .brand-panel:before{width:380px;height:380px;right:-190px;top:-110px}.brand-panel:after{width:280px;height:280px;right:-135px;top:-60px}
        .brand-content{position:relative;z-index:1}
        .brand-mark{width:48px;height:48px;border-radius:15px;display:grid;place-items:center;background:linear-gradient(135deg,#7dd3fc,#60a5fa);color:#10213c;box-shadow:0 10px 28px #0b172c66}
        .brand-title{font-size:1.05rem;font-weight:750;letter-spacing:.01em}.brand-caption{font-size:.75rem;color:#b6c9e5;letter-spacing:.13em;text-transform:uppercase}
        .visual-wrap{height:255px;display:grid;place-items:center;margin:28px 0 24px}
        .phone{width:126px;height:226px;border:5px solid #b9d8ff;border-radius:27px;background:linear-gradient(160deg,#f1f8ff,#b9d9ff);box-shadow:0 22px 50px #07162b66;position:relative;transform:rotate(-8deg)}
        .phone:before{content:"";position:absolute;width:38px;height:5px;border-radius:9px;background:#7593b7;top:8px;left:calc(50% - 19px)}
        .phone-screen{position:absolute;inset:24px 8px 9px;border-radius:17px;background:linear-gradient(155deg,#274d82,#63a9e9 58%,#d8edff);display:flex;align-items:flex-end;padding:13px;color:white}
        .phone-screen span{font-size:.65rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase}
        .spark{position:absolute;color:#9bdcff;font-size:1.4rem}.spark.one{top:28px;right:24px}.spark.two{bottom:42px;left:22px;font-size:1rem}
        .feature{display:flex;gap:12px;align-items:flex-start;color:#d8e6f8;font-size:.9rem;margin-top:16px}
        .check{flex:0 0 22px;height:22px;display:grid;place-items:center;border-radius:50%;background:#ffffff18;color:#8fe2cd;font-size:.8rem}
        .form-panel{padding:46px 54px}
        .eyebrow{font-size:.73rem;font-weight:750;letter-spacing:.14em;text-transform:uppercase;color:var(--blue)}
        h1{font-size:clamp(1.8rem,3vw,2.25rem);font-weight:760;letter-spacing:-.04em;margin:.45rem 0 .4rem}
        .intro{color:var(--muted);font-size:.94rem;margin-bottom:25px}
        .form-label{font-size:.84rem;font-weight:650;color:#36445b;margin-bottom:7px}
        .form-control{min-height:47px;border-color:var(--line);border-radius:11px;padding:.68rem .85rem;font-size:.92rem;color:var(--ink)}
        .form-control::placeholder{color:#a5afbf}.form-control:focus{border-color:#83a9ff;box-shadow:0 0 0 .22rem rgba(40,100,232,.12)}
        .field-error{font-size:.78rem;color:#c0394b;margin-top:5px}.form-control.is-invalid{border-color:#dc3545}.invalid-feedback{font-size:.78rem}
        .password-hint{font-size:.75rem;color:var(--muted);margin-top:6px}
        .submit-btn{min-height:49px;border:0;border-radius:11px;background:linear-gradient(100deg,#2864e8,#377cf1);font-weight:700;box-shadow:0 9px 18px rgba(40,100,232,.2)}
        .submit-btn:hover{background:linear-gradient(100deg,#2056cc,#286ce1);transform:translateY(-1px)}
        .signin{font-size:.9rem;color:var(--muted)}.signin a{color:var(--blue);font-weight:700;text-decoration:none}.signin a:hover{text-decoration:underline}
        .privacy-note{font-size:.75rem;color:#8591a4}.privacy-note svg{vertical-align:-3px}
        @media(max-width:767.98px){.register-shell{padding:18px 12px!important}.brand-panel{min-height:auto;padding:24px 26px}.visual-wrap{display:none}.brand-panel .feature-list{display:none}.brand-caption{font-size:.67rem}.form-panel{padding:30px 24px}.register-card{border-radius:19px}}
        @media(min-width:768px) and (max-width:991.98px){.brand-panel{padding:34px 28px}.form-panel{padding:38px 32px}}
    </style>
</head>
<body class="repair-app">
<main class="container register-shell py-5 px-3">
    <section class="register-card row g-0">
        <aside class="col-md-5 brand-panel">
            <div class="brand-content">
                <div class="d-flex align-items-center gap-3">
                    <img class="register-brand-logo" src="{{ asset('images/kim-apple-tech-logo.png') }}" alt="Kim Apple Tech logo">
                    <div><div class="brand-title">Kim Apple Tech</div><div class="brand-caption">Smartphone repair · Davao City</div></div>
                </div>
                <div class="visual-wrap" aria-hidden="true"><span class="spark one">✦</span><span class="spark two">✧</span><div class="phone"><div class="phone-screen"><span>Back to<br>connected.</span></div></div></div>
                <h2 class="h3 fw-bold lh-sm mb-2">Your phone deserves<br>a careful repair.</h2>
                <p class="text-white-50 mb-4">Create an account to request service and follow every update.</p>
                <div class="feature-list"><div class="feature"><span class="check">✓</span><span>Request an appointment when it suits you</span></div><div class="feature"><span class="check">✓</span><span>See your confirmed repair schedule</span></div><div class="feature"><span class="check">✓</span><span>Keep your repair history in one place</span></div></div>
            </div>
        </aside>
        <div class="col-md-7 form-panel">
            <div class="eyebrow">Customer account</div>
            <h1>Create your account</h1>
            <p class="intro">Enter your details to get started with your smartphone repair.</p>
            @if($errors->any())<div class="alert alert-danger py-2" role="alert"><strong>Please check your details.</strong><ul class="mb-0 mt-1 ps-3">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            <form method="post" action="/register" autocomplete="on">
                @csrf
                <div class="row g-3">
                    <div class="col-sm-6"><label class="form-label" for="first_name">First name</label><input class="form-control @error('first_name') is-invalid @enderror" id="first_name" name="first_name" value="{{ old('first_name') }}" autocomplete="given-name" maxlength="50" required>@error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-sm-6"><label class="form-label" for="last_name">Last name</label><input class="form-control @error('last_name') is-invalid @enderror" id="last_name" name="last_name" value="{{ old('last_name') }}" autocomplete="family-name" maxlength="50" required>@error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-sm-6"><label class="form-label" for="email">Email address</label><input class="form-control @error('email') is-invalid @enderror" id="email" name="email" type="email" value="{{ old('email') }}" placeholder="you@example.com" autocomplete="email" maxlength="255" required>@error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-sm-6"><label class="form-label" for="contact_number">Contact number</label><input class="form-control @error('contact_number') is-invalid @enderror" id="contact_number" name="contact_number" type="tel" inputmode="numeric" pattern="09[0-9]{9}" value="{{ old('contact_number') }}" placeholder="09XXXXXXXXX" autocomplete="tel" maxlength="11" required>@error('contact_number')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-12"><label class="form-label" for="address">Address <span class="text-muted fw-normal">(optional)</span></label><input class="form-control @error('address') is-invalid @enderror" id="address" name="address" value="{{ old('address') }}" placeholder="Barangay, city or municipality" autocomplete="street-address" maxlength="255">@error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-sm-6"><label class="form-label" for="password">Password</label><input class="form-control @error('password') is-invalid @enderror" id="password" name="password" type="password" autocomplete="new-password" minlength="8" required><div class="password-hint">Use at least 8 characters.</div>@error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-sm-6"><label class="form-label" for="password_confirmation">Confirm password</label><input class="form-control @error('password') is-invalid @enderror" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" minlength="8" required>@error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                </div>
                <div class="form-check mt-4">
                    <input class="form-check-input @error('data_privacy_consent') is-invalid @enderror" type="checkbox" value="1" id="data_privacy_consent" name="data_privacy_consent" @checked(old('data_privacy_consent')) required>
                    <label class="form-check-label small" for="data_privacy_consent">I agree to the collection and processing of my personal information under the <a href="{{ route('privacy-notice') }}" target="_blank" rel="noopener">Data Privacy Notice</a>, in accordance with the Data Privacy Act of 2012 (RA 10173).</label>
                    @error('data_privacy_consent')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <button class="btn btn-primary submit-btn w-100 mt-4" type="submit">Create account</button>
            </form>
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-4"><p class="signin mb-0">Already have an account? <a href="/login">Sign in</a></p><span class="privacy-note"><svg width="14" height="14" viewBox="0 0 16 16" fill="none"><path d="M4 7V5a4 4 0 0 1 8 0v2M3 7h10v7H3V7Z" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg> Your details stay private</span></div>
        </div>
    </section>
    <p class="text-center text-secondary small mt-3 mb-0">© {{ date('Y') }} Kim Apple Tech · Davao City</p>
</main>
</body>
</html>
