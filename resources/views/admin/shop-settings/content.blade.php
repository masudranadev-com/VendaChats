@extends('admin.master')

@section('title', $title)

@section('admin.content')
  <div class="page-header settings-page-header">
    <div>
      <h1 class="page-title">{{ $title }}</h1>
      <p class="page-subtitle">{{ $subtitle }}</p>
    </div>

    <div class="settings-header-actions">
      <button type="button" class="btn btn-secondary">Open Editorial Calendar</button>
      <button type="button" class="btn btn-primary">Publish Approved Content</button>
    </div>
  </div>

  @include('admin.shop-settings.partials.tab-row')

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

  <div class="settings-layout mt-xl">
    <section class="settings-main-column">
      <article class="card settings-panel">
        <div class="card-header">
          <div>
            <h3 class="card-title">Content Health Snapshot</h3>
            <p class="settings-panel-subtitle">Track quality and freshness of banners, pages, FAQs, and blog assets.</p>
          </div>
          <button type="button" class="btn btn-ghost btn-sm">Run Freshness Scan</button>
        </div>

        <div class="settings-content-grid">
          @foreach ($contentData as $content)
            @php
              $contentClass = match ($content['status']) {
                'Healthy' => 'badge-success',
                'Review Needed' => 'badge-warning',
                default => 'badge-info',
              };
            @endphp
            <article class="settings-content-card">
              <div class="settings-content-head">
                <strong>{{ $content['title'] }}</strong>
                <span class="badge {{ $contentClass }}">{{ $content['status'] }}</span>
              </div>
              <p>{{ $content['note'] }}</p>
              <small>{{ $content['meta'] }}</small>
              <button type="button" class="btn btn-ghost btn-sm">Manage</button>
            </article>
          @endforeach
        </div>
      </article>

      <article class="card settings-panel mt-xl">
        <div class="card-header">
          <div>
            <h3 class="card-title">Editorial Pipeline</h3>
            <p class="settings-panel-subtitle">Follow content assets from draft to publication across web surfaces.</p>
          </div>
          <button type="button" class="btn btn-ghost btn-sm">Add Pipeline Item</button>
        </div>

        <div class="table-container">
          <table class="table">
            <thead>
              <tr>
                <th>Asset</th>
                <th>Owner</th>
                <th>Stage</th>
                <th>Publish Date</th>
                <th>Channel</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($pipeline as $item)
                @php
                  $stageClass = $item['stage'] === 'In Review' ? 'badge-warning' : 'badge-success';
                @endphp
                <tr>
                  <td class="settings-cell-strong">{{ $item['asset'] }}</td>
                  <td>{{ $item['owner'] }}</td>
                  <td><span class="badge {{ $stageClass }}">{{ $item['stage'] }}</span></td>
                  <td>{{ $item['publish'] }}</td>
                  <td>{{ $item['channel'] }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </article>

      <article class="card settings-panel mt-xl">
        <div class="card-header">
          <div>
            <h3 class="card-title">SEO and Compliance</h3>
            <p class="settings-panel-subtitle">Monitor metadata, schema coverage, and legal policy review cadence.</p>
          </div>
          <button type="button" class="btn btn-ghost btn-sm">Run SEO Audit</button>
        </div>

        <div class="table-container">
          <table class="table">
            <thead>
              <tr>
                <th>Page Scope</th>
                <th>Metadata</th>
                <th>Schema</th>
                <th>Last Audit</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($seoChecks as $seo)
                <tr>
                  <td class="settings-cell-strong">{{ $seo['page'] }}</td>
                  <td>{{ $seo['meta'] }}</td>
                  <td>{{ $seo['schema'] }}</td>
                  <td>{{ $seo['last_audit'] }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>

        <div class="settings-inline-actions">
          <button type="button" class="btn btn-primary btn-sm">Save Compliance Notes</button>
          <button type="button" class="btn btn-secondary btn-sm">Send for Legal Review</button>
        </div>
      </article>
    </section>

    <section class="settings-side-column">
      <article class="card settings-panel">
        <div class="card-header">
          <h3 class="card-title">Content Governance Checklist</h3>
          <span class="badge badge-info">Editorial Ops</span>
        </div>

        <ul class="settings-focus-list">
          @foreach ($checklist as $item)
            <li>{{ $item }}</li>
          @endforeach
        </ul>
      </article>

      @include('admin.shop-settings.partials.recent-activity')
    </section>
  </div>
@endsection
