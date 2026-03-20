@extends('ecommerce.theme1.master')

@section('title', 'Cart | Electro')

@section('ecom-master')
    @php
        $cartProducts = array_slice($themeProducts, 0, 3);
        $quantities = [1, 2, 1];
        $parseCurrency = static function (string $price): float {
            return (float) preg_replace('/[^\d.]/', '', str_replace(',', '', $price));
        };

        $cartItems = [];
        $subtotal = 0.0;

        foreach ($cartProducts as $index => $product) {
            $quantity = $quantities[$index] ?? 1;
            $lineValue = $parseCurrency($product['price']) * $quantity;
            $subtotal += $lineValue;

            $cartItems[] = $product + [
                'quantity' => $quantity,
                'line_total' => '$' . number_format($lineValue, 2),
            ];
        }

        $cartItemCount = array_sum(array_column($cartItems, 'quantity'));
        $shipping = $subtotal >= 1500 ? 0.0 : 35.0;
        $total = $subtotal + $shipping;
    @endphp

    @include('ecommerce.theme1.partials.page-header', [
        'title' => 'Cart Items',
        'subtitle' => 'A signed-in style cart layout with saved items, quantity controls, and a checkout summary.',
        'breadcrumbs' => [
            ['label' => 'Home', 'url' => route('ecommerce.index')],
            ['label' => 'Account'],
            ['label' => 'Cart Items'],
        ],
    ])

    <div class="container-fluid py-5">
        <div class="container py-5">
            <div class="row g-5">
                <div class="col-lg-4 col-xl-3">
                    @include('ecommerce.theme1.partials.account-sidebar')
                </div>

                <div class="col-lg-8 col-xl-9">
                    <div class="theme-panel theme-panel-highlight mb-4 wow fadeInUp" data-wow-delay="0.1s">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                            <div>
                                <span class="theme-kicker"><i class="fas fa-shopping-cart"></i> Shopping Cart</span>
                                <h3 class="mt-3 mb-2">Review your selected items</h3>
                                <p class="mb-0 text-muted">Check your products, update quantities, and proceed to checkout when you are ready.</p>
                            </div>
                            <div class="d-flex flex-wrap gap-3">
                                <a href="{{ route('ecommerce.shop') }}" class="btn btn-secondary rounded-pill px-4 py-2">Continue Shopping</a>
                                <a href="{{ route('ecommerce.cheackout') }}" class="btn btn-primary rounded-pill px-4 py-2">Proceed Checkout</a>
                            </div>
                        </div>
                    </div>

                    <div class="theme-stat-grid mb-4">
                        <div class="theme-stat-card wow fadeInUp" data-wow-delay="0.1s">
                            <h3 class="text-primary">{{ $cartItemCount }}</h3>
                            <p class="mb-0">Items in cart</p>
                        </div>
                        <div class="theme-stat-card wow fadeInUp" data-wow-delay="0.2s">
                            <h3 class="text-primary">${{ number_format($subtotal, 2) }}</h3>
                            <p class="mb-0">Current subtotal</p>
                        </div>
                        <div class="theme-stat-card wow fadeInUp" data-wow-delay="0.3s">
                            <h3 class="text-primary">{{ $shipping > 0 ? '$' . number_format($shipping, 2) : 'Free' }}</h3>
                            <p class="mb-0">Estimated shipping</p>
                        </div>
                    </div>

                    <div class="row g-4">
                        <div class="col-xl-8">
                            <div class="theme-panel mb-4 wow fadeInUp" data-wow-delay="0.2s">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                                    <div>
                                        <h3 class="mb-1">Your Cart</h3>
                                        <p class="mb-0 text-muted">Review product quantities, save items for later, or remove them before checkout.</p>
                                    </div>
                                    <a href="{{ route('ecommerce.wishlist') }}" class="btn btn-light border rounded-pill px-4 py-2">
                                        <i class="fas fa-heart me-2 text-danger"></i> Open Wishlist
                                    </a>
                                </div>
                            </div>

                            @foreach ($cartItems as $item)
                                <div class="theme-saved-item theme-cart-item wow fadeInUp" data-wow-delay="{{ number_format(($loop->index + 2) / 10, 1) }}s">
                                    <div class="theme-saved-thumb">
                                        <img src="{{ asset('assets/theme1/img/' . $item['image']) }}" class="img-fluid" alt="{{ $item['name'] }}">
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between gap-3 flex-wrap">
                                            <div>
                                                <p class="text-primary mb-1">{{ $item['category_name'] }}</p>
                                                <h4 class="mb-2">{{ $item['name'] }}</h4>
                                                <p class="text-muted mb-3">{{ $item['excerpt'] }}</p>
                                            </div>

                                            <div class="theme-cart-price text-lg-end">
                                                <small class="text-muted d-block mb-1">Unit price</small>
                                                <span class="fs-5 text-primary fw-bold d-block">{{ $item['price'] }}</span>
                                                <small class="text-muted d-block mt-3 mb-1">Line total</small>
                                                <span class="fw-bold">{{ $item['line_total'] }}</span>
                                            </div>
                                        </div>

                                        <div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mt-4">
                                            <div>
                                                <small class="text-muted d-block mb-2">Quantity</small>
                                                <div class="input-group quantity theme-cart-quantity">
                                                    <div class="input-group-btn">
                                                        <button class="btn btn-sm btn-minus rounded-circle bg-light border" type="button">
                                                            <i class="fa fa-minus"></i>
                                                        </button>
                                                    </div>
                                                    <input type="text" class="form-control form-control-sm text-center border-0" value="{{ $item['quantity'] }}">
                                                    <div class="input-group-btn">
                                                        <button class="btn btn-sm btn-plus rounded-circle bg-light border" type="button">
                                                            <i class="fa fa-plus"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="d-flex flex-wrap gap-3">
                                                <a href="{{ route('ecommerce.product.show', ['slug' => $item['slug']]) }}" class="btn btn-light border rounded-pill px-4 py-2">View Product</a>
                                                <button type="button" class="btn btn-light border rounded-pill px-4 py-2">Remove</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="col-xl-4">
                            <div class="theme-panel theme-cart-summary wow fadeInUp" data-wow-delay="0.3s">
                                <span class="theme-kicker">Order Summary</span>
                                <h3 class="mt-3 mb-4">Checkout estimate</h3>

                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="text-muted">Subtotal</span>
                                    <strong>${{ number_format($subtotal, 2) }}</strong>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="text-muted">Shipping</span>
                                    <strong>{{ $shipping > 0 ? '$' . number_format($shipping, 2) : 'Free' }}</strong>
                                </div>

                                <div class="d-flex justify-content-between align-items-center py-3 border-top border-bottom mb-4">
                                    <span class="fw-bold">Estimated total</span>
                                    <span class="fs-4 text-primary fw-bold">${{ number_format($total, 2) }}</span>
                                </div>

                                <div class="mb-4">
                                    <label for="cartCouponCode" class="form-label fw-semibold">Promo code</label>
                                    <input id="cartCouponCode" type="text" class="form-control theme-form-control" placeholder="Enter coupon code">
                                </div>

                                <div class="d-grid gap-3">
                                    <button type="button" class="btn btn-primary rounded-pill py-3">Apply Coupon</button>
                                    <a href="{{ route('ecommerce.cheackout') }}" class="btn btn-secondary rounded-pill py-3">Proceed To Checkout</a>
                                </div>

                                <ul class="theme-bullet-list mt-4">
                                    <li>Authentication can be attached here later without changing the page structure.</li>
                                    <li>Cart and wishlist now share the same account-dashboard language.</li>
                                    <li>Quantity controls remain visible on both desktop and mobile.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
