@extends('user.master')

@section('title', 'User Data Deletion | Venda Motion Bot')

@section('user.master')

  <main id="page-data-deletion" class="page active">
    <section class="page-hero">
      <div class="container">
        <span class="section-tag">Legal</span>
        <h1 class="page-hero-title">User Data <span class="gradient-text">Deletion</span></h1>
        <p class="page-hero-sub">Last updated: February 19, 2026</p>
      </div>
    </section>
    <section class="section">
      <div class="container">
        <div class="fd-card">
          <h3>How to Request Deletion via Facebook</h3>
          <p>If you connected to Venda Motion Bot using Facebook Login, you can request deletion of your app data directly from Facebook.</p>
          <p>Go to <strong>Facebook</strong> &gt; <strong>Settings &amp; Privacy</strong> &gt; <strong>Settings</strong> &gt; <strong>Apps and Websites</strong>, find this app, then click <strong>Send Request</strong>.</p>

          <h3>What Happens Next</h3>
          <p>When Facebook sends us your request, we create a deletion request record and return a confirmation code and a status URL.</p>
          <p>You can use the status URL to view a human-readable update for your deletion request.</p>

          <h3>Data Deletion Callback URL</h3>
          <p>For platform verification, the callback endpoint is:</p>
          <p><code>{{ route('data-deletion.callback') }}</code></p>

          <h3>Need Help?</h3>
          <p>If you have questions about your deletion request, please contact us from the <a href="{{ route('contact.index') }}">Contact Page</a>.</p>
        </div>
      </div>
    </section>
  </main>

@endsection
