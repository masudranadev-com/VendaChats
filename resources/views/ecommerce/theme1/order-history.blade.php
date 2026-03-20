@extends('ecommerce.theme1.master')

@section('title', 'Order History | Electro')

@section('ecom-master')
    @php
        $orders = [
            ['id' => '#ELX-20489', 'date' => '19 Mar 2026', 'items' => '2 items', 'total' => '$1,458.00', 'status' => 'Delivered', 'class' => 'is-success'],
            ['id' => '#ELX-20412', 'date' => '11 Mar 2026', 'items' => '1 item', 'total' => '$249.00', 'status' => 'Shipped', 'class' => 'is-info'],
            ['id' => '#ELX-20344', 'date' => '27 Feb 2026', 'items' => '3 items', 'total' => '$518.00', 'status' => 'Processing', 'class' => 'is-warning'],
            ['id' => '#ELX-20187', 'date' => '14 Feb 2026', 'items' => '1 item', 'total' => '$89.00', 'status' => 'Refunded', 'class' => 'is-danger'],
        ];
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
                            <h3 class="text-primary">12</h3>
                            <p class="mb-0">Total orders</p>
                        </div>
                        <div class="theme-stat-card wow fadeInUp" data-wow-delay="0.2s">
                            <h3 class="text-primary">$4.2k</h3>
                            <p class="mb-0">Lifetime spend</p>
                        </div>
                        <div class="theme-stat-card wow fadeInUp" data-wow-delay="0.3s">
                            <h3 class="text-primary">2</h3>
                            <p class="mb-0">Open orders</p>
                        </div>
                    </div>

                    <div class="theme-order-table-wrap wow fadeInUp" data-wow-delay="0.2s">
                        <div class="table-responsive">
                            <table class="table theme-order-table">
                                <thead>
                                    <tr>
                                        <th>Order</th>
                                        <th>Date</th>
                                        <th>Items</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($orders as $order)
                                        <tr>
                                            <td>{{ $order['id'] }}</td>
                                            <td>{{ $order['date'] }}</td>
                                            <td>{{ $order['items'] }}</td>
                                            <td>{{ $order['total'] }}</td>
                                            <td><span class="theme-status-pill {{ $order['class'] }}">{{ $order['status'] }}</span></td>
                                            <td class="text-end">
                                                <a href="{{ route('ecommerce.track-order') }}" class="btn btn-light border rounded-pill px-3 py-2">View</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
