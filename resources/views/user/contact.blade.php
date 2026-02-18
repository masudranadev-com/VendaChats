@extends('user.master')

@section('title', 'Venda Motion Bot - যোগাযোগ')

@section('user.master')

  <main id="page-contact" class="page active">
    <section class="page-hero">
      <div class="container">
        <span class="section-tag">যোগাযোগ</span>
        <h1 class="page-hero-title">আমাদের সাথে <span class="gradient-text">যোগ দিন</span></h1>
      </div>
    </section>
    <section class="section">
      <div class="container contact-container">
        <div class="contact-info">
          <h2>যোগাযোগের উপায়</h2>
          <div class="contact-options">
            <div class="contact-option">
              <div class="co-icon">
                📱
              </div>
              <div>
                <h4>WhatsApp</h4>
                <p>+880 1700-000000</p>
              </div>
            </div>
            <div class="contact-option">
              <div class="co-icon">
                📧
              </div>
              <div>
                <h4>Email</h4>
                <p>hello@fcommercebot.com</p>
              </div>
            </div>
          </div>
        </div>
        <div class="contact-form-wrap">
          <h2>মেসেজ পাঠান</h2>
          <form class="contact-form" onsubmit="submitForm(event)">
            <div class="form-group">
              <label>আপনার নাম *</label><input type="text" required="">
            </div>
            <div class="form-group">
              <label>ফোন *</label><input type="tel" required="">
            </div>
            <div class="form-group">
              <label>বার্তা</label>
              <textarea rows="4"></textarea>
            </div><button type="submit" class="btn btn-primary btn-block">মেসেজ পাঠান</button>
          </form>
          <div class="form-success" id="formSuccess">
            <h3>ধন্যবাদ!</h3>
            <p>শীঘ্রই যোগাযোগ করা হবে।</p>
          </div>
        </div>
      </div>
    </section>
  </main>

@endsection
