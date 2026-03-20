@extends('ecommerce.theme1.master')

@section('title', 'Order History | Electro')

@section('ecom-master')
    @php
        $parseCurrency = static function (string $price): float {
            return (float) preg_replace('/[^\d.]/', '', str_replace(',', '', $price));
        };

        $orders = [
            [
                'id' => '#ELX-20489',
                'date' => '19 Mar 2026',
                'status' => 'In Transit',
                'class' => 'is-info',
                'total' => '$1,458.00',
                'payment' => 'Visa ending 4242',
                'delivery' => 'Today, 6:00 PM - 9:00 PM',
                'address' => 'House 12, Road 7, Dhanmondi, Dhaka',
                'products' => [
                    $themeProducts[1],
                    $themeProducts[4],
                ],
            ],
            [
                'id' => '#ELX-20412',
                'date' => '11 Mar 2026',
                'status' => 'Ready to Dispatch',
                'class' => 'is-warning',
                'total' => '$249.00',
                'payment' => 'Cash on delivery',
                'delivery' => 'Tomorrow, 1:00 PM - 5:00 PM',
                'address' => 'Uttara Sector 11, Dhaka',
                'products' => [
                    $themeProducts[2],
                ],
            ],
            [
                'id' => '#ELX-20504',
                'date' => '27 Feb 2026',
                'status' => 'Waiting for Confirmation',
                'class' => 'is-info',
                'total' => '$1,199.00',
                'payment' => 'Mastercard ending 9031',
                'delivery' => 'Confirmation in progress',
                'address' => 'Banani, Dhaka',
                'products' => [
                    $themeProducts[7],
                ],
            ],
            [
                'id' => '#ELX-20187',
                'date' => '14 Feb 2026',
                'status' => 'Delivered',
                'class' => 'is-success',
                'total' => '$89.00',
                'payment' => 'Mobile wallet',
                'delivery' => 'Delivered on 18 Mar 2026',
                'address' => 'Zindabazar, Sylhet',
                'products' => [
                    $themeProducts[8],
                ],
            ],
        ];

        $perPage = 2;
        $totalOrders = count($orders);
        $lastPage = max(1, (int) ceil($totalOrders / $perPage));
        $currentPage = max(1, min((int) request('page', 1), $lastPage));
        $currentOffset = ($currentPage - 1) * $perPage;
        $visibleOrders = array_slice($orders, $currentOffset, $perPage);
        $fromOrder = $totalOrders === 0 ? 0 : $currentOffset + 1;
        $toOrder = min($currentOffset + $perPage, $totalOrders);
        $lifetimeSpend = array_sum(array_map(static fn (array $order): float => $parseCurrency($order['total']), $orders));
        $openOrders = count(array_filter($orders, static fn (array $order): bool => in_array($order['status'], ['Waiting for Confirmation', 'Ready to Dispatch', 'In Transit'], true)));
    @endphp

    @include('ecommerce.theme1.partials.page-header', [
        'title' => 'Order History',
        'subtitle' => 'Review previous purchases, totals, and current order states from one dashboard.',
        'breadcrumbs' => [
            ['label' => 'Home', 'url' => route('ecommerce.index')],
            ['label' => 'Account'],
            ['label' => 'Order History'],
        ],
    ])

    <div class="container-fluid py-5">
        <div class="container py-5">
            <div class="row g-5">
                <div class="col-lg-4 col-xl-3">
                    @include('ecommerce.theme1.partials.account-sidebar')
                </div>

                <div class="col-lg-8 col-xl-9">
                    <div class="theme-stat-grid mb-4">
                        <div class="theme-stat-card wow fadeInUp" data-wow-delay="0.1s">
                            <h3 class="text-primary">{{ count($orders) }}</h3>
                            <p class="mb-0">Recent orders</p>
                        </div>
                        <div class="theme-stat-card wow fadeInUp" data-wow-delay="0.2s">
                            <h3 class="text-primary">${{ number_format($lifetimeSpend, 2) }}</h3>
                            <p class="mb-0">Tracked spend</p>
                        </div>
                        <div class="theme-stat-card wow fadeInUp" data-wow-delay="0.3s">
                            <h3 class="text-primary">{{ $openOrders }}</h3>
                            <p class="mb-0">Active deliveries</p>
                        </div>
                    </div>

                    <div class="theme-panel mb-4 wow fadeInUp" data-wow-delay="0.15s">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                            <div>
                                <span class="theme-kicker"><i class="fas fa-box-open"></i> Orders</span>
                                <h3 class="mt-3 mb-2">Your order timeline</h3>
                                <p class="mb-0 text-muted">Each order now uses a card layout that stays easy to scan on mobile, tablet, and desktop.</p>
                            </div>
                            <div class="text-lg-end">
                                <p class="mb-2 text-muted">Showing {{ $fromOrder }}-{{ $toOrder }} of {{ $totalOrders }} orders</p>
                                <a href="{{ route('ecommerce.shop') }}" class="btn btn-secondary rounded-pill px-4 py-2">Shop Again</a>
                            </div>
                        </div>
                    </div>

                    <div class="theme-order-card-stack">
                        @foreach ($visibleOrders as $order)
                            <article class="theme-order-card wow fadeInUp" data-wow-delay="{{ number_format(($loop->index + 2) / 10, 1) }}s">
                                <div class="theme-order-card-head">
                                    <div>
                                        <span class="theme-kicker">Order {{ $order['id'] }}</span>
                                        <h3 class="theme-order-card-title mt-3 mb-2">{{ count($order['products']) }} {{ \Illuminate\Support\Str::plural('item', count($order['products'])) }} in this order</h3>
                                        <p class="mb-0 text-muted">Placed on {{ $order['date'] }}</p>
                                    </div>
                                    <div class="text-lg-end">
                                        <span class="theme-status-pill {{ $order['class'] }}">{{ $order['status'] }}</span>
                                        <div class="theme-order-total mt-3">{{ $order['total'] }}</div>
                                    </div>
                                </div>

                                <div class="theme-order-meta-grid">
                                    <div class="theme-order-meta-card">
                                        <small class="theme-order-meta-label">Payment</small>
                                        <strong>{{ $order['payment'] }}</strong>
                                    </div>
                                    <div class="theme-order-meta-card">
                                        <small class="theme-order-meta-label">Delivery</small>
                                        <strong>{{ $order['delivery'] }}</strong>
                                    </div>
                                    <div class="theme-order-meta-card">
                                        <small class="theme-order-meta-label">Address</small>
                                        <strong>{{ $order['address'] }}</strong>
                                    </div>
                                </div>

                                <div class="theme-order-item-list">
                                    @foreach ($order['products'] as $product)
                                        <div class="theme-order-item">
                                            <div class="theme-order-item-thumb">
                                                <img src="{{ asset('assets/theme1/img/' . $product['image']) }}" class="img-fluid" alt="{{ $product['name'] }}">
                                            </div>
                                            <div class="flex-grow-1">
                                                <p class="text-primary mb-1">{{ $product['category_name'] }}</p>
                                                <h5 class="mb-1">{{ $product['name'] }}</h5>
                                                <p class="text-muted mb-2">{{ $product['excerpt'] }}</p>
                                                <span class="theme-status-pill {{ str_contains($product['stock'], 'Limited') ? 'is-warning' : 'is-success' }}">
                                                    {{ $product['stock'] }}
                                                </span>
                                            </div>
                                            <div class="theme-order-item-price">
                                                <span class="fw-bold text-primary">{{ $product['price'] }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="theme-order-action-bar">
                                    <a href="{{ route('ecommerce.track-order.public', ['orderId' => ltrim($order['id'], '#')]) }}" class="btn btn-primary rounded-pill px-4 py-2">Track Order</a>
                                    <a href="{{ route('ecommerce.shop') }}" class="btn btn-light border rounded-pill px-4 py-2">Buy Again</a>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    @if ($lastPage > 1)
                        <div class="pagination d-flex justify-content-center mt-5 wow fadeInUp" data-wow-delay="0.2s">
                            @if ($currentPage > 1)
                                <a href="{{ request()->fullUrlWithQuery(['page' => $currentPage - 1]) }}" class="rounded">&laquo;</a>
                            @endif

                            @for ($page = 1; $page <= $lastPage; $page++)
                                <a href="{{ request()->fullUrlWithQuery(['page' => $page]) }}" class="{{ $page === $currentPage ? 'active rounded' : 'rounded' }}">
                                    {{ $page }}
                                </a>
                            @endfor

                            @if ($currentPage < $lastPage)
                                <a href="{{ request()->fullUrlWithQuery(['page' => $currentPage + 1]) }}" class="rounded">&raquo;</a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
