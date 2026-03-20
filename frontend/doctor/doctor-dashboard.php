<?php
session_start();
if (empty($_SESSION['doctor_id'])) {
  header('Location: /medivita/frontend/auth/login.html');
  exit;
}
$doctor_name = $_SESSION['doctor_name'] ?? 'Doctor';
$doctor_id = $_SESSION['doctor_id'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1.0" />
  <title>Dashboard — MediVita Doctor Portal</title>
  <meta name="description"
    content="MediVita Doctor Portal Dashboard — view your live schedule and patient appointments." />
  <link rel="stylesheet" href="/medivita/assets/css/doctor.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <style>
    /* ── Welcome hero ── */
    .welcome-hero {
      background: linear-gradient(135deg, var(--navy-2), var(--navy-4));
      border-radius: var(--r-xl);
      padding: 28px 32px;
      display: flex;
      align-items: center;
      gap: 24px;
      color: white;
      position: relative;
      overflow: hidden;
      margin-bottom: 20px;
    }

    .welcome-hero::before {
      content: '';
      position: absolute;
      right: -50px;
      top: -50px;
      width: 200px;
      height: 200px;
      border-radius: 50%;
      background: rgba(14, 165, 233, .07);
    }

    .welcome-hero::after {
      content: '';
      position: absolute;
      right: 60px;
      bottom: -60px;
      width: 140px;
      height: 140px;
      border-radius: 50%;
      background: rgba(20, 184, 166, .06);
    }

    .wh-text h2 {
      font-family: var(--f-display);
      font-size: 1.35rem;
      font-weight: 700;
      margin-bottom: 6px;
      line-height: 1.3;
    }

    .wh-text p {
      font-size: .83rem;
      color: rgba(255, 255, 255, .65);
      max-width: 460px;
      line-height: 1.7;
    }

    .wh-meta {
      display: flex;
      gap: 18px;
      margin-top: 16px;
      flex-wrap: wrap;
    }

    .wh-meta-item {
      display: flex;
      align-items: center;
      gap: 7px;
      font-size: .76rem;
      color: rgba(255, 255, 255, .6);
    }

    .wh-meta-item i {
      color: var(--teal);
    }

    .wh-art {
      position: relative;
      z-index: 1;
      flex-shrink: 0;
    }

    .wh-art-circle {
      width: 90px;
      height: 90px;
      background: rgba(255, 255, 255, .08);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 2.2rem;
      border: 2px solid rgba(255, 255, 255, .12);
    }

    /* ── Quick Actions ── */
    .qa-card {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 9px;
      padding: 18px 14px;
      border-radius: var(--r-lg);
      background: white;
      border: 1.5px solid var(--border);
      cursor: pointer;
      transition: all var(--dur) var(--ease);
      text-align: center;
    }

    .qa-card:hover {
      border-color: var(--blue);
      background: var(--blue-dim);
      transform: translateY(-2px);
      box-shadow: var(--sh-md);
    }

    .qa-icon {
      width: 46px;
      height: 46px;
      border-radius: var(--r-md);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.05rem;
    }

    .qa-card p {
      font-size: .78rem;
      font-weight: 600;
      color: var(--text-2);
    }

    .qa-card:hover p {
      color: var(--blue-dk);
    }

    /* ── Schedule preview ── */
    .sched-item {
      display: flex;
      align-items: center;
      gap: 13px;
      padding: 13px 0;
      border-bottom: 1px solid var(--surface);
    }

    .sched-item:last-child {
      border-bottom: none;
    }

    .sched-avatar {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: .8rem;
      font-weight: 700;
      color: white;
      flex-shrink: 0;
    }

    .sched-info {
      flex: 1;
      min-width: 0;
    }

    .sched-info p {
      font-size: .83rem;
      font-weight: 600;
      color: var(--text-1);
    }

    .sched-info span {
      font-size: .73rem;
      color: var(--text-3);
    }

    .sched-time {
      text-align: right;
      flex-shrink: 0;
    }

    .sched-time p {
      font-size: .8rem;
      font-weight: 700;
      color: var(--blue);
    }

    .sched-time span {
      font-size: .7rem;
      color: var(--text-4);
    }

    /* ── Skeleton ── */
    .skel {
      background: var(--surface);
      border-radius: 8px;
      animation: pulse 1.4s ease-in-out infinite;
    }

    @keyframes pulse {

      0%,
      100% {
        opacity: 1
      }

      50% {
        opacity: .45
      }
    }
  </style>
</head>

<body>
  <div class="portal-wrap">
    <aside class="sidebar"></aside>
    <div class="sb-overlay"></div>
    <div class="main-area">
      <div class="topbar"></div>
      <div class="page-content">

        <!-- Welcome Hero -->
        <div class="welcome-hero afu">
          <div class="wh-text">
            <h2 id="greet-msg">Welcome,
              <?php echo htmlspecialchars($doctor_name); ?> 👨‍⚕️
            </h2>
            <p id="heroSubtitle">Loading your schedule for today…</p>
            <div class="wh-meta">
              <div class="wh-meta-item"><i class="fas fa-id-badge"></i> ID:
                <?php echo htmlspecialchars(substr($doctor_id, -8)); ?>
              </div>
              <div class="wh-meta-item"><i class="fas fa-calendar-day"></i> <span id="todayDate">Today</span></div>
            </div>
          </div>
          <div class="wh-art">
            <div class="wh-art-circle">🩺</div>
          </div>
        </div>

        <!-- Stat Cards (dynamic) -->
        <div class="g4 sec-gap" id="statCards">
          <!-- Filled dynamically -->
          <div class="stat-card afu d1">
            <div class="sc-icon" style="background:var(--blue-dim);color:var(--blue)"><i
                class="fas fa-spinner fa-spin"></i></div>
            <h2 id="statTotal">—</h2>
            <div class="sc-label">Total Appointments</div>
            <div class="sc-trend neu" id="statTotalTrend"><i class="fas fa-circle"></i> All time</div>
          </div>
          <div class="stat-card afu d2">
            <div class="sc-icon" style="background:var(--amber-dim);color:var(--amber)"><i class="fas fa-clock"></i>
            </div>
            <h2 id="statPending">—</h2>
            <div class="sc-label">Pending Review</div>
            <div class="sc-trend warn"><i class="fas fa-exclamation-circle"></i> Awaiting action</div>
          </div>
          <div class="stat-card afu d3">
            <div class="sc-icon" style="background:var(--green-dim);color:var(--green)"><i
                class="fas fa-check-circle"></i></div>
            <h2 id="statCompleted">—</h2>
            <div class="sc-label">Completed</div>
            <div class="sc-trend up"><i class="fas fa-arrow-up"></i> Consultations done</div>
          </div>
          <div class="stat-card afu d4">
            <div class="sc-icon" style="background:var(--violet-dim);color:var(--violet)"><i
                class="fas fa-calendar-check"></i></div>
            <h2 id="statApproved">—</h2>
            <div class="sc-label">Approved / Upcoming</div>
            <div class="sc-trend up"><i class="fas fa-calendar"></i> Scheduled</div>
          </div>
        </div>

        <!-- Middle Row -->
        <div class="g2 sec-gap" style="gap:18px">

          <!-- Schedule Preview -->
          <div class="card afu d2">
            <div class="card-hd">
              <h3><i class="fas fa-calendar-alt"></i> Schedules</h3>
              <a href="/medivita/frontend/doctor/schedules.html" class="btn btn-ghost btn-xs">View All</a>
            </div>
            <div class="card-body">
              <div id="schedPreview">
                <!-- Skeleton placeholders -->
                <div class="skel" style="height:52px;margin-bottom:10px"></div>
                <div class="skel" style="height:52px;margin-bottom:10px"></div>
                <div class="skel" style="height:52px;"></div>
              </div>
              <div style="margin-top:16px">
                <a href="/medivita/frontend/doctor/schedules.html" class="btn btn-primary"
                  style="width:100%;justify-content:center">
                  <i class="fas fa-calendar-alt"></i> Full Schedules
                </a>
              </div>
            </div>
          </div>

          <!-- Right column: Quick Actions -->
          <div style="display:flex;flex-direction:column;gap:18px">
            <div class="card afu d3">
              <div class="card-hd">
                <h3><i class="fas fa-bolt"></i> Quick Actions</h3>
              </div>
              <div class="card-body">
                <div class="g3" style="gap:12px">
                  <div class="qa-card"
                    onclick="window.location.href = '/medivita/frontend/doctor/add-prescription.html'">
                    <div class="qa-icon" style="background:var(--blue-dim);color:var(--blue)"><i
                        class="fas fa-file-prescription"></i></div>
                    <p>Add Prescription</p>
                  </div>
                  <div class="qa-card" onclick="window.location.href = '/medivita/frontend/doctor/schedules.html'">
                    <div class="qa-icon" style="background:var(--teal-dim);color:var(--teal)"><i
                        class="fas fa-calendar-alt"></i></div>
                    <p>View Schedules</p>
                  </div>
                  <div class="qa-card"
                    onclick="window.location.href = '/medivita/frontend/doctor/doctor-messages.html'">
                    <div class="qa-icon" style="background:var(--violet-dim);color:var(--violet)"><i
                        class="fas fa-comment-dots"></i></div>
                    <p>Messages</p>
                  </div>
                  <div class="qa-card"
                    onclick="window.location.href = '/medivita/frontend/doctor/patient-history.html'">
                    <div class="qa-icon" style="background:var(--green-dim);color:var(--green)"><i
                        class="fas fa-users"></i></div>
                    <p>Patient History</p>
                  </div>

                </div>
              </div>
            </div>

            <!-- Today's Summary Card -->
            <div class="card afu d4">
              <div class="card-hd">
                <h3><i class="fas fa-chart-pie"></i> Today's Summary</h3>
              </div>
              <div class="card-body">
                <div id="todaySummary" style="display:flex;flex-direction:column;gap:12px">
                  <div class="skel" style="height:36px"></div>
                  <div class="skel" style="height:36px"></div>
                  <div class="skel" style="height:36px"></div>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div><!-- end page-content -->
    </div>
  </div>

  <script src="/medivita/assets/js/doctor.js?v=3"></script>
  <script>
    // ── Avatar gradients ────────────────────────────────────────────────────────
    const GRADIENTS = [
      'linear-gradient(135deg,#667eea,#764ba2)',
      'linear-gradient(135deg,#f093fb,#f5576c)',
      'linear-gradient(135deg,#4facfe,#00f2fe)',
      'linear-gradient(135deg,#43e97b,#38f9d7)',
      'linear-gradient(135deg,#fa709a,#fee140)',
      'linear-gradient(135deg,#a18cd1,#fbc2eb)',
      'linear-gradient(135deg,#fccb90,#d57eeb)',
      'linear-gradient(135deg,#30cfd0,#330867)',
    ];

    const STATUS_BADGE = {
      pending: '<span class="badge badge-amber"><i class="fas fa-clock" style="font-size:.45rem"></i> Pending</span>',
      approved: '<span class="badge badge-blue"><i class="fas fa-circle" style="font-size:.45rem"></i> Approved</span>',
      completed: '<span class="badge badge-green"><i class="fas fa-check" style="font-size:.45rem"></i> Completed</span>',
      cancelled: '<span class="badge badge-gray"><i class="fas fa-times" style="font-size:.45rem"></i> Cancelled</span>',
    };

    // ── Set today's date label ──────────────────────────────────────────────────
    document.getElementById('todayDate').textContent =
      new Date().toLocaleDateString('en-IN', { weekday: 'long', day: '2-digit', month: 'long' });

    // ── Load dashboard data ─────────────────────────────────────────────────────
    async function loadDashboard() {
      try {
        const res = await fetch('/medivita/backend/doctor/get_doctor_schedules.php');
        const data = await res.json();

        if (!data.success) throw new Error(data.message);

        const schedules = data.schedules || [];
        const stats = data.stats || {};

        // Update stat cards
        document.getElementById('statTotal').textContent = stats.total ?? 0;
        document.getElementById('statPending').textContent = stats.pending ?? 0;
        document.getElementById('statCompleted').textContent = stats.completed ?? 0;
        document.getElementById('statApproved').textContent = stats.approved ?? 0;

        const total = stats.total ?? 0;
        document.getElementById('statTotalTrend').innerHTML =
          `<i class="fas fa-calendar"></i> ${total} in your schedule`;

        // Update hero subtitle
        const now = new Date();
        const pad = n => String(n).padStart(2, '0');
        const todayStr = `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}`;
        const todayAppts = schedules.filter(s => s.appointment_date === todayStr);
        const pending = schedules.filter(s => s.status === 'pending' || s.status === 'approved');
        document.getElementById('heroSubtitle').textContent =
          `You have ${todayAppts.length} appointment${todayAppts.length !== 1 ? 's' : ''} today. ` +
          `${pending.length} patient${pending.length !== 1 ? 's' : ''} awaiting consultation.`;

        // Render schedule preview (show up to 5 upcoming)
        const upcoming = schedules
          .filter(s => !['cancelled', 'completed'].includes(s.status))
          .slice(0, 5);
        renderSchedPreview(upcoming, schedules);

        // Today's summary
        renderTodaySummary(todayAppts);

      } catch (e) {
        document.getElementById('schedPreview').innerHTML =
          `<p style="font-size:.82rem;color:var(--red);text-align:center;padding:16px">
        <i class="fas fa-exclamation-circle"></i> Failed to load schedule.
        <a href="javascript:loadDashboard()" style="color:var(--blue)">Retry</a>
      </p>`;
        console.error('loadDashboard error:', e);
      }
    }

    function renderSchedPreview(upcoming, all) {
      const container = document.getElementById('schedPreview');
      if (all.length === 0) {
        container.innerHTML = `
      <p style="font-size:.82rem;color:var(--text-4);text-align:center;padding:20px">
        <i class="fas fa-calendar-plus" style="font-size:1.4rem;display:block;margin-bottom:8px;color:var(--border-2)"></i>
        No appointments in your schedule yet.<br/>
        <span style="font-size:.74rem">Appointments booked by patients will appear here.</span>
      </p>`;
        return;
      }
      if (upcoming.length === 0) {
        container.innerHTML = `
      <p style="font-size:.82rem;color:var(--text-4);text-align:center;padding:20px">
        <i class="fas fa-check-circle" style="font-size:1.4rem;display:block;margin-bottom:8px;color:var(--green)"></i>
        No upcoming appointments — all up to date!
      </p>`;
        return;
      }

      container.innerHTML = upcoming.map((s, i) => {
        const grad = GRADIENTS[i % GRADIENTS.length];
        const initials = s.patient_name.split(' ').map(w => w[0]).join('').toUpperCase().substring(0, 2);
        const d = new Date(s.appointment_date).toLocaleDateString('en-IN', { day: '2-digit', month: 'short' });
        const badge = STATUS_BADGE[s.status] || `<span class="badge badge-gray">${s.status}</span>`;
        const ageStr = s.patient_age ? `${s.patient_age} yrs` : '';
        const reason = s.reason ? (s.reason.length > 40 ? s.reason.substring(0, 40) + '…' : s.reason) : s.department;

        return `<div class="sched-item">
      <div class="sched-avatar" style="background:${grad}">${initials}</div>
      <div class="sched-info">
        <p>${s.patient_name}${ageStr ? ` <span style="font-weight:400;color:var(--text-4)">(${ageStr})</span>` : ''}</p>
        <span title="${s.reason || ''}">${reason}</span>
      </div>
      <div class="sched-time">
        <p>${s.appointment_time}</p>
        <span>${d}</span>
        <div style="margin-top:3px">${badge}</div>
      </div>
    </div>`;
      }).join('');
    }

    function renderTodaySummary(todayAppts) {
      const container = document.getElementById('todaySummary');
      const pending = todayAppts.filter(a => a.status === 'pending').length;
      const approved = todayAppts.filter(a => a.status === 'approved').length;
      const completed = todayAppts.filter(a => a.status === 'completed').length;
      const total = todayAppts.length;

      if (total === 0) {
        container.innerHTML = `<p style="font-size:.8rem;color:var(--text-4);text-align:center;padding:8px">No appointments today.</p>`;
        return;
      }

      const pct = total > 0 ? Math.round((completed / total) * 100) : 0;
      container.innerHTML = `
    <div style="display:flex;align-items:center;justify-content:space-between;font-size:.82rem;font-weight:600;color:var(--text-2)">
      <span>Today's Appointments</span><span style="color:var(--blue)">${total} total</span>
    </div>
    <div style="background:var(--surface);border-radius:99px;height:8px;overflow:hidden">
      <div style="width:${pct}%;height:100%;background:linear-gradient(90deg,var(--blue),var(--teal));border-radius:99px;transition:width 1s ease"></div>
    </div>
    <div style="display:flex;gap:12px;font-size:.76rem;flex-wrap:wrap">
      <span style="color:var(--green)"><i class="fas fa-check"></i> ${completed} Completed</span>
      <span style="color:var(--blue)"><i class="fas fa-clock"></i> ${approved} Approved</span>
      <span style="color:var(--amber)"><i class="fas fa-hourglass"></i> ${pending} Pending</span>
    </div>
  `;
    }

    // ── Bootstrap ───────────────────────────────────────────────────────────────
    buildPortal('doctor-dashboard', 'Dashboard').then(() => loadDashboard());
  </script>
</body>

</html>