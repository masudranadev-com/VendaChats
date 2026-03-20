@php
    $badge = strtolower($product['badge'] ?? '');
    $delay = $delay ?? '0.1s';
    $rating = (int) ($product['rating'] ?? 0);
@endphp

<div class="product-item rounded wow fadeInUp" data-wow-delay="{{ $delay }}">
    <div class="product-item-inner border rounded">
        <div class="product-item-inner-item">
            <img src="{{ asset('assets/theme1/img/' . $product['image']) }}" class="img-fluid w-100 rounded-top" alt="{{ $product['name'] }}">
            @if ($badge === 'sale')
                <div class="product-sale">{{ $product['badge'] }}</div>
            @elseif ($badge !== '')
                <div class="product-new">{{ $product['badge'] }}</div>
            @endif
            <div class="product-details">
                <a href="{{ route('ecommerce.product.show', ['slug' => $product['slug']]) }}"><i class="fa fa-eye fa-1x"></i></a>
            </div>
        </div>
        <div class="text-center rounded-bottom p-4">
            <a href="{{ route('ecommerce.category.show', ['slug' => $product['category_slug']]) }}" class="d-block mb-2">
                {{ $product['category_name'] }}
            </a>
            <a href="{{ route('ecommerce.product.show', ['slug' => $product['slug']]) }}" class="d-block h4">
                {{ $product['name'] }}
            </a>
            <p class="text-muted mb-2">{{ $product['excerpt'] }}</p>
            <del class="me-2 fs-5">{{ $product['old_price'] }}</del>
            <span class="text-primary fs-5">{{ $product['price'] }}</span>
        </div>
    </div>
    <div class="product-item-add border border-top-0 rounded-bottom text-center p-4 pt-0">
        <a href="{{ route('ecommerce.cart') }}" class="btn btn-primary border-secondary rounded-pill py-2 px-4 mb-4">
            <i class="fas fa-shopping-cart me-2"></i> Add To Cart
        </a>
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex">
                @for ($star = 1; $star <= 5; $star++)
                    <i class="fas fa-star {{ $star <= $rating ? 'text-primary' : '' }}"></i>
                @endfor
            </div>
            <div class="d-flex">
                <a href="{{ route('ecommerce.track-order') }}" class="text-primary d-flex align-items-center justify-content-center me-3">
                    <span class="rounded-circle btn-sm-square border"><i class="fas fa-truck"></i></span>
                </a>
                <a href="{{ route('ecommerce.wishlist') }}" class="text-primary d-flex align-items-center justify-content-center me-0">
                    <span class="rounded-circle btn-sm-square border"><i class="fas fa-heart"></i></span>
                </a>
            </div>
        </div>
    </div>
</div>
