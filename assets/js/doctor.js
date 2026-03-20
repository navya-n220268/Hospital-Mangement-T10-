/* =====================================================
   MEDIVITA DOCTOR PORTAL — SHARED JAVASCRIPT
   ===================================================== */

// ── SIDEBAR BUILD ───────────────────────────────────────
const NAV_ITEMS = [
  { id: 'doctor-dashboard', href: '/medivita/frontend/doctor/doctor-dashboard.php', icon: 'fas fa-th-large', label: 'Dashboard', section: 'OVERVIEW' },
  { id: 'schedules', href: '/medivita/frontend/doctor/schedules.html', icon: 'fas fa-calendar-alt', label: 'Schedules', section: 'SCHEDULE' },
  { id: 'patient-history', href: '/medivita/frontend/doctor/patient-history.html', icon: 'fas fa-users', label: 'Patient History', section: 'SCHEDULE' },
  { id: 'add-prescription', href: '/medivita/frontend/doctor/add-prescription.html', icon: 'fas fa-file-prescription', label: 'Add Prescription', section: 'CLINICAL' },
  { id: 'messages', href: '/medivita/frontend/doctor/doctor-messages.html', icon: 'fas fa-comment-dots', label: 'Messages', section: 'CLINICAL' },
  { id: 'profile', href: '/medivita/frontend/doctor/doctor_profile.html', icon: 'fas fa-user-circle', label: 'Profile Settings', section: 'ACCOUNT' },
];

// ── AUTH CHECK ──────────────────────────────────────────
async function checkAuth(requiredRole = 'doctor') {
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

async function buildPortal(pageId, pageTitle, pageSubtitle) {
  const user = await checkAuth('doctor');
  if (!user) return null;

  // Sidebar
  const sb = document.querySelector('.sidebar');
  let lastSec = '';
  let navHTML = '';
  NAV_ITEMS.forEach(item => {
    if (item.section !== lastSec) {
      navHTML += `<div class="sb-sec">${item.section}</div>`;
      lastSec = item.section;
    }
    navHTML += `
      <a href="${item.href}" class="sb-link ${pageId === item.id ? 'active' : ''}">
        <i class="${item.icon}"></i>
        <span>${item.label}</span>
        ${item.badge ? `<span class="sb-num">${item.badge}</span>` : ''}
      </a>`;
  });

  sb.innerHTML = `
    <div class="sb-top">
      <div class="sb-logo">
        <div class="sb-logo-mark"><i class="fas fa-heartbeat"></i></div>
        <div>
          <span class="sb-brand-name">MediVita</span>
          <span class="sb-brand-sub">Doctor Portal</span>
        </div>
      </div>
    </div>
    <div class="sb-doctor">
      <div class="sb-avatar">${user.name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2)}</div>
      <div style="min-width:0">
        <div class="sb-doc-name">${user.name}</div>
        <div class="sb-doc-status">
          <span class="sb-dot"></span>
          <span>Doctor · On Duty</span>
        </div>
      </div>
    </div>
    <nav class="sb-nav">${navHTML}</nav>
    <div class="sb-footer">
      <a href="#" class="sb-link sb-danger" onclick="confirmLogout(event)">
        <i class="fas fa-sign-out-alt"></i>
        <span>Logout</span>
      </a>
    </div>`;

  // Topbar
  const tb = document.querySelector('.topbar');
  const today = new Date().toLocaleDateString('en-IN', { weekday: 'long', day: '2-digit', month: 'long', year: 'numeric' });
  tb.innerHTML = `
    <button class="mob-toggle" id="mobToggle"><i class="fas fa-bars"></i></button>
    <div class="tb-title">
      <h1>${pageTitle}</h1>
      ${pageSubtitle ? `<p>${pageSubtitle}</p>` : `<p>${getGreeting()}, Dr. ${user.name.split(' ').pop()} 👋</p>`}
    </div>
    <div class="tb-right">
      <button class="tb-alert" onclick="showEmergencyAlert()">
        <i class="fas fa-exclamation-triangle"></i>
        <span>Emergency</span>
      </button>
      <button class="tb-btn" onclick="window.location.href = '/medivita/frontend/doctor/doctor-messages.html'" title="Messages">
        <i class="fas fa-comment-dots"></i>
        <span class="dot"></span>
      </button>
    </div>`;

  // Prevent overriding the PHP injected welcome message
  // const greetMsg = document.getElementById('greet-msg');
  // if (greetMsg) greetMsg.textContent = `${getGreeting()}, Dr. ${user.name.split(' ').pop()} 👨‍⚕️`;

  // Mobile sidebar
  const overlay = document.querySelector('.sb-overlay');
  document.getElementById('mobToggle').addEventListener('click', () => {
    sb.classList.toggle('open');
    overlay.classList.toggle('show');
  });
  overlay.addEventListener('click', () => {
    sb.classList.remove('open');
    overlay.classList.remove('show');
  });

  // Dynamic names injection
  window.currentUser = user;
  let docName = user.name.replace(/^Dr[.,]?\s*/i, '');
  document.querySelectorAll('.dyn-doc-name').forEach(el => {
    if (el) el.textContent = 'Dr. ' + docName;
  });
  if (docName) {
    const parts = docName.split(' ');
    const initials = parts.map(n => n[0]).join('').toUpperCase().substring(0, 2);
    document.querySelectorAll('.dyn-doc-initials').forEach(el => {
      if (el) el.textContent = initials;
    });

    const cFirst = document.getElementById('docFirstName');
    const cLast = document.getElementById('docLastName');
    if (cFirst) cFirst.value = parts[0] || '';
    if (cLast) cLast.value = parts.length > 1 ? parts.slice(1).join(' ') : '';
  }

  return user;
}

// ── EMERGENCY ALERT ─────────────────────────────────────
function showEmergencyAlert() {
  showModal(`
    <div style="text-align:center;padding:8px">
      <div style="width:64px;height:64px;background:var(--red-dim);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:1.5rem;color:var(--red);">
        <i class="fas fa-ambulance"></i>
      </div>
      <h3 style="font-family:var(--f-display);font-size:1rem;font-weight:700;margin-bottom:8px;color:var(--text-1)">Emergency Alert</h3>
      <p style="font-size:.82rem;color:var(--text-3);margin-bottom:6px">Patient in Room 302 requires immediate attention.</p>
      <p style="font-size:.78rem;color:var(--red);font-weight:600;margin-bottom:22px">⚠ Cardiac event reported — Critical</p>
      <div style="display:flex;gap:10px;justify-content:center">
        <button class="btn btn-red btn-sm" onclick="closeModal()"><i class="fas fa-running"></i> Respond Now</button>
        <button class="btn btn-ghost btn-sm" onclick="closeModal()">Dismiss</button>
      </div>
    </div>`);
}

// ── VIDEO CONSULT ────────────────────────────────────────
function startVideoConsult() {
  showModal(`
    <div style="text-align:center;padding:8px">
      <div style="width:64px;height:64px;background:var(--blue-dim);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:1.5rem;color:var(--blue);">
        <i class="fas fa-video"></i>
      </div>
      <h3 style="font-family:var(--f-display);font-size:1rem;font-weight:700;margin-bottom:8px">Start Video Consultation</h3>
      <p style="font-size:.82rem;color:var(--text-3);margin-bottom:16px">Choose a patient to begin the teleconsultation session.</p>
      <select class="form-control" style="margin-bottom:16px">
        <option>Rajiv Mehta — Scheduled 11:30 AM</option>
        <option>Priya Singh — Scheduled 2:00 PM</option>
        <option>Arun Kumar — On Request</option>
      </select>
      <div style="display:flex;gap:10px;justify-content:center">
        <button class="btn btn-primary btn-sm" onclick="closeModal();showToast('Video session starting...')"><i class="fas fa-video"></i> Start Call</button>
        <button class="btn btn-ghost btn-sm" onclick="closeModal()">Cancel</button>
      </div>
    </div>`);
}

// ── MODAL ───────────────────────────────────────────────
function showModal(html) {
  let existing = document.getElementById('portalModal');
  if (existing) existing.remove();
  const modal = document.createElement('div');
  modal.id = 'portalModal';
  modal.style.cssText = `
    position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.55);
    backdrop-filter:blur(6px);display:flex;align-items:center;justify-content:center;
    animation:fadeIn .2s ease;
  `;
  modal.innerHTML = `
    <div style="background:white;border-radius:20px;padding:32px;max-width:400px;width:90%;
      box-shadow:0 20px 60px rgba(0,0,0,.25);animation:bounceIn .3s ease;">
      <button onclick="closeModal()" style="position:absolute;margin-left:calc(100% - 120px);
        background:var(--surface);border:1px solid var(--border);width:28px;height:28px;
        border-radius:50%;cursor:pointer;font-size:.75rem;color:var(--text-3)">✕</button>
      ${html}
    </div>`;
  modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });
  document.body.appendChild(modal);
}
function closeModal() {
  const m = document.getElementById('portalModal');
  if (m) m.remove();
}

// ── TOAST ────────────────────────────────────────────────
function showToast(msg, icon = 'fa-check-circle') {
  let t = document.getElementById('portalToast');
  if (!t) {
    t = document.createElement('div');
    t.id = 'portalToast';
    t.className = 'toast';
    document.body.appendChild(t);
  }
  t.innerHTML = `<i class="fas ${icon}"></i> ${msg}`;
  t.classList.add('show');
  setTimeout(() => t.classList.remove('show'), 3000);
}

// ── LOGOUT ───────────────────────────────────────────────
function confirmLogout(e) {
  e.preventDefault();
  showModal(`
    <div style="text-align:center;padding:8px">
      <div style="font-size:2rem;margin-bottom:14px">👋</div>
      <h3 style="font-family:var(--f-display);font-size:1rem;font-weight:700;margin-bottom:8px">Log out of MediVita?</h3>
      <p style="font-size:.82rem;color:var(--text-3);margin-bottom:22px">Your session will be ended securely.</p>
      <div style="display:flex;gap:10px;justify-content:center">
        <button class="btn btn-red btn-sm" onclick="window.location.href = '/medivita/backend/auth/logout.php'"><i class="fas fa-sign-out-alt"></i> Logout</button>
        <button class="btn btn-ghost btn-sm" onclick="closeModal()">Stay</button>
      </div>
    </div>`);
}

// ── GREETING ─────────────────────────────────────────────
function getGreeting() {
  const h = new Date().getHours();
  if (h < 12) return 'Good morning';
  if (h < 17) return 'Good afternoon';
  return 'Good evening';
}

// CSS for modal animation (injected once)
const styleTag = document.createElement('style');
styleTag.textContent = `
  @keyframes fadeIn{from{opacity:0}to{opacity:1}}
  @keyframes bounceIn{0%{transform:scale(.85);opacity:0}60%{transform:scale(1.04)}100%{transform:scale(1);opacity:1}}
`;
document.head.appendChild(styleTag);
