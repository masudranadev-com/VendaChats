@extends('admin.master')

@section('title', $title)

@section('admin.content')
  @php
    $asOf = now()->format('d M Y, h:i A');
    $todayDate = now()->toDateString();
    $tomorrowDate = now()->copy()->addDay()->toDateString();
    $threeDaysDate = now()->copy()->addDays(3)->toDateString();

    $workingModules = [
      [
        'title' => 'Competition Monitor',
        'detail' => 'Domain add and re-scan flow works with session-backed competitor state.',
        'route' => route('admin.competition'),
        'cta' => 'Open Monitor',
      ],
      [
        'title' => 'Orders - Discount and Invoice',
        'detail' => 'Manual discount apply/remove and invoice preview are implemented.',
        'route' => route('admin.orders'),
        'cta' => 'Open Orders',
      ],
      [
        'title' => 'Bot Settings - Facebook OAuth',
        'detail' => 'Connect/disconnect Facebook and load managed pages from Graph API.',
        'route' => route('admin.bot-settings'),
        'cta' => 'Open Bot Settings',
      ],
      [
        'title' => 'Users and Posts Filters',
        'detail' => 'Users and post activity filters are available for manual review flows.',
        'route' => route('admin.users'),
        'cta' => 'Open Users',
      ],
    ];

    $actions = [
      [
        'title' => 'Re-scan failed competitor domains',
        'why' => 'Competition module already supports per-domain sync in the table.',
        'impact' => 'Keeps pricing intelligence fresh for manual decisions.',
        'effort' => 'Low',
        'owner' => 'Growth',
        'priority' => 'High',
        'route' => route('admin.competition', ['status' => 'failed']),
        'cta' => 'Open Failed Queue',
      ],
      [
        'title' => 'Review highest-comment posts from last 7 days',
        'why' => 'Post module provides comment-based sorting and time filter options.',
        'impact' => 'Helps support team prioritize high-traffic conversations.',
        'effort' => 'Low',
        'owner' => 'Support',
        'priority' => 'High',
        'route' => route('admin.posts', ['order_by' => 'highest_comments', 'days' => '7']),
        'cta' => 'Open Post Ranking',
      ],
      [
        'title' => 'Audit price-sensitive angry users',
        'why' => 'Users module supports emotion and buyer-type filtering.',
        'impact' => 'Improves retention through targeted manual follow-up.',
        'effort' => 'Medium',
        'owner' => 'CRM',
        'priority' => 'Medium',
        'route' => route('admin.users', ['emotion' => 'Angry', 'user_type' => 'Price-sensitive']),
        'cta' => 'Open Filtered Users',
      ],
      [
        'title' => 'Check Facebook connection health',
        'why' => 'Bot settings can fail if Facebook token/session state is invalid.',
        'impact' => 'Prevents silent disruption in Messenger/comment automation.',
        'effort' => 'Low',
        'owner' => 'Automation',
        'priority' => 'Medium',
        'route' => route('admin.bot-settings'),
        'cta' => 'Verify Connection',
      ],
    ];

    $gaps = [
      [
        'label' => 'Autonomous execution engine',
        'value' => 'Not implemented. Coach cannot auto-run pricing, campaign, or bot actions across modules.',
        'severity' => 'danger',
      ],
      [
        'label' => 'Persistent coach task storage',
        'value' => 'Not implemented. Tasks and coaching history are not saved in a dedicated database model.',
        'severity' => 'danger',
      ],
      [
        'label' => 'Background workers and scheduling',
        'value' => 'No job queue workflow is wired here for timed execution, retries, or batch automation.',
        'severity' => 'danger',
      ],
      [
        'label' => 'Cross-channel real-time ingestion',
        'value' => 'Website, WhatsApp, and Instagram intelligence is mostly static/demo in current admin flows.',
        'severity' => 'warning',
      ],
      [
        'label' => 'Unified AI confidence scoring',
        'value' => 'No single scoring pipeline currently aggregates signals from orders, posts, users, and competition.',
        'severity' => 'warning',
      ],
    ];

    $tracker = [
      [
        'task' => 'Sync failed competitor domains',
        'priority' => 'High',
        'owner' => 'Growth',
        'status' => 'Manual',
        'due' => $todayDate,
        'result' => 'Run Competition Sync manually',
      ],
      [
        'task' => 'Review top comments queue',
        'priority' => 'High',
        'owner' => 'Support',
        'status' => 'Manual',
        'due' => $todayDate,
        'result' => 'Sort by highest comments',
      ],
      [
        'task' => 'Reconnect Facebook if needed',
        'priority' => 'Medium',
        'owner' => 'Automation',
        'status' => 'Partial',
        'due' => $tomorrowDate,
        'result' => 'Use OAuth reconnect from Bot Settings',
      ],
      [
        'task' => 'Design queue-based automation architecture',
        'priority' => 'Medium',
        'owner' => 'Engineering',
        'status' => 'Not Started',
        'due' => $threeDaysDate,
        'result' => 'Required for autonomous coach',
      ],
    ];

    $history = [
      ['date' => $asOf, 'text' => 'Coach page aligned with implemented admin modules and route capabilities.'],
      ['date' => now()->copy()->subHours(2)->format('d M Y, h:i A'), 'text' => 'Gap list updated to highlight hard features not yet available.'],
      ['date' => now()->copy()->subDay()->format('d M Y, h:i A'), 'text' => 'Manual action links mapped to existing pages: competition, posts, users, bot settings, orders.'],
      ['date' => now()->copy()->subDays(2)->format('d M Y, h:i A'), 'text' => 'Coach transformed from generic AI claims into execution-first runbook mode.'],
    ];

    $playbooks = [
      [
        'title' => 'Price Pressure Sweep',
        'steps' => 'Open failed competitor queue, re-sync domains, then review affected SKUs manually.',
        'fit' => 'When market pricing changes quickly',
        'route' => route('admin.competition'),
      ],
      [
        'title' => 'Comment Heat Response',
        'steps' => 'Sort posts by highest comments in 7-day window, then assign manual replies.',
        'fit' => 'When support load spikes',
        'route' => route('admin.posts', ['order_by' => 'highest_comments', 'days' => '7']),
      ],
      [
        'title' => 'Retention Risk Pass',
        'steps' => 'Filter angry + price-sensitive users, then pair with order history for follow-up.',
        'fit' => 'When drop-off risk is rising',
        'route' => route('admin.users', ['emotion' => 'Angry', 'user_type' => 'Price-sensitive']),
      ],
    ];

    $hardCount = collect($gaps)->where('severity', 'danger')->count();
    $workingCount = count($workingModules);
    $actionCount = count($actions);
    $partialCount = 2;
  @endphp

  <div class="page-header coach-page-header">
    <div>
      <h1 class="page-title">{{ $title }}</h1>
      <p class="page-subtitle">{{ $subtitle }}</p>
    </div>

    <div class="coach-header-actions">
      <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">Dashboard</a>
      <a href="{{ route('admin.competition') }}" class="btn btn-primary">Start From Competition</a>
    </div>
  </div>

  <section class="card coach-filter-card">
    <div class="card-header">
      <h3 class="card-title">Reality Scope</h3>
      <span class="badge badge-info">As of {{ $asOf }}</span>
    </div>

    <div class="coach-filter-grid">
      <div class="form-group">
        <label class="form-label">Coach Mode</label>
        <select class="form-select" disabled>
          <option>Manual guidance only</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Primary Data Type</label>
        <select class="form-select" disabled>
          <option>Static plus session-backed data</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Write Capability</label>
        <select class="form-select" disabled>
          <option>Module-limited actions only</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Autonomous AI</label>
        <select class="form-select" disabled>
          <option>Not available</option>
        </select>
      </div>
    </div>
  </section>

  <section class="coach-kpi-grid mt-xl">
    <article class="coach-kpi-card coach-kpi-highlight">
      <span>Working Modules</span>
      <strong>{{ $workingCount }}</strong>
      <small>Routes with usable actions today</small>
    </article>
    <article class="coach-kpi-card">
      <span>Action Queue</span>
      <strong>{{ $actionCount }}</strong>
      <small>Manual recommendations ready</small>
    </article>
    <article class="coach-kpi-card">
      <span>Hard Gaps</span>
      <strong>{{ $hardCount }}</strong>
      <small>Needs architecture work</small>
    </article>
    <article class="coach-kpi-card">
      <span>Partial Areas</span>
      <strong>{{ $partialCount }}</strong>
      <small>Readable but not automated</small>
    </article>
  </section>

  <div class="coach-layout mt-xl">
    <section class="card">
      <div class="card-header">
        <h3 class="card-title">Recommended Actions (Real Routes)</h3>
        <span class="badge badge-primary">Manual execution</span>
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
              <span><strong>Effort:</strong> {{ $action['effort'] }}</span>
              <span><strong>Owner:</strong> {{ $action['owner'] }}</span>
            </div>
            <div class="coach-action-buttons">
              <a href="{{ $action['route'] }}" class="btn btn-primary btn-sm">{{ $action['cta'] }}</a>
              <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary btn-sm">Track Manually</a>
            </div>
          </article>
        @endforeach
      </div>
    </section>

    <section class="card coach-side-card">
      <div class="card-header">
        <h3 class="card-title">Missing / Hard Features</h3>
        <span class="badge badge-warning">Do not over-claim</span>
      </div>

      <div class="coach-issue-list">
        @foreach ($gaps as $issue)
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
        <span class="badge badge-info">Manual runbook</span>
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
                $statusClass = $row['status'] === 'Partial' ? 'badge-info' : ($row['status'] === 'Manual' ? 'badge-warning' : 'badge-danger');
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
        <h3 class="card-title">Confirmed Working Modules</h3>
        <span class="badge badge-success">Safe to use</span>
      </div>

      <ul class="coach-history-list">
        @foreach ($workingModules as $module)
          <li>
            <span>{{ $module['title'] }}</span>
            <p>{{ $module['detail'] }}</p>
            <a href="{{ $module['route'] }}" class="btn btn-secondary btn-sm">{{ $module['cta'] }}</a>
          </li>
        @endforeach
      </ul>
    </section>
  </div>

  <section class="card mt-xl">
    <div class="card-header">
      <h3 class="card-title">Coach Change Log</h3>
      <span class="badge badge-primary">Latest updates</span>
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

  <section class="card mt-xl">
    <div class="card-header">
      <h3 class="card-title">Practical Playbooks</h3>
      <span class="badge badge-primary">Route-based</span>
    </div>

    <div class="coach-playbook-grid">
      @foreach ($playbooks as $playbook)
        <article class="coach-playbook-card">
          <h4>{{ $playbook['title'] }}</h4>
          <p><strong>Steps:</strong> {{ $playbook['steps'] }}</p>
          <p><strong>Best Fit:</strong> {{ $playbook['fit'] }}</p>
          <a href="{{ $playbook['route'] }}" class="btn btn-secondary btn-sm">Open Module</a>
        </article>
      @endforeach
    </div>
  </section>
@endsection
