@extends('ecommerce.theme1.master')

@section('title', ($selectedTrackingOrder ? 'Order ' . $requestedTrackingOrderId : 'Track Your Order') . ' | Electro')

@section('ecom-master')
    @php
        $orderStages = [
            'waiting_for_call',
            'waiting_for_confirmation',
            'ready_to_dispatch',
            'in_transit',
            'success',
        ];
        $formatStageLabel = static fn(string $stage): string => \Illuminate\Support\Str::headline($stage);
        $stageDescriptions = [
            'waiting_for_call' => 'Your order is awaiting the first confirmation call from our team.',
            'waiting_for_confirmation' => 'Order details are being reviewed and approved for processing.',
            'ready_to_dispatch' => 'Your package is packed and queued for courier handover.',
            'in_transit' => 'The shipment is moving through the delivery network to your address.',
            'success' => 'The order has been delivered successfully and marked as completed.',
        ];

        $currentStageIndex = $selectedTrackingOrder ? array_search($selectedTrackingOrder['status'], $orderStages, true) : false;
        $reachedSteps = $selectedTrackingOrder && $currentStageIndex !== false ? $currentStageIndex + 1 : 0;
        $progressPercent = count($orderStages) > 0 ? (int) round(($reachedSteps / count($orderStages)) * 100) : 0;
    @endphp

    @include('ecommerce.theme1.partials.page-header', [
        'title' => 'Track Your Order',
        'subtitle' => 'Follow the latest delivery updates for your order from one tracking page.',
        'breadcrumbs' => [
            ['label' => 'Home', 'url' => route('ecommerce.index')],
            ['label' => 'Track Your Order'],
            ['label' => $requestedTrackingOrderId],
        ],
    ])

    <div class="container-fluid py-5">
        <div class="container py-5">
            <div class="row g-4">
                <div class="col-xl-4 wow fadeInLeft" data-wow-delay="0.1s">
                    <div class="theme-panel h-100 theme-track-public-summary">
                        @if ($selectedTrackingOrder)
                            <span class="theme-kicker"><i class="fas fa-box"></i> Order Overview</span>
                            <h3 class="mt-4 mb-2">Shipment details</h3>
                            <p class="text-muted mb-4">Review the current delivery stage, shipment information, and latest activity for this order.</p>

                            <div class="theme-order-meta-grid">
                                <div class="theme-order-meta-card">
                                    <small class="theme-order-meta-label">Order ID</small>
                                    <strong>{{ $selectedTrackingOrder['id'] }}</strong>
                                </div>
                                <div class="theme-order-meta-card">
                                    <small class="theme-order-meta-label">Tracking Access</small>
                                    <strong>Available online</strong>
                                </div>
                                <div class="theme-order-meta-card">
                                    <small class="theme-order-meta-label">Current status</small>
                                    <strong>{{ $formatStageLabel($selectedTrackingOrder['status']) }}</strong>
                                </div>
                            </div>

                            <div class="theme-track-public-note mt-4">
                                <h6 class="mb-2">Tracking information</h6>
                                <p class="mb-0 text-muted">Completed steps are finished, the highlighted step is currently running, and pending steps will update automatically as delivery moves forward.</p>
                            </div>
                        @else
                            <span class="theme-kicker"><i class="fas fa-exclamation-circle"></i> Tracking Unavailable</span>
                            <h3 class="mt-4 mb-2">We couldn't load this order</h3>
                            <p class="text-muted mb-4">The tracking link may be incomplete or the order reference may no longer be available.</p>

                            <div class="theme-steps">
                                <div class="theme-step-item">
                                    <span class="theme-step-number">1</span>
                                    <div>
                                        <h5 class="mb-1">Review the link</h5>
                                        <p class="mb-0 text-muted">Make sure the tracking URL matches the order reference you received.</p>
                                    </div>
                                </div>
                                <div class="theme-step-item">
                                    <span class="theme-step-number">2</span>
                                    <div>
                                        <h5 class="mb-1">Try the latest update</h5>
                                        <p class="mb-0 text-muted">Open the most recent tracking message or order confirmation link shared with you.</p>
                                    </div>
                                </div>
                                <div class="theme-step-item">
                                    <span class="theme-step-number">3</span>
                                    <div>
                                        <h5 class="mb-1">Contact support</h5>
                                        <p class="mb-0 text-muted">If the page still does not load, contact support with your order ID for assistance.</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="col-xl-8 wow fadeInRight" data-wow-delay="0.2s">
                    <div class="theme-panel h-100 theme-track-result-card">
                        @if ($selectedTrackingOrder)
                            <div class="theme-public-track-hero mb-4">
                                <div>
                                    <span class="theme-kicker"><i class="fas fa-shipping-fast"></i> Shipment Status</span>
                                    <h3 class="mt-3 mb-1">Order {{ $selectedTrackingOrder['id'] }}</h3>
                                    <p class="mb-0 text-muted">{{ $selectedTrackingOrder['items'] }}</p>
                                </div>
                                <span class="theme-status-pill {{ $selectedTrackingOrder['status_class'] }}">{{ $formatStageLabel($selectedTrackingOrder['status']) }}</span>
                            </div>

                            <p class="mb-4 text-muted">{{ $selectedTrackingOrder['summary'] }}</p>

                            <div class="theme-track-progress-shell mb-4">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                                    <strong>Total order progress</strong>
                                    <span class="text-muted">{{ $reachedSteps }} of {{ count($orderStages) }} steps completed</span>
                                </div>
                                <div class="theme-track-progress-bar">
                                    <span style="width: {{ $progressPercent }}%;"></span>
                                </div>
                            </div>

                            <div class="theme-track-stage-grid mb-4">
                                @foreach ($orderStages as $stage)
                                    @php
                                        $stageIndex = array_search($stage, $orderStages, true);
                                        $stageClass = ' is-pending';
                                        $stageStateLabel = 'Pending';
                                        $stageIcon = 'fas fa-lock';

                                        if ($currentStageIndex !== false && $stageIndex < $currentStageIndex) {
                                            $stageClass = ' is-complete';
                                            $stageStateLabel = 'Completed';
                                            $stageIcon = 'fas fa-check';
                                        } elseif ($currentStageIndex !== false && $stageIndex === $currentStageIndex) {
                                            $stageClass = ' is-current';
                                            $stageStateLabel = 'Running';
                                            $stageIcon = 'fas fa-sync-alt';
                                        }
                                    @endphp
                                    <div class="theme-track-stage{{ $stageClass }}">
                                        <div class="theme-track-stage-head">
                                            <span class="theme-track-stage-step">Step {{ $loop->iteration }}</span>
                                            <span class="theme-track-stage-icon" aria-hidden="true">
                                                <i class="{{ $stageIcon }}"></i>
                                            </span>
                                        </div>
                                        <strong>{{ $formatStageLabel($stage) }}</strong>
                                        <span class="theme-track-stage-status">{{ $stageStateLabel }}</span>
                                        <p class="theme-track-stage-copy mb-0">{{ $stageDescriptions[$stage] }}</p>
                                    </div>
                                @endforeach
                            </div>

                            <div class="theme-order-meta-grid mb-4">
                                <div class="theme-order-meta-card">
                                    <small class="theme-order-meta-label">Estimated arrival</small>
                                    <strong>{{ $selectedTrackingOrder['eta'] }}</strong>
                                </div>
                                <div class="theme-order-meta-card">
                                    <small class="theme-order-meta-label">Courier</small>
                                    <strong>{{ $selectedTrackingOrder['courier'] }}</strong>
                                </div>
                                <div class="theme-order-meta-card">
                                    <small class="theme-order-meta-label">Shipment ID</small>
                                    <strong>{{ $selectedTrackingOrder['shipment'] }}</strong>
                                </div>
                                <div class="theme-order-meta-card">
                                    <small class="theme-order-meta-label">Last update</small>
                                    <strong>{{ $selectedTrackingOrder['last_update'] }}</strong>
                                </div>
                                <div class="theme-order-meta-card">
                                    <small class="theme-order-meta-label">Delivery address</small>
                                    <strong>{{ $selectedTrackingOrder['address'] }}</strong>
                                </div>
                            </div>

                            <div class="theme-timeline">
                                @foreach ($selectedTrackingOrder['timeline'] as $item)
                                    <div class="theme-timeline-item is-{{ $item['state'] }}">
                                        <div class="d-flex justify-content-between flex-wrap gap-2">
                                            <h5 class="mb-1">{{ $formatStageLabel($item['title']) }}</h5>
                                            <small class="text-muted">{{ $item['time'] }}</small>
                                        </div>
                                        <p class="mb-0">{{ $item['body'] }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="theme-track-state theme-track-error">
                                <span class="theme-kicker"><i class="fas fa-search"></i> Order Not Found</span>
                                <h3 class="mt-4 mb-3">Tracking details are unavailable</h3>
                                <p class="mb-4 text-muted">We couldn't find tracking information for order {{ $requestedTrackingOrderId }}. Please check the link or contact support for help.</p>
                                <div class="d-flex flex-wrap gap-3">
                                    <a href="{{ route('ecommerce.index') }}" class="btn btn-primary rounded-pill px-4 py-2">Back to Home</a>
                                    <a href="{{ route('ecommerce.contact') }}" class="btn btn-light border rounded-pill px-4 py-2">Contact Support</a>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="col-12 wow fadeInUp" data-wow-delay="0.3s">
                    <div class="theme-panel theme-panel-highlight">
                        <div class="row g-4 align-items-center">
                            <div class="col-lg-8">
                                <h4 class="mb-2">Need help with your shipment?</h4>
                                <p class="mb-0">If your delivery is delayed, the address needs to be updated, or the courier cannot reach you, contact support with your order ID.</p>
                            </div>
                            <div class="col-lg-4 text-lg-end">
                                <a href="{{ route('ecommerce.contact') }}" class="btn btn-primary rounded-pill px-5 py-3">Contact Support</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
