<div class="container-fluid px-0">
    <div class="row g-0">
        @foreach ($themeServices as $index => $service)
            <div class="col-6 col-md-4 col-lg-2 {{ $index === 0 ? 'border-start' : '' }} border-end wow fadeInUp"
                data-wow-delay="{{ number_format((($index + 1) / 10), 1) }}s">
                <div class="p-4 h-100">
                    <div class="d-flex align-items-start">
                        <i class="{{ $service['icon'] }} fa-2x text-primary"></i>
                        <div class="ms-4">
                            <h6 class="text-uppercase mb-2">{{ $service['title'] }}</h6>
                            <p class="mb-0">{{ $service['description'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
