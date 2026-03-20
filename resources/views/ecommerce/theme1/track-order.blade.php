@extends('ecommerce.theme1.master')

@section('title', 'Track Your Order | Electro')

@section('ecom-master')
    @php
        $formatStageLabel = static fn(string $stage): string => \Illuminate\Support\Str::headline($stage);
    @endphp

    @include('ecommerce.theme1.partials.page-header', [
        'title' => 'Track Your Order',
        'subtitle' => 'Look up the current stage of a purchase using only your order ID.',
        'breadcrumbs' => [
            ['label' => 'Home', 'url' => route('ecommerce.index')],
            ['label' => 'Account'],
            ['label' => 'Track Your Order'],
        ],
    ])

    <div class="container-fluid py-5" data-track-order-app>
        <div class="container py-5">
            <div class="row g-5">
                <div class="col-lg-4 col-xl-3">
                    @include('ecommerce.theme1.partials.account-sidebar')
                </div>

                <div class="col-lg-8 col-xl-9">
                    <div class="row g-4">
                        <div class="col-lg-5 wow fadeInLeft" data-wow-delay="0.1s">
                            <div class="theme-panel h-100 theme-track-search-card">
                                <span class="theme-kicker">Order Lookup</span>
                                <h3 class="mt-4 mb-2">Track shipment progress</h3>
                                <p class="text-muted mb-4">Enter your order ID and press the button to view the latest delivery updates.</p>

                                <form class="row g-3 theme-order-search" data-track-order-form novalidate>
                                    <div class="col-12">
                                        <label for="trackOrderNumber" class="form-label">Order number</label>
                                        <input id="trackOrderNumber" type="text" class="form-control theme-form-control"
                                            placeholder="#ELX-20489" value="{{ request('order') }}" data-track-order-number>
                                    </div>
                                    <div class="col-12">
                                        <p class="small text-danger mb-0" data-track-order-feedback hidden></p>
                                    </div>
                                    <div class="col-12 d-grid">
                                        <button type="submit" class="btn btn-primary rounded-pill px-4 py-3"
                                            data-track-order-submit>
                                            <span data-track-order-button-label>Track Order</span>
                                        </button>
                                    </div>
                                </form>

                                <div class="theme-auth-divider my-4">Try Demo Orders</div>
                                <div class="theme-track-demo-grid">
                                    @foreach ($trackingOrders as $order)
                                        <button type="button" class="theme-track-demo-btn"
                                            data-demo-order="{{ $order['id'] }}">
                                            <strong>{{ $order['id'] }}</strong>
                                            <span>{{ $formatStageLabel($order['status']) }} | {{ $order['items'] }}</span>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-7 wow fadeInRight" data-wow-delay="0.2s">
                            <div class="theme-panel h-100 theme-track-result-card">
                                <div class="theme-track-state" data-track-state-idle>
                                    <span class="theme-kicker"><i class="fas fa-route"></i> Stage Tracking</span>
                                    <h3 class="mt-4 mb-3">Check the total order progress</h3>
                                    <p class="text-muted mb-4">Each delivery step is shown as Completed, Running, or Pending so the current status is clear at a glance.</p>

                                    <div class="theme-steps">
                                        <div class="theme-step-item">
                                            <span class="theme-step-number">1</span>
                                            <div>
                                                <h5 class="mb-1">Enter your order ID</h5>
                                                <p class="mb-0 text-muted">Provide the order number shown after checkout or in your order history.</p>
                                            </div>
                                        </div>
                                        <div class="theme-step-item">
                                            <span class="theme-step-number">2</span>
                                            <div>
                                                <h5 class="mb-1">Click Track Order</h5>
                                                <p class="mb-0 text-muted">The page will switch from the default state into a live tracking result panel.</p>
                                            </div>
                                        </div>
                                        <div class="theme-step-item">
                                            <span class="theme-step-number">3</span>
                                            <div>
                                                <h5 class="mb-1">Review all 5 stages</h5>
                                                <p class="mb-0 text-muted">You will see which steps are completed, which step is currently running, and which steps are still pending.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="theme-track-state theme-track-loading" data-track-state-loading hidden>
                                    <span class="theme-icon-badge"><i class="fas fa-sync-alt fa-spin"></i></span>
                                    <h3 class="mt-4 mb-2">Checking your order</h3>
                                    <p class="mb-0 text-muted">We are fetching the latest stage updates and delivery milestones.</p>
                                </div>

                                <div class="theme-track-state" data-track-state-success hidden>
                                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
                                        <div>
                                            <h3 class="mb-1" data-track-order-id></h3>
                                            <p class="mb-0 text-muted" data-track-order-items></p>
                                        </div>
                                        <span class="theme-status-pill" data-track-order-status></span>
                                    </div>

                                    <p class="mb-4 text-muted" data-track-order-summary></p>

                                    <div class="theme-track-progress-shell mb-4">
                                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                                            <strong>Total order progress</strong>
                                            <span class="text-muted" data-track-progress-label></span>
                                        </div>
                                        <div class="theme-track-progress-bar">
                                            <span data-track-progress-bar></span>
                                        </div>
                                    </div>

                                    <div class="theme-track-stage-grid mb-4" data-track-stage-grid></div>

                                    <div class="theme-order-meta-grid mb-4">
                                        <div class="theme-order-meta-card">
                                            <small class="theme-order-meta-label">Estimated arrival</small>
                                            <strong data-track-order-eta></strong>
                                        </div>
                                        <div class="theme-order-meta-card">
                                            <small class="theme-order-meta-label">Courier</small>
                                            <strong data-track-order-courier></strong>
                                        </div>
                                        <div class="theme-order-meta-card">
                                            <small class="theme-order-meta-label">Shipment ID</small>
                                            <strong data-track-order-shipment></strong>
                                        </div>
                                        <div class="theme-order-meta-card">
                                            <small class="theme-order-meta-label">Last update</small>
                                            <strong data-track-order-last-update></strong>
                                        </div>
                                        <div class="theme-order-meta-card">
                                            <small class="theme-order-meta-label">Delivery address</small>
                                            <strong data-track-order-address></strong>
                                        </div>
                                    </div>

                                    <div class="theme-timeline" data-track-order-timeline></div>
                                </div>

                                <div class="theme-track-state theme-track-error" data-track-state-error hidden>
                                    <span class="theme-kicker"><i class="fas fa-search"></i> Not Found</span>
                                    <h3 class="mt-4 mb-3">We could not find that order</h3>
                                    <p class="mb-0 text-muted" data-track-order-error>Check the order ID and try again.</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 wow fadeInUp" data-wow-delay="0.3s">
                            <div class="theme-panel theme-panel-highlight">
                                <div class="row g-4 align-items-center">
                                    <div class="col-lg-8">
                                        <h4 class="mb-2">Delivery problem or delay?</h4>
                                        <p class="mb-0">If the courier cannot reach you, or you need to update the destination, contact support with the order number and new instructions.</p>
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
        </div>
    </div>

    <script id="themeTrackOrderData" type="application/json">
        {!! json_encode($trackingOrders, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}
    </script>
    <script>
        (function() {
            const app = document.querySelector('[data-track-order-app]');
            const seed = document.getElementById('themeTrackOrderData');

            if (!app || !seed) {
                return;
            }

            let orders = [];

            try {
                orders = JSON.parse(seed.textContent || '[]');
            } catch (error) {
                orders = [];
            }

            const orderStages = [
                'waiting_for_call',
                'waiting_for_confirmation',
                'ready_to_dispatch',
                'in_transit',
                'success'
            ];
            const stageDescriptions = {
                waiting_for_call: 'Your order is awaiting the first confirmation call from our team.',
                waiting_for_confirmation: 'Order details are being reviewed and approved for processing.',
                ready_to_dispatch: 'Your package is packed and queued for courier handover.',
                in_transit: 'The shipment is moving through the delivery network to your address.',
                success: 'The order has been delivered successfully and marked as completed.'
            };

            const form = app.querySelector('[data-track-order-form]');
            const orderNumberInput = app.querySelector('[data-track-order-number]');
            const feedback = app.querySelector('[data-track-order-feedback]');
            const submitButton = app.querySelector('[data-track-order-submit]');
            const buttonLabel = app.querySelector('[data-track-order-button-label]');
            const timelineContainer = app.querySelector('[data-track-order-timeline]');
            const statusBadge = app.querySelector('[data-track-order-status]');
            const emptyState = app.querySelector('[data-track-state-idle]');
            const loadingState = app.querySelector('[data-track-state-loading]');
            const successState = app.querySelector('[data-track-state-success]');
            const errorState = app.querySelector('[data-track-state-error]');
            const errorText = app.querySelector('[data-track-order-error]');
            const orderId = app.querySelector('[data-track-order-id]');
            const orderItems = app.querySelector('[data-track-order-items]');
            const orderSummary = app.querySelector('[data-track-order-summary]');
            const orderEta = app.querySelector('[data-track-order-eta]');
            const orderCourier = app.querySelector('[data-track-order-courier]');
            const orderShipment = app.querySelector('[data-track-order-shipment]');
            const orderLastUpdate = app.querySelector('[data-track-order-last-update]');
            const orderAddress = app.querySelector('[data-track-order-address]');
            const progressLabel = app.querySelector('[data-track-progress-label]');
            const progressBar = app.querySelector('[data-track-progress-bar]');
            const stageGrid = app.querySelector('[data-track-stage-grid]');

            const states = {
                idle: emptyState,
                loading: loadingState,
                success: successState,
                error: errorState,
            };

            const normalize = (value) => (value || '').replace(/\s+/g, ' ').trim();
            const normalizeOrderId = (value) => normalize(value).toUpperCase().replace(/^#/, '').replace(/[^A-Z0-9-]/g, '');
            const formatStageLabel = (value) => normalize(value)
                .replace(/[_-]+/g, ' ')
                .toLowerCase()
                .replace(/\b\w/g, (char) => char.toUpperCase());
            const escapeHtml = (value) => String(value || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');

            const setState = (state) => {
                Object.keys(states).forEach((key) => {
                    states[key].hidden = key !== state;
                });
            };

            const setFeedback = (message) => {
                const normalizedMessage = normalize(message);
                feedback.hidden = normalizedMessage === '';
                feedback.textContent = normalizedMessage;
            };

            const setButtonLoading = (isLoading) => {
                submitButton.disabled = isLoading;
                buttonLabel.textContent = isLoading ? 'Tracking...' : 'Track Order';
            };

            const renderTimeline = (timeline) => {
                timelineContainer.innerHTML = timeline.map((item) => {
                    const stateClass = item.state ? ' is-' + item.state : '';

                    return `
                        <div class="theme-timeline-item${stateClass}">
                            <div class="d-flex justify-content-between flex-wrap gap-2">
                                <h5 class="mb-1">${escapeHtml(formatStageLabel(item.title))}</h5>
                                <small class="text-muted">${escapeHtml(item.time)}</small>
                            </div>
                            <p class="mb-0">${escapeHtml(item.body)}</p>
                        </div>
                    `;
                }).join('');
            };

            const renderStageGrid = (currentStage) => {
                const currentStageIndex = orderStages.indexOf(currentStage);

                stageGrid.innerHTML = orderStages.map((stage, index) => {
                    let stageClass = ' is-pending';
                    let stageStateLabel = 'Pending';
                    let stageIcon = 'fas fa-lock';

                    if (index < currentStageIndex) {
                        stageClass = ' is-complete';
                        stageStateLabel = 'Completed';
                        stageIcon = 'fas fa-check';
                    } else if (index === currentStageIndex) {
                        stageClass = ' is-current';
                        stageStateLabel = 'Running';
                        stageIcon = 'fas fa-sync-alt';
                    }

                    return `
                        <div class="theme-track-stage${stageClass}">
                            <div class="theme-track-stage-head">
                                <span class="theme-track-stage-step">Step ${index + 1}</span>
                                <span class="theme-track-stage-icon" aria-hidden="true">
                                    <i class="${stageIcon}"></i>
                                </span>
                            </div>
                            <strong>${escapeHtml(formatStageLabel(stage))}</strong>
                            <span class="theme-track-stage-status">${escapeHtml(stageStateLabel)}</span>
                            <p class="theme-track-stage-copy mb-0">${escapeHtml(stageDescriptions[stage] || '')}</p>
                        </div>
                    `;
                }).join('');
            };

            const renderOrder = (order) => {
                const currentStageIndex = Math.max(0, orderStages.indexOf(order.status));
                const reachedSteps = currentStageIndex + 1;
                const progress = Math.round((reachedSteps / orderStages.length) * 100);

                orderId.textContent = 'Order ' + order.id;
                orderItems.textContent = order.items;
                orderSummary.textContent = order.summary;
                orderEta.textContent = order.eta;
                orderCourier.textContent = order.courier;
                orderShipment.textContent = order.shipment;
                orderLastUpdate.textContent = order.last_update;
                orderAddress.textContent = order.address;
                statusBadge.textContent = formatStageLabel(order.status);
                statusBadge.className = 'theme-status-pill ' + order.status_class;
                progressLabel.textContent = reachedSteps + ' of ' + orderStages.length + ' steps completed';
                progressBar.style.width = progress + '%';
                renderStageGrid(order.status);
                renderTimeline(order.timeline);
            };

            const findOrder = (orderNumber) => {
                const normalizedOrderNumber = normalizeOrderId(orderNumber);

                return orders.find((order) => {
                    return normalizeOrderId(order.id) === normalizedOrderNumber;
                }) || null;
            };

            form.addEventListener('submit', function(event) {
                event.preventDefault();
                setFeedback('');

                const enteredOrderNumber = normalize(orderNumberInput.value);

                if (enteredOrderNumber === '') {
                    setState('idle');
                    setFeedback('Enter an order number to continue.');
                    orderNumberInput.focus();
                    return;
                }

                setButtonLoading(true);
                setState('loading');

                window.setTimeout(function() {
                    const order = findOrder(enteredOrderNumber);

                    setButtonLoading(false);

                    if (!order) {
                        errorText.textContent = 'No order was found for "' + enteredOrderNumber + '". Check the order ID and try again.';
                        setState('error');
                        return;
                    }

                    renderOrder(order);
                    setState('success');
                }, 700);
            });

            app.querySelectorAll('[data-demo-order]').forEach((button) => {
                button.addEventListener('click', function() {
                    orderNumberInput.value = button.getAttribute('data-demo-order') || '';
                    setFeedback('');
                    setState('idle');
                    orderNumberInput.focus();
                });
            });

            const initialOrder = normalize(new URLSearchParams(window.location.search).get('order'));
            if (initialOrder !== '') {
                orderNumberInput.value = initialOrder;
                window.setTimeout(function() {
                    form.dispatchEvent(new Event('submit', {
                        bubbles: true,
                        cancelable: true
                    }));
                }, 100);
            }
        })();
    </script>
@endsection
