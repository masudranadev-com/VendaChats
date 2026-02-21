@extends('admin.master')

@section('title', $title)

@section('admin.content')
  <div class="page-header users-page-header">
    <div>
      <h1 class="page-title">{{ $title }}</h1>
      <p class="page-subtitle">{{ $subtitle }}</p>
    </div>

    <div class="users-header-meta">
      <div class="users-meta-card">
        <span>Total Users</span>
        <strong>{{ $totalUsers }}</strong>
      </div>
      <div class="users-meta-card">
        <span>WhatsApp Users</span>
        <strong>{{ $whatsAppUsers }}</strong>
      </div>
      <div class="users-meta-card">
        <span>Price-sensitive</span>
        <strong>{{ $priceSensitiveUsers }}</strong>
      </div>
    </div>
  </div>

  <section class="card users-filters-card">
    <div class="card-header">
      <h3 class="card-title">Filtering Options</h3>
      <span class="badge badge-info">{{ $filteredUsers }} shown</span>
    </div>

    <form method="GET" action="{{ route('admin.users') }}" class="users-filters-form">
      <div class="users-filter-grid">
        <div class="form-group">
          <label class="form-label" for="users-search">Search</label>
          <input
            id="users-search"
            class="form-input"
            type="text"
            name="q"
            value="{{ $filters['q'] }}"
            placeholder="Name, User ID, Sender ID"
          >
        </div>

        <div class="form-group">
          <label class="form-label" for="users-whatsapp">WhatsApp</label>
          <select id="users-whatsapp" class="form-select" name="whatsapp">
            <option value="all" {{ $filters['whatsapp'] === 'all' ? 'selected' : '' }}>All</option>
            <option value="yes" {{ $filters['whatsapp'] === 'yes' ? 'selected' : '' }}>Yes</option>
            <option value="no" {{ $filters['whatsapp'] === 'no' ? 'selected' : '' }}>No</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label" for="users-emotion">Emotions</label>
          <select id="users-emotion" class="form-select" name="emotion">
            <option value="all" {{ $filters['emotion'] === 'all' ? 'selected' : '' }}>All emotions</option>
            @foreach ($availableEmotions as $emotion)
              <option value="{{ $emotion }}" {{ $filters['emotion'] === $emotion ? 'selected' : '' }}>
                {{ $emotion }}
              </option>
            @endforeach
          </select>
        </div>

        <div class="form-group">
          <label class="form-label" for="users-type">User Type</label>
          <select id="users-type" class="form-select" name="user_type">
            <option value="all" {{ $filters['user_type'] === 'all' ? 'selected' : '' }}>All types</option>
            <option value="Price-sensitive" {{ $filters['user_type'] === 'Price-sensitive' ? 'selected' : '' }}>Price-sensitive</option>
            <option value="Quality-focused" {{ $filters['user_type'] === 'Quality-focused' ? 'selected' : '' }}>Quality-focused</option>
          </select>
        </div>
      </div>

      <div class="users-filter-actions">
        <button type="submit" class="btn btn-primary">Apply Filters</button>
        <a href="{{ route('admin.users') }}" class="btn btn-secondary">Reset</a>
        <div class="users-filter-result">{{ $filteredUsers }} of {{ $totalUsers }} users</div>
      </div>
    </form>
  </section>

  <section class="card mt-xl">
    <div class="card-header">
      <h3 class="card-title">Users Table</h3>
      <span class="badge badge-primary">Profile, Emotion, Type</span>
    </div>

    <div class="table-container users-table-wrap">
      <table class="table users-table">
        <thead>
          <tr>
            <th>Profile</th>
            <th>Name</th>
            <th>Channel</th>
            <th>WhatsApp</th>
            <th>Emotions</th>
            <th>User Type</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($users as $user)
            @php
              $waNumber = preg_replace('/\D+/', '', (string) ($user['whatsapp_number'] ?? ''));
            @endphp
            <tr>
              <td>
                <div class="users-table-profile">
                  <img
                    src="{{ $user['profile_pic'] }}"
                    class="users-avatar"
                    alt="{{ $user['name'] }}"
                    loading="lazy"
                  >
                </div>
              </td>

              <td>
                <div class="users-name-block">
                  <div class="users-name">{{ $user['name'] }}</div>
                  <div class="users-sub-id">{{ $user['user_id'] }}</div>
                </div>
              </td>

              <td>
                <div class="users-channels">
                  @forelse (($user['channels'] ?? []) as $channel)
                    <span class="badge users-channel-badge users-channel-{{ \Illuminate\Support\Str::slug($channel) }}">
                      {{ $channel }}
                    </span>
                  @empty
                    <span class="badge">N/A</span>
                  @endforelse
                </div>
              </td>

              <td>
                <span class="badge {{ ($user['whatsapp'] ?? false) ? 'badge-success' : 'badge-warning' }}">
                  {{ ($user['whatsapp'] ?? false) ? 'Yes' : 'No' }}
                </span>
              </td>

              <td>
                <div class="users-emotions">
                  @foreach ($user['emotions'] as $emotion)
                    <span class="badge emotion-badge emotion-{{ \Illuminate\Support\Str::slug($emotion) }}">
                      {{ $emotion }}
                    </span>
                  @endforeach
                </div>
              </td>

              <td>
                <span class="badge {{ ($user['user_type'] ?? '') === 'Price-sensitive' ? 'badge-warning' : 'badge-primary' }}">
                  {{ $user['user_type'] }}
                </span>
              </td>

              <td>
                <div class="users-actions">
                  @if (($user['whatsapp'] ?? false) && $waNumber !== '')
                    <a
                      href="https://wa.me/{{ $waNumber }}?text={{ rawurlencode('Hello '.$user['name']) }}"
                      target="_blank"
                      rel="noopener noreferrer"
                      class="btn btn-primary btn-sm"
                    >
                      Message
                    </a>
                  @else
                    <button type="button" class="btn btn-secondary btn-sm" disabled>Message</button>
                  @endif

                  <a href="{{ route('admin.users.views', ['user_id' => $user['user_id']]) }}" class="btn btn-info btn-sm">
                    View
                  </a>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="users-empty">
                No users found for the selected filters.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </section>
@endsection
