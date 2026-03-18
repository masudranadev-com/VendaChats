@extends('admin.master')

@section('title', $title)

@section('admin.content')
  <div class="campaign-shell" data-campaigns-page data-api-base-url="{{ $campaignsApiBaseUrl }}" data-per-page="6">
    <div class="page-header campaign-page-header">
      <div>
        <h1 class="page-title">{{ $title }}</h1>
        <p class="page-subtitle">{{ $subtitle }}</p>
      </div>

      <div class="campaign-header-actions">
        <button type="button" class="btn btn-secondary" data-campaigns-reload>Refresh Data</button>
      </div>
    </div>

    <section class="campaign-metrics">
      <article class="campaign-metric-card">
        <span>Total Campaigns</span>
        <strong data-campaign-metric-total>--</strong>
        <small>Stored campaign records in your account</small>
      </article>
      <article class="campaign-metric-card">
        <span>Live Campaigns</span>
        <strong data-campaign-metric-live>--</strong>
        <small>Currently running right now</small>
      </article>
      <article class="campaign-metric-card">
        <span>Scheduled Queue</span>
        <strong data-campaign-metric-scheduled>--</strong>
        <small>Waiting for launch time</small>
      </article>
      <article class="campaign-metric-card">
        <span>Active Templates</span>
        <strong data-campaign-metric-templates>--</strong>
        <small>Ready to reuse in new campaigns</small>
      </article>
    </section>

    <div class="campaign-layout mt-xl">
      <section class="card campaign-form-card">
        <div class="card-header">
          <div>
            <h3 class="card-title">Campaign Builder</h3>
            <p class="campaign-panel-subtitle">Choose product, template, audience, and launch mode in one place.</p>
          </div>
        </div>

        <form class="campaign-builder-form" data-campaign-builder-form novalidate>
          <div class="campaign-form-grid">
            <div class="form-group">
              <label class="form-label" for="campaignName">Campaign Name</label>
              <input id="campaignName" type="text" class="form-input" maxlength="160" placeholder="Example: Weekend Conversion Push" data-campaign-name-input>
            </div>

            <div class="form-group">
              <label class="form-label" for="campaignProduct">Product Focus</label>
              <select id="campaignProduct" class="form-select" data-campaign-product-input>
                <option value="">Loading products...</option>
              </select>
            </div>

            <div class="form-group">
              <label class="form-label" for="campaignAudience">Audience Type</label>
              <select id="campaignAudience" class="form-select" data-campaign-audience-input>
                <option value="">Loading audiences...</option>
              </select>
            </div>

            <div class="form-group">
              <label class="form-label" for="campaignTemplate">Template</label>
              <select id="campaignTemplate" class="form-select" data-campaign-template-input>
                <option value="">Loading templates...</option>
              </select>
            </div>

            <div class="form-group">
              <label class="form-label" for="campaignChannel">Primary Channel</label>
              <select id="campaignChannel" class="form-select" data-campaign-channel-input>
                <option value="">Loading channels...</option>
              </select>
            </div>

            <div class="form-group campaign-form-field-full">
              <label class="form-label" for="campaignNotes">Internal Notes</label>
              <textarea id="campaignNotes" class="form-textarea" rows="3" maxlength="600" placeholder="Optional notes for operators or future review." data-campaign-notes-input></textarea>
              <small class="form-help">This note stays internal and helps the team understand campaign intent later.</small>
            </div>
          </div>

          <div class="campaign-mode-panel">
            <h4 class="campaign-panel-title">Launch Mode</h4>
            <div class="campaign-mode-options">
              <label class="campaign-mode-item">
                <input type="radio" name="campaignLaunchMode" value="instant" data-campaign-mode checked>
                <span>Go Live Now</span>
              </label>
              <label class="campaign-mode-item">
                <input type="radio" name="campaignLaunchMode" value="scheduled" data-campaign-mode>
                <span>Schedule</span>
              </label>
              <label class="campaign-mode-item">
                <input type="radio" name="campaignLaunchMode" value="draft" data-campaign-mode>
                <span>Save Draft</span>
              </label>
            </div>
            <small class="form-help">Pick one clear path: launch now, queue it for later, or keep it as a draft.</small>
          </div>

          <div class="campaign-schedule-config hidden" data-campaign-schedule-fields>
            <div class="campaign-form-grid">
              <div class="form-group">
                <label class="form-label" for="campaignStartDate">Start Date</label>
                <input id="campaignStartDate" type="date" class="form-input" data-campaign-start-date disabled>
              </div>

              <div class="form-group">
                <label class="form-label" for="campaignStartTime">Start Time</label>
                <input id="campaignStartTime" type="time" class="form-input" data-campaign-start-time disabled>
              </div>
            </div>
          </div>

          <div class="campaign-builder-status" data-campaign-builder-status aria-live="polite">
            Loading campaign setup...
          </div>

          <div class="page-actions">
            <button type="button" class="btn btn-secondary" data-campaign-form-reset>Reset Form</button>
            <button type="submit" class="btn btn-success" data-campaign-submit-action>Create Campaign</button>
          </div>
        </form>
      </section>

      <aside class="card campaign-side-card">
        <div class="card-header">
          <div>
            <h3 class="card-title">Upcoming Queue</h3>
            <p class="campaign-panel-subtitle">Scheduled campaigns that will go live automatically.</p>
          </div>
          <span class="badge badge-primary" data-campaign-schedule-badge>Loading</span>
        </div>

        <ul class="campaign-schedule-list" data-campaign-schedule-list>
          <li class="campaign-side-empty">Loading scheduled campaigns...</li>
        </ul>
      </aside>
    </div>

    <div class="campaign-layout mt-xl">
      <section class="card">
        <div class="card-header campaign-board-header">
          <div>
            <h3 class="card-title">Campaign Board</h3>
            <p class="campaign-panel-subtitle">Search, filter, and control campaign lifecycle from one list.</p>
          </div>
          <div class="campaign-header-actions">
            <button type="button" class="btn btn-secondary" data-campaigns-reload>Refresh Board</button>
          </div>
        </div>

        <div class="campaign-toolbar">
          <div class="form-group">
            <label class="form-label" for="campaignSearch">Search</label>
            <input id="campaignSearch" type="search" class="form-input" placeholder="Search by campaign, product, or template" data-campaign-search>
          </div>

          <div class="form-group">
            <label class="form-label" for="campaignStatusFilter">Status</label>
            <select id="campaignStatusFilter" class="form-select" data-campaign-status-filter>
              <option value="all">All Statuses</option>
            </select>
          </div>

          <button type="button" class="btn btn-primary" data-campaign-apply>Apply</button>
          <button type="button" class="btn btn-ghost" data-campaign-reset-filters>Reset</button>
        </div>

        <div class="campaign-history-meta" data-campaign-history-meta>
          Loading campaign history...
        </div>

        <div class="campaign-board" data-campaign-board>
          <p class="campaign-history-empty">Loading campaigns...</p>
        </div>

        <div class="campaign-table-footer">
          <p class="campaign-pagination-summary" data-campaign-pagination-summary>Loading pages...</p>
          <nav class="campaign-pagination-controls" aria-label="Campaign pagination" data-campaign-pagination-controls></nav>
        </div>
      </section>

      <aside class="card campaign-side-card">
        <div class="card-header">
          <div>
            <h3 class="card-title">Template Library</h3>
            <p class="campaign-panel-subtitle">Reusable messaging directions from the campaign API.</p>
          </div>
          <span class="badge badge-warning" data-campaign-template-count>-- templates</span>
        </div>

        <div class="campaign-builder-status campaign-template-preview" data-campaign-template-preview>
          Pick a template to preview the messaging angle before you create a campaign.
        </div>

        <ul class="campaign-template-list" data-campaign-template-list>
          <li class="campaign-side-empty">Loading templates...</li>
        </ul>
      </aside>
    </div>

    <section class="card mt-xl">
      <div class="card-header">
        <div>
          <h3 class="card-title">Channel Split</h3>
          <p class="campaign-panel-subtitle">Calculated from stored campaigns in your account.</p>
        </div>
        <span class="badge badge-info">Live Breakdown</span>
      </div>

      <div data-campaign-channel-split>
        <p class="campaign-side-empty">Loading channel split...</p>
      </div>
    </section>
  </div>
@endsection
