@php
    $primaryProductSlug = $themePrimaryProduct['slug'] ?? 'apple-ipad-mini-g2356';

    $dashboardLinks = [
        ['label' => 'Login', 'route' => 'ecommerce.login'],
        ['label' => 'Signup', 'route' => 'ecommerce.signup'],
        ['label' => 'Wishlist', 'route' => 'ecommerce.wishlist'],
        ['label' => 'Order History', 'route' => 'ecommerce.order-history'],
        ['label' => 'Notifications', 'route' => 'ecommerce.notifications'],
        ['label' => 'My Account', 'route' => 'ecommerce.my-account'],
    ];

    $shopPageLinks = [
        ['label' => 'Bestseller', 'route' => 'ecommerce.bestseller'],
        ['label' => 'Gift Vouchers', 'route' => 'ecommerce.gift-vouchers'],
        ['label' => 'Wishlist', 'route' => 'ecommerce.wishlist'],
        ['label' => 'Track Your Order', 'route' => 'ecommerce.track-order'],
        ['label' => 'Order History', 'route' => 'ecommerce.order-history'],
        ['label' => 'My Account', 'route' => 'ecommerce.my-account'],
        ['label' => 'Notifications', 'route' => 'ecommerce.notifications'],
        ['label' => 'Cart Page', 'route' => 'ecommerce.cart'],
        ['label' => 'Cheackout', 'route' => 'ecommerce.cheackout'],
    ];

    $supportLinks = [
        ['label' => 'About Us', 'route' => 'ecommerce.about-us'],
        ['label' => 'FAQ', 'route' => 'ecommerce.faq'],
        ['label' => 'Returns & Warranty', 'route' => 'ecommerce.returns-warranty'],
        ['label' => 'Privacy Policy', 'route' => 'ecommerce.privacy-policy'],
        ['label' => 'Terms & Conditions', 'route' => 'ecommerce.terms-conditions'],
    ];

    $footerCustomerLinks = [
        ['label' => 'Contact Us', 'route' => 'ecommerce.contact'],
        ['label' => 'Track Your Order', 'route' => 'ecommerce.track-order'],
        ['label' => 'Order History', 'route' => 'ecommerce.order-history'],
        ['label' => 'My Account', 'route' => 'ecommerce.my-account'],
    ];

    $footerExtraLinks = [
        ['label' => 'Gift Vouchers', 'route' => 'ecommerce.gift-vouchers'],
        ['label' => 'Wishlist', 'route' => 'ecommerce.wishlist'],
        ['label' => 'Login', 'route' => 'ecommerce.login'],
        ['label' => 'Signup', 'route' => 'ecommerce.signup'],
    ];
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Electro | Smart Tech Store')</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600;700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <link href="{{ asset('assets/theme1/') }}/lib/animate/animate.min.css" rel="stylesheet">
    <link href="{{ asset('assets/theme1/') }}/lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="{{ asset('assets/theme1/') }}/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('assets/theme1/') }}/css/style.css" rel="stylesheet">
    <link href="{{ asset('assets/theme1/') }}/css/custom-pages.css" rel="stylesheet">
    <script>
        (function() {
            try {
                var raw = localStorage.getItem('theme1-css-vars-v2');
                if (!raw) {
                    return;
                }

                var variables = JSON.parse(raw);
                Object.keys(variables).forEach(function(key) {
                    document.documentElement.style.setProperty(key, variables[key]);
                });
            } catch (error) {}
        })();
    </script>
</head>

<body>
    <div id="spinner"
        class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>

    <div class="container-fluid px-5 d-none border-bottom d-lg-block">
        <div class="row gx-0 align-items-center">
            <div class="col-lg-4 text-center text-lg-start mb-lg-0">
                <div class="d-inline-flex align-items-center" style="height: 45px;">
                    <a href="{{ route('ecommerce.returns-warranty') }}" class="text-muted me-2">Returns</a><small> / </small>
                    <a href="{{ route('ecommerce.faq') }}" class="text-muted mx-2">FAQ</a><small> / </small>
                    <a href="{{ route('ecommerce.contact') }}" class="text-muted ms-2">Contact</a>
                </div>
            </div>
            <div class="col-lg-4 text-center d-flex align-items-center justify-content-center">
                <small class="text-dark">Call Us:</small>
                <a href="tel:+0121234567890" class="text-muted ms-2">(+012) 1234 567890</a>
            </div>
            <div class="col-lg-4 text-center text-lg-end">
                <div class="d-inline-flex align-items-center" style="height: 45px;">
                    <div class="dropdown">
                        <a href="#" class="dropdown-toggle text-muted me-2" data-bs-toggle="dropdown"><small>USD</small></a>
                        <div class="dropdown-menu rounded">
                            <a href="#" class="dropdown-item">USD</a>
                            <a href="#" class="dropdown-item">EUR</a>
                            <a href="#" class="dropdown-item">BDT</a>
                        </div>
                    </div>
                    <div class="dropdown">
                        <a href="#" class="dropdown-toggle text-muted mx-2" data-bs-toggle="dropdown"><small>English</small></a>
                        <div class="dropdown-menu rounded">
                            <a href="#" class="dropdown-item">English</a>
                            <a href="#" class="dropdown-item">Bangla</a>
                        </div>
                    </div>
                    <div class="dropdown">
                        <a href="#" class="dropdown-toggle text-muted ms-2" data-bs-toggle="dropdown">
                            <small><i class="fa fa-home me-2"></i>My Dashboard</small>
                        </a>
                        <div class="dropdown-menu rounded">
                            @foreach ($dashboardLinks as $link)
                                <a href="{{ route($link['route']) }}" class="dropdown-item">{{ $link['label'] }}</a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid px-5 py-4 d-none d-lg-block">
        <div class="row gx-0 align-items-center text-center">
            <div class="col-md-4 col-lg-3 text-center text-lg-start">
                <div class="d-inline-flex align-items-center">
                    <a href="{{ route('ecommerce.index') }}" class="navbar-brand p-0">
                        <h1 class="display-5 text-primary m-0">
                            <i class="fas fa-shopping-bag text-secondary me-2"></i>Electro
                        </h1>
                    </a>
                </div>
            </div>
            <div class="col-md-4 col-lg-6 text-center">
                <div class="position-relative ps-4">
                    <div class="d-flex border rounded-pill">
                        <input class="form-control border-0 rounded-pill w-100 py-3" type="text"
                            placeholder="Search devices, accessories, brands...">
                        <select class="form-select text-dark border-0 border-start rounded-0 p-3" style="width: 220px;">
                            <option value="">All Categories</option>
                            @foreach ($themeCategories as $category)
                                <option value="{{ $category['slug'] }}">{{ $category['name'] }}</option>
                            @endforeach
                        </select>
                        <button type="button" class="btn btn-primary rounded-pill py-3 px-5" style="border: 0;">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-3 text-center text-lg-end">
                <div class="d-inline-flex align-items-center">
                    <a href="{{ route('ecommerce.track-order') }}" class="text-muted d-flex align-items-center justify-content-center me-3">
                        <span class="rounded-circle btn-md-square border"><i class="fas fa-truck"></i></span>
                    </a>
                    <a href="{{ route('ecommerce.wishlist') }}" class="text-muted d-flex align-items-center justify-content-center me-3">
                        <span class="rounded-circle btn-md-square border"><i class="fas fa-heart"></i></span>
                    </a>
                    <a href="{{ route('ecommerce.cart') }}" class="text-muted d-flex align-items-center justify-content-center">
                        <span class="rounded-circle btn-md-square border"><i class="fas fa-shopping-cart"></i></span>
                        <span class="text-dark ms-2">$0.00</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid nav-bar p-0">
        <div class="row gx-0 bg-primary px-5 align-items-center">
            <div class="col-lg-3 d-none d-lg-block">
                <nav class="navbar navbar-light position-relative" style="width: 250px;">
                    <button class="navbar-toggler border-0 fs-4 w-100 px-0 text-start" type="button"
                        data-bs-toggle="collapse" data-bs-target="#allCat">
                        <h4 class="m-0"><i class="fa fa-bars me-2"></i>All Categories</h4>
                    </button>
                    <div class="collapse navbar-collapse rounded-bottom" id="allCat">
                        <div class="navbar-nav ms-auto py-0">
                            <ul class="list-unstyled categories-bars">
                                @foreach ($themeCategories as $category)
                                    <li>
                                        <div class="categories-bars-item">
                                            <a href="{{ route('ecommerce.category.show', ['slug' => $category['slug']]) }}">
                                                {{ $category['name'] }}
                                            </a>
                                            <span>({{ $category['count'] }})</span>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </nav>
            </div>
            <div class="col-12 col-lg-9">
                <nav class="navbar navbar-expand-lg navbar-light bg-primary">
                    <a href="{{ route('ecommerce.index') }}" class="navbar-brand d-block d-lg-none">
                        <h1 class="display-5 text-secondary m-0">
                            <i class="fas fa-shopping-bag text-white me-2"></i>Electro
                        </h1>
                    </a>
                    <button class="navbar-toggler ms-auto" type="button" data-bs-toggle="collapse"
                        data-bs-target="#navbarCollapse">
                        <span class="fa fa-bars fa-1x"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarCollapse">
                        <div class="navbar-nav ms-auto py-0">
                            <a href="{{ route('ecommerce.index') }}"
                                class="nav-item nav-link {{ request()->routeIs('ecommerce.index') ? 'active' : '' }}">Home</a>
                            <a href="{{ route('ecommerce.shop') }}"
                                class="nav-item nav-link {{ request()->routeIs('ecommerce.shop', 'ecommerce.category.show') ? 'active' : '' }}">Shop</a>
                            <a href="{{ route('ecommerce.product.show', ['slug' => $primaryProductSlug]) }}"
                                class="nav-item nav-link {{ request()->routeIs('ecommerce.product.show') ? 'active' : '' }}">Single Page</a>
                            <div class="nav-item dropdown">
                                <a href="#"
                                    class="nav-link dropdown-toggle {{ request()->routeIs('ecommerce.about-us', 'ecommerce.faq', 'ecommerce.returns-warranty', 'ecommerce.privacy-policy', 'ecommerce.terms-conditions') ? 'active' : '' }}"
                                    data-bs-toggle="dropdown">Support</a>
                                <div class="dropdown-menu m-0">
                                    @foreach ($supportLinks as $link)
                                        <a href="{{ route($link['route']) }}" class="dropdown-item">{{ $link['label'] }}</a>
                                    @endforeach
                                </div>
                            </div>
                            <div class="nav-item dropdown">
                                <a href="#"
                                    class="nav-link dropdown-toggle {{ request()->routeIs('ecommerce.login', 'ecommerce.signup', 'ecommerce.gift-vouchers', 'ecommerce.wishlist', 'ecommerce.track-order', 'ecommerce.order-history', 'ecommerce.my-account', 'ecommerce.notifications', 'ecommerce.bestseller', 'ecommerce.cart', 'ecommerce.cheackout', 'ecommerce.not-found') ? 'active' : '' }}"
                                    data-bs-toggle="dropdown">Pages</a>
                                <div class="dropdown-menu m-0">
                                    @foreach ($shopPageLinks as $link)
                                        <a href="{{ route($link['route']) }}" class="dropdown-item">{{ $link['label'] }}</a>
                                    @endforeach
                                    <a href="{{ route('ecommerce.not-found') }}" class="dropdown-item">404 Page</a>
                                </div>
                            </div>
                            <a href="{{ route('ecommerce.contact') }}"
                                class="nav-item nav-link me-2 {{ request()->routeIs('ecommerce.contact') ? 'active' : '' }}">Contact</a>
                            <div class="nav-item dropdown d-block d-lg-none mb-3">
                                <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">All Category</a>
                                <div class="dropdown-menu m-0">
                                    <ul class="list-unstyled categories-bars">
                                        @foreach ($themeCategories as $category)
                                            <li>
                                                <div class="categories-bars-item">
                                                    <a href="{{ route('ecommerce.category.show', ['slug' => $category['slug']]) }}">
                                                        {{ $category['name'] }}
                                                    </a>
                                                    <span>({{ $category['count'] }})</span>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <a href="tel:+0121234567890" class="btn btn-secondary rounded-pill py-2 px-4 px-lg-3 mb-3 mb-md-3 mb-lg-0">
                            <i class="fa fa-mobile-alt me-2"></i> +0123 456 7890
                        </a>
                    </div>
                </nav>
            </div>
        </div>
    </div>

    @yield('ecom-master')

    <div class="container-fluid footer py-5 wow fadeIn" data-wow-delay="0.2s">
        <div class="container py-5">
            <div class="row g-5">
                <div class="col-md-6 col-lg-6 col-xl-3">
                    <div class="footer-item d-flex flex-column">
                        <div class="footer-item">
                            <h4 class="text-primary mb-4">Newsletter</h4>
                            <p class="mb-3">New launches, exclusive drops, and member-only deals from the Electro storefront.</p>
                            <div class="position-relative mx-auto rounded-pill">
                                <input class="form-control rounded-pill w-100 py-3 ps-4 pe-5" type="text"
                                    placeholder="Enter your email">
                                <button type="button"
                                    class="btn btn-primary rounded-pill position-absolute top-0 end-0 py-2 mt-2 me-2">Sign Up</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-6 col-xl-3">
                    <div class="footer-item d-flex flex-column">
                        <h4 class="text-primary mb-4">Customer Service</h4>
                        @foreach ($footerCustomerLinks as $link)
                            <a href="{{ route($link['route']) }}"><i class="fas fa-angle-right me-2"></i> {{ $link['label'] }}</a>
                        @endforeach
                    </div>
                </div>
                <div class="col-md-6 col-lg-6 col-xl-3">
                    <div class="footer-item d-flex flex-column">
                        <h4 class="text-primary mb-4">Information</h4>
                        @foreach ($supportLinks as $link)
                            <a href="{{ route($link['route']) }}"><i class="fas fa-angle-right me-2"></i> {{ $link['label'] }}</a>
                        @endforeach
                    </div>
                </div>
                <div class="col-md-6 col-lg-6 col-xl-3">
                    <div class="footer-item d-flex flex-column">
                        <h4 class="text-primary mb-4">Extras</h4>
                        @foreach ($footerExtraLinks as $link)
                            <a href="{{ route($link['route']) }}"><i class="fas fa-angle-right me-2"></i> {{ $link['label'] }}</a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid copyright py-4">
        <div class="container">
            <div class="row g-4 align-items-center">
                <div class="col-md-6 text-center text-md-start mb-md-0">
                    <span class="text-white">
                        <a href="{{ route('ecommerce.index') }}" class="border-bottom text-white">
                            <i class="fas fa-copyright text-light me-2"></i>Electro
                        </a>, All rights reserved.
                    </span>
                </div>
                <div class="col-md-6 text-center text-md-end text-white">
                    Designed By <a class="border-bottom text-white" href="https://htmlcodex.com">HTML Codex</a>.
                    Distributed By <a class="border-bottom text-white" href="https://themewagon.com">ThemeWagon</a>
                </div>
            </div>
        </div>
    </div>

    <a href="#" class="btn btn-primary btn-lg-square back-to-top"><i class="fa fa-arrow-up"></i></a>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('assets/theme1/') }}/lib/wow/wow.min.js"></script>
    <script src="{{ asset('assets/theme1/') }}/lib/owlcarousel/owl.carousel.min.js"></script>

    <script src="{{ asset('assets/theme1/') }}/js/apis.js"></script>
    <script src="{{ asset('assets/theme1/') }}/js/main.js"></script>
</body>

</html>
