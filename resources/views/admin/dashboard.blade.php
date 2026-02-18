@extends('admin.master')

@section('title', 'Dashboard')

@section('admin.content')
  <div class="page-header">
    <h1 class="page-title">Dashboard</h1>
    <p class="page-subtitle">Welcome back! Here's what's happening with your shop today.</p>
  </div>

  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-label">Today's Revenue</div>
      <div class="stat-value">৳12,450</div>
      <div class="stat-change positive">
        <span>↑</span>
        <span>+18.2%</span>
      </div>
    </div>

    <div class="stat-card success">
      <div class="stat-label">Total Orders</div>
      <div class="stat-value">156</div>
      <div class="stat-change positive">
        <span>↑</span>
        <span>+12 today</span>
      </div>
    </div>

    <div class="stat-card">
      <div class="stat-label">Active Conversations</div>
      <div class="stat-value">23</div>
      <div class="stat-change negative">
        <span>↓</span>
        <span>-5 from yesterday</span>
      </div>
    </div>

    <div class="stat-card success">
      <div class="stat-label">Conversion Rate</div>
      <div class="stat-value">42%</div>
      <div class="stat-change positive">
        <span>↑</span>
        <span>+8.5%</span>
      </div>
    </div>
  </div>

  <div class="grid grid-2">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Revenue Overview</h3>
        <select class="form-select" style="width: auto;">
          <option>Last 7 days</option>
          <option>Last 30 days</option>
          <option>Last 3 months</option>
        </select>
      </div>
      <div class="chart-container" id="revenueChart">
        Revenue chart will appear here
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Top Selling Products</h3>
        <a href="{{ route('admin.products') }}" class="btn btn-ghost btn-sm">View All</a>
      </div>
      <div class="card-body">
        <div style="display:flex; flex-direction:column; gap:16px;">
          <div style="display:flex; align-items:center; gap:12px; padding:12px; background:var(--bg-tertiary); border-radius:8px;">
            <div style="width:48px; height:48px; background:var(--gradient-primary); border-radius:8px;"></div>
            <div style="flex:1;">
              <div style="font-weight:600; margin-bottom:4px;">Blue Saree</div>
              <div style="font-size:13px; color:var(--text-secondary);">156 sold • ৳1,800</div>
            </div>
            <div style="font-weight:700; color:var(--brand-success);">৳280,800</div>
          </div>
          <div style="display:flex; align-items:center; gap:12px; padding:12px; background:var(--bg-tertiary); border-radius:8px;">
            <div style="width:48px; height:48px; background:var(--gradient-success); border-radius:8px;"></div>
            <div style="flex:1;">
              <div style="font-weight:600; margin-bottom:4px;">Cotton Punjabi</div>
              <div style="font-size:13px; color:var(--text-secondary);">89 sold • ৳1,200</div>
            </div>
            <div style="font-weight:700; color:var(--brand-success);">৳106,800</div>
          </div>
          <div style="display:flex; align-items:center; gap:12px; padding:12px; background:var(--bg-tertiary); border-radius:8px;">
            <div style="width:48px; height:48px; background:var(--gradient-danger); border-radius:8px;"></div>
            <div style="flex:1;">
              <div style="font-weight:600; margin-bottom:4px;">Designer Kurta</div>
              <div style="font-size:13px; color:var(--text-secondary);">67 sold • ৳950</div>
            </div>
            <div style="font-weight:700; color:var(--brand-success);">৳63,650</div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="card mt-xl">
    <div class="card-header">
      <h3 class="card-title">Recent Orders</h3>
      <a href="{{ route('admin.orders') }}" class="btn btn-primary btn-sm">View All Orders</a>
    </div>
    <div class="table-container">
      <table class="table">
        <thead>
          <tr>
            <th>Order ID</th>
            <th>Customer</th>
            <th>Product</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>#ORD-1234</td>
            <td>Rabeya Sultana</td>
            <td>Blue Saree</td>
            <td>৳1,800</td>
            <td><span class="badge badge-warning">Pending</span></td>
            <td>2 min ago</td>
            <td>
              <button class="btn btn-sm btn-success">Confirm</button>
              <button class="btn btn-sm btn-ghost">View</button>
            </td>
          </tr>
          <tr>
            <td>#ORD-1233</td>
            <td>Mehedi Khan</td>
            <td>Cotton Punjabi</td>
            <td>৳1,200</td>
            <td><span class="badge badge-success">Processing</span></td>
            <td>15 min ago</td>
            <td>
              <button class="btn btn-sm btn-ghost">View</button>
            </td>
          </tr>
          <tr>
            <td>#ORD-1232</td>
            <td>Farhan Ahmed</td>
            <td>Designer Kurta</td>
            <td>৳950</td>
            <td><span class="badge badge-primary">In Transit</span></td>
            <td>1 hour ago</td>
            <td>
              <button class="btn btn-sm btn-ghost">Track</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
@endsection
