@extends('admin.master')

@section('title', $title)

@section('admin.content')
  <div class="page-header users-page-header">
    <div>
      <h1 class="page-title">{{ $title }}</h1>
      <p class="page-subtitle">{{ $subtitle }}</p>
    </div>

    <div class="users-header-meta">
      <div class="users-meta-card">
        <span>Total Users</span>
        <strong data-users-total-users>--</strong>
      </div>
      <div class="users-meta-card">
        <span>WhatsApp Users</span>
        <strong data-users-total-whatsapp>--</strong>
      </div>
      <div class="users-meta-card">
        <span>Price-sensitive</span>
        <strong data-users-total-price-sensitive>--</strong>
      </div>
    </div>
  </div>

  <section
    class="card users-filters-card"
    id="usersCatalogSection"
    data-api-base-url="{{ $usersApiBaseUrl }}"
    data-refresh-token="{{ $usersRefreshToken }}"
    data-per-page="10"
    data-view-url="{{ route('admin.users.views') }}"
  >
    <div class="card-header">
      <h3 class="card-title">Filtering Options</h3>
      <span class="badge badge-info" data-users-filtered-badge>Loading...</span>
    </div>

    <div class="users-filters-form">
      <div class="users-filter-grid">
        <div class="form-group">
          <label class="form-label" for="users-search">Search</label>
          <input
            id="users-search"
            class="form-input"
            type="text"
            data-users-search
            placeholder="Name, User ID, Sender ID"
          >
        </div>

        <div class="form-group">
          <label class="form-label" for="users-channel">Channel</label>
          <select id="users-channel" class="form-select" data-users-channel>
            <option value="all">All channels</option>
            <option value="Facebook">Facebook</option>
            <option value="WhatsApp">WhatsApp</option>
            <option value="Website">Website</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label" for="users-emotion">Emotions</label>
          <select id="users-emotion" class="form-select" data-users-emotion>
            <option value="all">All emotions</option>
            <option value="Curious">Curious</option>
            <option value="Happy">Happy</option>
            <option value="Sad">Sad</option>
            <option value="Neutral">Neutral</option>
            <option value="Excited">Excited</option>
            <option value="Angry">Angry</option>
            <option value="Impatient">Impatient</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label" for="users-type">User Type</label>
          <select id="users-type" class="form-select" data-users-user-type>
            <option value="all">All types</option>
            <option value="Price-sensitive">Price-sensitive</option>
            <option value="Quality-focused">Quality-focused</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label" for="users-status">Status</label>
          <select id="users-status" class="form-select" data-users-status>
            <option value="all">All status</option>
            <option value="Active">Active</option>
            <option value="Inactive">Inactive</option>
          </select>
        </div>
      </div>

      <div class="users-filter-actions">
        <button type="button" class="btn btn-primary" data-users-apply>Apply Filters</button>
        <button type="button" class="btn btn-secondary" data-users-reset>Reset</button>
        <div class="users-filter-result" data-users-result>Loading users...</div>
      </div>
    </div>

    <div class="table-container users-table-wrap mt-lg">
      <table class="table users-table">
        <thead>
          <tr>
            <th>Profile</th>
            <th>Name</th>
            <th>Channel</th>
            <th>Emotions</th>
            <th>User Type</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody data-users-tbody>
          <tr>
            <td colspan="7" class="users-empty">Loading users...</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="users-table-footer" data-users-pagination-wrap hidden>
      <p class="users-pagination-summary" data-users-pagination-summary>Page 1 of 1</p>
      <nav class="users-pagination-controls" data-users-pagination-controls aria-label="Users pagination"></nav>
    </div>
  </section>
@endsection
