<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Employee Portal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif; background:#0f172a; color:#e2e8f0; }
        .wrap { max-width: 960px; margin: 4vh auto; padding: 0 16px; }
        .top { display:flex; justify-content: space-between; align-items:center; margin-bottom: 18px; }
        .card { background:#111827; border:1px solid #1f2937; border-radius:12px; padding:18px; margin-bottom:16px; }
        .muted { color:#94a3b8; }
        .grid { display:grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap:16px; }
        a.btn { background:#f59e0b; color:#111827; border-radius:8px; padding:8px 12px; font-weight:700; text-decoration:none; }
        table { width:100%; border-collapse:collapse; }
        td { padding:6px 0; border-bottom:1px solid #1f2937; }
        h2 { margin:0 0 8px; font-size:18px; }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="top">
            <div>
                <div class="muted">Welcome</div>
                <h1 style="margin:4px 0 0; font-size:22px;">{{ $user->name }}</h1>
            </div>
            <div>
                <a class="btn" href="{{ route('portal.logout') }}" onclick="event.preventDefault(); document.getElementById('logout').submit();">Logout</a>
                <form id="logout" method="post" action="{{ route('portal.logout') }}" style="display:none;">@csrf</form>
            </div>
        </div>

        <div class="grid">
            <div class="card">
                <h2>Profile</h2>
                @if($profile)
                <table>
                    <tr><td>Name</td><td>{{ $profile->full_name }}</td></tr>
                    <tr><td>Branch</td><td>{{ $profile->branch?->name ?? '—' }}</td></tr>
                    <tr><td>Position</td><td>{{ $profile->position?->name ?? '—' }}</td></tr>
                    <tr><td>Employee ID</td><td>{{ $profile->employee_id ?? '—' }}</td></tr>
                    <tr><td>Joining Date</td><td>{{ optional($profile->joining_date)->toDateString() ?? '—' }}</td></tr>
                </table>
                @else
                <div class="muted">No employee profile found yet.</div>
                @endif
            </div>

            <div class="card">
                <h2>Contract</h2>
                @if($latestContract)
                    <div>Status: <strong>{{ ucfirst($latestContract->status) }}</strong></div>
                    <div>Number: {{ $latestContract->contract_number }}</div>
                @else
                    <div class="muted">No contract yet.</div>
                @endif
            </div>

            <div class="card">
                <h2>KYC</h2>
                @if($latestKyc)
                    <div>Status: <strong>{{ ucfirst($latestKyc->status) }}</strong></div>
                    <div>Type: {{ str_replace('_',' ', $latestKyc->type) }}</div>
                @else
                    <div class="muted">No KYC record yet.</div>
                @endif
            </div>

            <div class="card">
                <h2>Notices</h2>
                <div class="muted">No notices yet.</div>
            </div>
        </div>
    </div>
</body>
</html>


