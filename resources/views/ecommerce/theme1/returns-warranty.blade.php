@extends('ecommerce.theme1.master')

@section('title', 'Returns & Warranty | Electro')

@section('ecom-master')
    @php
        $highlights = [
            ['value' => '7 Days', 'label' => 'Return window for eligible items'],
            ['value' => '12 Months', 'label' => 'Standard hardware warranty support'],
            ['value' => '48 Hours', 'label' => 'Initial claim review target'],
            ['value' => 'Pickup', 'label' => 'Courier-assisted return collection'],
        ];

        $steps = [
            ['title' => 'Start your request', 'body' => 'Submit the order number, item details, and the reason for return or warranty service.'],
            ['title' => 'Get approval and packing instructions', 'body' => 'Our support team confirms eligibility, pickup options, and accessory requirements.'],
            ['title' => 'Inspection and resolution', 'body' => 'Approved products are repaired, replaced, or refunded depending on stock and claim type.'],
        ];

        $coverage = [
            'Manufacturer defects, dead-on-arrival issues, and verified hardware faults.',
            'Warranty assistance for official products purchased through the Electro storefront.',
            'Exchange support for wrong items, transit damage, or incomplete deliveries.',
        ];

        $exclusions = [
            'Physical damage caused after delivery, liquid exposure, or unauthorized repair.',
            'Missing retail box items, free gifts, or broken security seals where required.',
            'Software issues caused by unofficial flashing, rooting, or unsupported modification.',
        ];
    @endphp

    @include('ecommerce.theme1.partials.page-header', [
        'title' => 'Returns & Warranty',
        'subtitle' => 'Clear policies, fast claim handling, and guided support from request to resolution.',
        'breadcrumbs' => [
            ['label' => 'Home', 'url' => route('ecommerce.index')],
            ['label' => 'Support'],
            ['label' => 'Returns & Warranty'],
        ],
    ])

    <div class="container-fluid py-5">
        <div class="container py-5">
            <div class="theme-stat-grid mb-5">
                @foreach ($highlights as $item)
                    <div class="theme-stat-card wow fadeInUp" data-wow-delay="{{ number_format(($loop->index + 1) / 10, 1) }}s">
                        <h3 class="text-primary">{{ $item['value'] }}</h3>
                        <p class="mb-0">{{ $item['label'] }}</p>
                    </div>
                @endforeach
            </div>

            <div class="row g-5">
                <div class="col-lg-7 wow fadeInLeft" data-wow-delay="0.1s">
                    <div class="theme-panel h-100">
                        <span class="theme-kicker">How It Works</span>
                        <h3 class="mt-4 mb-4">Simple claim steps</h3>
                        <div class="theme-steps">
                            @foreach ($steps as $step)
                                <div class="theme-step-item">
                                    <span class="theme-step-number">{{ $loop->iteration }}</span>
                                    <div>
                                        <h5 class="mb-2">{{ $step['title'] }}</h5>
                                        <p class="mb-0">{{ $step['body'] }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="col-lg-5 wow fadeInRight" data-wow-delay="0.2s">
                    <div class="theme-panel h-100">
                        <span class="theme-kicker">Policy Snapshot</span>
                        <h4 class="mt-4 mb-3">What is covered</h4>
                        <ul class="theme-bullet-list mb-4">
                            @foreach ($coverage as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ul>

                        <h4 class="mb-3">What is not covered</h4>
                        <ul class="theme-bullet-list">
                            @foreach ($exclusions as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <div class="col-12 wow fadeInUp" data-wow-delay="0.3s">
                    <div class="theme-panel theme-panel-highlight">
                        <div class="row g-4 align-items-center">
                            <div class="col-lg-8">
                                <h3 class="mb-2">Need to start a claim?</h3>
                                <p class="mb-0">Use the track order page to locate your purchase first, then contact support with photos, serial number, and your issue summary.</p>
                            </div>
                            <div class="col-lg-4 text-lg-end">
                                <a href="{{ route('ecommerce.track-order') }}" class="btn btn-primary rounded-pill px-5 py-3">Track Your Order</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('ecommerce.theme1.partials.services-strip')
@endsection
