<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0f172a">
    <title>Login - Cipher Academy</title>
    <link rel="icon" type="image/png" href="{{ asset('images/school_logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="auth-page">
    <main class="auth-layout">
        <section class="auth-panel">
            <div class="auth-brand">
                <span class="brand-mark"><img src="{{ asset('images/school_logo.png') }}" alt="Cipher Academy logo"></span>
                <div>
                    <h1>Cipher Academy</h1>
                </div>
            </div>

            @if($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="form-stack">
                @csrf
                <div class="auth-heading">
                    <h2>Sign in</h2>
                </div>
                <div class="field">
                    <label>Employee ID</label>
                    <input class="form-control" name="employee_id" value="{{ old('employee_id') }}" placeholder="ADMIN-001" required autofocus>
                </div>
                <div class="field">
                    <label>Password</label>
                    <input type="password" class="form-control" name="password" placeholder="Enter your password" required>
                </div>
                <label class="check-row">
                    <input type="checkbox" name="remember">
                    <span>Remember me</span>
                </label>
                <button class="btn btn-primary btn-block">Log In</button>
            </form>
        </section>
    </main>
</body>
</html>
