@extends('ecommerce.theme1.master')

@section('title', 'FAQ | Electro')

@section('ecom-master')
    @php
        $faqItems = [
            ['question' => 'How do I track my order after checkout?', 'answer' => 'Use the Track Your Order page and enter the order number with your phone or email details to see the latest status.'],
            ['question' => 'Can I return a product if I change my mind?', 'answer' => 'Eligible items can usually be returned within the stated return window if they remain in acceptable condition with the original box and accessories.'],
            ['question' => 'Do you offer warranty support on devices?', 'answer' => 'Yes. Coverage depends on the product and brand policy, and the Returns & Warranty page explains the standard claim flow.'],
            ['question' => 'Can I save products for later?', 'answer' => 'Yes. The wishlist page is designed to hold saved products, compare priorities, and move items into the cart later.'],
            ['question' => 'Do gift vouchers expire?', 'answer' => 'Voucher expiration rules depend on the campaign or amount, and those details should be shown on the voucher card before purchase.'],
            ['question' => 'How do I manage notifications?', 'answer' => 'The notifications page can be used to control order updates, promotional messages, wishlist price-drop alerts, and back-in-stock reminders.'],
        ];
    @endphp

    @include('ecommerce.theme1.partials.page-header', [
        'title' => 'FAQ',
        'subtitle' => 'Answers to the most common questions about shopping, delivery, accounts, and support.',
        'breadcrumbs' => [
            ['label' => 'Home', 'url' => route('ecommerce.index')],
            ['label' => 'Support'],
            ['label' => 'FAQ'],
        ],
    ])

    <div class="container-fluid py-5">
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-xl-9 wow fadeInUp" data-wow-delay="0.1s">
                    <div class="accordion theme-accordion" id="storeFaq">
                        @foreach ($faqItems as $item)
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="faq-heading-{{ $loop->iteration }}">
                                    <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#faq-collapse-{{ $loop->iteration }}"
                                        aria-expanded="{{ $loop->first ? 'true' : 'false' }}"
                                        aria-controls="faq-collapse-{{ $loop->iteration }}">
                                        {{ $item['question'] }}
                                    </button>
                                </h2>
                                <div id="faq-collapse-{{ $loop->iteration }}"
                                    class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}"
                                    aria-labelledby="faq-heading-{{ $loop->iteration }}" data-bs-parent="#storeFaq">
                                    <div class="accordion-body">
                                        {{ $item['answer'] }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
