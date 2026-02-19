@extends('user.master')

@section('title', 'Deletion Request Status | Venda Motion Bot')

@section('user.master')

  <main id="page-data-deletion-status" class="page active">
    <section class="page-hero">
      <div class="container">
        <span class="section-tag">Legal</span>
        <h1 class="page-hero-title">Deletion Request <span class="gradient-text">Status</span></h1>
        <p class="page-hero-sub">Track your Facebook data deletion request</p>
      </div>
    </section>
    <section class="section">
      <div class="container">
        <div class="fd-card">
          <h3>Request Summary</h3>
          <p><strong>Confirmation Code:</strong> {{ $requestRecord->confirmation_code }}</p>
          <p><strong>Status:</strong> {{ ucfirst($requestRecord->status) }}</p>
          <p><strong>Requested At:</strong> {{ $requestRecord->requested_at?->format('F d, Y h:i A') }}</p>
          @if ($requestRecord->completed_at)
            <p><strong>Completed At:</strong> {{ $requestRecord->completed_at->format('F d, Y h:i A') }}</p>
          @endif

          @if ($requestRecord->status === 'completed')
            <h3>Current State</h3>
            <p>Your data deletion request has been processed.</p>
          @elseif ($requestRecord->status === 'pending')
            <h3>Current State</h3>
            <p>Your request has been received and is being processed.</p>
          @else
            <h3>Current State</h3>
            <p>Your request could not be completed automatically. Please contact support for assistance.</p>
          @endif

          @if ($requestRecord->notes)
            <h3>Details</h3>
            <p>{{ $requestRecord->notes }}</p>
          @endif

          <h3>Support</h3>
          <p>For any questions about this request, please use the <a href="{{ route('contact.index') }}">Contact Page</a>.</p>
        </div>
      </div>
    </section>
  </main>

@endsection
