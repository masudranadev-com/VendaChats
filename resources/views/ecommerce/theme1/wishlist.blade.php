@extends('ecommerce.theme1.master')

@section('title', 'Wishlist | Electro')

@section('ecom-master')
    @php
        $savedProducts = $themeProducts;
        $perPage = 4;
        $totalSavedProducts = count($savedProducts);
        $lastPage = max(1, (int) ceil($totalSavedProducts / $perPage));
        $currentPage = max(1, min((int) request('page', 1), $lastPage));
        $currentOffset = ($currentPage - 1) * $perPage;
        $visibleSavedProducts = array_slice($savedProducts, $currentOffset, $perPage);
        $fromProduct = $totalSavedProducts === 0 ? 0 : $currentOffset + 1;
        $toProduct = min($currentOffset + $perPage, $totalSavedProducts);
    @endphp

    @include('ecommerce.theme1.partials.page-header', [
        'title' => 'Wishlist',
        'subtitle' => 'Saved products, quick comparisons, and easy move-to-cart actions for later.',
        'breadcrumbs' => [
            ['label' => 'Home', 'url' => route('ecommerce.index')],
            ['label' => 'Account'],
            ['label' => 'Wishlist'],
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
                                <h3 class="mb-1">Saved For Later</h3>
                                <p class="mb-0 text-muted">Use this list to shortlist products before moving them into your cart.</p>
                            </div>
                            <div class="text-lg-end">
                                <p class="mb-2 text-muted">Showing {{ $fromProduct }}-{{ $toProduct }} of {{ $totalSavedProducts }} saved items</p>
                                <a href="{{ route('ecommerce.shop') }}" class="btn btn-secondary rounded-pill px-4 py-2">Continue Shopping</a>
                            </div>
                        </div>
                    </div>

                    @foreach ($visibleSavedProducts as $product)
                        <div class="theme-saved-item wow fadeInUp" data-wow-delay="{{ number_format(($loop->index + 1) / 10, 1) }}s">
                            <div class="theme-saved-thumb">
                                <img src="{{ asset('assets/theme1/img/' . $product['image']) }}" class="img-fluid" alt="{{ $product['name'] }}">
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between gap-3 flex-wrap">
                                    <div>
                                        <p class="text-primary mb-1">{{ $product['category_name'] }}</p>
                                        <h4 class="mb-2">{{ $product['name'] }}</h4>
                                        <p class="text-muted mb-2">{{ $product['excerpt'] }}</p>
                                        <span class="theme-status-pill {{ str_contains($product['stock'], 'Limited') ? 'is-warning' : 'is-success' }}">
                                            {{ $product['stock'] }}
                                        </span>
                                    </div>
                                    <div class="text-lg-end">
                                        <del class="d-block text-muted">{{ $product['old_price'] }}</del>
                                        <span class="fs-4 text-primary fw-bold">{{ $product['price'] }}</span>
                                    </div>
                                </div>
                                <div class="d-flex flex-wrap gap-3 mt-4">
                                    <a href="{{ route('ecommerce.cart') }}" class="btn btn-primary rounded-pill px-4 py-2">Add To Cart</a>
                                    <a href="{{ route('ecommerce.product.show', ['slug' => $product['slug']]) }}" class="btn btn-light border rounded-pill px-4 py-2">View Product</a>
                                    <button type="button" class="btn btn-light border rounded-pill px-4 py-2">Remove</button>
                                </div>
                            </div>
                        </div>
                    @endforeach

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
