@extends('admin.master')

@section('title', $title)

@section('admin.content')
  <div class="page-header settings-page-header">
    <div>
      <h1 class="page-title">{{ $title }}</h1>
      <p class="page-subtitle">{{ $subtitle }}</p>
    </div>

    <div class="settings-header-actions">
      <a href="{{ route('admin.shop-settings') }}" class="btn btn-secondary">Back to Shop Settings</a>
      <a href="{{ route('home.index') }}" class="btn btn-primary" target="_blank" rel="noopener noreferrer">Preview Store</a>
    </div>
  </div>

  <section class="settings-section-intro">
    <h3>{{ $sectionHeading }}</h3>
    <p>{{ $sectionSubtitle }}</p>
  </section>

  <section class="settings-stats-grid">
    @foreach ($quickStats as $stat)
      <article class="settings-stat-card is-{{ $stat['tone'] }}">
        <span>{{ $stat['label'] }}</span>
        <strong>{{ $stat['value'] }}</strong>
        <small>{{ $stat['note'] }}</small>
      </article>
    @endforeach
  </section>

  <nav class="settings-tab-row" aria-label="Website content navigation">
    @foreach ($contentTabs as $tab)
      <a href="{{ route($tab['route']) }}" class="settings-tab {{ $activeContentTab === $tab['key'] ? 'active' : '' }}">
        {{ $tab['label'] }}
      </a>
    @endforeach
  </nav>

  @yield('website-content-body')
@endsection
