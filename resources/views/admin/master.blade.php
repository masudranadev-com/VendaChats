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
    'admin.shop-settings.content.page-editor' => 'settings',
    'admin.shop-settings.content.contact' => 'settings',
    'admin.shop-settings.content.footer' => 'settings',
    'admin.packages' => 'packages',
  ];
  $shopSettingsRoutes = [
    'admin.shop-settings' => 'First-time Setup',
    'admin.shop-settings.domain' => 'Domain',
    'admin.shop-settings.theme' => 'Theme',
    'admin.shop-settings.offers' => 'Offers',
    'admin.shop-settings.content' => 'Website Content',
  ];
  $isShopSettingsRoute = array_key_exists($routeName, $shopSettingsRoutes)
    || str_starts_with((string) $routeName, 'admin.shop-settings.content.');
  $currentPage = $routeToPage[$routeName] ?? 'dashboard';
  $navIcon = static function (string $name): \Illuminate\Support\HtmlString {
    $svg = match ($name) {
      'dashboard' => '<svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1.5"></rect><rect x="14" y="3" width="7" height="7" rx="1.5"></rect><rect x="3" y="14" width="7" height="7" rx="1.5"></rect><rect x="14" y="14" width="7" height="7" rx="1.5"></rect></svg>',
      'orders' => '<svg viewBox="0 0 24 24"><path d="M8 7V6a4 4 0 1 1 8 0v1"></path><path d="M6 8h12l-1 11H7L6 8Z"></path></svg>',
      'products' => '<svg viewBox="0 0 24 24"><path d="m12 3 7 4v10l-7 4-7-4V7l7-4Z"></path><path d="m12 12 7-5"></path><path d="m12 12-7-5"></path><path d="M12 12v9"></path></svg>',
      'customers' => '<svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"></path><circle cx="9.5" cy="7" r="3"></circle><path d="M21 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16.5 4.13a3 3 0 0 1 0 5.74"></path></svg>',
      'posts' => '<svg viewBox="0 0 24 24"><path d="M7 10h10"></path><path d="M7 14h6"></path><path d="M5 5h14a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H9l-4 3v-3H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z"></path></svg>',
      'bot-settings' => '<svg viewBox="0 0 24 24"><path d="M12 5v3"></path><rect x="7" y="8" width="10" height="8" rx="2"></rect><path d="M9 12h.01"></path><path d="M15 12h.01"></path><path d="M8 18h8"></path><path d="M5 10h2"></path><path d="M17 10h2"></path></svg>',
      'order-call' => '<svg viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.86 19.86 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.86 19.86 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.12.9.33 1.78.63 2.62a2 2 0 0 1-.45 2.11L8 9.89a16 16 0 0 0 6.11 6.11l1.44-1.29a2 2 0 0 1 2.11-.45c.84.3 1.72.51 2.62.63A2 2 0 0 1 22 16.92Z"></path></svg>',
      'campaigns' => '<svg viewBox="0 0 24 24"><path d="m3 11 11-5v12L3 13v-2Z"></path><path d="M14 8c3.5 0 5.5-1 7-3v14c-1.5-2-3.5-3-7-3"></path><path d="M6 14v4a2 2 0 0 0 2 2h1"></path></svg>',
      'competition' => '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="8"></circle><circle cx="12" cy="12" r="3"></circle><path d="M12 2v3"></path><path d="M12 19v3"></path><path d="M2 12h3"></path><path d="M19 12h3"></path></svg>',
      'courier' => '<svg viewBox="0 0 24 24"><path d="M10 17H5V7h10v10"></path><path d="M15 11h3l3 3v3h-6v-6Z"></path><circle cx="7.5" cy="18.5" r="1.5"></circle><circle cx="17.5" cy="18.5" r="1.5"></circle></svg>',
      'settings' => '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06A1.65 1.65 0 0 0 15 19.4a1.65 1.65 0 0 0-1 .6 1.65 1.65 0 0 1-2 0 1.65 1.65 0 0 0-1-.6 1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.6 15a1.65 1.65 0 0 0-.6-1 1.65 1.65 0 0 1 0-2 1.65 1.65 0 0 0 .6-1 1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.6a1.65 1.65 0 0 0 1-.6 1.65 1.65 0 0 1 2 0 1.65 1.65 0 0 0 1 .6 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9c0 .38.14.74.4 1a1.65 1.65 0 0 1 0 2c-.26.26-.4.62-.4 1Z"></path></svg>',
      'packages' => '<svg viewBox="0 0 24 24"><path d="m12 3 8 4-8 4-8-4 8-4Z"></path><path d="m4 13 8 4 8-4"></path><path d="m4 17 8 4 8-4"></path></svg>',
      default => '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"></circle></svg>',
    };

    return new \Illuminate\Support\HtmlString($svg);
  };
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
            <span class="nav-icon" aria-hidden="true">{{ $navIcon('dashboard') }}</span>
            <span class="nav-label">Dashboard</span>
          </a>
        </div>

        <div class="nav-group">
          <div class="nav-group-title">Sales</div>
          <a href="{{ route('admin.orders') }}" class="nav-item {{ $currentPage === 'orders' ? 'active' : '' }}">
            <span class="nav-icon" aria-hidden="true">{{ $navIcon('orders') }}</span>
            <span class="nav-label">Orders</span>
          </a>
          <a href="{{ route('admin.products') }}" class="nav-item {{ $currentPage === 'products' ? 'active' : '' }}">
            <span class="nav-icon" aria-hidden="true">{{ $navIcon('products') }}</span>
            <span class="nav-label">Products</span>
          </a>
           <a href="{{ route('admin.users') }}" class="nav-item {{ $currentPage === 'users' ? 'active' : '' }}">
            <span class="nav-icon" aria-hidden="true">{{ $navIcon('customers') }}</span>
            <span class="nav-label">Customers</span>
          </a>
        </div>

        <div class="nav-group">
          <div class="nav-group-title">Automation</div>
          <a href="{{ route('admin.posts') }}" class="nav-item {{ $currentPage === 'posts' ? 'active' : '' }}">
            <span class="nav-icon" aria-hidden="true">{{ $navIcon('posts') }}</span>
            <span class="nav-label">Posts</span>
          </a>
          <a href="{{ route('admin.bot-settings') }}" class="nav-item {{ $currentPage === 'bot-settings' ? 'active' : '' }}">
            <span class="nav-icon" aria-hidden="true">{{ $navIcon('bot-settings') }}</span>
            <span class="nav-label">Bot Settings</span>
          </a>
          <a href="{{ route('admin.order-call') }}" class="nav-item {{ $currentPage === 'order-call' ? 'active' : '' }}">
            <span class="nav-icon" aria-hidden="true">{{ $navIcon('order-call') }}</span>
            <span class="nav-label">Call Confirm</span>
          </a>
          <a href="{{ route('admin.campaigns') }}" class="nav-item {{ $currentPage === 'campaigns' ? 'active' : '' }}">
            <span class="nav-icon" aria-hidden="true">{{ $navIcon('campaigns') }}</span>
            <span class="nav-label">Campaigns</span>
          </a>
        </div>

        <div class="nav-group">
          <div class="nav-group-title">Intelligence</div>
          <a href="{{ route('admin.competition') }}" class="nav-item {{ $currentPage === 'competition' ? 'active' : '' }}">
            <span class="nav-icon" aria-hidden="true">{{ $navIcon('competition') }}</span>
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
            <span class="nav-icon" aria-hidden="true">{{ $navIcon('courier') }}</span>
            <span class="nav-label">Courier Manager</span>
          </a>

          <details class="nav-accordion {{ $isShopSettingsRoute ? 'is-current' : '' }}" data-nav-accordion {{ $isShopSettingsRoute ? 'open' : '' }}>
            <summary class="nav-item nav-item-parent {{ $currentPage === 'settings' ? 'active' : '' }}">
              <span class="nav-icon" aria-hidden="true">{{ $navIcon('settings') }}</span>
              <span class="nav-label">Shop Settings</span>
              <span class="nav-caret" aria-hidden="true">
                <svg viewBox="0 0 20 20">
                  <path d="M5 7.5L10 12.5L15 7.5"></path>
                </svg>
              </span>
            </summary>
            <div class="nav-submenu" aria-label="Shop settings navigation">
              @foreach ($shopSettingsRoutes as $shopSettingsRoute => $shopSettingsLabel)
                <a href="{{ route($shopSettingsRoute) }}" class="nav-subitem {{ $routeName === $shopSettingsRoute ? 'active' : '' }}">
                  {{ $shopSettingsLabel }}
                </a>
              @endforeach
            </div>
          </details>
          <a href="{{ route('admin.packages') }}" class="nav-item {{ $currentPage === 'packages' ? 'active' : '' }}">
            <span class="nav-icon" aria-hidden="true">{{ $navIcon('packages') }}</span>
            <span class="nav-label">Packages</span>
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
