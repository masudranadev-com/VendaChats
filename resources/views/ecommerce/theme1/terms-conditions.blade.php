@extends('ecommerce.theme1.master')

@section('title', 'Terms & Conditions | Electro')

@section('ecom-master')
    @php
        $sections = [
            ['title' => 'Orders and pricing', 'body' => 'All orders are subject to stock confirmation, price validation, and payment review. Promotional pricing may change without prior notice.'],
            ['title' => 'Product information', 'body' => 'We try to present accurate specifications, colors, and packaging details, but minor manufacturer changes may occur between batches.'],
            ['title' => 'Account responsibilities', 'body' => 'Customers are responsible for keeping account credentials safe and for reviewing order details before completing a purchase.'],
            ['title' => 'Shipping and delivery', 'body' => 'Delivery timelines are estimates. Electro is not responsible for delays caused by courier issues, weather events, or incomplete address information.'],
            ['title' => 'Returns and warranties', 'body' => 'Return and warranty decisions follow the dedicated returns policy, including inspection requirements and condition checks.'],
            ['title' => 'Store content and usage', 'body' => 'Site content, imagery, and brand assets remain protected. Misuse, scraping, or abusive activity may result in access restrictions.'],
        ];
    @endphp

    @include('ecommerce.theme1.partials.page-header', [
        'title' => 'Terms & Conditions',
        'subtitle' => 'The operating terms for browsing, purchasing, and using this storefront.',
        'breadcrumbs' => [
            ['label' => 'Home', 'url' => route('ecommerce.index')],
            ['label' => 'Support'],
            ['label' => 'Terms & Conditions'],
        ],
    ])

    <div class="container-fluid py-5">
        <div class="container py-5">
            <div class="row g-4">
                @foreach ($sections as $section)
                    <div class="col-lg-6 wow fadeInUp" data-wow-delay="{{ number_format(($loop->index + 1) / 10, 1) }}s">
                        <div class="theme-panel h-100">
                            <h4 class="mb-3">{{ $section['title'] }}</h4>
                            <p class="mb-0">{{ $section['body'] }}</p>
                        </div>
                    </div>
                @endforeach

                <div class="col-12 wow fadeInUp" data-wow-delay="0.4s">
                    <div class="theme-panel">
                        <h4 class="mb-3">Questions about store terms?</h4>
                        <p class="mb-0">If you need clarification on order acceptance, cancellations, or store policies, please contact our support team before placing the order.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
