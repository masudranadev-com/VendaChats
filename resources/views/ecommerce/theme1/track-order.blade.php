@extends('ecommerce.theme1.master')

@section('title', 'Track Your Order | Electro')

@section('ecom-master')
    @php
        $timeline = [
            ['title' => 'Order placed', 'body' => 'Your order was received and payment verification completed.', 'time' => '19 Mar 2026, 10:14 AM', 'class' => 'is-complete'],
            ['title' => 'Packed at warehouse', 'body' => 'Items were quality checked, packed, and assigned to the courier team.', 'time' => '19 Mar 2026, 03:10 PM', 'class' => 'is-complete'],
            ['title' => 'Out for delivery', 'body' => 'The package is currently with the courier and heading to the delivery address.', 'time' => '20 Mar 2026, 09:00 AM', 'class' => ''],
            ['title' => 'Delivered', 'body' => 'Delivery will be marked complete after handoff confirmation.', 'time' => 'Pending', 'class' => ''],
        ];
    @endphp

    @include('ecommerce.theme1.partials.page-header', [
        'title' => 'Track Your Order',
        'subtitle' => 'Look up the current stage of a purchase with order and contact details.',
        'breadcrumbs' => [
            ['label' => 'Home', 'url' => route('ecommerce.index')],
            ['label' => 'Account'],
            ['label' => 'Track Your Order'],
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
                        <div class="col-lg-5 wow fadeInLeft" data-wow-delay="0.1s">
                            <div class="theme-panel h-100">
                                <span class="theme-kicker">Order Lookup</span>
                                <h3 class="mt-4 mb-3">Track shipment progress</h3>
                                <form class="row g-3 theme-order-search">
                                    <div class="col-12">
                                        <label class="form-label">Order number</label>
                                        <input type="text" class="form-control" value="#ELX-20489">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Email or phone</label>
                                        <input type="text" class="form-control" value="emma@example.com">
                                    </div>
                                    <div class="col-12">
                                        <button type="button" class="btn btn-primary rounded-pill px-4 py-3">Track Order</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="col-lg-7 wow fadeInRight" data-wow-delay="0.2s">
                            <div class="theme-panel h-100">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                                    <div>
                                        <h3 class="mb-1">Order #ELX-20489</h3>
                                        <p class="mb-0 text-muted">Vortex ProBook 15 and Orbit Earbuds X</p>
                                    </div>
                                    <span class="theme-status-pill is-info">Expected tomorrow</span>
                                </div>

                                <div class="theme-timeline">
                                    @foreach ($timeline as $item)
                                        <div class="theme-timeline-item {{ $item['class'] }}">
                                            <div class="d-flex justify-content-between flex-wrap gap-2">
                                                <h5 class="mb-1">{{ $item['title'] }}</h5>
                                                <small class="text-muted">{{ $item['time'] }}</small>
                                            </div>
                                            <p class="mb-0">{{ $item['body'] }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="col-12 wow fadeInUp" data-wow-delay="0.3s">
                            <div class="theme-panel theme-panel-highlight">
                                <div class="row g-4 align-items-center">
                                    <div class="col-lg-8">
                                        <h4 class="mb-2">Delivery problem or delay?</h4>
                                        <p class="mb-0">If the courier cannot reach you, or you need to update the destination, contact support with the order number and new instructions.</p>
                                    </div>
                                    <div class="col-lg-4 text-lg-end">
                                        <a href="{{ route('ecommerce.contact') }}" class="btn btn-primary rounded-pill px-5 py-3">Contact Support</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
