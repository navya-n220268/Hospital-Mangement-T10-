<?php
/**
 * Sanjeevani Hospital Management System
 * ─────────────────────────────────────
 * seed_appointments.php
 *
 * Visit: http://localhost/Hospital-Mangement-T10-/seed_appointments.php
 *
 * 1. Adds 'department' field to existing doctors in MongoDB if missing.
 * 2. Inserts 5 sample appointments into the 'appointments' collection.
 * 3. Links appointments to real patient and doctor IDs from the database.
 *
 * Run once to populate the database for testing.
 */

session_start();
require_once __DIR__ . '/config.php';

$db = getDB();
$log = [];

// ══════════════════════════════════════════════════════════════════════════════
// STEP 1 — Add 'department' field to doctors who only have 'specialization'
// ══════════════════════════════════════════════════════════════════════════════

$specializationToDept = [
    'General Physician'  => 'General Medicine',
    'General Medicine'   => 'General Medicine',
    'Cardiologist'       => 'Cardiology',
    'Cardiology'         => 'Cardiology',
    'Neurologist'        => 'Neurology',
    'Neurology'          => 'Neurology',
    'Orthopedic'         => 'Orthopedics',
    'Orthopedics'        => 'Orthopedics',
    'Dermatologist'      => 'Dermatology',
    'Dermatology'        => 'Dermatology',
    'Pediatrician'       => 'Pediatrics',
    'Pediatrics'         => 'Pediatrics',
    'ENT Specialist'     => 'ENT',
    'ENT'                => 'ENT',
    'Gynecologist'       => 'Gynecology',
    'Gynecology'         => 'Gynecology',
    'Psychiatrist'       => 'Psychiatry',
    'Psychiatry'         => 'Psychiatry',
    'Radiologist'        => 'Radiology',
    'Radiology'          => 'Radiology',
    'Ophthalmologist'    => 'Ophthalmology',
    'Ophthalmology'      => 'Ophthalmology',
    'Endocrinologist'    => 'Endocrinology',
    'Endocrinology'      => 'Endocrinology',
    'Pulmonologist'      => 'Pulmonology',
    'Pulmonology'        => 'Pulmonology',
    'Nephrologist'       => 'Nephrology',
    'Nephrology'         => 'Nephrology',
    'Gastroenterologist' => 'Gastroenterology',
    'Gastroenterology'   => 'Gastroenterology',
    'Oncologist'         => 'Oncology',
    'Oncology'           => 'Oncology',
];

$doctorsUpdated = 0;
$allDoctors = $db->doctors->find([]);
foreach ($allDoctors as $doc) {
    // Only update doctors who don't already have a 'department' field
    if (empty($doc['department'])) {
        $spec = $doc['specialization'] ?? '';
        $dept = $specializationToDept[$spec] ?? $spec;
        if (!empty($dept)) {
            $db->doctors->updateOne(
                ['_id' => $doc['_id']],
                ['$set' => ['department' => $dept]]
            );
            $doctorsUpdated++;
        }
    }
}
$log[] = "✓ Updated $doctorsUpdated doctor(s) with 'department' field.";

// ══════════════════════════════════════════════════════════════════════════════
// STEP 2 — Fetch real patient and doctor IDs for sample appointments
// ══════════════════════════════════════════════════════════════════════════════

// Get first 3 patients from the database
$patients = [];
$patientCursor = $db->patients->find([], ['limit' => 3]);
foreach ($patientCursor as $p) {
    $patients[] = [
        'id'   => (string)$p['_id'],
        'name' => $p['full_name'] ?? $p['name'] ?? 'Patient',
    ];
}

// Get first 5 doctors from the database
$doctors = [];
$doctorCursor = $db->doctors->find([], ['limit' => 5]);
foreach ($doctorCursor as $d) {
    $docs[] = [
        'id'         => (string)$d['_id'],
        'name'       => $d['full_name'] ?? $d['name'] ?? 'Doctor',
        'department' => $d['department'] ?? $d['specialization'] ?? 'General Medicine',
    ];
}

if (empty($patients) || empty($docs)) {
    $log[] = '⚠ No patients or doctors found — please run seed_data.php first!';
} else {
    // ══════════════════════════════════════════════════════════════════════════
    // STEP 3 — Insert 5 sample appointments
    // ══════════════════════════════════════════════════════════════════════════

    $sampleAppointments = [
        [
            'patient_id'       => $patients[0]['id'],
            'patient_name'     => $patients[0]['name'],
            'doctor_id'        => $docs[0]['id'],
            'doctor_name'      => $docs[0]['name'],
            'department'       => $docs[0]['department'],
            'appointment_date' => '2026-03-20',
            'appointment_time' => '09:00 AM',
            'appointment_type' => 'consultation',
            'reason'           => 'Routine health checkup and blood pressure monitoring.',
            'status'           => 'pending',
        ],
        [
            'patient_id'       => $patients[0]['id'],
            'patient_name'     => $patients[0]['name'],
            'doctor_id'        => $docs[1]['id'],
            'doctor_name'      => $docs[1]['name'],
            'department'       => $docs[1]['department'],
            'appointment_date' => '2026-03-25',
            'appointment_time' => '11:00 AM',
            'appointment_type' => 'followup',
            'reason'           => 'Follow-up visit for medication review.',
            'status'           => 'approved',
        ],
        [
            'patient_id'       => isset($patients[1]) ? $patients[1]['id'] : $patients[0]['id'],
            'patient_name'     => isset($patients[1]) ? $patients[1]['name'] : $patients[0]['name'],
            'doctor_id'        => $docs[2]['id'],
            'doctor_name'      => $docs[2]['name'],
            'department'       => $docs[2]['department'],
            'appointment_date' => '2026-03-18',
            'appointment_time' => '02:00 PM',
            'appointment_type' => 'teleconsult',
            'reason'           => 'Online consultation for headache and dizziness.',
            'status'           => 'pending',
        ],
        [
            'patient_id'       => isset($patients[1]) ? $patients[1]['id'] : $patients[0]['id'],
            'patient_name'     => isset($patients[1]) ? $patients[1]['name'] : $patients[0]['name'],
            'doctor_id'        => $docs[3]['id'],
            'doctor_name'      => $docs[3]['name'],
            'department'       => $docs[3]['department'],
            'appointment_date' => '2026-02-28',
            'appointment_time' => '10:30 AM',
            'appointment_type' => 'consultation',
            'reason'           => 'Knee pain evaluation and X-ray review.',
            'status'           => 'completed',
        ],
        [
            'patient_id'       => isset($patients[2]) ? $patients[2]['id'] : $patients[0]['id'],
            'patient_name'     => isset($patients[2]) ? $patients[2]['name'] : $patients[0]['name'],
            'doctor_id'        => $docs[4]['id'],
            'doctor_name'      => $docs[4]['name'],
            'department'       => $docs[4]['department'],
            'appointment_date' => '2026-03-30',
            'appointment_time' => '03:30 PM',
            'appointment_type' => 'consultation',
            'reason'           => 'Skin rash and allergy assessment.',
            'status'           => 'pending',
        ],
    ];

    $now = new MongoDB\BSON\UTCDateTime();
    $inserted = 0;
    foreach ($sampleAppointments as $appt) {
        $result = $db->appointments->insertOne(array_merge($appt, [
            'created_at' => $now,
            'updated_at' => $now,
        ]));
        if ($result->getInsertedId()) $inserted++;
    }
    $log[] = "✓ Inserted $inserted sample appointment(s).";
}

// ══════════════════════════════════════════════════════════════════════════════
// HTML Output
// ══════════════════════════════════════════════════════════════════════════════
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<title>Sanjeevani — Seed Appointments</title>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap"/>
<style>
  body{font-family:'Plus Jakarta Sans',sans-serif;background:#0f172a;color:#e2e8f0;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0}
  .card{background:#1e293b;border:1px solid #334155;border-radius:18px;padding:40px 48px;max-width:580px;width:100%;box-shadow:0 20px 60px rgba(0,0,0,.5)}
  h1{font-size:1.5rem;font-weight:800;color:#fff;margin-bottom:6px}
  p.sub{font-size:.85rem;color:#94a3b8;margin-bottom:28px}
  .log-item{display:flex;align-items:flex-start;gap:10px;padding:14px 18px;background:#0f172a;border-radius:12px;margin-bottom:10px;font-size:.84rem;color:#94a3b8}
  .log-item.ok{border-left:3px solid #34d399}
  .log-item.warn{border-left:3px solid #fbbf24;color:#fbbf24}
  .divider{border:none;border-top:1px solid #334155;margin:24px 0}
  .btn{display:inline-flex;align-items:center;gap:8px;padding:12px 24px;background:linear-gradient(135deg,#2563eb,#3b82f6);color:white;border-radius:99px;font-size:.85rem;font-weight:700;text-decoration:none;cursor:pointer;border:none;transition:.2s}
  .btn:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(37,99,235,.5)}
  .btn.green{background:linear-gradient(135deg,#059669,#10b981)}
  .logo{display:flex;align-items:center;gap:10px;margin-bottom:24px}
  .logo-mark{width:40px;height:40px;background:linear-gradient(135deg,#2563eb,#2dd4bf);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.2rem}
  .logo-text{font-size:1.1rem;font-weight:800;color:#fff}
  .logo-text small{display:block;font-size:.6rem;font-weight:400;color:#64748b;letter-spacing:.1em;text-transform:uppercase}
  .note{font-size:.76rem;color:#64748b;margin-top:20px;line-height:1.7}
</style>
</head>
<body>
<div class="card">
  <div class="logo">
    <div class="logo-mark">🏥</div>
    <div class="logo-text">Sanjeevani<small>Appointment Seed</small></div>
  </div>
  <h1>Appointment Setup Complete</h1>
  <p class="sub">MongoDB appointments collection has been seeded with sample data.</p>

  <?php foreach ($log as $line): ?>
    <div class="log-item <?= str_contains($line,'⚠') ? 'warn' : 'ok' ?>">
      <?= htmlspecialchars($line) ?>
    </div>
  <?php endforeach; ?>

  <hr class="divider"/>

  <div style="display:flex;gap:12px;flex-wrap:wrap">
    <a href="login.html" class="btn">🔐 Go to Login</a>
    <a href="dashboard.html" class="btn green">📊 Patient Dashboard</a>
  </div>

  <p class="note">
    ✅ Collections seeded: <strong style="color:#e2e8f0">appointments</strong><br/>
    Doctors have been updated with a <code>department</code> field.<br/>
    Test patient: <strong style="color:#e2e8f0">ravi1@Sanjeevani.com / 123456</strong>
  </p>
</div>
</body>
</html>
