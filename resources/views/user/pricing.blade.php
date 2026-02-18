@extends('user.master')

@section('title', 'Venda Motion Bot - প্রাইসিং')

@section('user.master')

  <main id="page-pricing" class="page active">
    <section class="page-hero">
      <div class="container">
        <span class="section-tag">প্রাইসিং</span>
        <h1 class="page-hero-title">আপনার growth stage অনুযায়ী <span class="gradient-text">সঠিক প্ল্যান</span></h1>
        <p class="page-hero-sub">ছোট seller থেকে high-volume brand পর্যন্ত scalable automation plan।</p>
        <p class="page-hero-sub">বাংলাদেশ marketplace seller দের জন্য recommended package: <strong>গ্রোথ (৳2499/মাস)</strong>।</p>

        <div class="billing-toggle">
          <span id="monthlyLabel" class="toggle-label active">Monthly</span>
          <label class="toggle-switch" aria-label="বিলিং টগল">
            <input id="billingToggle" type="checkbox" onchange="toggleBilling()">
            <span class="toggle-slider"></span>
          </label>
          <span id="yearlyLabel" class="toggle-label">Yearly <span class="save-badge">Save 2 Months</span></span>
        </div>
      </div>
    </section>

    <section class="section pricing-section">
      <div class="container">
        <div class="pricing-grid">
          <div class="pricing-card">
            <div class="plan-name">স্টার্টার</div>
            <div class="plan-price">
              <span class="price-currency">৳</span>
              <span class="price-amount" data-monthly="999" data-yearly="9990">999</span>
              <span class="price-period">/মাস</span>
            </div>
            <p class="plan-desc">নতুন seller বা ছোট business page এর জন্য দ্রুত শুরু।</p>
            <ul class="plan-features">
              <li><span class="check">✓</span><span>Basic AI inbox + comment reply</span></li>
              <li><span class="check">✓</span><span>Manual approve order flow</span></li>
              <li><span class="check">✓</span><span>Daily summary report</span></li>
              <li><span class="cross">✕</span><span>Auto bargaining নেই</span></li>
              <li><span class="cross">✕</span><span>Recovery campaign নেই</span></li>
            </ul>
            <div class="fd-highlights">
              <span>AI Access: Core</span><span>5+ tools</span>
            </div>
            <a href="{{ route('contact.index') }}" class="btn btn-outline btn-block">স্টার্টার নিন</a>
          </div>

          <div class="pricing-card pricing-card--popular">
            <div class="popular-badge">বাংলাদেশে বেস্ট</div>
            <div class="plan-name">গ্রোথ</div>
            <div class="plan-price">
              <span class="price-currency">৳</span>
              <span class="price-amount" data-monthly="2499" data-yearly="24990">2499</span>
              <span class="price-period">/মাস</span>
            </div>
            <p class="plan-desc">বাংলাদেশ marketplace seller-এর সাধারণ order volume, ad spend ও team size অনুযায়ী সবচেয়ে balanced package।</p>
            <ul class="plan-features">
              <li><span class="check">✓</span><span>Smart inbox + comment auto reply</span></li>
              <li><span class="check">✓</span><span>Auto bargaining (floor price safe)</span></li>
              <li><span class="check">✓</span><span>WhatsApp recovery flow</span></li>
              <li><span class="check">✓</span><span>Courier rate compare</span></li>
              <li><span class="check">✓</span><span>Weekly performance report</span></li>
            </ul>
            <div class="fd-highlights">
              <span>AI Access: Growth</span><span>12+ tools</span>
            </div>
            <a href="{{ route('contact.index') }}" class="btn btn-primary btn-block">গ্রোথ প্ল্যান শুরু</a>
          </div>

          <div class="pricing-card">
            <div class="plan-name">প্রো</div>
            <div class="plan-price">
              <span class="price-currency">৳</span>
              <span class="price-amount" data-monthly="4999" data-yearly="49990">4999</span>
              <span class="price-period">/মাস</span>
            </div>
            <p class="plan-desc">উচ্চ ভলিউম seller, brand scaling এবং multi-agent অপারেশনের জন্য।</p>
            <ul class="plan-features">
              <li><span class="check">✓</span><span>Advanced AI rules + comment moderation</span></li>
              <li><span class="check">✓</span><span>Priority support & onboarding</span></li>
              <li><span class="check">✓</span><span>Custom funnel & upsell logic</span></li>
              <li><span class="check">✓</span><span>Role-based dashboard access</span></li>
              <li><span class="check">✓</span><span>Dedicated success manager</span></li>
            </ul>
            <div class="fd-highlights">
              <span>AI Access: Full</span><span>All 16 tools</span>
            </div>
            <a href="{{ route('contact.index') }}" class="btn btn-outline btn-block">প্রো ডেমো বুক করুন</a>
          </div>
        </div>

        <div class="agency-plan">
          <div class="agency-content">
            <div class="agency-icon">🏷️</div>
            <div class="agency-text">
              <h3>Agency / White Label Plan</h3>
              <p>নিজের ব্র্যান্ড নামে client onboarding, managed service, এবং recurring commission model।</p>
            </div>
          </div>
          <a href="{{ route('contact.index') }}" class="btn btn-primary">Agency নিয়ে কথা বলুন</a>
        </div>
      </div>
    </section>

    <section class="section">
      <div class="container">
        <div class="section-header">
          <span class="section-tag">AI Feature Matrix</span>
          <h2 class="section-title">AI ফিচার প্ল্যান অনুযায়ী</h2>
          <p class="section-sub">Starter, Growth এবং Pro প্ল্যানে কোন AI feature কতটুকু available, এক নজরে দেখুন।</p>
        </div>

        <div class="compare-table-wrap">
          <table class="compare-table" aria-label="AI feature matrix by plan">
            <thead>
              <tr>
                <th>AI ফিচার</th>
                <th class="col-others">স্টার্টার</th>
                <th class="col-us">গ্রোথ</th>
                <th class="col-others">প্রো</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>Inbox Auto Reply</td>
                <td class="partial">Basic</td>
                <td class="yes">Smart</td>
                <td class="yes">Advanced</td>
              </tr>
              <tr>
                <td>Comment Auto Reply</td>
                <td class="partial">Basic</td>
                <td class="yes">Smart</td>
                <td class="yes">Smart + Moderation</td>
              </tr>
              <tr>
                <td>Voice Note Understanding</td>
                <td class="no">No</td>
                <td class="yes">Yes</td>
                <td class="yes">Yes</td>
              </tr>
              <tr>
                <td>Smart Bargaining AI</td>
                <td class="no">No</td>
                <td class="yes">Yes</td>
                <td class="yes">Advanced</td>
              </tr>
              <tr>
                <td>Buyer Psychology Engine</td>
                <td class="no">No</td>
                <td class="partial">Partial</td>
                <td class="yes">Yes</td>
              </tr>
              <tr>
                <td>Emotion Escalation Alert</td>
                <td class="no">No</td>
                <td class="yes">Yes</td>
                <td class="yes">Yes</td>
              </tr>
              <tr>
                <td>Address OCR</td>
                <td class="partial">Partial</td>
                <td class="yes">Yes</td>
                <td class="yes">Yes</td>
              </tr>
              <tr>
                <td>WhatsApp Recovery AI</td>
                <td class="no">No</td>
                <td class="yes">Yes</td>
                <td class="yes">Yes</td>
              </tr>
              <tr>
                <td>AI Performance Coach</td>
                <td class="no">No</td>
                <td class="yes">Weekly</td>
                <td class="yes">Advanced Weekly</td>
              </tr>
              <tr>
                <td>Live Competition Monitor</td>
                <td class="no">No</td>
                <td class="partial">Limited</td>
                <td class="yes">Yes</td>
              </tr>
              <tr>
                <td>Upsell Recommendation AI</td>
                <td class="no">No</td>
                <td class="yes">Yes</td>
                <td class="yes">Yes</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </section>

    <section class="section comparison-section">
      <div class="container">
        <div class="section-header">
          <span class="section-tag">Plan Comparison</span>
          <h2 class="section-title">কোন প্ল্যানে কোন সুবিধা</h2>
          <p class="section-sub">সংক্ষেপে মূল পার্থক্য দেখুন।</p>
        </div>

        <div class="compare-table-wrap">
          <table class="compare-table" aria-label="Pricing plan comparison">
            <thead>
              <tr>
                <th>ফিচার</th>
                <th class="col-others">স্টার্টার</th>
                <th class="col-us">গ্রোথ</th>
                <th class="col-others">প্রো</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>AI Reply Engine</td>
                <td class="partial">Basic</td>
                <td class="yes">Advanced</td>
                <td class="yes">Advanced+</td>
              </tr>
              <tr>
                <td>Comment Auto Reply</td>
                <td class="partial">Basic</td>
                <td class="yes">Smart</td>
                <td class="yes">Smart + Rules</td>
              </tr>
              <tr>
                <td>Negotiation Automation</td>
                <td class="no">No</td>
                <td class="yes">Yes</td>
                <td class="yes">Yes</td>
              </tr>
              <tr>
                <td>Recovery Campaign</td>
                <td class="no">No</td>
                <td class="yes">Yes</td>
                <td class="yes">Yes</td>
              </tr>
              <tr>
                <td>Custom Workflow</td>
                <td class="no">No</td>
                <td class="partial">Limited</td>
                <td class="yes">Full</td>
              </tr>
              <tr>
                <td>Support SLA</td>
                <td class="partial">Standard</td>
                <td class="yes">Priority</td>
                <td class="yes">Dedicated</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </section>

    <section class="section">
      <div class="container pricing-faq">
        <h3>প্রাইসিং সম্পর্কিত সাধারণ প্রশ্ন</h3>
        <div class="faq-grid">
          <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">বাংলাদেশ মার্কেটপ্লেস seller এর জন্য best package কোনটা?<span class="faq-arrow">▼</span></button>
            <div class="faq-answer">বেশিরভাগ active seller-এর জন্য Growth (৳2499/মাস) best fit, কারণ এতে inbox + comment auto reply, bargaining, recovery, এবং weekly insight একসাথে পাওয়া যায়।</div>
          </div>
          <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">Yearly নিলে কিভাবে save হবে?<span class="faq-arrow">▼</span></button>
            <div class="faq-answer">Yearly billing এ ১২ মাসের বদলে কার্যত ১০ মাসের সমপরিমাণ চার্জ হয়, তাই ২ মাস equivalent save করতে পারেন।</div>
          </div>
          <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">প্ল্যান upgrade/downgrade করা যাবে?<span class="faq-arrow">▼</span></button>
            <div class="faq-answer">হ্যাঁ, business requirement অনুযায়ী মাস শেষে বা billing cycle অনুযায়ী plan change করা যাবে।</div>
          </div>
          <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">ফ্রি ট্রায়াল আছে?<span class="faq-arrow">▼</span></button>
            <div class="faq-answer">ডেমো কলের পরে selected sellers এর জন্য limited pilot/trial enable করা হয়, use-case অনুযায়ী।</div>
          </div>
          <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">পেমেন্ট মেথড কী কী?<span class="faq-arrow">▼</span></button>
            <div class="faq-answer">Bank transfer, card, mobile financial service এবং invoice ভিত্তিক payment option available।</div>
          </div>
        </div>
      </div>
    </section>

    <section class="cta-banner">
      <div class="container cta-inner">
        <div class="cta-text">
          <h2>আপনার জন্য ঠিক কোন প্ল্যান - নিশ্চিত নন?</h2>
          <p>১০ মিনিটে use-case audit করে আমাদের টিম best-fit plan সাজেস্ট করবে।</p>
        </div>
        <div class="cta-actions">
          <a href="{{ route('contact.index') }}" class="btn btn-white btn-lg">ফ্রি কনসালটেশন বুক করুন</a>
          <a href="{{ route('how-it-works.index') }}" class="btn btn-outline-white btn-lg">ওয়ার্কফ্লো দেখুন</a>
        </div>
      </div>
    </section>
  </main>

@endsection
