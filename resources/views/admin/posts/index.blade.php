@extends('admin.master')

@section('title', $title)

@section('admin.content')
  <div class="page-header posts-page-header">
    <div>
      <h1 class="page-title">{{ $title }}</h1>
      <p class="page-subtitle">{{ $subtitle }}</p>
    </div>

    <div class="posts-meta-grid">
      <div class="posts-meta-card">
        <span>Total Posts</span>
        <strong>{{ $totalPosts }}</strong>
      </div>
      <div class="posts-meta-card">
        <span>Total Comments</span>
        <strong>{{ number_format($totalComments) }}</strong>
      </div>
    </div>
  </div>

  <section class="card posts-countdown-card">
    <div class="posts-countdown-copy">
      <h3>Next auto reply will execute:</h3>
      <p>(count down)</p>
    </div>

    <div
      class="posts-countdown-time"
      data-auto-reply-countdown
      data-seconds="{{ $countdownSeconds }}"
      aria-live="polite"
    >
      10m 10s later
    </div>
  </section>

  <section class="card mt-xl">
    <div class="card-header">
      <h3 class="card-title">Filtering Options</h3>
      <span class="badge badge-info">{{ count($posts) }} shown</span>
    </div>

    <form method="GET" action="{{ route('admin.posts') }}" class="posts-filter-form">
      <div class="posts-filter-grid">
        <div class="form-group">
          <label class="form-label" for="posts-order-by">Order By</label>
          <select id="posts-order-by" class="form-select" name="order_by">
            <option value="latest" {{ $filters['order_by'] === 'latest' ? 'selected' : '' }}>Order By Latest Post</option>
            <option value="highest_comments" {{ $filters['order_by'] === 'highest_comments' ? 'selected' : '' }}>Order By Heighet Comments Post</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label" for="posts-days">Days</label>
          <select id="posts-days" class="form-select" name="days">
            <option value="today" {{ $filters['days'] === 'today' ? 'selected' : '' }}>Today's Post</option>
            <option value="7" {{ $filters['days'] === '7' ? 'selected' : '' }}>Last 7 Days Post</option>
            <option value="30" {{ $filters['days'] === '30' ? 'selected' : '' }}>Last 30 Days Post</option>
            <option value="all" {{ $filters['days'] === 'all' ? 'selected' : '' }}>All Posts</option>
          </select>
        </div>
      </div>

      <div class="posts-filter-actions">
        <button type="submit" class="btn btn-primary">Apply Filters</button>
        <a href="{{ route('admin.posts') }}" class="btn btn-secondary">Reset</a>
      </div>
    </form>
  </section>

  @if ($showPostsTable)
    <section class="card mt-xl">
      <div class="card-header">
        <h3 class="card-title">Posts Table</h3>
        <span class="badge badge-primary">Live Feed</span>
      </div>

      <div class="table-container posts-table-wrap">
        <table class="table">
          <thead>
            <tr>
              <th>Post Title</th>
              <th>Total Comments</th>
              <th>Time</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($posts as $post)
              <tr>
                <td class="posts-title-cell" title="{{ $post['title'] }}">
                  {{ \Illuminate\Support\Str::limit($post['title'], 25, '...') }}
                </td>
                <td>
                  <span class="posts-comment-count">{{ number_format($post['total_comments']) }}</span>
                </td>
                <td>
                  <span class="posts-time">{{ $post['time_ago'] }}</span>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="3" class="posts-empty">
                  No posts found for the selected filter.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </section>
  @endif

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const countdown = document.querySelector('[data-auto-reply-countdown]');

      if (!countdown) {
        return;
      }

      let remaining = Number.parseInt(countdown.dataset.seconds || '610', 10);
      if (!Number.isFinite(remaining) || remaining < 0) {
        remaining = 610;
      }

      const render = () => {
        if (remaining <= 0) {
          countdown.textContent = 'Executing now';
          return;
        }

        const minutes = Math.floor(remaining / 60);
        const seconds = remaining % 60;
        countdown.textContent = `${minutes}m ${String(seconds).padStart(2, '0')}s later`;
      };

      render();

      const timer = window.setInterval(() => {
        if (remaining <= 0) {
          window.clearInterval(timer);
          return;
        }

        remaining -= 1;
        render();
      }, 1000);
    });
  </script>
@endsection
