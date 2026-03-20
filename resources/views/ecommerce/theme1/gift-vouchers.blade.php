@extends('ecommerce.theme1.master')

@section('title', 'Gift Vouchers | Electro')

@section('ecom-master')
    @php
        $voucherOptions = [
            ['amount' => '$25', 'title' => 'Starter Gift', 'body' => 'Good for accessories, cables, chargers, and small upgrades.'],
            ['amount' => '$100', 'title' => 'Popular Pick', 'body' => 'A flexible gift option for headphones, wearables, and streaming gear.'],
            ['amount' => '$250', 'title' => 'Big Upgrade', 'body' => 'Best for tablets, monitors, premium accessories, and multi-item gifting.'],
        ];

        $steps = [
            'Choose the voucher amount that matches your budget or occasion.',
            'Add a recipient name and optional delivery note before checkout.',
            'The voucher code can be redeemed during checkout on eligible purchases.',
        ];
    @endphp

    @include('ecommerce.theme1.partials.page-header', [
        'title' => 'Gift Vouchers',
        'subtitle' => 'A fast way to gift choice without guessing the exact model, size, or color.',
        'breadcrumbs' => [
            ['label' => 'Home', 'url' => route('ecommerce.index')],
            ['label' => 'Pages'],
            ['label' => 'Gift Vouchers'],
        ],
    ])

    <div class="container-fluid py-5">
        <div class="container py-5">
            <div class="row g-4">
                @foreach ($voucherOptions as $voucher)
                    <div class="col-lg-4 wow fadeInUp" data-wow-delay="{{ number_format(($loop->index + 1) / 10, 1) }}s">
                        <div class="theme-voucher-card h-100">
                            <span class="theme-kicker">Gift Card</span>
                            <h2 class="display-5 text-primary mt-4 mb-2">{{ $voucher['amount'] }}</h2>
                            <h4 class="mb-3">{{ $voucher['title'] }}</h4>
                            <p class="mb-4">{{ $voucher['body'] }}</p>
                            <a href="{{ route('ecommerce.cheackout') }}" class="btn btn-primary rounded-pill px-4 py-2">Choose Voucher</a>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="row g-5 mt-1">
                <div class="col-lg-7 wow fadeInLeft" data-wow-delay="0.2s">
                    <div class="theme-panel">
                        <span class="theme-kicker">Redeem Flow</span>
                        <h3 class="mt-4 mb-4">How voucher gifting works</h3>
                        <div class="theme-steps">
                            @foreach ($steps as $step)
                                <div class="theme-step-item">
                                    <span class="theme-step-number">{{ $loop->iteration }}</span>
                                    <p class="mb-0">{{ $step }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="col-lg-5 wow fadeInRight" data-wow-delay="0.3s">
                    <div class="theme-panel theme-panel-highlight h-100">
                        <span class="theme-kicker">Corporate Gifting</span>
                        <h4 class="mt-4 mb-3">Need bulk vouchers?</h4>
                        <p class="mb-4">For teams, partners, or campaign rewards, gift vouchers can be issued in controlled amounts with a custom note and validity window.</p>
                        <a href="{{ route('ecommerce.contact') }}" class="btn btn-primary rounded-pill px-4 py-2">Talk to Sales</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
