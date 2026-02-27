@extends('user.master')

@section('title', 'Terms and Conditions | Venda Motion Bot')
@section('og_title', 'Terms and Conditions | Venda Motion Bot')
@section('og_description', 'Read our Terms and Conditions to understand your rights and responsibilities when using Venda Motion Bot services.')

@section('user.master')

  <main id="page-terms" class="page active">
    <section class="page-hero">
      <div class="container">
        <span class="section-tag">Legal</span>
        <h1 class="page-hero-title">Terms & <span class="gradient-text">Conditions</span></h1>
        <p class="page-hero-sub">Last updated: February 17, 2026</p>
      </div>
    </section>
    <section class="section">
      <div class="container">
        <div class="fd-card">
          <h3>1. Acceptance of Terms</h3>
          <p>By using Venda Motion Bot, you agree to these Terms and Conditions. If you do not agree, please do not use the platform.</p>
          <h3>2. Use of Service</h3>
          <p>You may use our service only for lawful business activities. Illegal, misleading, or harmful campaigns/content are prohibited.</p>
          <h3>3. Account Responsibilities</h3>
          <p>You are responsible for keeping your login credentials secure. Please notify us immediately if you suspect unauthorized account activity.</p>
          <h3>4. Subscription and Payments</h3>
          <p>Paid plans require applicable subscription fees. If payment is not made on time, service access may be limited or suspended.</p>
          <h3>5. Intellectual Property</h3>
          <p>The platform, software, design, and branding are the property of Venda Motion Bot. You may not copy or distribute them without written permission.</p>
          <h3>6. Limitation of Liability</h3>
          <p>While we strive to provide reliable service, we do not guarantee uninterrupted or error-free operation. Our liability is limited to the extent permitted by law.</p>
          <h3>7. Service Changes</h3>
          <p>We may update features, pricing, or these terms when needed. Material changes will be posted on the website.</p>
          <h3>8. Account Suspension or Termination</h3>
          <p>We may suspend or terminate accounts, with or without notice, if there is a terms violation, misuse, or security risk.</p>
          <h3>9. Governing Law</h3>
          <p>These Terms and Conditions are governed by the applicable laws of Bangladesh.</p>
          <h3>10. Contact</h3>
          <p>If you have any questions about these terms, please contact us through the <a href="{{ route('contact.index') }}">Contact Page</a>.</p>
        </div>
      </div>
    </section>
  </main>

@endsection
