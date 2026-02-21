@php
  $routeName = request()->route()?->getName();
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
    'admin.categories' => 'products',
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
    'admin.shop-settings.category' => 'settings',
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
  <title>@yield('title', 'Dashboard') - A Metafy Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&amp;family=DM+Sans:wght@400;500;600;700&amp;display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/css/admin.css') }}">
</head>
<body data-theme="light">
  <div class="admin-wrapper">
    <aside class="sidebar" id="sidebar">
      <div class="sidebar-logo">
        <span class="logo-icon">⚡</span>
        <span class="logo-text">A Metafy</span>
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
          <a href="{{ route('admin.coach') }}" class="nav-item {{ $currentPage === 'coach' ? 'active' : '' }}">
            <span class="nav-icon">📊</span>
            <span class="nav-label">Performance Coach</span>
          </a>
          
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
              <div class="dropdown-item">Logout</div>
            </div>
          </div>
        </div>

      </header>

      <main class="page-content">
        @yield('admin.content')
      </main>
    </div>
  </div>

  <script>
    window.__ADMIN_PAGE = @json($currentPage);
  </script>
  <script src="{{ asset('assets/js/admin.js') }}"></script>
</body>
</html>
