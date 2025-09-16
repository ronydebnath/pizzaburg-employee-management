<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Employee Portal Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif; background:#0f172a; color:#e2e8f0; }
        .container { max-width: 420px; margin: 10vh auto; background:#111827; border:1px solid #1f2937; border-radius:12px; padding:28px; }
        h1 { font-size: 22px; margin:0 0 16px; }
        label { display:block; font-size:13px; margin:12px 0 6px; color:#94a3b8; }
        input { width:100%; padding:10px 12px; border-radius:8px; border:1px solid #334155; background:#0b1220; color:#e2e8f0; }
        button { width:100%; margin-top:16px; background:#f59e0b; color:#111827; border:0; border-radius:8px; padding:10px 14px; font-weight:700; cursor:pointer; }
        .error { color:#fca5a5; font-size:13px; margin-top:8px; }
        .muted { color:#94a3b8; font-size:12px; text-align:center; margin-top:14px; }
    </style>
    @vite('resources/css/app.css')
    @vite('resources/js/app.js')
    @csrf
</head>
<body>
    <div class="container">
        <h1>Employee Portal</h1>
        <form method="post" action="{{ route('portal.login.submit') }}">
            @csrf
            <label>Email</label>
            <input name="email" type="email" value="{{ old('email') }}" required>
            @error('email')
            <div class="error">{{ $message }}</div>
            @enderror

            <label>Password</label>
            <input name="password" type="password" required>
            @error('password')
            <div class="error">{{ $message }}</div>
            @enderror

            <button type="submit">Sign in</button>
        </form>
        <div class="muted">Forgot password? Contact HR.</div>
    </div>
</body>
</html>


