<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facebook OAuth Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f7fb;
            color: #1f2937;
            margin: 0;
        }
        .container {
            max-width: 980px;
            margin: 32px auto;
            padding: 24px;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
        }
        h1, h2, h3 {
            margin: 0 0 12px;
        }
        .section {
            margin-top: 26px;
            padding-top: 18px;
            border-top: 1px solid #e5e7eb;
        }
        .grid {
            display: grid;
            gap: 16px;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        }
        .card {
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 14px;
            background: #fafafa;
        }
        pre {
            background: #111827;
            color: #f9fafb;
            padding: 12px;
            border-radius: 8px;
            overflow-x: auto;
            font-size: 12px;
        }
        form {
            display: grid;
            gap: 10px;
        }
        label {
            font-size: 13px;
            font-weight: 700;
        }
        input, select {
            width: 100%;
            padding: 10px 11px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            box-sizing: border-box;
        }
        .btn {
            display: inline-block;
            padding: 11px 14px;
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
        .btn-danger {
            background: #ef4444;
            color: #fff;
        }
        .actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 10px;
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
        <h1>Facebook OAuth Dashboard</h1>
        <p>Current Graph API version: <code>{{ $graphVersion }}</code></p>

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

        <div class="actions">
            <a class="btn btn-secondary" href="{{ route('facebook.oauth') }}">Back to OAuth Page</a>
            <a class="btn btn-secondary" href="{{ route('facebook.auth.redirect') }}">Reconnect Facebook</a>
            <form method="POST" action="{{ route('facebook.disconnect') }}">
                @csrf
                <button class="btn btn-danger" type="submit">Clear Session Data</button>
            </form>
        </div>

        <section class="section">
            <h2>OAuth Result</h2>

            @if (! $user)
                <p>No connected Facebook user in session yet. Run OAuth from the login page first.</p>
            @else
                <div class="grid">
                    <div class="card">
                        <h3>User Info</h3>
                        <pre>{{ json_encode($user, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                    </div>
                    <div class="card">
                        <h3>Pages (with access tokens)</h3>
                        <pre>{{ json_encode($pages, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                    </div>
                </div>
            @endif
        </section>

        <section class="section">
            <h2>Send Messenger Message</h2>
            <form method="POST" action="{{ route('facebook.send-message') }}">
                @csrf
                <div>
                    <label for="send-page-id">Page ID</label>
                    <select id="send-page-id" name="page_id" required>
                        <option value="">Select a page</option>
                        @foreach ($pages as $page)
                            <option value="{{ $page['id'] ?? '' }}">{{ $page['name'] ?? 'Unnamed Page' }} ({{ $page['id'] ?? 'No ID' }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="recipient-psid">PSID User ID</label>
                    <input id="recipient-psid" type="text" name="recipient_psid" placeholder="PSID_USER_ID" required>
                </div>
                <div>
                    <label for="message-text">Message</label>
                    <input id="message-text" type="text" name="message_text" placeholder="Hello from Laravel app!" required>
                </div>
                <button type="submit" class="btn btn-primary">Send Message</button>
            </form>
        </section>

        <section class="section">
            <h2>Reply to Comment</h2>
            <form method="POST" action="{{ route('facebook.reply-comment') }}">
                @csrf
                <div>
                    <label for="comment-page-id">Page ID</label>
                    <select id="comment-page-id" name="page_id" required>
                        <option value="">Select a page</option>
                        @foreach ($pages as $page)
                            <option value="{{ $page['id'] ?? '' }}">{{ $page['name'] ?? 'Unnamed Page' }} ({{ $page['id'] ?? 'No ID' }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="comment-id">Comment ID</label>
                    <input id="comment-id" type="text" name="comment_id" placeholder="COMMENT_ID" required>
                </div>
                <div>
                    <label for="comment-reply">Reply Message</label>
                    <input id="comment-reply" type="text" name="comment_reply" placeholder="Thanks for your comment!" required>
                </div>
                <button type="submit" class="btn btn-primary">Reply Comment</button>
            </form>
        </section>

        <section class="section">
            <h2>Subscribe Page Webhook</h2>
            <form method="POST" action="{{ route('facebook.subscribe-webhook') }}">
                @csrf
                <div>
                    <label for="webhook-page-id">Page ID</label>
                    <select id="webhook-page-id" name="page_id" required>
                        <option value="">Select a page</option>
                        @foreach ($pages as $page)
                            <option value="{{ $page['id'] ?? '' }}">{{ $page['name'] ?? 'Unnamed Page' }} ({{ $page['id'] ?? 'No ID' }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="subscribed-fields">Subscribed Fields</label>
                    <input id="subscribed-fields" type="text" name="subscribed_fields" value="feed,messages" required>
                </div>
                <button type="submit" class="btn btn-primary">Subscribe Webhook</button>
            </form>
        </section>

        <section class="section">
            <h2>Webhook Setup Reference</h2>
            <p>Webhook verify endpoint: <code>{{ $webhookVerifyUrl }}</code></p>
            <p>Verify token from env: <code>{{ $verifyToken ?: 'Not configured' }}</code></p>
            <p>App ID loaded: <code>{{ $appId ?: 'Not configured' }}</code></p>
        </section>

        @if (session('facebook_api_response'))
            <section class="section">
                <h2>Last Graph API Response</h2>
                <pre>{{ json_encode(session('facebook_api_response'), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
            </section>
        @endif
    </main>
</body>
</html>
