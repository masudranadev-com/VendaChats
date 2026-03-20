@php
    $subtitle = $subtitle ?? null;
    $breadcrumbs = $breadcrumbs ?? [
        ['label' => 'Home', 'url' => route('ecommerce.index')],
    ];
@endphp

<div class="container-fluid page-header py-5">
    <div class="container py-2">
        <h1 class="text-center text-white display-6 wow fadeInUp" data-wow-delay="0.1s">{{ $title }}</h1>
        @if ($subtitle)
            <p class="text-center text-white-50 mb-3 wow fadeInUp" data-wow-delay="0.2s">{{ $subtitle }}</p>
        @endif
        <ol class="breadcrumb justify-content-center mb-0 wow fadeInUp" data-wow-delay="0.3s">
            @foreach ($breadcrumbs as $breadcrumb)
                @if (!empty($breadcrumb['url']))
                    <li class="breadcrumb-item">
                        <a href="{{ $breadcrumb['url'] }}">{{ $breadcrumb['label'] }}</a>
                    </li>
                @else
                    <li class="breadcrumb-item active text-white">{{ $breadcrumb['label'] }}</li>
                @endif
            @endforeach
        </ol>
    </div>
</div>
