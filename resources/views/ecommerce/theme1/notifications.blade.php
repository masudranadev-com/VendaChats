@extends('ecommerce.theme1.master')

@section('title', 'Notifications | Electro')

@section('ecom-master')
    @php
        $notifications = [
            ['title' => 'Order out for delivery', 'time' => '10 minutes ago', 'body' => 'Order #ELX-20489 is on the way and expected to arrive tomorrow morning.', 'class' => 'is-unread'],
            ['title' => 'Price drop alert', 'time' => '2 hours ago', 'body' => 'UltraWide Monitor Q27 is now listed below your saved target price.', 'class' => 'is-unread'],
            ['title' => 'Warranty request updated', 'time' => 'Yesterday', 'body' => 'Your recent accessory claim has moved to inspection and review.', 'class' => ''],
        ];
    @endphp

    @include('ecommerce.theme1.partials.page-header', [
        'title' => 'Notifications',
        'subtitle' => 'Stay on top of order progress, price changes, and account activity.',
        'breadcrumbs' => [
            ['label' => 'Home', 'url' => route('ecommerce.index')],
            ['label' => 'Account'],
            ['label' => 'Notifications'],
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
                        <div class="col-lg-7 wow fadeInLeft" data-wow-delay="0.1s">
                            @foreach ($notifications as $notification)
                                <div class="theme-notification-item {{ $notification['class'] }}">
                                    <div class="theme-notification-meta">
                                        <h5 class="mb-0">{{ $notification['title'] }}</h5>
                                        <span class="text-muted">{{ $notification['time'] }}</span>
                                    </div>
                                    <p class="mb-0">{{ $notification['body'] }}</p>
                                </div>
                            @endforeach
                        </div>

                        <div class="col-lg-5 wow fadeInRight" data-wow-delay="0.2s">
                            <div class="theme-panel h-100">
                                <h4 class="mb-3">Notification Preferences</h4>
                                <div class="theme-preferences-list">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="notify-orders" checked>
                                        <label class="form-check-label" for="notify-orders">Order status updates</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="notify-price" checked>
                                        <label class="form-check-label" for="notify-price">Wishlist price drops</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="notify-stock">
                                        <label class="form-check-label" for="notify-stock">Back in stock alerts</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="notify-marketing">
                                        <label class="form-check-label" for="notify-marketing">Promotional campaigns</label>
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
