@php
  $routeName = request()->route()?->getName();
  $routeToPage = [
    'admin.dashboard' => 'dashboard',
    'admin.analytics' => 'analytics',
    'admin.orders' => 'orders',
    'admin.conversations' => 'conversations',
    'admin.customers' => 'customers',
    'admin.products' => 'products',
    'admin.bot-settings' => 'bot-settings',
    'admin.bargaining' => 'bargaining',
    'admin.whatsapp-recovery' => 'whatsapp-recovery',
    'admin.campaigns' => 'campaigns',
    'admin.competition' => 'competition',
    'admin.coach' => 'coach',
    'admin.courier' => 'courier',
    'admin.settings' => 'settings',
    'admin.billing' => 'billing',
  ];
  $currentPage = $routeToPage[$routeName] ?? 'dashboard';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'Dashboard') - SellBuzz AI Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&amp;family=DM+Sans:wght@400;500;600;700&amp;display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/css/admin.css') }}">
</head>
<body data-theme="light">
  <div class="admin-wrapper">
    <aside class="sidebar" id="sidebar">
      <div class="sidebar-logo">
        <span class="logo-icon">⚡</span>
        <span class="logo-text">SellBuzz AI</span>
      </div>

      <nav class="sidebar-nav">
        <div class="nav-group">
          <div class="nav-group-title">Overview</div>
          <a href="{{ route('admin.dashboard') }}" class="nav-item {{ $currentPage === 'dashboard' ? 'active' : '' }}">
            <span class="nav-icon">📊</span>
            <span class="nav-label">Dashboard</span>
          </a>
          <a href="{{ route('admin.analytics') }}" class="nav-item {{ $currentPage === 'analytics' ? 'active' : '' }}">
            <span class="nav-icon">📈</span>
            <span class="nav-label">Analytics</span>
          </a>
        </div>

        <div class="nav-group">
          <div class="nav-group-title">Sales</div>
          <a href="{{ route('admin.orders') }}" class="nav-item {{ $currentPage === 'orders' ? 'active' : '' }}">
            <span class="nav-icon">🛒</span>
            <span class="nav-label">Orders</span>
            <span class="nav-badge">12</span>
          </a>
          <a href="{{ route('admin.conversations') }}" class="nav-item {{ $currentPage === 'conversations' ? 'active' : '' }}">
            <span class="nav-icon">💬</span>
            <span class="nav-label">Conversations</span>
            <span class="nav-badge">5</span>
          </a>
          <a href="{{ route('admin.customers') }}" class="nav-item {{ $currentPage === 'customers' ? 'active' : '' }}">
            <span class="nav-icon">👥</span>
            <span class="nav-label">Customers</span>
          </a>
          <a href="{{ route('admin.products') }}" class="nav-item {{ $currentPage === 'products' ? 'active' : '' }}">
            <span class="nav-icon">📦</span>
            <span class="nav-label">Products</span>
          </a>
        </div>

        <div class="nav-group">
          <div class="nav-group-title">Automation</div>
          <a href="{{ route('admin.bot-settings') }}" class="nav-item {{ $currentPage === 'bot-settings' ? 'active' : '' }}">
            <span class="nav-icon">🤖</span>
            <span class="nav-label">Bot Settings</span>
          </a>
          <a href="{{ route('admin.bargaining') }}" class="nav-item {{ $currentPage === 'bargaining' ? 'active' : '' }}">
            <span class="nav-icon">💰</span>
            <span class="nav-label">Bargaining Rules</span>
          </a>
          <a href="{{ route('admin.whatsapp-recovery') }}" class="nav-item {{ $currentPage === 'whatsapp-recovery' ? 'active' : '' }}">
            <span class="nav-icon">📱</span>
            <span class="nav-label">WhatsApp Recovery</span>
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
          <a href="{{ route('admin.courier') }}" class="nav-item {{ $currentPage === 'courier' ? 'active' : '' }}">
            <span class="nav-icon">🚚</span>
            <span class="nav-label">Courier Manager</span>
          </a>
        </div>

        <div class="nav-group">
          <div class="nav-group-title">Settings</div>
          <a href="{{ route('admin.settings') }}" class="nav-item {{ $currentPage === 'settings' ? 'active' : '' }}">
            <span class="nav-icon">⚙️</span>
            <span class="nav-label">Shop Settings</span>
          </a>
          <a href="{{ route('admin.billing') }}" class="nav-item {{ $currentPage === 'billing' ? 'active' : '' }}">
            <span class="nav-icon">💳</span>
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
          <button class="header-btn" id="themeToggle">🌙</button>

          <div class="dropdown">
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
          </div>

          <div class="dropdown">
            <div class="user-menu">
              <div class="user-avatar">RA</div>
              <div class="user-info">
                <div class="user-name">Rahim Ahmed</div>
                <div class="user-role">Owner</div>
              </div>
            </div>
            <div class="dropdown-menu">
              <div class="dropdown-item">👤 My Profile</div>
              <div class="dropdown-item">⚙️ Settings</div>
              <div class="dropdown-item">❓ Help Center</div>
              <div class="dropdown-divider"></div>
              <div class="dropdown-item">🚪 Logout</div>
            </div>
          </div>
        </div>
      </header>

      <main class="page-content">
        @yield('admin.content')
      </main>
    </div>
  </div>

  <script>window.__ADMIN_PAGE = @json($currentPage);</script>
  <script src="{{ asset('assets/js/admin.js') }}"></script>
</body>
</html>
