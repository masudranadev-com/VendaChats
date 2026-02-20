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
        <h3 class="card-title">Notification Preferences</h3>
        <span class="badge badge-primary">Account</span>
      </div>
      <div class="card-body">
        <div style="display:flex; flex-direction:column; gap:12px;">
          <label class="flex-between">
            <span>Email notifications</span>
            <input type="checkbox" {{ $preferences['email_notifications'] ? 'checked' : '' }}>
          </label>
          <label class="flex-between">
            <span>SMS notifications</span>
            <input type="checkbox" {{ $preferences['sms_notifications'] ? 'checked' : '' }}>
          </label>
          <label class="flex-between">
            <span>Browser notifications</span>
            <input type="checkbox" {{ $preferences['browser_notifications'] ? 'checked' : '' }}>
          </label>
          <label class="flex-between">
            <span>Weekly summary report</span>
            <input type="checkbox" {{ $preferences['weekly_summary'] ? 'checked' : '' }}>
          </label>
          <label class="flex-between">
            <span>Dark mode preference</span>
            <input type="checkbox" {{ $preferences['dark_mode'] ? 'checked' : '' }}>
          </label>
        </div>
      </div>
    </section>

    <section class="card">
      <div class="card-header">
        <h3 class="card-title">Security</h3>
        <span class="badge {{ $security['two_factor_enabled'] ? 'badge-success' : 'badge-warning' }}">
          {{ $security['two_factor_enabled'] ? '2FA Enabled' : '2FA Off' }}
        </span>
      </div>
      <div class="card-body">
        <div style="display:flex; flex-direction:column; gap:10px;">
          <div class="flex-between"><span>Last password change</span><strong>{{ $security['last_password_change'] }}</strong></div>
          <div class="flex-between"><span>Active sessions</span><strong>{{ $security['active_sessions'] }}</strong></div>
          <div class="flex-between"><span>Two-factor authentication</span><strong>{{ $security['two_factor_enabled'] ? 'Enabled' : 'Disabled' }}</strong></div>
        </div>
        <div class="page-actions mt-md">
          <button type="button" class="btn btn-secondary btn-sm">Change Password</button>
          <button type="button" class="btn btn-primary btn-sm">Enable 2FA</button>
        </div>
      </div>
    </section>
  </div>

  <section class="card mt-xl">
    <div class="card-header">
      <h3 class="card-title">Session and Access Controls</h3>
      <span class="badge badge-info">UI Demo</span>
    </div>
    <div class="grid grid-2">
      <div class="form-group">
        <label class="form-label">Default Landing Page</label>
        <select class="form-select">
          <option selected>Dashboard</option>
          <option>Orders</option>
          <option>Shop Settings</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Session Timeout</label>
        <select class="form-select">
          <option>15 minutes</option>
          <option selected>30 minutes</option>
          <option>60 minutes</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Allowed Login Region</label>
        <input type="text" class="form-input" value="Bangladesh">
      </div>
      <div class="form-group">
        <label class="form-label">Backup Email</label>
        <input type="email" class="form-input" placeholder="backup@example.com">
      </div>
    </div>
    <div class="page-actions">
      <button type="button" class="btn btn-primary">Save Settings</button>
      <a href="{{ route('admin.profile') }}" class="btn btn-secondary">Back to Profile</a>
    </div>
  </section>
@endsection
