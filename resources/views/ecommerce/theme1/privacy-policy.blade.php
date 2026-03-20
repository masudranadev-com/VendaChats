@extends('ecommerce.theme1.master')

@section('title', 'Privacy Policy | Electro')

@section('ecom-master')
    @php
        $privacyBlocks = [
            [
                'title' => 'Information we collect',
                'body' => 'We collect the details required to process purchases, manage shipping, communicate order updates, and provide support for claims or questions.',
            ],
            [
                'title' => 'How we use it',
                'body' => 'Your information helps us fulfill orders, prevent fraud, improve product recommendations, and send essential account notifications.',
            ],
            [
                'title' => 'Sharing and partners',
                'body' => 'We only share the minimum required information with delivery, payment, and operational partners that support the storefront experience.',
            ],
            [
                'title' => 'Your choices',
                'body' => 'You can request updates to your profile, adjust marketing preferences, or contact support about data-related questions tied to your orders.',
            ],
        ];
    @endphp

    @include('ecommerce.theme1.partials.page-header', [
        'title' => 'Privacy Policy',
        'subtitle' => 'A summary of how customer information is collected, used, and protected within the storefront.',
        'breadcrumbs' => [
            ['label' => 'Home', 'url' => route('ecommerce.index')],
            ['label' => 'Support'],
            ['label' => 'Privacy Policy'],
        ],
    ])

    <div class="container-fluid py-5">
        <div class="container py-5">
            <div class="row g-4">
                @foreach ($privacyBlocks as $block)
                    <div class="col-lg-6 wow fadeInUp" data-wow-delay="{{ number_format(($loop->index + 1) / 10, 1) }}s">
                        <div class="theme-panel h-100">
                            <h4 class="mb-3">{{ $block['title'] }}</h4>
                            <p class="mb-0">{{ $block['body'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="row g-4 mt-1">
                <div class="col-lg-8 wow fadeInLeft" data-wow-delay="0.2s">
                    <div class="theme-panel h-100">
                        <h4 class="mb-3">Retention and security</h4>
                        <p class="mb-3">Order and account records are retained for support, warranty, and compliance needs. Sensitive access is restricted to authorized workflows and partner systems involved in order fulfillment.</p>
                        <p class="mb-0">If you need assistance with account preferences or communication settings, use your account notifications page or contact support directly.</p>
                    </div>
                </div>
                <div class="col-lg-4 wow fadeInRight" data-wow-delay="0.3s">
                    <div class="theme-panel theme-panel-highlight h-100">
                        <span class="theme-kicker">Privacy Control</span>
                        <h4 class="mt-4 mb-2">Need a data-related update?</h4>
                        <p class="mb-4">For account edits, marketing preferences, or deletion-related questions, our support team can guide the next step.</p>
                        <a href="{{ route('ecommerce.contact') }}" class="btn btn-primary rounded-pill px-4 py-2">Contact Support</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
