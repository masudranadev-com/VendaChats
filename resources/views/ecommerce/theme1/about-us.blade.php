@extends('ecommerce.theme1.master')

@section('title', 'About Us | Electro')

@section('ecom-master')
    @php
        $stats = [
            ['value' => '15k+', 'label' => 'Orders delivered'],
            ['value' => '98%', 'label' => 'Positive support rating'],
            ['value' => '120+', 'label' => 'Curated tech products'],
            ['value' => '5', 'label' => 'Core product categories'],
        ];

        $values = [
            ['icon' => 'fas fa-check-circle', 'title' => 'Curated only', 'body' => 'We focus on products that are easier to trust, support, and recommend repeatedly.'],
            ['icon' => 'fas fa-bolt', 'title' => 'Fast support', 'body' => 'Order help, warranty guidance, and delivery updates are treated as core storefront features.'],
            ['icon' => 'fas fa-shield-alt', 'title' => 'Clear policies', 'body' => 'We keep pricing, availability, and claim expectations understandable before you buy.'],
        ];
    @endphp

    @include('ecommerce.theme1.partials.page-header', [
        'title' => 'About Us',
        'subtitle' => 'Electro is a curated storefront for everyday tech, practical upgrades, and dependable support.',
        'breadcrumbs' => [
            ['label' => 'Home', 'url' => route('ecommerce.index')],
            ['label' => 'Support'],
            ['label' => 'About Us'],
        ],
    ])

    <div class="container-fluid py-5">
        <div class="container py-5">
            <div class="row g-5 align-items-center">
                <div class="col-lg-6 wow fadeInLeft" data-wow-delay="0.1s">
                    <div class="theme-panel theme-panel-highlight">
                        <span class="theme-kicker">Our Story</span>
                        <h2 class="display-6 mt-4 mb-3">Built for practical tech shopping</h2>
                        <p class="mb-4">Electro brings together mobile devices, accessories, home entertainment, and work-ready electronics in a storefront designed around clarity. Customers should understand what they are buying, what happens after they buy, and how to get help when they need it.</p>
                        <p class="mb-0">That is why product pages, account pages, tracking, and policy pages are treated as one connected experience instead of separate afterthoughts.</p>
                    </div>
                </div>
                <div class="col-lg-6 wow fadeInRight" data-wow-delay="0.2s">
                    <div class="theme-stat-grid">
                        @foreach ($stats as $stat)
                            <div class="theme-stat-card">
                                <h3 class="text-primary">{{ $stat['value'] }}</h3>
                                <p class="mb-0">{{ $stat['label'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="row g-4 mt-1">
                @foreach ($values as $value)
                    <div class="col-lg-4 wow fadeInUp" data-wow-delay="{{ number_format(($loop->index + 1) / 10, 1) }}s">
                        <div class="theme-panel h-100">
                            <span class="theme-icon-badge"><i class="{{ $value['icon'] }}"></i></span>
                            <h4 class="mt-4 mb-3">{{ $value['title'] }}</h4>
                            <p class="mb-0">{{ $value['body'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    @include('ecommerce.theme1.partials.services-strip')
@endsection
