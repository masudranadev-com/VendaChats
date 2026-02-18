<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facebook OAuth Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f7fb;
            color: #1f2937;
            margin: 0;
        }
        .container {
            max-width: 760px;
            margin: 48px auto;
            padding: 24px;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
        }
        h1, h2 {
            margin: 0 0 12px;
        }
        p {
            margin: 0 0 12px;
            line-height: 1.45;
        }
        .btn {
            display: inline-block;
            padding: 12px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 700;
            border: 0;
            cursor: pointer;
        }
        .btn-primary {
            background: #1877f2;
            color: #fff;
        }
        .btn-secondary {
            background: #e5e7eb;
            color: #111827;
        }
        .actions {
            margin-top: 20px;
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }
        .alert {
            padding: 10px 12px;
            border-radius: 8px;
            margin-bottom: 12px;
        }
        .alert-success {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #065f46;
        }
        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }
        .list {
            margin: 12px 0 0;
            padding-left: 18px;
        }
        code {
            background: #f3f4f6;
            border: 1px solid #e5e7eb;
            padding: 2px 6px;
            border-radius: 6px;
        }
    </style>
</head>
<body>
    <main class="container">
        <h1>Facebook OAuth Login</h1>
        <p>This page starts the Facebook OAuth flow and stores user info plus page access tokens in session.</p>

        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-error">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <h2>Configuration</h2>
        <p>Graph Version: <code>{{ $graphVersion }}</code></p>
        <p>Redirect URI: <code>{{ $redirectUri }}</code></p>

        @if ($missingConfig !== [])
            <div class="alert alert-error">
                Missing environment keys:
                <ul class="list">
                    @foreach ($missingConfig as $key)
                        <li>{{ $key }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="actions">
            @if ($missingConfig === [])
                <a class="btn btn-primary" href="{{ route('facebook.auth.redirect') }}">Login with Facebook</a>
            @else
                <button class="btn btn-secondary" type="button" disabled>Fix env keys to continue</button>
            @endif
            <a class="btn btn-secondary" href="{{ route('facebook.dashboard') }}">Open OAuth Dashboard</a>
        </div>

        @if ($connectedUser)
            <p style="margin-top: 18px;">
                Connected user: <strong>{{ $connectedUser['name'] ?? 'Unknown' }}</strong>
                ({{ $connectedUser['id'] ?? 'No ID' }})
            </p>
            <p>Connected pages in session: <strong>{{ count($connectedPages) }}</strong></p>
        @endif
    </main>
</body>
</html>
