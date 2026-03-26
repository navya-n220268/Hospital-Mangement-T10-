/* =====================================================
   Sanjeevani ADMIN PORTAL — SHARED JAVASCRIPT
   ===================================================== */

const NAV_ITEMS = [
  { id: 'admin-dashboard',    href: 'admin-dashboard.html',      icon: 'fas fa-th-large',       label: 'Dashboard',          section: 'OVERVIEW' },
  { id: 'manage-doctors',     href: 'manage-doctors.html',       icon: 'fas fa-user-md',        label: 'Manage Doctors',     section: 'MANAGEMENT' },
  { id: 'manage-patients',    href: 'manage-patients.html',      icon: 'fas fa-users',          label: 'Manage Patients',    section: 'MANAGEMENT' },
  { id: 'manage-appointments',href: 'appointment-analytics.html',icon: 'fas fa-calendar-alt',   label: 'Appointments',       section: 'MANAGEMENT' },
  { id: 'leave-management',   href: 'leave-management.html',     icon: 'fas fa-calendar-minus', label: 'Leave Management',   section: 'MANAGEMENT' },
  { id: 'notifications',      href: 'notifications.html',        icon: 'fas fa-bell',           label: 'Notifications',      section: 'MANAGEMENT', badgeId: 'adminNotifBadge' },
  { id: 'profile-settings',   href: 'settings.html',             icon: 'fas fa-cog',            label: 'Settings',           section: 'ACCOUNT' },
];

async function checkAuthAdmin() {
  try {
    const resp = await fetch('/Hospital-Mangement-T10-/backend/patient/get_user.php');
    if (!resp.ok) {
      window.location.href = '/Hospital-Mangement-T10-/frontend/auth/login.html';
      return null;
    }
    const data = await resp.json();
    if (!data.success || data.user.role !== 'admin') {
      window.location.href = '/Hospital-Mangement-T10-/frontend/auth/login.html';
      return null;
    }
    return data.user;
  } catch (err) {
    window.location.href = '/Hospital-Mangement-T10-/frontend/auth/login.html';
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
        navHTML += `<div class="sidebar-section-label">${item.section}</div>`;
        lastSec = item.section;
      }
      navHTML += `
        <a href="${item.href}" class="nav-item ${pageId === item.id ? 'active' : ''}" id="nav-${item.id}">
          <i class="nav-icon ${item.icon}"></i>
          <span>${item.label}</span>
          ${item.badgeId ? `<span class="nav-badge" id="${item.badgeId}" style="display:none">0</span>` : ''}
        </a>`;
    });

    const initials = (user.name || 'Admin').split(' ').map(n=>n[0]).join('').toUpperCase().substring(0,2);

    sb.innerHTML = `
      <div class="sidebar-brand">
        <div class="brand-logo">
          <div class="brand-icon"><i class="fas fa-heartbeat"></i></div>
          <div>
            <div class="brand-name">Sanjeevani</div>
            <div class="brand-sub">Admin Portal</div>
          </div>
        </div>
      </div>
      <nav class="sidebar-nav">${navHTML}</nav>
      <div class="sidebar-footer">
        <div class="admin-profile" style="margin-bottom: 12px;">
          <div class="admin-avatar">${initials}</div>
          <div>
            <div class="admin-name">${user.name}</div>
            <div class="admin-role">Administrator</div>
          </div>
        </div>
        <a href="/Hospital-Mangement-T10-/backend/auth/logout.php" class="nav-item" style="color: var(--danger);">
          <i class="nav-icon fas fa-sign-out-alt"></i>
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
      <div class="topbar-left" style="display: flex; align-items: center;">
        <button class="mob-toggle" id="mobToggle"><i class="fas fa-bars"></i></button>
        <div class="tb-title">
          <h1>${pageTitle}</h1>
          ${pageSubtitle ? `<p>${pageSubtitle}</p>` : `<p>${greet}, ${user.name.split(' ')[0]} 👋</p>`}
        </div>
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

// Load unread admin notification count and show badge
async function loadAdminNotifBadge() {
  try {
    const resp = await fetch('/Hospital-Mangement-T10-/backend/admin/get_notifications.php');
    const data = await resp.json();
    if (data.success) {
      const count = (data.notifications || []).filter(n => n.status === 'unread').length;
      const badge = document.getElementById('adminNotifBadge');
      if (badge) {
        if (count > 0) { badge.textContent = count; badge.style.display = ''; }
        else badge.style.display = 'none';
      }
    }
  } catch (e) { /* silent fail */ }
}

// Auto-refresh badge every 30s
setInterval(loadAdminNotifBadge, 30000);
