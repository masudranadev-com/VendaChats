@extends('admin.master')

@section('title', $title)

@section('admin.content')
  @php
    $actions = [
      [
        'title' => 'Launch Earbuds Price Defense Campaign',
        'why' => 'Competitor undercut detected for Wireless Earbuds Pro.',
        'impact' => '+8-12% conversion lift',
        'confidence' => '92%',
        'eta' => '24h',
        'priority' => 'High',
      ],
      [
        'title' => 'Enable Follow-up Sequence for Dropped Buyers',
        'why' => 'Recovery flow is inactive for 2 high-intent segments.',
        'impact' => '+6% recovered checkouts',
        'confidence' => '87%',
        'eta' => '12h',
        'priority' => 'High',
      ],
      [
        'title' => 'Refresh Creative in Campaign #C-349',
        'why' => 'CTR dropped for 3 consecutive days.',
        'impact' => '+15% CTR potential',
        'confidence' => '79%',
        'eta' => '48h',
        'priority' => 'Medium',
      ],
    ];

    $issues = [
      ['label' => 'Response Delay', 'value' => 'Average reply time is 11m 20s', 'severity' => 'warning'],
      ['label' => 'Price Pressure', 'value' => '2 competitors pricing below your top SKU', 'severity' => 'danger'],
      ['label' => 'Campaign Fatigue', 'value' => 'Frequency > 4.5 for retargeting set', 'severity' => 'warning'],
      ['label' => 'Recovery Gap', 'value' => '31 buyers dropped without follow-up', 'severity' => 'danger'],
    ];

    $playbooks = [
      ['title' => 'Price-sensitive Rescue', 'steps' => 'Discount + urgency copy + 6h reminder', 'fit' => 'High intent, low margin products'],
      ['title' => 'Quality-focused Push', 'steps' => 'Social proof + product comparison + warranty mention', 'fit' => 'Premium SKUs'],
      ['title' => 'Cart Recovery Sprint', 'steps' => 'WhatsApp nudge + Messenger fallback + last-call promo', 'fit' => 'Abandoned checkout users'],
    ];

    $tracker = [
      ['task' => 'Fix Earbuds Pricing Gap', 'priority' => 'High', 'owner' => 'Marketing', 'status' => 'In Progress', 'due' => 'Today', 'result' => '-'],
      ['task' => 'Activate Recovery Template Set', 'priority' => 'High', 'owner' => 'Automation', 'status' => 'Open', 'due' => 'Today', 'result' => '-'],
      ['task' => 'Creative Batch v4', 'priority' => 'Medium', 'owner' => 'Design', 'status' => 'Done', 'due' => 'Yesterday', 'result' => 'CTR +9.1%'],
      ['task' => 'Messenger SLA Tuning', 'priority' => 'Low', 'owner' => 'Support', 'status' => 'Snoozed', 'due' => '2 days', 'result' => '-'],
    ];

    $history = [
      ['date' => 'Today 09:30 AM', 'text' => 'AI recommended price defense campaign for earbuds.'],
      ['date' => 'Yesterday 06:10 PM', 'text' => 'Recovery automation action applied for abandoned buyers.'],
      ['date' => 'Yesterday 01:20 PM', 'text' => 'Campaign creative refresh completed and tracked.'],
      ['date' => '2 days ago', 'text' => 'Messenger delay alert resolved via quick-reply tuning.'],
    ];
  @endphp

  <div class="page-header coach-page-header">
    <div>
      <h1 class="page-title">{{ $title }}</h1>
      <p class="page-subtitle">{{ $subtitle }}</p>
    </div>

    <div class="coach-header-actions">
      <button type="button" class="btn btn-secondary">Export Weekly Summary</button>
      <button type="button" class="btn btn-primary">Apply Top Actions</button>
    </div>
  </div>

  <section class="card coach-filter-card">
    <div class="card-header">
      <h3 class="card-title">Filter</h3>
      <span class="badge badge-info">UI only</span>
    </div>

    <form class="coach-filter-form">
      <div class="coach-filter-grid">
        <div class="form-group">
          <label class="form-label">Time Range</label>
          <select class="form-select">
            <option>Today</option>
            <option>Last 7 Days</option>
            <option>Last 30 Days</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Channel</label>
          <select class="form-select">
            <option>All Channels</option>
            <option>Facebook</option>
            <option>Messenger</option>
            <option>WhatsApp</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Priority</label>
          <select class="form-select">
            <option>All Priorities</option>
            <option>High</option>
            <option>Medium</option>
            <option>Low</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Status</label>
          <select class="form-select">
            <option>All Status</option>
            <option>Open</option>
            <option>In Progress</option>
            <option>Done</option>
            <option>Snoozed</option>
          </select>
        </div>
      </div>
    </form>
  </section>

  <section class="coach-kpi-grid mt-xl">
    <article class="coach-kpi-card coach-kpi-highlight">
      <span>Weekly Health Score</span>
      <strong>81/100</strong>
      <small>+6 points from last week</small>
    </article>
    <article class="coach-kpi-card">
      <span>Recovery Rate</span>
      <strong>32%</strong>
      <small>Target 40%</small>
    </article>
    <article class="coach-kpi-card">
      <span>Campaign ROI</span>
      <strong>3.4x</strong>
      <small>Stable trend</small>
    </article>
    <article class="coach-kpi-card">
      <span>Avg Reply Time</span>
      <strong>11m</strong>
      <small>Needs improvement</small>
    </article>
  </section>

  <div class="coach-layout mt-xl">
    <section class="card">
      <div class="card-header">
        <h3 class="card-title">Top AI Actions</h3>
        <span class="badge badge-primary">Priority queue</span>
      </div>

      <div class="coach-actions-list">
        @foreach ($actions as $action)
          @php
            $priorityClass = $action['priority'] === 'High' ? 'badge-danger' : ($action['priority'] === 'Medium' ? 'badge-warning' : 'badge-success');
          @endphp
          <article class="coach-action-card">
            <div class="coach-action-top">
              <h4>{{ $action['title'] }}</h4>
              <span class="badge {{ $priorityClass }}">{{ $action['priority'] }}</span>
            </div>
            <p>{{ $action['why'] }}</p>
            <div class="coach-action-meta">
              <span><strong>Impact:</strong> {{ $action['impact'] }}</span>
              <span><strong>Confidence:</strong> {{ $action['confidence'] }}</span>
              <span><strong>ETA:</strong> {{ $action['eta'] }}</span>
            </div>
            <div class="coach-action-buttons">
              <button type="button" class="btn btn-primary btn-sm">Apply</button>
              <button type="button" class="btn btn-secondary btn-sm">Snooze</button>
            </div>
          </article>
        @endforeach
      </div>
    </section>

    <section class="card coach-side-card">
      <div class="card-header">
        <h3 class="card-title">Detected Problems</h3>
        <span class="badge badge-warning">Live alerts</span>
      </div>

      <div class="coach-issue-list">
        @foreach ($issues as $issue)
          @php
            $tone = $issue['severity'] === 'danger' ? 'coach-issue-danger' : 'coach-issue-warning';
          @endphp
          <article class="coach-issue-card {{ $tone }}">
            <strong>{{ $issue['label'] }}</strong>
            <p>{{ $issue['value'] }}</p>
          </article>
        @endforeach
      </div>
    </section>
  </div>

  <div class="coach-layout mt-xl">
    <section class="card">
      <div class="card-header">
        <h3 class="card-title">Execution Tracker</h3>
        <span class="badge badge-info">Task board</span>
      </div>

      <div class="table-container">
        <table class="table">
          <thead>
            <tr>
              <th>Task</th>
              <th>Priority</th>
              <th>Owner</th>
              <th>Status</th>
              <th>Due</th>
              <th>Result</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($tracker as $row)
              @php
                $priorityClass = $row['priority'] === 'High' ? 'badge-danger' : ($row['priority'] === 'Medium' ? 'badge-warning' : 'badge-success');
                $statusClass = $row['status'] === 'Done' ? 'badge-success' : ($row['status'] === 'In Progress' ? 'badge-primary' : ($row['status'] === 'Snoozed' ? 'badge-warning' : 'badge-info'));
              @endphp
              <tr>
                <td class="coach-cell-strong">{{ $row['task'] }}</td>
                <td><span class="badge {{ $priorityClass }}">{{ $row['priority'] }}</span></td>
                <td>{{ $row['owner'] }}</td>
                <td><span class="badge {{ $statusClass }}">{{ $row['status'] }}</span></td>
                <td>{{ $row['due'] }}</td>
                <td>{{ $row['result'] }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </section>

    <section class="card coach-side-card">
      <div class="card-header">
        <h3 class="card-title">Learning History</h3>
        <span class="badge badge-success">Recent updates</span>
      </div>

      <ul class="coach-history-list">
        @foreach ($history as $item)
          <li>
            <span>{{ $item['date'] }}</span>
            <p>{{ $item['text'] }}</p>
          </li>
        @endforeach
      </ul>
    </section>
  </div>

  <section class="card mt-xl">
    <div class="card-header">
      <h3 class="card-title">Optimization Playbooks</h3>
      <span class="badge badge-primary">Action templates</span>
    </div>

    <div class="coach-playbook-grid">
      @foreach ($playbooks as $playbook)
        <article class="coach-playbook-card">
          <h4>{{ $playbook['title'] }}</h4>
          <p><strong>Steps:</strong> {{ $playbook['steps'] }}</p>
          <p><strong>Best Fit:</strong> {{ $playbook['fit'] }}</p>
          <button type="button" class="btn btn-secondary btn-sm">Use Playbook</button>
        </article>
      @endforeach
    </div>
  </section>
@endsection
