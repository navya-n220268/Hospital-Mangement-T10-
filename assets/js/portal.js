/* =============================================
   MEDIVITA PORTAL — SHARED JAVASCRIPT
   ============================================= */

// ---- AUTH CHECK ----
async function checkAuth(requiredRole = 'patient') {
  try {
    const resp = await fetch('/medivita/backend/patient/get_user.php');
    if (!resp.ok) {
      window.location.href = '/medivita/frontend/auth/login.html';
      return null;
    }
    const data = await resp.json();
    if (!data.success || data.user.role !== requiredRole) {
      window.location.href = '/medivita/frontend/auth/login.html';
      return null;
    }
    return data.user;
  } catch (err) {
    window.location.href = '/medivita/frontend/auth/login.html';
    return null;
  }
}

// ---- SIDEBAR INJECTION ----
function buildSidebar(activePage, user) {
  const navItems = [
    { id: 'dashboard',         href: 'dashboard.html',         icon: 'fas fa-th-large',           label: 'Dashboard',          section: 'MAIN' },
    { id: 'symptom-checker',   href: 'symptom-checker.html',   icon: 'fas fa-robot',              label: 'AI Symptom Checker', section: 'MAIN' },
    { id: 'book-appointment',  href: 'book-appointment.html',  icon: 'fas fa-calendar-plus',      label: 'Book Appointment',   section: 'APPOINTMENTS' },
    { id: 'my-appointments',   href: 'my-appointments.html',   icon: 'fas fa-calendar-check',     label: 'My Appointments',    section: 'APPOINTMENTS' },
    { id: 'message-to-doctor', href: 'message-to-doctor.html', icon: 'fas fa-comment-medical',    label: 'Message to Doctor',  section: 'APPOINTMENTS' },
    { id: 'medical-records',   href: 'medical-records.html',   icon: 'fas fa-file-medical',       label: 'Medical Records',    section: 'HEALTH' },
    { id: 'profile',           href: 'patient-profile.html',   icon: 'fas fa-user-circle',        label: 'Profile Settings',   section: 'ACCOUNT' },
  ];

  let lastSection = '';
  let navHTML = '';
  navItems.forEach(item => {
    if (item.section !== lastSection) {
      navHTML += `<div class="nav-section-label">${item.section}</div>`;
      lastSection = item.section;
    }
    navHTML += `
      <a href="${item.href}" class="nav-item ${activePage === item.id ? 'active' : ''}">
        <i class="${item.icon}"></i>
        <span>${item.label}</span>
        ${item.badge ? `<span class="nav-badge">${item.badge}</span>` : ''}
      </a>`;
  });

  const userName = user ? user.name : 'Patient';
  const initials = userName.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);

  const sidebar = document.querySelector('.sidebar');
  sidebar.innerHTML = `
    <div class="sidebar-top">
      <div class="sidebar-logo"><i class="fas fa-heartbeat"></i></div>
      <div class="sidebar-brand">MediVita<small>Patient Portal</small></div>
    </div>
    <div class="sidebar-patient">
      <div class="patient-avatar">${initials}</div>
      <div class="patient-info">
        <p>${userName}</p>
        <div class="patient-status">
          <span class="status-dot"></span>
          <span style="font-size:.68rem;color:rgba(255,255,255,.45)">Active Patient</span>
        </div>
      </div>
    </div>
    <nav class="sidebar-nav">${navHTML}</nav>
    <div class="sidebar-footer">
      <a href="/medivita/backend/auth/logout.php" class="nav-item danger">
        <i class="fas fa-sign-out-alt"></i>
        <span>Logout</span>
      </a>
    </div>
  `;
}

// ---- TOPBAR INJECTION ----
function buildTopbar(title, subtitle) {
  const topbar = document.querySelector('.topbar');
  topbar.innerHTML = `
    <button class="mob-toggle" id="mobToggle"><i class="fas fa-bars"></i></button>
    <div class="topbar-title">
      <h1>${title}</h1>
      ${subtitle ? `<p>${subtitle}</p>` : ''}
    </div>
    <div class="topbar-right">
      <button class="topbar-btn">
        <i class="fas fa-search"></i>
      </button>
      <button class="emg-btn" onclick="triggerEmergency()">
        <i class="fas fa-exclamation-triangle"></i>
        <span>Emergency</span>
      </button>
    </div>
  `;
}

// ---- MOBILE SIDEBAR ----
function initMobileSidebar() {
  const toggle = document.getElementById('mobToggle');
  const sidebar = document.querySelector('.sidebar');
  const overlay = document.querySelector('.sidebar-overlay');
  if (!toggle) return;
  toggle.addEventListener('click', () => {
    sidebar.classList.toggle('open');
    overlay.classList.toggle('show');
  });
  overlay.addEventListener('click', () => {
    sidebar.classList.remove('open');
    overlay.classList.remove('show');
  });
}

// ---- EMERGENCY ----
function triggerEmergency() {
  const modal = document.createElement('div');
  modal.style.cssText = `
    position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.6);backdrop-filter:blur(6px);
    display:flex;align-items:center;justify-content:center;animation:fadeIn .2s ease;
  `;
  modal.innerHTML = `
    <div style="background:#fff;border-radius:18px;padding:36px;max-width:380px;width:90%;text-align:center;box-shadow:0 20px 60px rgba(0,0,0,.3);animation:bounceIn .3s ease;">
      <div style="width:64px;height:64px;background:#fef2f2;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:1.6rem;color:#dc2626;">
        <i class="fas fa-ambulance"></i>
      </div>
      <h3 style="font-size:1.1rem;font-weight:800;color:#0f172a;margin-bottom:8px">Emergency Alert Sent</h3>
      <p style="font-size:.85rem;color:#64748b;line-height:1.7;margin-bottom:24px">
        Hospital emergency team has been notified. Help is on the way.<br/>
        <strong style="color:#dc2626">Emergency Hotline: 1800-MED-911</strong>
      </p>
      <button onclick="this.closest('[style]').remove()" style="background:#dc2626;color:white;border:none;padding:10px 28px;border-radius:99px;font-size:.85rem;font-weight:700;cursor:pointer;">
        Understood
      </button>
    </div>
  `;
  document.body.appendChild(modal);
}

// ---- HEALTH TIPS ----
const healthTips = [
  { icon: '💧', tip: '<strong>Stay Hydrated:</strong> Drink at least 8 glasses of water daily for optimal organ function.' },
  { icon: '🏃', tip: '<strong>Daily Exercise:</strong> 30 minutes of moderate activity reduces heart disease risk by 35%.' },
  { icon: '😴', tip: '<strong>Quality Sleep:</strong> 7–9 hours of sleep per night strengthens your immune system.' },
  { icon: '🥦', tip: '<strong>Eat Your Greens:</strong> Leafy vegetables provide essential vitamins and minerals.' },
  { icon: '🧘', tip: '<strong>Manage Stress:</strong> Chronic stress elevates cortisol and weakens immunity.' },
  { icon: '🩺', tip: '<strong>Regular Checkups:</strong> Annual health screenings catch problems before they escalate.' },
];

function initHealthTips(containerId) {
  const el = document.getElementById(containerId);
  if (!el) return;
  let idx = 0;
  const render = () => {
    const t = healthTips[idx];
    el.innerHTML = `
      <div class="tips-ticker anim-fade-up">
        <span class="tips-icon">${t.icon}</span>
        <div class="tips-text">${t.tip}</div>
        <button onclick="nextTip()" style="flex-shrink:0;background:rgba(255,255,255,.2);border:none;color:white;width:28px;height:28px;border-radius:50%;cursor:pointer;font-size:.8rem;">
          <i class="fas fa-chevron-right"></i>
        </button>
      </div>`;
  };
  window.nextTip = () => { idx = (idx+1)%healthTips.length; render(); };
  render();
  setInterval(window.nextTip, 6000);
}

// ---- GREETING ----
function getGreeting() {
  const h = new Date().getHours();
  if (h < 12) return 'Good morning';
  if (h < 17) return 'Good afternoon';
  return 'Good evening';
}

// ---- DATE HELPER ----
function formatDate(d) {
  return new Date(d).toLocaleDateString('en-IN', { day:'2-digit', month:'short', year:'numeric' });
}

// ---- INIT ALL ----
async function initPortal(pageId, title, subtitle) {
  const user = await checkAuth('patient');
  if (!user) return null;

  buildSidebar(pageId, user);
  buildTopbar(title, subtitle || `Welcome back, ${user.name}`);
  initMobileSidebar();

  // Update specific elements if they exist
  const greetMsg = document.getElementById('greetMsg');
  if (greetMsg) greetMsg.textContent = `${getGreeting()}, ${user.name.split(' ')[0]} 👋`;

  return user;
}
