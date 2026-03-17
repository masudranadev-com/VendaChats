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
<body data-theme="light" class="admin-onboarding-standalone-page">
  @include('admin.partials.onboarding-wizard', ['adminOnboardingStandalone' => true])

  <script>
    window.__ADMIN_PAGE = 'onboarding';
  </script>
  <script src="{{ asset('assets/js/api.js') }}"></script>
  <script src="{{ asset('assets/js/admin.js') }}"></script>
</body>
</html>
