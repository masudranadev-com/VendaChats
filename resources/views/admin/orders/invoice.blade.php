@extends('admin.master')

@section('title', $title)

@section('admin.content')
  <style>
    @media print {
      .sidebar,
      .top-header,
      .mobile-overlay,
      .page-header,
      .orders-confirm-message {
        display: none !important;
      }

      .main-content,
      .sidebar.collapsed ~ .main-content {
        margin-left: 0 !important;
        width: 100% !important;
      }

      .page-content {
        max-width: 100% !important;
        padding: 0 !important;
      }

      .orders-invoice-card {
        margin-top: 0 !important;
        border: 0 !important;
        box-shadow: none !important;
      }

      body {
        background: #fff !important;
      }
    }
  </style>

  <div class="page-header orders-page-header">
    <div>
      <h1 class="page-title">Invoice {{ $invoice['code'] }}</h1>
      <p class="page-subtitle">{{ $subtitle }}</p>
    </div>

    <div class="orders-header-actions">
      <a href="{{ route('admin.orders.view', ['orderId' => $order['id']]) }}" class="btn btn-secondary">Back to Order</a>
      <button type="button" class="btn btn-primary" onclick="window.print()">Print Invoice</button>
    </div>
  </div>

  @if (session('success'))
    <div class="orders-confirm-message">
      <span class="badge badge-success">{{ session('success') }}</span>
    </div>
  @endif

  <section class="card orders-invoice-card mt-xl">
    <div class="orders-invoice-head">
      <div>
        <p class="orders-hero-eyebrow">Demo Invoice</p>
        <h3>Order {{ $order['id'] }}</h3>
        <p>Issued: {{ $invoice['issued_at'] }}</p>
      </div>
      <div class="orders-invoice-meta">
        <span>Invoice No</span>
        <strong>{{ $invoice['code'] }}</strong>
      </div>
    </div>

    <div class="orders-invoice-party-grid">
      <article class="orders-invoice-party">
        <h4>From</h4>
        <p>A Metafy Store</p>
        <p>House 42, Tejgaon, Dhaka</p>
        <p>support@ametafy.example</p>
      </article>

      <article class="orders-invoice-party">
        <h4>Bill To</h4>
        <p>{{ $order['customer']['name'] }} ({{ $order['customer']['id'] }})</p>
        <p>{{ $order['customer']['address'] }}</p>
        <p>{{ $order['customer']['phone'] }}</p>
      </article>
    </div>

    <div class="table-container orders-products-table-wrap mt-lg">
      <table class="table orders-products-table">
        <thead>
          <tr>
            <th>Product</th>
            <th>SKU</th>
            <th>Variant</th>
            <th>Qty</th>
            <th>Unit Price</th>
            <th>Total</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($order['products'] as $item)
            <tr>
              <td>{{ $item['name'] }}</td>
              <td>{{ $item['sku'] }}</td>
              <td>{{ $item['variant'] }}</td>
              <td>{{ $item['qty'] }}</td>
              <td>BDT {{ number_format($item['unit_price']) }}</td>
              <td class="orders-cell-strong">BDT {{ number_format($item['qty'] * $item['unit_price']) }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <div class="orders-invoice-total-box">
      <div>
        <span>Subtotal</span>
        <strong>{{ $invoice['subtotal_text'] }}</strong>
      </div>
      <div>
        <span>Shipping Fee</span>
        <strong>{{ $invoice['shipping_text'] }}</strong>
      </div>
      <div>
        <span>Discount</span>
        <strong>- {{ $invoice['discount_text'] }}</strong>
      </div>
      <div class="orders-total-grand">
        <span>Grand Total</span>
        <strong>{{ $invoice['grand_total_text'] }}</strong>
      </div>
    </div>

    <p class="orders-invoice-note">
      This is a demo invoice preview. Later this will be replaced by dynamic database-backed invoicing.
    </p>
  </section>
@endsection
