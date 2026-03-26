// Sanjeevani Shared Navigation
function getCurrentPage() {
  const path = window.location.pathname;
  const file = path.split('/').pop() || 'admin-dashboard.html';
  return file;
}

// ---- AUTH CHECK ----
async function checkAuth(requiredRole = 'admin') {
  try {
    const resp = await fetch('/Hospital-Mangement-T10-/backend/patient/get_user.php');
    if (!resp.ok) {
      window.location.href = '/Hospital-Mangement-T10-/frontend/auth/login.html';
      return null;
    }
    const data = await resp.json();
    if (!data.success || data.user.role !== requiredRole) {
      window.location.href = '/Hospital-Mangement-T10-/frontend/auth/login.html';
      return null;
    }
    return data.user;
  } catch (err) {
    window.location.href = '/Hospital-Mangement-T10-/frontend/auth/login.html';
    return null;
  }
}

function renderSidebar(user) {
  const current = getCurrentPage();
  const nav = [
    { href: 'admin-dashboard.html', icon: dashboardIcon(), label: 'Dashboard', section: 'main' },
    { href: 'manage-doctors.html', icon: doctorIcon(), label: 'Manage Doctors', section: 'main' },
    { href: 'manage-patients.html', icon: patientsIcon(), label: 'Manage Patients', section: 'main' },
    { href: 'appointment-analytics.html', icon: analyticsIcon(), label: 'Appointment Analytics', section: 'main' },
    { href: 'settings.html', icon: settingsIcon(), label: 'Settings', section: 'system' },
    { href: '../../backend/auth/logout.php', icon: logoutIcon(), label: 'Logout', section: 'system', logout: true },
  ];

  let mainItems = '', systemItems = '';

  nav.forEach(item => {
    const isActive = item.href === current;
    const badgeHtml = item.badge ? `<span class="nav-badge">${item.badge}</span>` : '';
    const itemHtml = `
      <a href="${item.href}" class="nav-item ${isActive ? 'active' : ''}" ${item.logout ? 'onclick="return confirm(\'Logout from Sanjeevani?\')"' : ''}>
        <span class="nav-icon">${item.icon}</span>
        ${item.label}
        ${badgeHtml}
      </a>`;
    if (item.section === 'main') mainItems += itemHtml;
    else systemItems += itemHtml;
  });

  const userName = user ? user.name : 'Admin';
  const initials = userName.split(' ').map(n=>n[0]).join('').toUpperCase().substring(0,2);

  return `
    <aside class="sidebar">
      <div class="sidebar-brand">
        <div class="brand-logo">
          <div class="brand-icon">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path d="M19 3H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2V5a2 2 0 00-2-2zm-7 3a1 1 0 011 1v3h3a1 1 0 010 2h-3v3a1 1 0 01-2 0v-3H8a1 1 0 010-2h3V7a1 1 0 011-1z"/>
            </svg>
          </div>
          <div class="brand-text">
            <span class="brand-name">Sanjeevani</span>
            <span class="brand-sub">Hospital Admin</span>
          </div>
        </div>
      </div>
      <div class="sidebar-admin">
        <div class="admin-avatar">${initials}</div>
        <div class="admin-info">
          <span class="admin-name">${userName}</span>
          <span class="admin-role">Super Admin</span>
        </div>
      </div>
      <nav class="sidebar-nav">
        <div class="nav-section-label">Main Menu</div>
        ${mainItems}
        <div class="nav-section-label" style="margin-top:8px">System</div>
        ${systemItems}
      </nav>
      <div class="sidebar-footer">
        <div class="sidebar-version">Sanjeevani v2.4.1 &bull; 2025</div>
      </div>
    </aside>`;
}

function renderTopbar(title, subtitle) {
  return `
    <header class="topbar">
      <div class="topbar-title">${title} ${subtitle ? `<span class="topbar-subtitle">/ ${subtitle}</span>` : ''}</div>
      <div class="topbar-actions">
        <button class="topbar-btn" title="Search">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
          </svg>
        </button>
        <button class="topbar-btn" title="Profile">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
          </svg>
        </button>
      </div>
    </header>`;
}

// Icon SVGs
function dashboardIcon() {
  return `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>`;
}
function doctorIcon() {
  return `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/><path d="M16 3.5c1.5.5 2.5 2 2.5 3.5"/></svg>`;
}
function patientsIcon() {
  return `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>`;
}
function analyticsIcon() {
  return `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>`;
}
function reportsIcon() {
  return `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>`;
}
function bellIcon() {
  return `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>`;
}
function settingsIcon() {
  return `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>`;
}
function logoutIcon() {
  return `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>`;
}

async function initPage(title, subtitle) {
  const user = await checkAuth('admin');
  if (!user) return;
  document.body.insertAdjacentHTML('afterbegin', renderSidebar(user) + `<div class="main-wrapper">${renderTopbar(title, subtitle)}<div class="page-content" id="pageContent"></div></div>`);
}
