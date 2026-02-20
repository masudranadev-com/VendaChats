@extends('admin.master')

@section('title', $title)

@section('admin.content')
  <div class="page-header">
    <h1 class="page-title">{{ $title }}</h1>
    <p class="page-subtitle">{{ $subtitle }}</p>
  </div>

  <div class="grid grid-2">
    <section class="card">
      <div class="card-header">
        <h3 class="card-title">Account Snapshot</h3>
        <span class="badge badge-primary">{{ $profile['role'] }}</span>
      </div>
      <div class="card-body">
        <div class="flex" style="align-items:center; gap:12px; margin-bottom:14px;">
          <div class="user-avatar" style="width:46px; height:46px;">{{ strtoupper(substr($profile['name'], 0, 2)) }}</div>
          <div>
            <strong style="display:block; color:var(--text-primary);">{{ $profile['name'] }}</strong>
            <small style="color:var(--text-tertiary);">{{ $profile['department'] }}</small>
          </div>
        </div>
        <div style="display:flex; flex-direction:column; gap:10px;">
          <div class="flex-between"><span>Email</span><strong>{{ $profile['email'] }}</strong></div>
          <div class="flex-between"><span>Phone</span><strong>{{ $profile['phone'] }}</strong></div>
          <div class="flex-between"><span>Joined</span><strong>{{ $profile['joined_at'] }}</strong></div>
          <div class="flex-between"><span>Last Login</span><strong>{{ $profile['last_login'] }}</strong></div>
        </div>
      </div>
    </section>

    <section class="card">
      <div class="card-header">
        <h3 class="card-title">Recent Activity</h3>
        <span class="badge badge-info">{{ count($recentActivities) }} entries</span>
      </div>
      <div class="card-body">
        <div style="display:flex; flex-direction:column; gap:10px;">
          @foreach ($recentActivities as $activity)
            <div style="border:1px solid var(--border); border-radius:8px; padding:10px; background:var(--bg-secondary);">
              <div style="font-size:12px; color:var(--text-tertiary); margin-bottom:4px;">{{ $activity['time'] }}</div>
              <div style="font-size:14px;">{{ $activity['text'] }}</div>
            </div>
          @endforeach
        </div>
      </div>
    </section>
  </div>

  <section class="card mt-xl">
    <div class="card-header">
      <h3 class="card-title">Edit Profile</h3>
      <span class="badge badge-warning">UI Demo</span>
    </div>
    <div class="grid grid-2">
      <div class="form-group">
        <label class="form-label">Full Name</label>
        <input class="form-input" type="text" value="{{ $profile['name'] }}">
      </div>
      <div class="form-group">
        <label class="form-label">Email</label>
        <input class="form-input" type="email" value="{{ $profile['email'] }}">
      </div>
      <div class="form-group">
        <label class="form-label">Phone</label>
        <input class="form-input" type="text" value="{{ $profile['phone'] }}">
      </div>
      <div class="form-group">
        <label class="form-label">Role</label>
        <input class="form-input" type="text" value="{{ $profile['role'] }}" disabled>
      </div>
      <div class="form-group">
        <label class="form-label">Timezone</label>
        <select class="form-select">
          <option selected>{{ $profile['timezone'] }}</option>
          <option>Asia/Kolkata</option>
          <option>UTC</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Language</label>
        <select class="form-select">
          <option selected>{{ $profile['language'] }}</option>
          <option>Bangla</option>
        </select>
      </div>
    </div>
    <div class="page-actions">
      <button type="button" class="btn btn-primary">Save Profile</button>
      <a href="{{ route('admin.settings') }}" class="btn btn-secondary">Go to Settings</a>
    </div>
  </section>
@endsection
