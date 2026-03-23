/* =====================================================
   MEDIVITA ADMIN PORTAL — SHARED JAVASCRIPT
   ===================================================== */

const NAV_ITEMS = [
  { id: 'admin-dashboard',   href: 'admin-dashboard.html',  icon: 'fas fa-th-large',       label: 'Dashboard',        section: 'OVERVIEW' },
  { id: 'manage-doctors',    href: 'manage-doctors.html',   icon: 'fas fa-user-md',        label: 'Manage Doctors',   section: 'MANAGEMENT' },
  { id: 'manage-patients',   href: 'manage-patients.html',  icon: 'fas fa-users',          label: 'Manage Patients',  section: 'MANAGEMENT' },
  { id: 'manage-appointments',href: 'appointment-analytics.html', icon: 'fas fa-calendar-alt',label: 'Appointments',    section: 'MANAGEMENT' },
  { id: 'manage-departments',href: '#',                     icon: 'fas fa-hospital',       label: 'Departments',      section: 'MANAGEMENT' },
  { id: 'profile-settings',  href: 'settings.html',         icon: 'fas fa-cog',            label: 'Settings',         section: 'ACCOUNT' },
];

async function checkAuthAdmin() {
  try {
    const resp = await fetch('/medivita/backend/patient/get_user.php');
    if (!resp.ok) {
      window.location.href = '/medivita/frontend/auth/login.html';
      return null;
    }
    const data = await resp.json();
    if (!data.success || data.user.role !== 'admin') {
      window.location.href = '/medivita/frontend/auth/login.html';
      return null;
    }
    return data.user;
  } catch (err) {
    window.location.href = '/medivita/frontend/auth/login.html';
    return null;
  }
}

async function buildAdminPortal(pageId, pageTitle, pageSubtitle) {
  const user = await checkAuthAdmin();
  if (!user) return null;

  // Sidebar
  const sb = document.querySelector('.sidebar');
  if (sb) {
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

    const initials = (user.name || 'Admin').split(' ').map(n=>n[0]).join('').toUpperCase().substring(0,2);

    sb.innerHTML = `
      <div class="sb-top">
        <div class="sb-logo">
          <div class="sb-logo-mark"><i class="fas fa-heartbeat"></i></div>
          <div>
            <span class="sb-brand-name">MediVita</span>
            <span class="sb-brand-sub">Admin Portal</span>
          </div>
        </div>
      </div>
      <div class="sb-doctor">
        <div class="sb-avatar" style="background:linear-gradient(135deg,var(--blue-900),var(--blue))">${initials}</div>
        <div style="min-width:0">
          <div class="sb-doc-name">${user.name}</div>
          <div class="sb-doc-status">
            <span class="sb-dot" style="background:var(--green)"></span>
            <span>Administrator</span>
          </div>
        </div>
      </div>
      <nav class="sb-nav">${navHTML}</nav>
      <div class="sb-footer">
        <a href="/medivita/backend/auth/logout.php" class="sb-link sb-danger">
          <i class="fas fa-sign-out-alt"></i>
          <span>Logout</span>
        </a>
      </div>`;
  }

  // Topbar
  const tb = document.querySelector('.topbar');
  if (tb) {
    const today = new Date().toLocaleDateString('en-IN', { weekday:'long', day:'2-digit', month:'long', year:'numeric' });
    
    // Quick greeting logic
    const hr = new Date().getHours();
    let greet = "Good evening";
    if (hr < 12) greet = "Good morning";
    else if (hr < 17) greet = "Good afternoon";

    tb.innerHTML = `
      <button class="mob-toggle" id="mobToggle"><i class="fas fa-bars"></i></button>
      <div class="tb-title">
        <h1>${pageTitle}</h1>
        ${pageSubtitle ? `<p>${pageSubtitle}</p>` : `<p>${greet}, ${user.name.split(' ')[0]} 👋</p>`}
      </div>
      <div class="tb-right">
        <div style="background:white;border:1px solid var(--border);padding:8px 16px;border-radius:99px;font-size:.8rem;font-weight:600;color:var(--text-2);box-shadow:var(--sh-xs); display:flex; align-items:center; gap:8px">
          <i class="fas fa-clock" style="color:var(--blue)"></i> ${today}
        </div>
      </div>`;
    
    // Mobile toggle
    document.getElementById('mobToggle')?.addEventListener('click', () => {
      document.querySelector('.sidebar').classList.add('active');
      document.querySelector('.sb-overlay').classList.add('active');
    });
  }

  // Overlay
  const overlay = document.querySelector('.sb-overlay');
  if (overlay) {
    overlay.addEventListener('click', () => {
      document.querySelector('.sidebar').classList.remove('active');
      overlay.classList.remove('active');
    });
  }

  return user;
}
