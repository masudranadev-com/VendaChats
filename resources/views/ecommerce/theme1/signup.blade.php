@extends('ecommerce.theme1.master')

@section('title', 'Signup | Electro')

@section('ecom-master')
    @include('ecommerce.theme1.partials.page-header', [
        'title' => 'Signup',
        'subtitle' => 'Create a store account to save favourites, manage deliveries, and receive launch alerts.',
        'breadcrumbs' => [
            ['label' => 'Home', 'url' => route('ecommerce.index')],
            ['label' => 'Shop', 'url' => route('ecommerce.shop')],
            ['label' => 'Signup'],
        ],
    ])

    <div class="container-fluid py-5">
        <div class="container py-5">
            <div class="row g-5 align-items-stretch">
                <div class="col-lg-6 wow fadeInLeft" data-wow-delay="0.1s">
                    <div class="theme-panel h-100">
                        <span class="theme-kicker">New Members</span>
                        <h2 class="display-6 mt-4 mb-3">Set up your Electro profile</h2>
                        <p class="mb-4">Your account becomes the home for saved addresses, gift voucher balance, warranty claims, and personalized recommendations.</p>

                        <div class="theme-stat-grid">
                            <div class="theme-stat-card">
                                <h3 class="text-primary">24/7</h3>
                                <p class="mb-0">Order visibility and account access</p>
                            </div>
                            <div class="theme-stat-card">
                                <h3 class="text-primary">1 Click</h3>
                                <p class="mb-0">Wishlist to cart from any device</p>
                            </div>
                            <div class="theme-stat-card">
                                <h3 class="text-primary">Secure</h3>
                                <p class="mb-0">Profile and notification preferences</p>
                            </div>
                        </div>

                        <ul class="theme-bullet-list mt-4">
                            <li>Receive stock alerts and limited-drop updates for the categories you follow.</li>
                            <li>Track returns, warranty requests, and purchase history without contacting support.</li>
                            <li>Store multiple delivery addresses for work, home, and gifting.</li>
                        </ul>
                    </div>
                </div>

                <div class="col-lg-6 wow fadeInRight" data-wow-delay="0.2s">
                    <div class="theme-auth-card h-100">
                        <div class="mb-4">
                            <h3 class="mb-1">Create Account</h3>
                            <p class="mb-0 text-muted">Use this layout to connect your customer registration flow.</p>
                        </div>

                        <form class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label">First name</label>
                                <input type="text" class="form-control" placeholder="Emma">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Last name</label>
                                <input type="text" class="form-control" placeholder="Ahmed">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Email address</label>
                                <input type="email" class="form-control" placeholder="name@example.com">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone number</label>
                                <input type="text" class="form-control" placeholder="+8801XXXXXXXXX">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Preferred category</label>
                                <select class="form-select">
                                    <option>Mobiles & Tablets</option>
                                    @foreach ($themeCategories as $category)
                                        <option>{{ $category['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Password</label>
                                <input type="password" class="form-control" placeholder="At least 8 characters">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Confirm password</label>
                                <input type="password" class="form-control" placeholder="Repeat your password">
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="signup-terms">
                                    <label class="form-check-label" for="signup-terms">
                                        I agree to the <a href="{{ route('ecommerce.terms-conditions') }}">terms</a> and <a href="{{ route('ecommerce.privacy-policy') }}">privacy policy</a>.
                                    </label>
                                </div>
                            </div>
                            <div class="col-12 d-flex flex-wrap gap-3">
                                <button type="button" class="btn btn-primary rounded-pill py-3 px-5">Create My Account</button>
                                <a href="{{ route('ecommerce.login') }}" class="btn btn-light border rounded-pill py-3 px-5">Already have an account?</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('ecommerce.theme1.partials.services-strip')
@endsection
