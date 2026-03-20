@extends('ecommerce.theme1.master')

@section('title', 'Notifications | Electro')

@section('ecom-master')
    @php
        $notifications = [
            ['title' => 'Order out for delivery', 'time' => '10 minutes ago', 'body' => 'Order #ELX-20489 is on the way and expected to arrive tomorrow morning.', 'class' => 'is-unread'],
            ['title' => 'Price drop alert', 'time' => '2 hours ago', 'body' => 'UltraWide Monitor Q27 is now listed below your saved target price.', 'class' => 'is-unread'],
            ['title' => 'Payment confirmed', 'time' => '5 hours ago', 'body' => 'Your payment for order #ELX-20412 has been confirmed successfully.', 'class' => ''],
            ['title' => 'Warranty request updated', 'time' => 'Yesterday', 'body' => 'Your recent accessory claim has moved to inspection and review.', 'class' => ''],
            ['title' => 'Wishlist item back in stock', 'time' => '2 days ago', 'body' => 'Nova Cam 4K is available again and ready to order from your wishlist.', 'class' => ''],
            ['title' => 'Refund completed', 'time' => '3 days ago', 'body' => 'The refund for order #ELX-20187 has been returned to your original payment method.', 'class' => ''],
            ['title' => 'New member offer', 'time' => '5 days ago', 'body' => 'You unlocked a limited member discount on selected accessories and audio gear.', 'class' => ''],
        ];

        $perPage = 3;
        $totalNotifications = count($notifications);
        $lastPage = max(1, (int) ceil($totalNotifications / $perPage));
        $currentPage = max(1, min((int) request('page', 1), $lastPage));
        $currentOffset = ($currentPage - 1) * $perPage;
        $visibleNotifications = array_slice($notifications, $currentOffset, $perPage);
        $fromNotification = $totalNotifications === 0 ? 0 : $currentOffset + 1;
        $toNotification = min($currentOffset + $perPage, $totalNotifications);
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
                    <div class="theme-panel mb-4 wow fadeInUp" data-wow-delay="0.1s">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                            <div>
                                <h3 class="mb-1">Recent Activity</h3>
                                <p class="mb-0 text-muted">Keep track of account alerts, order progress, and product updates.</p>
                            </div>
                            <div class="text-lg-end">
                                <p class="mb-2 text-muted">Showing {{ $fromNotification }}-{{ $toNotification }} of {{ $totalNotifications }} notifications</p>
                                <a href="{{ route('ecommerce.shop') }}" class="btn btn-secondary rounded-pill px-4 py-2">Continue Shopping</a>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4">
                        <div class="col-12 wow fadeInLeft" data-wow-delay="0.1s">
                            @foreach ($visibleNotifications as $notification)
                                <div class="theme-notification-item {{ $notification['class'] }}">
                                    <div class="theme-notification-meta">
                                        <h5 class="mb-0">{{ $notification['title'] }}</h5>
                                        <span class="text-muted">{{ $notification['time'] }}</span>
                                    </div>
                                    <p class="mb-0">{{ $notification['body'] }}</p>
                                </div>
                            @endforeach
                        </div>
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
