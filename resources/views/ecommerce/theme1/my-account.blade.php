@extends('ecommerce.theme1.master')

@section('title', 'My Account | Electro')

@section('ecom-master')
    @include('ecommerce.theme1.partials.page-header', [
        'title' => 'My Account',
        'subtitle' => 'A customer dashboard for profile details, addresses, and store activity.',
        'breadcrumbs' => [
            ['label' => 'Home', 'url' => route('ecommerce.index')],
            ['label' => 'Account'],
            ['label' => 'My Account'],
        ],
    ])

    <div class="container-fluid py-5">
        <div class="container py-5">
            <div class="row g-5">
                <div class="col-lg-4 col-xl-3">
                    @include('ecommerce.theme1.partials.account-sidebar')
                </div>

                <div class="col-lg-8 col-xl-9">
                    <div class="row g-4">
                        <div class="col-12 wow fadeInUp" data-wow-delay="0.1s">
                            <div class="theme-profile-card">
                                <div class="row g-4 align-items-center">
                                    <div class="col-lg-7">
                                        <span class="theme-kicker">Profile Overview</span>
                                        <h3 class="mt-4 mb-2">Emma Ahmed</h3>
                                        <p class="mb-1">emma@example.com</p>
                                        <p class="mb-0">+8801XXXXXXXXX</p>
                                    </div>
                                    <div class="col-lg-5">
                                        <div class="theme-stat-grid">
                                            <div class="theme-stat-card">
                                                <h4 class="text-primary">4</h4>
                                                <p class="mb-0">Saved addresses</p>
                                            </div>
                                            <div class="theme-stat-card">
                                                <h4 class="text-primary">6</h4>
                                                <p class="mb-0">Wishlist items</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6 wow fadeInUp" data-wow-delay="0.2s">
                            <div class="theme-panel h-100">
                                <h4 class="mb-3">Default Shipping Address</h4>
                                <p class="mb-1">House 24, Road 11</p>
                                <p class="mb-1">Dhanmondi, Dhaka</p>
                                <p class="mb-0">Bangladesh</p>
                            </div>
                        </div>

                        <div class="col-lg-6 wow fadeInUp" data-wow-delay="0.3s">
                            <div class="theme-panel h-100">
                                <h4 class="mb-3">Payment Preferences</h4>
                                <p class="mb-2">Preferred method: Card ending in 1182</p>
                                <p class="mb-0">Cash on delivery enabled for selected items and zones.</p>
                            </div>
                        </div>

                        <div class="col-lg-6 wow fadeInUp" data-wow-delay="0.4s">
                            <div class="theme-panel h-100">
                                <h4 class="mb-3">Quick Actions</h4>
                                <div class="d-flex flex-wrap gap-3">
                                    <a href="{{ route('ecommerce.order-history') }}" class="btn btn-primary rounded-pill px-4 py-2">Orders</a>
                                    <a href="{{ route('ecommerce.wishlist') }}" class="btn btn-light border rounded-pill px-4 py-2">Wishlist</a>
                                    <a href="{{ route('ecommerce.notifications') }}" class="btn btn-light border rounded-pill px-4 py-2">Notifications</a>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6 wow fadeInUp" data-wow-delay="0.5s">
                            <div class="theme-panel h-100">
                                <h4 class="mb-3">Recent Activity</h4>
                                <ul class="theme-bullet-list">
                                    <li>Tracked order #ELX-20489 this morning.</li>
                                    <li>Added Neo Smart TV 55" to wishlist yesterday.</li>
                                    <li>Updated delivery note for your work address.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
