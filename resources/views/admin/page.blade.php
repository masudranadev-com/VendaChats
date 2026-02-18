@extends('admin.master')

@section('title', $title)

@section('admin.content')
  <div class="page-header">
    <h1 class="page-title">{{ $heading ?? $title }}</h1>
    <p class="page-subtitle">{{ $subtitle }}</p>
  </div>

  <div class="grid grid-2">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Module Status</h3>
      </div>
      <div class="card-body">
        <p>This module UI is now connected to Laravel routing and the shared admin layout.</p>
        <p class="mt-md">Next step: replace demo blocks with real data, filters, and CRUD actions.</p>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Quick Actions</h3>
      </div>
      <div class="card-body">
        <div class="flex flex-gap">
          <button type="button" class="btn btn-primary" onclick="showInfo('Design connected successfully')">Show Toast</button>
          <button type="button" class="btn btn-secondary" onclick="showWarning('Add backend data integration next')">Plan Next</button>
        </div>
      </div>
    </div>
  </div>

  <div class="card mt-xl">
    <div class="card-header">
      <h3 class="card-title">Starter Table</h3>
    </div>
    <div class="table-container">
      <table class="table">
        <thead>
          <tr>
            <th>Item</th>
            <th>Owner</th>
            <th>Status</th>
            <th>Updated</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>{{ $title }} setup</td>
            <td>Admin Team</td>
            <td><span class="badge badge-success">Ready</span></td>
            <td>Today</td>
          </tr>
          <tr>
            <td>Data API</td>
            <td>Backend</td>
            <td><span class="badge badge-warning">Pending</span></td>
            <td>Planned</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
@endsection
