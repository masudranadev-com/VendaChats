@php
  $adminGlobalConfig = session('admin.global_config', []);
  $websiteName = trim((string) ($adminGlobalConfig['website_name'] ?? 'A Metafy')) ?: 'A Metafy';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Onboarding - {{ $websiteName }} Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&amp;family=DM+Sans:wght@400;500;600;700&amp;display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/css/admin.css') }}">
</head>
<body data-theme="light">
  <main style="min-height: 100vh;">
    @include('admin.partials.onboarding-wizard', ['adminOnboardingStandalone' => true])

    <section style="min-height: 100vh; display: grid; place-items: center; padding: 24px;">
      <div class="card" style="width: min(640px, 100%);">
        <div class="card-header">
          <div>
            <h2 class="card-title">Standalone Onboarding Route</h2>
            <p class="card-subtitle">This page exists only for testing the onboarding flow at `/admin/onboarding`.</p>
          </div>
        </div>
        <div class="card-body">
          <p class="text-secondary">If you dismiss the overlay, refresh this page to open the onboarding flow again. After completion, continue from the regular admin dashboard.</p>
          <div class="mt-md">
            <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">Back to dashboard</a>
          </div>
        </div>
      </div>
    </section>
  </main>

  <script>
    window.__ADMIN_PAGE = 'onboarding';
  </script>
  <script src="{{ asset('assets/js/admin.js') }}"></script>
</body>
</html>
