@extends('user.master')

@section('title', 'Privacy Policy | Venda Motion Bot')
@section('og_title', 'Privacy Policy | Venda Motion Bot')
@section('og_description', 'Read our Privacy Policy to learn how Venda Motion Bot collects, uses, and protects your personal information.')

@section('user.master')

  <main id="page-privacy" class="page active">
    <section class="page-hero">
      <div class="container">
        <span class="section-tag">Legal</span>
        <h1 class="page-hero-title">Privacy <span class="gradient-text">Policy</span></h1>
        <p class="page-hero-sub">Last updated: February 19, 2026</p>
      </div>
    </section>
    <section class="section">
      <div class="container">
        <div class="fd-card">
          <h3>1. Information We Collect</h3>
          <p>We may collect account information (name, email, and phone number), usage data (logs, device, and browser details), and business information you provide while using our services.</p>
          <h3>2. How We Use Information</h3>
          <p>We use information to deliver services, improve performance, provide support, maintain security, and send important updates related to your account and our platform.</p>
          <h3>3. Cookies and Tracking</h3>
          <p>We may use cookies and similar technologies to analyze site usage and improve performance. You can manage cookie settings from your browser.</p>
          <h3>4. Information Sharing</h3>
          <p>We do not sell your personal data. We may share limited data with trusted service providers when necessary to operate and deliver our services.</p>
          <h3>5. Data Security</h3>
          <p>We apply reasonable technical and organizational safeguards to protect your data from unauthorized access, alteration, or disclosure.</p>
          <h3>6. Data Retention</h3>
          <p>We retain data only as long as needed for legal obligations, service delivery, or dispute resolution.</p>
          <h3>7. Your Rights</h3>
          <p>Subject to applicable law, you may request access to, correction of, or deletion of your personal information.</p>
          <h3>8. Third-Party Links</h3>
          <p>Our website may contain links to third-party websites. We are not responsible for their privacy practices or content.</p>
          <h3>9. Children's Privacy</h3>
          <p>Our services are not intended for children under 13, and we do not knowingly collect personal information from them.</p>
          <h3>10. Policy Updates</h3>
          <p>We may update this Privacy Policy from time to time. When we do, the "Last updated" date on this page will be revised.</p>
          <h3>11. Contact</h3>
          <p>For questions about privacy or data handling, please contact us through the <a href="{{ route('contact.index') }}">Contact Page</a>.</p>
          <h3>12. Facebook User Data Deletion</h3>
          <p>If you used Facebook Login, you can request data deletion from Facebook settings. For full instructions and request status details, visit our <a href="{{ route('data-deletion.index') }}">User Data Deletion</a> page.</p>
        </div>
      </div>
    </section>
  </main>

@endsection
