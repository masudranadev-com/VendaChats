@extends('ecommerce.theme1.master')

@section('title', 'Login | Electro')

@section('ecom-master')
    @include('ecommerce.theme1.partials.page-header', [
        'title' => 'Login',
        'subtitle' => 'Access saved items, checkout faster, and manage your store activity from one place.',
        'breadcrumbs' => [
            ['label' => 'Home', 'url' => route('ecommerce.index')],
            ['label' => 'Shop', 'url' => route('ecommerce.shop')],
            ['label' => 'Login'],
        ],
    ])

    <div class="container-fluid py-5">
        <div class="container py-5">
            <div class="row g-5 align-items-stretch">
                <div class="col-lg-5 wow fadeInLeft" data-wow-delay="0.1s">
                    <div class="theme-panel theme-panel-highlight h-100">
                        <span class="theme-kicker">Member Access</span>
                        <h2 class="display-6 mt-4 mb-3">Welcome back to Electro</h2>
                        <p class="mb-4">Sign in to continue your checkout, review previous orders, and keep your wishlist synced across devices.</p>

                        <div class="theme-feature-list">
                            <div class="theme-feature-item">
                                <span class="theme-feature-icon"><i class="fas fa-bolt"></i></span>
                                <div>
                                    <h6 class="mb-1">Faster Checkout</h6>
                                    <p class="mb-0">Saved contact information and delivery preferences when you are ready to buy.</p>
                                </div>
                            </div>
                            <div class="theme-feature-item">
                                <span class="theme-feature-icon"><i class="fas fa-heart"></i></span>
                                <div>
                                    <h6 class="mb-1">Synced Wishlist</h6>
                                    <p class="mb-0">Keep product picks, gift ideas, and price-drop targets organized in one place.</p>
                                </div>
                            </div>
                            <div class="theme-feature-item">
                                <span class="theme-feature-icon"><i class="fas fa-truck"></i></span>
                                <div>
                                    <h6 class="mb-1">Order Tracking</h6>
                                    <p class="mb-0">See your current delivery stage, courier updates, and handoff status instantly.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-7 wow fadeInRight" data-wow-delay="0.2s">
                    <div class="theme-auth-card h-100">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                            <div>
                                <h3 class="mb-1">Sign In</h3>
                                <p class="mb-0 text-muted">Use your customer email and password to continue.</p>
                            </div>
                            <a href="{{ route('ecommerce.signup') }}" class="btn btn-secondary rounded-pill px-4 py-2">Create Account</a>
                        </div>

                        <form class="row g-4">
                            <div class="col-12">
                                <label class="form-label">Email address</label>
                                <input type="email" class="form-control" placeholder="name@example.com">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Password</label>
                                <input type="password" class="form-control" placeholder="Enter your password">
                            </div>
                            <div class="col-md-6">
                                <div class="form-check pt-2">
                                    <input class="form-check-input" type="checkbox" id="remember-login">
                                    <label class="form-check-label" for="remember-login">Keep me signed in</label>
                                </div>
                            </div>
                            <div class="col-md-6 text-md-end pt-2">
                                <a href="{{ route('ecommerce.signup') }}" class="text-primary fw-semibold">Create new account?</a>
                            </div>
                            <div class="col-12">
                                <button type="button" class="btn btn-primary rounded-pill py-3 px-5">Login to Account</button>
                            </div>
                        </form>

                        <div class="theme-auth-divider my-4">or continue with</div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <button type="button" class="btn btn-light border rounded-pill w-100 py-3">Google</button>
                            </div>
                            <div class="col-md-6">
                                <button type="button" class="btn btn-light border rounded-pill w-100 py-3">Facebook</button>
                            </div>
                        </div>

                        <p class="text-muted mt-4 mb-0">This storefront layout is ready for your customer authentication flow and account integration.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('ecommerce.theme1.partials.services-strip')
@endsection
