@php
  $routeName = request()->route()?->getName();
  $adminGlobalConfig = session('admin.global_config', []);
  $websiteName = trim((string) ($adminGlobalConfig['website_name'] ?? 'A Metafy')) ?: 'A Metafy';
  $websiteLogo = trim((string) ($adminGlobalConfig['website_logo'] ?? '⚡')) ?: '⚡';
  $supportWhatsappNumber = trim((string) ($adminGlobalConfig['support_whatsapp_number'] ?? ''));
  $supportWhatsappDigits = preg_replace('/\D+/', '', $supportWhatsappNumber);
  $supportWhatsappHref = $supportWhatsappDigits !== ''
    ? 'https://wa.me/'.$supportWhatsappDigits.'?text='.rawurlencode('Hello, I need help with my account.')
    : null;
  $routeToPage = [
    'admin.dashboard' => 'dashboard',
    'admin.profile' => 'profile',
    'admin.settings' => 'profile',
    'admin.analytics' => 'analytics',
    'admin.orders' => 'orders',
    'admin.orders.view' => 'orders',
    'admin.orders.discount.apply' => 'orders',
    'admin.orders.discount.remove' => 'orders',
    'admin.orders.invoice' => 'orders',
    'admin.conversations' => 'conversations',
    'admin.customers' => 'customers',
    'admin.products' => 'products',
    'admin.products.create' => 'products',
    'admin.products.edit' => 'products',
    'admin.categories' => 'products',
    'admin.order-call' => 'order-call',
    'admin.users' => 'users',
    'admin.users.views' => 'users',
    'admin.posts' => 'posts',
    'admin.bot-settings' => 'bot-settings',
    'admin.bargaining' => 'bargaining',
    'admin.campaigns' => 'campaigns',
    'admin.competition' => 'competition',
    'admin.competition.view' => 'competition',
    'admin.coach' => 'coach',
    'admin.courier' => 'courier',
    'admin.shop-settings' => 'settings',
    'admin.shop-settings.domain' => 'settings',
    'admin.shop-settings.theme' => 'settings',
    'admin.shop-settings.offers' => 'settings',
    'admin.shop-settings.content' => 'settings',
    'admin.billing' => 'billing',
  ];
  $currentPage = $routeToPage[$routeName] ?? 'dashboard';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'Dashboard') - {{ $websiteName }} Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&amp;family=DM+Sans:wght@400;500;600;700&amp;display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/css/admin.css') }}">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-lite.min.css">
  <meta name="x-refresh-token" content="{{ session()->get("auth.refresh_token", "") }}">
</head>
<body data-theme="light">
  <div class="admin-wrapper">
    <aside class="sidebar" id="sidebar">
      <div class="sidebar-logo">
        <span class="logo-icon">{{ $websiteLogo }}</span>
        <span class="logo-text">{{ $websiteName }}</span>
      </div>

      <nav class="sidebar-nav">
        <div class="nav-group">
          <div class="nav-group-title">Overview</div>
          <a href="{{ route('admin.dashboard') }}" class="nav-item {{ $currentPage === 'dashboard' ? 'active' : '' }}">
            <span class="nav-icon">📊</span>
            <span class="nav-label">Dashboard</span>
          </a>
        </div>

        <div class="nav-group">
          <div class="nav-group-title">Sales</div>
          <a href="{{ route('admin.orders') }}" class="nav-item {{ $currentPage === 'orders' ? 'active' : '' }}">
            <span class="nav-icon">🛒</span>
            <span class="nav-label">Orders</span>
          </a>
          <a href="{{ route('admin.products') }}" class="nav-item {{ $currentPage === 'products' ? 'active' : '' }}">
            <span class="nav-icon">📦</span>
            <span class="nav-label">Products</span>
          </a>
           <a href="{{ route('admin.users') }}" class="nav-item {{ $currentPage === 'users' ? 'active' : '' }}">
            <span class="nav-icon">👥</span>
            <span class="nav-label">Customers</span>
          </a>
        </div>

        <div class="nav-group">
          <div class="nav-group-title">Automation</div>
          <a href="{{ route('admin.posts') }}" class="nav-item {{ $currentPage === 'posts' ? 'active' : '' }}">
            <span class="nav-icon">💬</span>
            <span class="nav-label">Posts</span>
          </a>
          <a href="{{ route('admin.bot-settings') }}" class="nav-item {{ $currentPage === 'bot-settings' ? 'active' : '' }}">
            <span class="nav-icon">🤖</span>
            <span class="nav-label">Bot Settings</span>
          </a>
          <a href="{{ route('admin.order-call') }}" class="nav-item {{ $currentPage === 'order-call' ? 'active' : '' }}">
            <span class="nav-icon">📞</span>
            <span class="nav-label">Call Confirm</span>
          </a>
          <a href="{{ route('admin.campaigns') }}" class="nav-item {{ $currentPage === 'campaigns' ? 'active' : '' }}">
            <span class="nav-icon">🚀</span>
            <span class="nav-label">Campaigns</span>
          </a>
        </div>

        <div class="nav-group">
          <div class="nav-group-title">Intelligence</div>
          <a href="{{ route('admin.competition') }}" class="nav-item {{ $currentPage === 'competition' ? 'active' : '' }}">
            <span class="nav-icon">🔍</span>
            <span class="nav-label">Competition Monitor</span>
          </a>
          {{-- <a href="{{ route('admin.coach') }}" class="nav-item {{ $currentPage === 'coach' ? 'active' : '' }}">
            <span class="nav-icon">📊</span>
            <span class="nav-label">Performance Coach</span>
          </a> --}}

        </div>

        <div class="nav-group">
          <div class="nav-group-title">Settings</div>
          <a href="{{ route('admin.courier') }}" class="nav-item {{ $currentPage === 'courier' ? 'active' : '' }}">
            <span class="nav-icon">🚚</span>
            <span class="nav-label">Courier Manager</span>
          </a>

          <a href="{{ route('admin.shop-settings') }}" class="nav-item {{ $currentPage === 'settings' ? 'active' : '' }}">
            <span class="nav-icon">⚙️</span>
            <span class="nav-label">Shop Settings</span>
          </a>
          <a href="{{ route('admin.billing') }}" class="nav-item {{ $currentPage === 'billing' ? 'active' : '' }}">
            <span class="nav-icon">$</span>
            <span class="nav-label">Billing</span>
          </a>
        </div>
      </nav>
    </aside>

    <div class="mobile-overlay" id="mobileOverlay"></div>

    <div class="main-content">
      <header class="top-header">
        <div class="header-left">
          <button class="menu-toggle" id="menuToggle">☰</button>

          <div class="search-bar">
            <span class="search-icon">🔍</span>
            <input type="text" class="search-input" placeholder="Search orders, customers, products..." id="globalSearch">
          </div>
        </div>

        <div class="header-right">
          {{-- <div class="dropdown">
            <button class="header-btn" type="button">
              🔔
              <span class="badge">3</span>
            </button>
            <div class="dropdown-menu">
              <div class="dropdown-item">
                <span>💬</span>
                <span>New message from customer</span>
              </div>
              <div class="dropdown-item">
                <span>🛒</span>
                <span>5 new orders received</span>
              </div>
              <div class="dropdown-item">
                <span>⚠️</span>
                <span>Low stock alert: Product ABC</span>
              </div>
              <div class="dropdown-divider"></div>
              <div class="dropdown-item">View all notifications</div>
            </div>
          </div> --}}

          <div class="dropdown">
            <div class="user-menu">
              <div class="user-avatar">RA</div>
              <div class="user-info">
                <div class="user-name">Rahim Ahmed</div>
                <div class="user-role">Owner</div>
              </div>
            </div>
            <div class="dropdown-menu">
              <a href="{{ route('admin.profile') }}" class="dropdown-item">My Profile</a>
              <a href="{{ route('admin.settings') }}" class="dropdown-item">Settings</a>
              <a href="{{ route('admin.shop-settings') }}" class="dropdown-item">Shop Settings</a>
              <div class="dropdown-divider"></div>
              <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" class="dropdown-item">Logout</button>
              </form>
            </div>
          </div>
        </div>

      </header>

      <main class="page-content">
        @yield('admin.content')
      </main>
    </div>
  </div>

  @if ($supportWhatsappHref)
    <a
      href="{{ $supportWhatsappHref }}"
      class="support-help-fab"
      target="_blank"
      rel="noopener noreferrer"
      aria-label="Open WhatsApp support chat"
      title="Chat on WhatsApp: {{ $supportWhatsappNumber }}"
    >
      <span class="support-help-fab-bubble is-visible" data-support-help-bubble aria-hidden="false">Need help?</span>
      <span class="support-help-fab-button" aria-hidden="true">
        <svg viewBox="0 0 32 32" role="presentation" focusable="false">
          <path d="M16 5.333c-5.891 0-10.667 4.62-10.667 10.32 0 2.011.601 3.882 1.64 5.456L5.333 26.667l5.783-1.512A10.92 10.92 0 0 0 16 26c5.891 0 10.667-4.62 10.667-10.347C26.667 9.954 21.891 5.333 16 5.333Zm0 19.014a9.082 9.082 0 0 1-4.641-1.273l-.332-.2-3.432.898.92-3.332-.227-.347a8.714 8.714 0 0 1-1.387-4.44c0-4.747 4.078-8.613 9.099-8.613 5.014 0 9.099 3.866 9.099 8.613 0 4.761-4.085 8.694-9.099 8.694Zm4.987-6.567c-.273-.133-1.613-.78-1.867-.867-.254-.087-.44-.133-.627.133-.187.267-.72.867-.88 1.04-.16.174-.32.2-.594.067-.273-.133-1.16-.42-2.214-1.34-.82-.707-1.374-1.58-1.534-1.846-.16-.267-.02-.414.12-.547.126-.12.273-.313.407-.467.133-.153.18-.266.273-.446.093-.18.047-.333-.02-.467-.067-.133-.626-1.493-.86-2.046-.226-.54-.46-.467-.627-.474l-.534-.007c-.187 0-.494.067-.754.333-.26.267-.993.967-.993 2.36 0 1.392 1.02 2.738 1.16 2.924.14.187 2 3.14 4.947 4.273.7.3 1.247.48 1.674.614.707.22 1.353.187 1.86.113.567-.087 1.613-.66 1.84-1.307.226-.646.226-1.2.16-1.306-.067-.107-.247-.174-.52-.307Z" />
        </svg>
      </span>
    </a>
  @endif

  <script>
    window.__ADMIN_PAGE = @json($currentPage);
  </script>
  <script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-lite.min.js"></script>
  <script src="{{ asset('assets/js/api.js') }}"></script>
  <script src="{{ asset('assets/js/admin.js') }}"></script>
</body>
</html>
