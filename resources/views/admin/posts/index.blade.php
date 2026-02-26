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
        <strong>100</strong>
      </div>
      <div class="posts-meta-card">
        <span>Total Comments</span>
        <strong>10</strong>
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
      data-seconds="200"
      aria-live="polite"
    >
      10m 10s later
    </div>
  </section>

  <section class="card mt-xl">
    <div class="card-header">
      <h3 class="card-title">Filtering Options</h3>
      <span class="badge badge-info">12 shown</span>
    </div>

    <form method="GET" action="{{ route('admin.posts') }}" class="posts-filter-form">
      <div class="">
        <div class="form-group">
          <label class="form-label" for="posts-order-by">Order By</label>
          <select id="posts-order-by" class="form-select" name="order_by">
            <option value="latest">Order By Latest Post</option>
            <option value="highest_comments">Order By Heighet Comments Post</option>
          </select>
        </div>
      </div>

      <div class="posts-filter-actions">
        <button type="submit" class="btn btn-primary">Apply Filters</button>
      </div>
    </form>
  </section>

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
          <tr>
              <td class="posts-title-cell" title="{{ "Hello world" }}">
                {{ \Illuminate\Support\Str::limit("Hello world", 25, '...') }}
              </td>
              <td>
                <span class="posts-comment-count">121</span>
              </td>
              <td>
                <span class="posts-time">10m ago</span>
              </td>
            </tr>
        </tbody>
      </table>
    </div>
  </section>

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
