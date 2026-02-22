<article class="card mt-xl settings-panel">
  @php
    $activityCount = method_exists($activityLog, 'total') ? $activityLog->total() : count($activityLog);
  @endphp

  <div class="card-header">
    <div>
      <h3 class="card-title">Recent Activity</h3>
      <p class="settings-panel-subtitle">Latest setting updates across teams.</p>
    </div>
    <span class="badge badge-warning">{{ $activityCount }} updates</span>
  </div>

  <ul class="settings-activity-list">
    @forelse ($activityLog as $activity)
      @php
        $activityClass = $activity['status'] === 'Pending' ? 'is-pending' : 'is-success';
        $activityBadge = $activity['status'] === 'Pending' ? 'badge-warning' : 'badge-success';
      @endphp
      <li>
        <span class="settings-activity-dot {{ $activityClass }}"></span>
        <div class="settings-activity-content">
          <strong>{{ $activity['event'] }}</strong>
          <p>{{ $activity['actor'] }} | {{ $activity['time'] }}</p>
        </div>
        <span class="badge {{ $activityBadge }}">{{ $activity['status'] }}</span>
      </li>
    @empty
      <li class="settings-activity-empty">
        <div class="settings-activity-content">
          <strong>No activity found</strong>
          <p>There are no recent updates yet.</p>
        </div>
      </li>
    @endforelse
  </ul>

  @if (method_exists($activityLog, 'hasPages') && $activityLog->hasPages())
    <div class="settings-activity-footer">
      <p class="settings-activity-summary">
        @if ($activityLog->count() > 0)
          Showing {{ $activityLog->firstItem() }}-{{ $activityLog->lastItem() }} of {{ $activityLog->total() }} updates
        @else
          Showing 0 updates
        @endif
      </p>

      <nav class="settings-pagination-controls" aria-label="Recent activity pagination">
        @if ($activityLog->onFirstPage())
          <span class="settings-page-btn is-disabled" aria-disabled="true">Prev</span>
        @else
          <a href="{{ $activityLog->previousPageUrl() }}" class="settings-page-btn">Prev</a>
        @endif

        @for ($page = 1; $page <= $activityLog->lastPage(); $page++)
          @if ($page === $activityLog->currentPage())
            <span class="settings-page-btn is-active" aria-current="page">{{ $page }}</span>
          @else
            <a href="{{ $activityLog->url($page) }}" class="settings-page-btn">{{ $page }}</a>
          @endif
        @endfor

        @if ($activityLog->hasMorePages())
          <a href="{{ $activityLog->nextPageUrl() }}" class="settings-page-btn">Next</a>
        @else
          <span class="settings-page-btn is-disabled" aria-disabled="true">Next</span>
        @endif
      </nav>
    </div>
  @endif
</article>
