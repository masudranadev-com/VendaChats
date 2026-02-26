@extends('admin.master')

@section('title', $title)

@section('admin.content')
  @php
    $coreFeatures = [
      [
        'key' => 'messenger_bot',
        'label' => 'Messenger Bot',
        'description' => 'Enable or disable automated Messenger conversations.',
      ],
      [
        'key' => 'comments_bot',
        'label' => 'Comments Bot',
        'description' => 'Enable or disable automated comment monitoring and replies.',
      ],
    ];

    $messengerFeatures = [
      [
        'key' => 'detect_users_emotions',
        'label' => 'Detect users emotions',
        'description' => 'Read emotional tone and adapt response style.',
      ],
      [
        'key' => 'detect_price_vs_quality_buyer',
        'label' => 'Detect users Price-sensitive vs quality-focused buyer',
        'description' => 'Classify buyer mindset for smarter replies.',
      ],
      [
        'key' => 'analysis_and_suggest_products',
        'label' => 'Analysis & Suggest new products',
        'description' => 'Analyze context and recommend suitable products.',
      ],
      [
        'key' => 'product_bergain',
        'label' => 'Product Bergain',
        'description' => 'Control whether bot can negotiate product pricing.',
      ],
      [
        'key' => 'voice_proccessing',
        'label' => 'Voice Proccessing',
        'description' => 'Control voice message understanding and responses.',
      ],
      [
        'key' => 'image_proccessing',
        'label' => 'Image Proccessing',
        'description' => 'Control image-based understanding and recommendations.',
      ],
    ];

    $totalFeatures = count($coreFeatures) + count($messengerFeatures);
    $enabledFeatures = 0;

    $safeTotalFeatures = max(1, $totalFeatures);
    $automationProgress = (int) round(($enabledFeatures / $safeTotalFeatures) * 100);
  @endphp

  <div class="page-header bot-page-header">
    <div>
      <h1 class="page-title">{{ $title }}</h1>
      <p class="page-subtitle">{{ $subtitle }}</p>
    </div>

    <div class="bot-header-chip">
      <div class="bot-chip-label">Automation Intelligence</div>
      <div class="bot-chip-value" data-enabled-count data-total="{{ $totalFeatures }}">
        {{ $enabledFeatures }}/{{ $totalFeatures }} enabled
      </div>
      <div class="bot-chip-progress">
        <span data-enabled-meter style="width: {{ $automationProgress }}%"></span>
      </div>
    </div>
  </div>

  @if (session('status'))
    <div class="bot-settings-alert success" role="status">
      <span>✓</span>
      <span>{{ session('status') }}</span>
    </div>
  @endif

  @if (session('facebook_status'))
    <div class="bot-settings-alert success" role="status">
      <span>✓</span>
      <span>{{ session('facebook_status') }}</span>
    </div>
  @endif

  @if ($errors->has('facebook'))
    <div class="bot-settings-alert error" role="alert">
      <span>!</span>
      <span>{{ $errors->first('facebook') }}</span>
    </div>
  @endif

  <section
    class="card bot-facebook-card mt-xl ui-loading-surface"
    data-facebook-card
    data-api-base-url="{{ $facebookApiBaseUrl }}"
    data-page-id="{{ $pageId }}"
  >
    <div class="card-header">
      <h3 class="card-title">Facebook Connection</h3>
      <span class="badge badge-warning" data-facebook-status-badge>Checking...</span>
    </div>
    <p class="bot-facebook-guide">
      One-time setup: connect Facebook once. The system auto-activates your first page with Messenger and auto comment reply.
    </p>

    <div class="ui-section-loader bot-inline-loader" data-ui-loader hidden>
      <span class="ui-section-loader-spinner" aria-hidden="true"></span>
      <div class="ui-section-loader-copy">
        <strong data-ui-loader-title>Checking Facebook connection</strong>
        <span data-ui-loader-message>Calling API for latest page status.</span>
      </div>
    </div>

    <p class="bot-facebook-status-msg" data-facebook-status-message>
      Loading latest Facebook connection status...
    </p>

    <div class="bot-facebook-connected" data-facebook-connected hidden>
      <div class="bot-facebook-top">
        <div class="bot-facebook-user">
          <strong data-facebook-page-name>{{ $pageName ?: 'Unknown page' }}</strong>
          <span>Page ID: <span data-facebook-page-id>{{ $pageId ?: '-' }}</span></span>
        </div>

        <div class="bot-facebook-actions">
          <button type="button" class="btn btn-danger btn-sm" data-bot-disconnect>
            Disconnect
          </button>
        </div>
      </div>

      <div class="bot-facebook-services">
        <label class="bot-service-check locked" aria-disabled="true">
          <input type="checkbox" data-facebook-service-message disabled>
          <span>Enable Messenger service</span>
        </label>

        <label class="bot-service-check locked" aria-disabled="true">
          <input type="checkbox" data-facebook-service-comment disabled>
          <span>Enable auto reply to comments</span>
        </label>
      </div>

      <div class="bot-selected-page mt-md">
        Active page: <strong data-facebook-active-page>{{ $pageName ?: '-' }}</strong>
      </div>
    </div>

    <div class="bot-facebook-disconnected" data-facebook-disconnected hidden>
      <p class="bot-facebook-empty" data-facebook-disconnected-message>
        Connect Facebook once to start Messenger and comment auto-reply automatically.
      </p>
      <a href="{{ route('admin.bot-settings.facebook.connect') }}" class="btn btn-primary">
        Connect Facebook
      </a>
    </div>
  </section>

  <div data-bot-settings-connected-area hidden>
    <form data-bot-settings>

      <div class="grid grid-2 bot-settings-grid">
        <section class="card bot-settings-card">
          <div class="card-header">
            <h3 class="card-title">Bot Settings</h3>
            <span class="badge badge-primary">Core</span>
          </div>
          <p class="bot-section-description">Turn each channel on or off.</p>

          <div class="bot-settings-list">
            @foreach ($coreFeatures as $feature)
              @php
                $id = 'setting_' . $feature['key'];
              @endphp
              <label class="bot-setting-row" for="{{ $id }}">
                <div class="bot-setting-info">
                  <h4>{{ $feature['label'] }}</h4>
                  <p>{{ $feature['description'] }}</p>
                </div>

                <span class="bot-setting-state off" data-state-for="{{ $id }}">
                  Off
                </span>

                <span class="bot-switch">
                  <input
                    id="{{ $id }}"
                    class="bot-toggle-input"
                    type="checkbox"
                    name="{{ $feature['key'] }}"
                    value="1"
                    data-group="core"
                  >
                  <span class="bot-switch-ui"></span>
                </span>
              </label>
            @endforeach
          </div>
        </section>

        <section class="card bot-overview-card">
          <div class="card-header">
            <h3 class="card-title">Automation Overview</h3>
            <span class="badge badge-success">Live</span>
          </div>

          <div class="bot-overview-grid">
            <div class="bot-mini-stat">
              <div class="bot-mini-label">Total Features</div>
              <div class="bot-mini-value">{{ $totalFeatures }}</div>
            </div>
            <div class="bot-mini-stat">
              <div class="bot-mini-label">Enabled</div>
              <div class="bot-mini-value" data-total-enabled>{{ $enabledFeatures }}</div>
            </div>
            <div class="bot-mini-stat">
              <div class="bot-mini-label">Disabled</div>
              <div class="bot-mini-value" data-total-disabled>{{ $totalFeatures - $enabledFeatures }}</div>
            </div>
          </div>

          <p class="bot-overview-copy">
            Use this panel to balance smart automation with brand tone and control. Save changes to apply new behavior.
          </p>
        </section>
      </div>

      <section class="card mt-xl bot-settings-card">
        <div class="card-header">
          <h3 class="card-title">Messenger Settings</h3>
          <span class="badge badge-info">AI Features</span>
        </div>
        <p class="bot-section-description">Configure how the Messenger bot analyzes users and responds. These settings freeze when Messenger Bot is off.</p>

        <div class="bot-settings-list">
          @foreach ($messengerFeatures as $feature)
            @php
              $id = 'setting_' . $feature['key'];
            @endphp
            <label class="bot-setting-row" for="{{ $id }}">
              <div class="bot-setting-info">
                <h4>{{ $feature['label'] }}</h4>
                <p>{{ $feature['description'] }}</p>
              </div>

              <span class="bot-setting-state off" data-state-for="{{ $id }}">
                Off
              </span>

              <span class="bot-switch">
                <input
                  id="{{ $id }}"
                  class="bot-toggle-input"
                  type="checkbox"
                  name="{{ $feature['key'] }}"
                  value="1"
                  data-group="messenger"
                >
                <span class="bot-switch-ui"></span>
              </span>
            </label>
          @endforeach
        </div>
      </section>

      <div class="page-actions mt-xl bot-actions">
        <div class="bot-unsaved-badge" data-unsaved-badge>Unsaved changes</div>
        <button type="button" class="btn btn-primary" data-bot-save>Save Changes</button>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">Back to Dashboard</a>
      </div>
    </form>
  </div>

  <section class="card mt-xl" data-bot-settings-locked-area hidden>
    <div class="card-header">
      <h3 class="card-title">Bot Panels Hidden</h3>
      <span class="badge badge-warning">Requires Facebook Page</span>
    </div>
    <div class="card-body">
      <p>Connect Facebook to unlock <strong>Bot Settings</strong>, <strong>Automation Overview</strong>, and <strong>Messenger Settings</strong>.</p>
    </div>
  </section>
@endsection
