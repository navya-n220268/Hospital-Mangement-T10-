<?php
session_start();
require_once __DIR__ . '/../config.php';

// 1. Security Check
if (!isset($_SESSION['admin_id']) && ($_SESSION['user_role'] ?? '') !== 'admin') {
    header("Location: login.html");
    exit();
}

try {
    $db = getDB();

    // 2. Dashboard Summary Cards
    $totalPatients = $db->patients->countDocuments([]);
    $totalDoctors = $db->doctors->countDocuments([]);
    $totalAppointments = $db->appointments->countDocuments([]);
    $totalPrescriptions = $db->prescriptions->countDocuments([]);

    // 3. Recent Appointments (Limit 5)
    $recentAppointments = $db->appointments->find(
        [],
        [
            "sort" => ["appointment_date" => -1, "appointment_time" => -1],
            "limit" => 5
        ]
    );

    // 4. Recent Registered Patients (Limit 5)
    $recentPatients = $db->patients->find(
        [],
        [
            "sort" => ["created_at" => -1],
            "limit" => 5
        ]
    );

    // 5. Recent Registered Doctors (Limit 5)
    $recentDoctors = $db->doctors->find(
        [],
        [
            "sort" => ["created_at" => -1],
            "limit" => 5
        ]
    );

} catch (Exception $e) {
    die("Database Error: " . $e->getMessage());
}

// Helper to format dates
function formatDate($dateStr) {
    if (!$dateStr) return '—';
    try {
        if ($dateStr instanceof \MongoDB\BSON\UTCDateTime) {
            return $dateStr->toDateTime()->format('d M Y');
        }
        return date('d M Y', strtotime($dateStr));
    } catch (Exception $e) {
        return $dateStr;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>Admin Dashboard — MediVita</title>
<link rel="stylesheet" href="doctor.css"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
<style>
/* ── Admin Specific Dashboard Styles ── */

.stat-group { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 18px; margin-bottom: 24px; }
.stat-box { background: white; border: 1px solid var(--border); border-radius: var(--r-lg); padding: 22px; box-shadow: var(--sh-sm); position: relative; overflow: hidden; transition: all var(--dur) var(--ease); }
.stat-box:hover { transform: translateY(-3px); box-shadow: var(--sh-md); border-color: var(--blue-lt); }
.sb-icon { width: 52px; height: 52px; border-radius: var(--r-md); display: flex; align-items: center; justify-content: center; font-size: 1.4rem; margin-bottom: 12px; }
.sb-val { font-family: var(--f-display); font-size: 2.2rem; font-weight: 800; color: var(--text-1); line-height: 1; margin-bottom: 6px; }
.sb-lbl { font-size: .85rem; font-weight: 600; color: var(--text-3); }

/* Tables */
.table-card { background: white; border: 1px solid var(--border); border-radius: var(--r-lg); box-shadow: var(--sh-xs); overflow: hidden; margin-bottom: 24px; }
.table-header { background: var(--surface); border-bottom: 1px solid var(--border); padding: 18px 22px; display: flex; align-items: center; gap: 10px; }
.table-header i { font-size: 1.1rem; }
.table-header h2 { font-family: var(--f-display); font-size: 1.15rem; font-weight: 800; color: var(--text-1); }

.resp-table { overflow-x: auto; width: 100%; }
table { width: 100%; border-collapse: collapse; min-width: 600px; }
th { text-align: left; padding: 14px 22px; font-size: .75rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--text-3); border-bottom: 2px solid var(--border); background: white; white-space: nowrap; }
td { padding: 18px 22px; font-size: .88rem; color: var(--text-2); border-bottom: 1px solid var(--surface); vertical-align: middle; }
tbody tr:hover { background: var(--blue-dim); }
tbody tr:last-child td { border-bottom: none; }

/* Cell Content */
.cell-principal { font-family: var(--f-display); font-size: .95rem; font-weight: 700; color: var(--text-1); margin-bottom: 4px; }
.cell-sub { font-size: .75rem; color: var(--text-4); }

.grid-cols { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
@media (max-width: 1024px) { .grid-cols { grid-template-columns: 1fr; } }
</style>
</head>
<body>

<div class="portal-wrap">
  <!-- Dynamic Sidebar -->
  <aside class="sidebar"></aside>
  <div class="sb-overlay"></div>

  <!-- Main Content -->
  <div class="main-area">
    <!-- Dynamic Topbar -->
    <div class="topbar"></div>

    <div class="page-content">

      <!-- Summary Cards -->
      <div class="stat-group afu">
        <div class="stat-box">
          <div class="sb-icon" style="background:var(--blue-dim);color:var(--blue-dk)"><i class="fas fa-users"></i></div>
          <div class="sb-val"><?php echo number_format($totalPatients); ?></div>
          <div class="sb-lbl">Total Patients</div>
          <div style="position:absolute;bottom:-20px;right:-15px;font-size:6rem;color:var(--blue);opacity:.03"><i class="fas fa-users"></i></div>
        </div>
        
        <div class="stat-box d1">
          <div class="sb-icon" style="background:var(--green-dim);color:var(--green-dk)"><i class="fas fa-user-md"></i></div>
          <div class="sb-val"><?php echo number_format($totalDoctors); ?></div>
          <div class="sb-lbl">Total Doctors</div>
          <div style="position:absolute;bottom:-20px;right:-15px;font-size:6rem;color:var(--green);opacity:.03"><i class="fas fa-user-md"></i></div>
        </div>
        
        <div class="stat-box d2">
          <div class="sb-icon" style="background:var(--violet-dim);color:var(--violet-dk)"><i class="fas fa-calendar-check"></i></div>
          <div class="sb-val"><?php echo number_format($totalAppointments); ?></div>
          <div class="sb-lbl">Appointments</div>
          <div style="position:absolute;bottom:-20px;right:-15px;font-size:6rem;color:var(--violet);opacity:.03"><i class="fas fa-calendar-alt"></i></div>
        </div>
        
        <div class="stat-box d3">
          <div class="sb-icon" style="background:var(--amber-dim);color:var(--amber-dk)"><i class="fas fa-file-prescription"></i></div>
          <div class="sb-val"><?php echo number_format($totalPrescriptions); ?></div>
          <div class="sb-lbl">Prescriptions</div>
          <div style="position:absolute;bottom:-20px;right:-15px;font-size:6rem;color:var(--amber);opacity:.05"><i class="fas fa-capsules"></i></div>
        </div>
      </div>

      <!-- Recent Appointments (Full Width) -->
      <div class="table-card afu d2">
        <div class="table-header">
          <i class="fas fa-calendar-alt" style="color:var(--blue)"></i>
          <h2>Recent Appointments</h2>
        </div>
        <div class="resp-table">
          <table>
            <thead>
              <tr>
                <th>Patient Details</th>
                <th>Doctor Assigned</th>
                <th>Schedule</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($recentAppointments as $appt): ?>
              <tr>
                <td>
                  <div class="cell-principal"><?php echo htmlspecialchars($appt['patient_name'] ?? '—'); ?></div>
                </td>
                <td>
                  <div class="cell-principal">Dr. <?php echo htmlspecialchars(str_replace('Dr. ', '', $appt['doctor_name'] ?? '—')); ?></div>
                  <div class="cell-sub"><?php echo htmlspecialchars($appt['department'] ?? '—'); ?></div>
                </td>
                <td>
                  <div class="cell-principal" style="color:var(--blue-dk)"><?php echo formatDate($appt['appointment_date'] ?? null); ?></div>
                  <div class="cell-sub"><i class="far fa-clock"></i> <?php echo htmlspecialchars($appt['appointment_time'] ?? '—'); ?></div>
                </td>
                <td>
                  <?php 
                    $status = strtolower($appt['status'] ?? 'pending');
                    $badge = $status === 'confirmed' ? 'badge-green' : ($status === 'cancelled' ? 'badge-amber' : 'badge-blue');
                  ?>
                  <span class="badge <?php echo $badge; ?>"><?php echo ucfirst($status); ?></span>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php if(empty($recentAppointments)): ?>
              <tr><td colspan="4" style="text-align:center;padding:40px;color:var(--text-4)">No recent appointments found.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Split Grid: Recent Patients | Recent Doctors -->
      <div class="grid-cols afu d3">
        
        <!-- Recent Patients -->
        <div class="table-card" style="margin-bottom:0">
          <div class="table-header">
            <i class="fas fa-user-injured" style="color:var(--emerald)"></i>
            <h2>New Patients</h2>
          </div>
          <div class="resp-table">
            <table style="min-width:100%">
              <thead>
                <tr>
                  <th>Patient Name</th>
                  <th>Registered</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach($recentPatients as $pat): ?>
                <tr>
                  <td>
                    <div class="cell-principal"><?php echo htmlspecialchars($pat['full_name'] ?? $pat['name'] ?? '—'); ?></div>
                    <div class="cell-sub"><?php echo htmlspecialchars($pat['email'] ?? $pat['phone'] ?? '—'); ?></div>
                  </td>
                  <td style="white-space:nowrap">
                    <div class="cell-principal"><?php echo formatDate($pat['created_at'] ?? null); ?></div>
                  </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($recentPatients)): ?>
                <tr><td colspan="2" style="text-align:center;padding:30px;color:var(--text-4)">No recent patients.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Recent Doctors -->
        <div class="table-card" style="margin-bottom:0">
          <div class="table-header">
            <i class="fas fa-stethoscope" style="color:var(--violet)"></i>
            <h2>New Doctors</h2>
          </div>
          <div class="resp-table">
            <table style="min-width:100%">
              <thead>
                <tr>
                  <th>Doctor Name</th>
                  <th>Joined</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach($recentDoctors as $doc): ?>
                <tr>
                  <td>
                    <div class="cell-principal">Dr. <?php echo htmlspecialchars(str_replace('Dr. ', '', $doc['full_name'] ?? $doc['name'] ?? '—')); ?></div>
                    <div class="cell-sub"><?php echo htmlspecialchars($doc['department'] ?? 'General'); ?></div>
                  </td>
                  <td style="white-space:nowrap">
                    <div class="cell-principal"><?php echo formatDate($doc['created_at'] ?? null); ?></div>
                  </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($recentDoctors)): ?>
                <tr><td colspan="2" style="text-align:center;padding:30px;color:var(--text-4)">No recent doctors.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

      </div>

    </div>
  </div>
</div>

<script src="admin.js"></script>
<script>
  buildAdminPortal('admin-dashboard', 'System Overview', 'Live snapshot of modern hospital statistics.');
</script>
</body>
</html>
