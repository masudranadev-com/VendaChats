/* ═══════════════════════════════════════════
   Venda Motion Bot — Application JavaScript
   All interactions, navigation, animations
═══════════════════════════════════════════ */

// ── DOM Ready ──────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  initTheme();
  initNavScroll();
  initBackToTop();
  initChatWidget();
  initChatDemo();
  initChatInput();
  initAuthFlow();
  initCounterAnimation();
  initScrollAnimations();
  const initialPage = resolveInitialPage();
  if (pageTitles[initialPage]) document.title = pageTitles[initialPage];
});

// ═══════════════════════════════════════════
// PAGE NAVIGATION
// ═══════════════════════════════════════════
function setActiveNavLink(pageId) {
  document.querySelectorAll('.nav-link').forEach(link => {
    link.classList.toggle('active', link.dataset.page === pageId);
  });
}

function resolveInitialPage() {
  if (typeof window !== 'undefined' && window.__INITIAL_SITE_PAGE) {
    return window.__INITIAL_SITE_PAGE;
  }

  const rawPath = window.location.pathname || '';
  const normalizedPath = rawPath.replace(/\/+$/, '') || '/';
  const fileName = normalizedPath.split('/').pop() || '';

  const fileRouteMap = {
    '': 'home',
    'index.html': 'home',
    'features.html': 'features',
    'pricing.html': 'pricing',
    'how-it-works.html': 'how-it-works',
    'about.html': 'about',
    'contact.html': 'contact',
    'login.html': 'login',
    'signup.html': 'signup'
  };

  if (fileRouteMap[fileName]) {
    return fileRouteMap[fileName];
  }

  const pathRouteMap = {
    '/': 'home',
    '/features': 'features',
    '/pricing': 'pricing',
    '/how-it-works': 'how-it-works',
    '/about': 'about',
    '/contact': 'contact',
    '/login': 'login',
    '/signup': 'signup'
  };

  return pathRouteMap[normalizedPath] || 'home';
}

// ═══════════════════════════════════════════
// MOBILE MENU
// ═══════════════════════════════════════════
const hamburger = document.getElementById('hamburger');
const navLinks  = document.getElementById('navLinks');

hamburger?.addEventListener('click', toggleMobileMenu);

function toggleMobileMenu() {
  const open = navLinks.classList.toggle('open');
  hamburger.classList.toggle('active', open);
  document.body.style.overflow = open ? 'hidden' : '';
}

function closeMobileMenu() {
  navLinks?.classList.remove('open');
  hamburger?.classList.remove('active');
  document.body.style.overflow = '';
}

// Close menu when clicking outside
document.addEventListener('click', (e) => {
  if (navLinks?.classList.contains('open') &&
      !navLinks.contains(e.target) &&
      !hamburger?.contains(e.target)) {
    closeMobileMenu();
  }
});

// ═══════════════════════════════════════════
// THEME TOGGLE
// ═══════════════════════════════════════════
function initTheme() {
  applyTheme('light');
}

function applyTheme(theme) {
  document.body.setAttribute('data-theme', 'light');
  localStorage.setItem('fcommerce-theme', 'light');
}

document.getElementById('themeToggle')?.addEventListener('click', () => {
  applyTheme('light');
});

// ═══════════════════════════════════════════
// NAVBAR SCROLL BEHAVIOR
// ═══════════════════════════════════════════
function initNavScroll() {
  const navbar = document.getElementById('navbar');
  let lastY = 0;

  window.addEventListener('scroll', () => {
    const y = window.scrollY;
    if (y > 80) {
      navbar.style.boxShadow = 'var(--nav-shadow)';
    } else {
      navbar.style.boxShadow = 'none';
    }
    lastY = y;
  }, { passive: true });
}

// ═══════════════════════════════════════════
// BACK TO TOP
// ═══════════════════════════════════════════
function initBackToTop() {
  const btn = document.getElementById('backToTop');
  window.addEventListener('scroll', () => {
    btn?.classList.toggle('visible', window.scrollY > 400);
  }, { passive: true });
}

function scrollToTop() {
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

// ═══════════════════════════════════════════
// CHAT DEMO (Hero Section)
// ═══════════════════════════════════════════
function initChatWidget() {
  const fab = document.getElementById('chatFab');
  const widget = document.getElementById('chatWidget');
  const closeBtn = document.getElementById('chatCloseBtn');

  if (!fab || !widget) return;

  const setChatOpen = (open) => {
    widget.classList.toggle('open', open);
    fab.classList.toggle('active', open);
    fab.setAttribute('aria-expanded', open ? 'true' : 'false');
    fab.setAttribute('aria-label', open ? 'চ্যাট বন্ধ করুন' : 'চ্যাট খুলুন');
  };

  fab.addEventListener('click', () => {
    const willOpen = !widget.classList.contains('open');
    setChatOpen(willOpen);
  });

  closeBtn?.addEventListener('click', () => {
    setChatOpen(false);
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && widget.classList.contains('open')) {
      setChatOpen(false);
    }
  });

  document.addEventListener('click', (e) => {
    if (!widget.classList.contains('open')) return;
    if (widget.contains(e.target) || fab.contains(e.target)) return;
    setChatOpen(false);
  });
}

function initChatDemo() {
  // Show typing then a sample reply after 2 seconds
  setTimeout(() => {
    addBotMessage('আমাদের নতুন নীল শাড়ি ৳১,৮০০। স্টকে মাত্র ৫টি বাকি! 🔥');
  }, 2000);
}

function initChatInput() {
  const form = document.getElementById('chatForm');
  const input = document.getElementById('chatInput');
  const chatBody = document.getElementById('chatBody');
  const typingEl = document.getElementById('typingIndicator');
  if (!form || !input || !chatBody || !typingEl) return;

  form.addEventListener('submit', (e) => {
    e.preventDefault();
    const userText = input.value.trim();
    if (!userText) return;

    const userMsg = createMsgEl('user', userText);
    chatBody.insertBefore(userMsg, typingEl);
    input.value = '';

    typingEl.style.display = 'flex';
    scrollChat(chatBody);

    const demoReply = getDemoReply(userText);
    setTimeout(() => {
      typingEl.style.display = 'none';
      const botMsg = createMsgEl('bot', demoReply);
      chatBody.insertBefore(botMsg, typingEl);
      scrollChat(chatBody);
    }, 1000);
  });
}

function getDemoReply(text) {
  const query = text.toLowerCase();

  if (query.includes('দাম') || query.includes('price') || query.includes('cost')) {
    return 'এই প্রোডাক্টের দাম ৳১,৮০০। আজ অর্ডার করলে ফ্রি ডেলিভারি পাবেন।';
  }

  if (query.includes('ডেলিভারি') || query.includes('delivery')) {
    return 'ঢাকার ভিতরে ২৪-৪৮ ঘণ্টা, ঢাকার বাইরে ২-৪ দিন সময় লাগে।';
  }

  if (query.includes('অর্ডার') || query.includes('order') || query.includes('buy')) {
    return 'অসাধারণ! আপনার নাম, মোবাইল নাম্বার আর ঠিকানা দিন, আমি অর্ডার কনফার্ম করে দিচ্ছি।';
  }

  if (query.includes('ডিসকাউন্ট') || query.includes('offer') || query.includes('ছাড়')) {
    return 'এই সপ্তাহে স্পেশাল অফার চলছে। ২টি নিলে অতিরিক্ত ১০% ছাড় পাবেন।';
  }

  if (query.includes('hello') || query.includes('হ্যালো') || query.includes('hi')) {
    return 'হ্যালো! কী জানতে চান? দাম, ডেলিভারি বা অর্ডার সম্পর্কে জিজ্ঞেস করতে পারেন।';
  }

  return 'ধন্যবাদ! এটা এখন ডেমো রিপ্লাই। আপনার ব্যাকএন্ড AI যুক্ত হলে এখানেই লাইভ রিপ্লাই দেখাবে।';
}

function addBotMessage(text) {
  const chatBody = document.getElementById('chatBody');
  const typingEl = document.getElementById('typingIndicator');
  if (!chatBody) return;

  if (typingEl) typingEl.style.display = 'none';
  const msg = createMsgEl('bot', text);
  chatBody.insertBefore(msg, typingEl);
  scrollChat(chatBody);
}

function createMsgEl(type, text) {
  const div = document.createElement('div');
  div.className = `chat-msg ${type}`;
  const now = new Date().toLocaleTimeString('bn-BD', { hour: '2-digit', minute: '2-digit' });

  const bubble = document.createElement('div');
  bubble.className = 'msg-bubble';
  bubble.textContent = text;

  const time = document.createElement('div');
  time.className = 'msg-time';
  time.textContent = now;

  div.appendChild(bubble);
  div.appendChild(time);
  return div;
}

function scrollChat(el) {
  setTimeout(() => { el.scrollTop = el.scrollHeight; }, 50);
}

// ═══════════════════════════════════════════
// PRICING BILLING TOGGLE
// ═══════════════════════════════════════════
function toggleBilling() {
  const isYearly = document.getElementById('billingToggle').checked;
  const monthlyLabel = document.getElementById('monthlyLabel');
  const yearlyLabel  = document.getElementById('yearlyLabel');

  monthlyLabel?.classList.toggle('active', !isYearly);
  yearlyLabel?.classList.toggle('active', isYearly);

  document.querySelectorAll('.price-amount').forEach(el => {
    const val = isYearly ? el.dataset.yearly : el.dataset.monthly;
    if (val) {
      animateNumber(el, parseInt(el.textContent.replace(/,/g, '')), parseInt(val));
    }
  });
}

function animateNumber(el, from, to) {
  const duration = 400;
  const start = performance.now();
  const update = (now) => {
    const progress = Math.min((now - start) / duration, 1);
    const eased = 1 - Math.pow(1 - progress, 3);
    const current = Math.round(from + (to - from) * eased);
    el.textContent = current.toLocaleString('bn-BD');
    if (progress < 1) requestAnimationFrame(update);
  };
  requestAnimationFrame(update);
}

// ═══════════════════════════════════════════
// FAQ ACCORDION
// ═══════════════════════════════════════════
function toggleFaq(btn) {
  const item = btn.closest('.faq-item');
  const answer = item.querySelector('.faq-answer');
  const isOpen = btn.classList.contains('open');

  // Close all
  document.querySelectorAll('.faq-question.open').forEach(q => {
    q.classList.remove('open');
    q.closest('.faq-item').querySelector('.faq-answer').classList.remove('open');
  });

  // Open clicked if not already open
  if (!isOpen) {
    btn.classList.add('open');
    answer.classList.add('open');
  }
}

// ═══════════════════════════════════════════
// CONTACT FORM
// ═══════════════════════════════════════════
function submitForm(e) {
  e.preventDefault();
  const form = e.target;
  const btn = form.querySelector('button[type="submit"]');
  const successEl = document.getElementById('formSuccess');

  // Loading state
  btn.textContent = 'পাঠানো হচ্ছে... ⏳';
  btn.disabled = true;

  // Simulate submission
  setTimeout(() => {
    form.style.display = 'none';
    if (successEl) {
      successEl.classList.add('show');
      successEl.style.display = 'block';
    }
  }, 1800);
}

// ═══════════════════════════════════════════
// LOGIN AUTH FLOW
// ═══════════════════════════════════════════
function initAuthFlow() {
  const authChoice = document.getElementById('authChoice');
  const emailAuthBtn = document.getElementById('emailAuthBtn');
  const authBackBtn = document.getElementById('authBackBtn');
  const loginForm = document.getElementById('loginForm');

  if (!authChoice || !emailAuthBtn || !authBackBtn || !loginForm) {
    return;
  }

  const showEmailForm = () => {
    authChoice.style.display = 'none';
    loginForm.hidden = false;
    authBackBtn.hidden = false;
    loginForm.querySelector('input[type="email"]')?.focus();
  };

  const resetAuthOptions = () => {
    loginForm.hidden = true;
    authChoice.style.display = 'grid';
    authBackBtn.hidden = true;
  };

  emailAuthBtn.addEventListener('click', showEmailForm);
  authBackBtn.addEventListener('click', resetAuthOptions);

  // Auto-show email form if there are validation errors
  if (document.querySelector('.bot-settings-alert.error')) {
    showEmailForm();
  }
}

// ═══════════════════════════════════════════
// COUNTER ANIMATIONS (Hero Stats)
// ═══════════════════════════════════════════
function initCounterAnimation() {
  const counters = document.querySelectorAll('.stat-num');
  counters.forEach(el => {
    el.dataset.animated = false;
  });

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting && !entry.target.dataset.animated) {
        entry.target.dataset.animated = true;
        // Just add a subtle fade-in
        entry.target.style.animation = 'none';
        entry.target.offsetHeight; // reflow
        entry.target.style.animation = 'countIn 0.6s ease both';
      }
    });
  }, { threshold: 0.5 });

  counters.forEach(el => observer.observe(el));
}

// ═══════════════════════════════════════════
// SCROLL ANIMATIONS
// ═══════════════════════════════════════════
function initScrollAnimations() {
  const animateEls = document.querySelectorAll(
    '.feature-card, .fd-card, .testi-card, .pricing-card, ' +
    '.step-card, .tech-card, .team-card, .value-card, .faq-item, ' +
    '.contact-option, .journey-item, .about-card'
  );

  // Add animation class
  animateEls.forEach((el, i) => {
    if (!el.dataset.observed) {
      el.style.opacity = '0';
      el.style.transform = 'translateY(20px)';
      el.style.transition = `opacity 0.5s ease ${(i % 6) * 0.07}s, transform 0.5s ease ${(i % 6) * 0.07}s`;
      el.dataset.observed = true;
    }
  });

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.style.opacity = '1';
        entry.target.style.transform = 'translateY(0)';
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });

  animateEls.forEach(el => observer.observe(el));
}

// ═══════════════════════════════════════════
// KEYBOARD NAVIGATION
// ═══════════════════════════════════════════
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') closeMobileMenu();
});

// ═══════════════════════════════════════════
// SMOOTH LINK BEHAVIOR (prevent default)
// ═══════════════════════════════════════════
document.querySelectorAll('a[href="#"]').forEach(a => {
  a.addEventListener('click', (e) => {
    // Only prevent if no onclick defined or handled by onclick
    if (!a.hasAttribute('onclick')) e.preventDefault();
  });
});

// ═══════════════════════════════════════════
// DYNAMIC YEAR IN FOOTER
// ═══════════════════════════════════════════
document.querySelectorAll('.footer-bottom p').forEach(p => {
  p.innerHTML = p.innerHTML.replace('2025', new Date().getFullYear());
});

// ═══════════════════════════════════════════
// ACTIVE NAV HIGHLIGHT ON SCROLL (home page)
// ═══════════════════════════════════════════
// Removed — handled via showPage function

// ═══════════════════════════════════════════
// ACCESSIBILITY: Trap focus in mobile menu
// ═══════════════════════════════════════════
document.addEventListener('keydown', (e) => {
  if (e.key === 'Tab' && navLinks?.classList.contains('open')) {
    const focusable = navLinks.querySelectorAll('.nav-link');
    const first = focusable[0];
    const last  = focusable[focusable.length - 1];
    if (e.shiftKey) {
      if (document.activeElement === first) { e.preventDefault(); last.focus(); }
    } else {
      if (document.activeElement === last) { e.preventDefault(); first.focus(); }
    }
  }
});

// ═══════════════════════════════════════════
// INJECT countIn ANIMATION
// ═══════════════════════════════════════════
const style = document.createElement('style');
style.textContent = `
  @keyframes countIn {
    from { transform: scale(0.8); opacity: 0; }
    to   { transform: scale(1);   opacity: 1; }
  }
`;
document.head.appendChild(style);

// ═══════════════════════════════════════════
// FEATURE CARD RIPPLE EFFECT
// ═══════════════════════════════════════════
document.addEventListener('click', (e) => {
  const card = e.target.closest('.feature-card, .fd-card');
  if (!card) return;

  const ripple = document.createElement('span');
  const rect = card.getBoundingClientRect();
  const size = Math.max(rect.width, rect.height);
  const x = e.clientX - rect.left - size / 2;
  const y = e.clientY - rect.top  - size / 2;

  ripple.style.cssText = `
    position: absolute;
    width: ${size}px; height: ${size}px;
    left: ${x}px; top: ${y}px;
    background: rgba(19,82,220,0.08);
    border-radius: 50%;
    transform: scale(0);
    animation: rippleAnim 0.5s ease-out forwards;
    pointer-events: none;
    z-index: 0;
  `;

  card.style.position = 'relative';
  card.style.overflow = 'hidden';
  card.appendChild(ripple);
  setTimeout(() => ripple.remove(), 600);
});

const rippleStyle = document.createElement('style');
rippleStyle.textContent = `
  @keyframes rippleAnim {
    to { transform: scale(2.5); opacity: 0; }
  }
`;
document.head.appendChild(rippleStyle);

// ═══════════════════════════════════════════
// SEO: Update page title dynamically
// ═══════════════════════════════════════════
const pageTitles = {
  home: 'Venda Motion Bot — ফেসবুক সেলারদের জন্য AI চ্যাটবট',
  features: 'ফিচারস — Venda Motion Bot | ১৬টি AI টুল',
  pricing: 'প্রাইসিং — Venda Motion Bot | প্ল্যান শুরু ৳৯৯৯/মাস',
  'how-it-works': 'কিভাবে কাজ করে — Venda Motion Bot | ৩০ মিনিটে লাইভ',
  about: 'আমাদের সম্পর্কে — Venda Motion Bot | ঢাকায় নির্মিত',
  contact: 'যোগাযোগ — Venda Motion Bot | আমাদের সাথে কথা বলুন',
  login: 'লগইন — Venda Motion Bot | নিরাপদ অ্যাকাউন্ট অ্যাক্সেস',
};


// ═══════════════════════════════════════════
// FORM INPUT FLOATING LABEL ANIMATION
// ═══════════════════════════════════════════
document.querySelectorAll('.form-group input, .form-group select, .form-group textarea').forEach(input => {
  input.addEventListener('focus', () => {
    input.parentElement?.classList.add('focused');
  });
  input.addEventListener('blur', () => {
    input.parentElement?.classList.remove('focused');
  });
});

// ═══════════════════════════════════════════
// INITIALIZE
// ═══════════════════════════════════════════
console.log('%c⚡ Venda Motion Bot loaded', 'background:#1352DC;color:white;padding:4px 12px;border-radius:4px;font-weight:bold;');
