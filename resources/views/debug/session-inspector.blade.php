<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Session Inspector</title>
  <style>
    :root {
      --bg: #0d1117;
      --panel: #161b22;
      --text: #e6edf3;
      --muted: #9da7b3;
      --border: #30363d;
      --accent: #2f81f7;
      --success: #1f9d55;
      --danger: #dc3545;
      --warning: #d29922;
    }

    * { box-sizing: border-box; }

    body {
      margin: 0;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
      background: var(--bg);
      color: var(--text);
      line-height: 1.5;
    }

    .wrap {
      max-width: 1200px;
      margin: 0 auto;
      padding: 24px;
      display: grid;
      gap: 16px;
    }

    .card {
      background: var(--panel);
      border: 1px solid var(--border);
      border-radius: 12px;
      padding: 16px;
    }

    h1, h2 {
      margin: 0 0 8px;
      font-weight: 700;
    }

    h1 { font-size: 24px; }
    h2 { font-size: 18px; }

    p { margin: 0 0 12px; color: var(--muted); }

    .meta {
      display: flex;
      gap: 16px;
      flex-wrap: wrap;
      font-size: 14px;
      color: var(--muted);
    }

    .meta code { color: var(--text); }

    .status {
      border: 1px solid rgba(31,157,85,.35);
      background: rgba(31,157,85,.15);
      color: #9ef0bf;
      padding: 10px 12px;
      border-radius: 8px;
      margin-bottom: 12px;
      font-size: 14px;
    }

    .error {
      border: 1px solid rgba(220,53,69,.45);
      background: rgba(220,53,69,.15);
      color: #ffb5bc;
      padding: 10px 12px;
      border-radius: 8px;
      margin-bottom: 12px;
      font-size: 14px;
    }

    .grid {
      display: grid;
      gap: 16px;
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    label {
      display: block;
      font-size: 13px;
      margin-bottom: 6px;
      color: var(--muted);
      font-weight: 600;
    }

    input, textarea, button {
      width: 100%;
      border-radius: 8px;
      border: 1px solid var(--border);
      background: #0b0f14;
      color: var(--text);
      padding: 10px 12px;
      font-size: 14px;
      font-family: inherit;
    }

    textarea { min-height: 90px; resize: vertical; }

    button {
      cursor: pointer;
      font-weight: 600;
      background: #111827;
    }

    button.primary { border-color: rgba(47,129,247,.5); background: rgba(47,129,247,.2); }
    button.warning { border-color: rgba(210,153,34,.5); background: rgba(210,153,34,.15); }
    button.danger { border-color: rgba(220,53,69,.5); background: rgba(220,53,69,.15); }

    pre {
      margin: 0;
      background: #0b0f14;
      border: 1px solid var(--border);
      border-radius: 10px;
      padding: 12px;
      overflow: auto;
      font-size: 12px;
      line-height: 1.45;
      white-space: pre-wrap;
      word-break: break-word;
    }

    .table-wrap {
      overflow: auto;
      max-width: 100%;
    }

    table {
      width: max-content;
      min-width: 100%;
      border-collapse: collapse;
      font-size: 13px;
    }

    th, td {
      border-bottom: 1px solid var(--border);
      text-align: left;
      padding: 8px;
      vertical-align: top;
      white-space: nowrap;
      word-wrap: normal;
    }

    th {
      color: var(--muted);
      font-size: 12px;
      text-transform: uppercase;
      letter-spacing: .04em;
    }

    .actions {
      display: grid;
      gap: 10px;
      grid-template-columns: 1fr auto;
      align-items: end;
    }

    .row {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
    }

    .row button { width: auto; min-width: 160px; }

    @media (max-width: 900px) {
      .grid { grid-template-columns: 1fr; }
      .actions { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>
  <div class="wrap">
    <div class="card">
      <h1>Session Inspector</h1>
      <p>Local-only debug tool for Laravel session + browser storage testing.</p>
      <div class="meta">
        <div>Session ID: <code>{{ $sessionId }}</code></div>
        <div>Driver: <code>{{ $sessionDriver }}</code></div>
        <div>Route: <code>{{ request()->path() }}</code></div>
      </div>
    </div>

    <div class="card">
      @if (session('status'))
        <div class="status">{{ session('status') }}</div>
      @endif
      @if ($errors->any())
        <div class="error">{{ $errors->first() }}</div>
      @endif

      <div class="grid">
        <form method="POST" action="{{ route('debug.session.set') }}">
          @csrf
          <h2>Set Session Key</h2>
          <label for="set-key">Key</label>
          <input id="set-key" type="text" name="key" placeholder="example.key" required>
          <label for="set-value" style="margin-top: 10px;">Value (string or JSON)</label>
          <textarea id="set-value" name="value" placeholder='{"enabled":true}'></textarea>
          <button class="primary" type="submit" style="margin-top: 10px;">Set Session</button>
        </form>

        <div>
          <form method="POST" action="{{ route('debug.session.forget') }}">
            @csrf
            <h2>Forget Session Key</h2>
            <label for="forget-key">Key</label>
            <div class="actions">
              <input id="forget-key" type="text" name="key" placeholder="example.key" required>
              <button class="warning" type="submit">Forget Key</button>
            </div>
          </form>

          <form method="POST" action="{{ route('debug.session.flush') }}" style="margin-top: 16px;">
            @csrf
            <h2>Danger Zone</h2>
            <p>Flush will remove all Laravel session keys for this browser session.</p>
            <button class="danger" type="submit">Flush Entire Session</button>
          </form>
        </div>
      </div>
    </div>

    <div class="card">
      <h2>Laravel Session Data</h2>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th style="width: 32%;">Key</th>
              <th>Value</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($sessionData as $key => $value)
              <tr>
                <td><code>{{ $key }}</code></td>
                <td><pre>{{ json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre></td>
              </tr>
            @empty
              <tr>
                <td colspan="2">No session data currently stored.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    <div class="card">
      <h2>Browser Storage (Client Side)</h2>
      <p>Shows `localStorage` and `sessionStorage` from your browser.</p>
      <div class="row">
        <button type="button" class="primary" id="refreshStorage">Refresh Storage View</button>
        <button type="button" id="setDemoStorage">Set Demo Values</button>
        <button type="button" class="danger" id="clearLocalStorage">Clear localStorage</button>
        <button type="button" class="danger" id="clearSessionStorage">Clear sessionStorage</button>
      </div>
      <h2 style="margin-top: 16px;">localStorage</h2>
      <pre id="localStorageDump">{}</pre>
      <h2 style="margin-top: 16px;">sessionStorage</h2>
      <pre id="sessionStorageDump">{}</pre>
    </div>

    <div class="card">
      <h2>Project Session/Storage Usage Search</h2>
      <p>Run this command in terminal to find all session/storage usage quickly.</p>
      <pre>rg -n "session\(|->session\(|Session::|localStorage|sessionStorage" app resources routes public tests config -S</pre>
    </div>
  </div>

  <script>
    function dumpStorage(storage) {
      const output = {};
      for (let i = 0; i < storage.length; i += 1) {
        const key = storage.key(i);
        output[key] = storage.getItem(key);
      }
      return output;
    }

    function renderStorage() {
      const localDump = document.getElementById('localStorageDump');
      const sessionDump = document.getElementById('sessionStorageDump');
      localDump.textContent = JSON.stringify(dumpStorage(window.localStorage), null, 2);
      sessionDump.textContent = JSON.stringify(dumpStorage(window.sessionStorage), null, 2);
    }

    document.getElementById('refreshStorage').addEventListener('click', renderStorage);
    document.getElementById('setDemoStorage').addEventListener('click', () => {
      window.localStorage.setItem('debug.local.example', JSON.stringify({ timestamp: Date.now(), from: 'session-inspector' }));
      window.sessionStorage.setItem('debug.session.example', JSON.stringify({ timestamp: Date.now(), from: 'session-inspector' }));
      renderStorage();
    });
    document.getElementById('clearLocalStorage').addEventListener('click', () => {
      window.localStorage.clear();
      renderStorage();
    });
    document.getElementById('clearSessionStorage').addEventListener('click', () => {
      window.sessionStorage.clear();
      renderStorage();
    });

    renderStorage();
  </script>
</body>
</html>
