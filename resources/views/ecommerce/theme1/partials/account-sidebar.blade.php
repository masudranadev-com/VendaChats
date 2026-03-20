<div class="theme-panel theme-account-sidebar wow fadeInUp" data-wow-delay="0.1s">
    <div class="theme-account-summary">
        <div class="theme-account-avatar">EA</div>
        <div>
            <h5 class="mb-1">Emma Ahmed</h5>
            <p class="mb-0 text-muted">Member since April 2024</p>
        </div>
    </div>

    <div class="theme-account-nav">
        <a href="{{ route('ecommerce.my-account') }}"
            class="theme-account-link {{ request()->routeIs('ecommerce.my-account') ? 'active' : '' }}">
            <i class="fas fa-user-circle"></i> My Account
        </a>
        <a href="{{ route('ecommerce.order-history') }}"
            class="theme-account-link {{ request()->routeIs('ecommerce.order-history') ? 'active' : '' }}">
            <i class="fas fa-box-open"></i> Order History
        </a>
        <a href="{{ route('ecommerce.wishlist') }}"
            class="theme-account-link {{ request()->routeIs('ecommerce.wishlist') ? 'active' : '' }}">
            <i class="fas fa-heart"></i> Wishlist
        </a>
        <a href="{{ route('ecommerce.notifications') }}"
            class="theme-account-link {{ request()->routeIs('ecommerce.notifications') ? 'active' : '' }}">
            <i class="fas fa-bell"></i> Notifications
        </a>
        <a href="{{ route('ecommerce.track-order') }}"
            class="theme-account-link {{ request()->routeIs('ecommerce.track-order') ? 'active' : '' }}">
            <i class="fas fa-truck"></i> Track Order
        </a>
        <a href="{{ route('ecommerce.login') }}"
            class="theme-account-link {{ request()->routeIs('ecommerce.login', 'ecommerce.signup') ? 'active' : '' }}">
            <i class="fas fa-sign-in-alt"></i> Login / Signup
        </a>
    </div>

    <div class="theme-help-card">
        <span class="theme-kicker">Need help?</span>
        <h6 class="mt-3 mb-2">Store support is live</h6>
        <p class="mb-3">Chat with our team about delivery, returns, account access, or product guidance.</p>
        <a href="{{ route('ecommerce.contact') }}" class="btn btn-primary rounded-pill px-4 py-2">Contact Support</a>
    </div>
</div>
