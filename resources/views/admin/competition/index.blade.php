@extends('admin.master')

@section('title', $title)

@section('admin.content')
  <div class="page-header competition-page-header">
    <div>
      <h1 class="page-title">{{ $title }}</h1>
      <p class="page-subtitle">{{ $subtitle }}</p>
    </div>
  </div>

  @if (session('competition_status'))
    <div class="competition-alert competition-alert-success">{{ session('competition_status') }}</div>
  @endif

  @if ($errors->has('domain'))
    <div class="competition-alert competition-alert-error">{{ $errors->first('domain') }}</div>
  @endif

  <section class="competition-metrics">
    <article class="competition-metric-card">
      <span>Total Domains</span>
      <strong>{{ $stats['tracked'] }}</strong>
      <small>Historical + new domains</small>
    </article>
    <article class="competition-metric-card">
      <span>Processing</span>
      <strong>{{ $stats['processing'] }}</strong>
      <small>Queued in AI pipeline</small>
    </article>
    <article class="competition-metric-card">
      <span>Success</span>
      <strong>{{ $stats['success'] }}</strong>
      <small>Reports ready</small>
    </article>
    <article class="competition-metric-card">
      <span>Failed</span>
      <strong>{{ $stats['failed'] }}</strong>
      <small>Need re-sync</small>
    </article>
  </section>

  <section class="card mt-xl competition-domain-card">
    <div class="card-header">
      <h3 class="card-title">Add Competitor Domain</h3>
    </div>

    <form method="POST" action="{{ route('admin.competition.store') }}" class="competition-domain-form">
      @csrf
      <div class="competition-domain-row">
        <div class="form-group">
          <input
            id="domain"
            class="form-input"
            type="text"
            name="domain"
            value="{{ old('domain') }}"
            placeholder="example.com"
            required
          >
        </div>

        <div class="competition-domain-actions">
          <button type="submit" class="btn btn-primary">Add + Start AI Scan</button>
        </div>
      </div>
      <div class="competition-domain-help">Add one domain per submit. You can add multiple domains by repeating this step.</div>
    </form>
  </section>

  <section class="card competition-filter-card mt-xl">
    <div class="card-header">
      <h3 class="card-title">Filters</h3>
      <span class="badge badge-info">{{ count($rows) }} rows</span>
    </div>

    <form method="GET" action="{{ route('admin.competition') }}" class="competition-filter-form">
      <div class="competition-filter-grid">
        <div class="form-group">
          <label class="form-label" for="q">Search Domain</label>
          <input id="q" class="form-input" type="text" name="q" value="{{ $filters['q'] }}" placeholder="Search domain...">
        </div>
        <div class="form-group">
          <label class="form-label" for="status">Status</label>
          <select id="status" class="form-select" name="status">
            <option value="all" {{ $filters['status'] === 'all' ? 'selected' : '' }}>All</option>
            <option value="processing" {{ $filters['status'] === 'processing' ? 'selected' : '' }}>Processing</option>
            <option value="success" {{ $filters['status'] === 'success' ? 'selected' : '' }}>Success</option>
            <option value="failed" {{ $filters['status'] === 'failed' ? 'selected' : '' }}>Failed</option>
          </select>
        </div>
      </div>

      <div class="competition-filter-actions">
        <button type="submit" class="btn btn-primary">Apply Filter</button>
        <a href="{{ route('admin.competition') }}" class="btn btn-secondary">Reset</a>
      </div>
    </form>
  </section>

  <section class="card mt-xl">
    <div class="card-header">
      <h3 class="card-title">Scan History</h3>
      <span class="badge badge-primary">Domain scan table</span>
    </div>

    <div class="table-container">
      <table class="table">
        <thead>
          <tr>
            <th>Domain</th>
            <th>Total Products</th>
            <th>Last Scan</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($rows as $row)
            @php
              $statusClass = match ($row['status']) {
                'success' => 'badge-success',
                'failed' => 'badge-danger',
                default => 'badge-warning',
              };
            @endphp
            <tr>
              <td class="competition-cell-strong">{{ $row['domain'] }}</td>
              <td>{{ number_format($row['total_products']) }}</td>
              <td>{{ $row['last_scan_human'] }}</td>
              <td><span class="badge {{ $statusClass }}">{{ ucfirst($row['status']) }}</span></td>
              <td>
                <div class="competition-table-actions">
                  <form method="POST" action="{{ route('admin.competition.sync', $row['id']) }}">
                    @csrf
                    <button type="submit" class="btn btn-secondary btn-sm">Sync</button>
                  </form>
                  <a href="{{ route('admin.competition.view', $row['id']) }}" class="btn btn-primary btn-sm">View</a>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="competition-empty-row">No scan data found for current filters.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </section>
@endsection
