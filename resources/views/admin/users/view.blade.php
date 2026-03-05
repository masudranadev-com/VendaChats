@extends('admin.master')

@section('title', $title)

@section('admin.content')
  @php
    $waNumber = preg_replace('/\D+/', '', (string) ($user['whatsapp_number'] ?? ''));
    $syncEndpoint = route('admin.bot-settings.facebook.info', [
      'sender_id' => $user['sender_id'],
      'page_id' => $user['page_id'],
    ]);
  @endphp

  <div class="page-header users-page-header">
    <div>
      <h1 class="page-title">User View</h1>
      <p class="page-subtitle">{{ $subtitle }}</p>
    </div>

    <div class="page-actions">
      <a href="{{ route('admin.users') }}" class="btn btn-secondary">Back to Users</a>
    </div>
  </div>

  <section class="card user-hero-card">
    <div class="user-hero-top">
      <img id="profileImage" src="{{ $user['profile_pic'] }}" alt="{{ $user['name'] }}" class="user-hero-avatar">

      <div class="user-hero-copy">
        <h3 id="profileName" class="card-title">{{ $user['name'] }}</h3>
        <div class="users-sub-id">{{ $user['user_id'] }}</div>

        <div class="user-id-lines">
          <span>Sender ID: <strong>{{ $user['sender_id'] }}</strong></span>
          <span>Page ID: <strong>{{ $user['page_id'] }}</strong></span>
        </div>
      </div>
    </div>

    <div class="user-badge-row">
      <span class="badge {{ ($user['whatsapp'] ?? false) ? 'badge-success' : 'badge-warning' }}">
        WhatsApp: {{ ($user['whatsapp'] ?? false) ? 'Yes' : 'No' }}
      </span>
      <span class="badge {{ ($user['user_type'] ?? '') === 'Price-sensitive' ? 'badge-warning' : 'badge-primary' }}">
        {{ $user['user_type'] }}
      </span>
      @foreach ($user['emotions'] as $emotion)
        <span class="badge emotion-badge emotion-{{ \Illuminate\Support\Str::slug($emotion) }}">{{ $emotion }}</span>
      @endforeach
    </div>

    <div class="page-actions mt-lg">
      @if (($user['whatsapp'] ?? false) && $waNumber !== '')
        <a
          href="https://wa.me/{{ $waNumber }}?text={{ rawurlencode('Hello '.$user['name']) }}"
          target="_blank"
          rel="noopener noreferrer"
          class="btn btn-primary"
        >
          Message Now
        </a>
      @else
        <button type="button" class="btn btn-secondary" disabled>Message Now</button>
      @endif

      <button type="button" id="syncProfileBtn" class="btn btn-secondary" data-endpoint="{{ $syncEndpoint }}">
        Sync Profile
      </button>
    </div>

    <p class="users-sync-hint">
      Sync updates name and profile picture from Facebook using sender id.
    </p>
  </section>

  <section class="card mt-xl">
    <div class="card-header">
      <h3 class="card-title">Current Discussing Product Details</h3>
      <span class="badge badge-info">Live Context</span>
    </div>

    <div class="product-discussion">
      <div class="product-thumb">
        <img src="{{ $user['product']['image'] }}" alt="{{ $user['product']['title'] }}" loading="lazy">
      </div>

      <div class="product-copy">
        <h4>{{ $user['product']['title'] }}</h4>
        <p>{{ $user['product']['description'] }}</p>
        <a href="{{ $user['product']['url'] }}" target="_blank" rel="noopener noreferrer" class="btn btn-primary btn-sm">
          View Product
        </a>
      </div>
    </div>
  </section>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const syncButton = document.getElementById('syncProfileBtn');
      const profileName = document.getElementById('profileName');
      const profileImage = document.getElementById('profileImage');

      if (!syncButton) {
        return;
      }

      syncButton.addEventListener('click', async () => {
        const endpoint = syncButton.dataset.endpoint;
        const originalLabel = syncButton.textContent.trim();

        if (!endpoint) {
          return;
        }

        syncButton.disabled = true;
        syncButton.textContent = 'Syncing...';

        try {
          const response = await fetch(endpoint, {
            headers: {
              'Accept': 'application/json',
            },
          });

          const payload = await response.json();

          if (!response.ok) {
            throw new Error(payload.message || 'Profile sync failed.');
          }

          const profile = payload.data || {};

          if (profile.name && profileName) {
            profileName.textContent = profile.name;
          }

          if (profile.profile_pic && profileImage) {
            profileImage.src = profile.profile_pic;
          }

          if (typeof window.showSuccess === 'function') {
            window.showSuccess('Profile synced successfully.');
          }
        } catch (error) {
          if (typeof window.showError === 'function') {
            window.showError(error.message || 'Unable to sync profile right now.');
          }
        } finally {
          syncButton.disabled = false;
          syncButton.textContent = originalLabel;
        }
      });
    });
  </script>
@endsection
