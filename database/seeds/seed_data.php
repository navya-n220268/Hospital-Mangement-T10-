<?php
/**
 * Sanjeevani — Seed Data Script
 * Visit: http://localhost/Hospital-Mangement-T10-/seed_data.php
 *
 * Inserts 20 sample patients and 15 sample doctors into MongoDB Atlas.
 * Run once to populate the database. Duplicate emails are skipped.
 */
session_start();
require_once __DIR__ . '/config.php';

$db = getDB();
$now = new MongoDB\BSON\UTCDateTime(strtotime('2024-01-15') * 1000);

// ── PATIENTS ──────────────────────────────────────────────────────────────────
$patients = [
    ['name'=>'Ravi Kumar',      'email'=>'ravi1@Sanjeevani.com',   'phone'=>'9876543201','age'=>28,'gender'=>'Male',  'blood_group'=>'O+'],
    ['name'=>'Priya Sharma',    'email'=>'priya2@Sanjeevani.com',  'phone'=>'9876543202','age'=>34,'gender'=>'Female','blood_group'=>'A+'],
    ['name'=>'Arjun Singh',     'email'=>'arjun3@Sanjeevani.com',  'phone'=>'9876543203','age'=>45,'gender'=>'Male',  'blood_group'=>'B+'],
    ['name'=>'Sunita Patel',    'email'=>'sunita4@Sanjeevani.com', 'phone'=>'9876543204','age'=>52,'gender'=>'Female','blood_group'=>'AB+'],
    ['name'=>'Mohan Das',       'email'=>'mohan5@Sanjeevani.com',  'phone'=>'9876543205','age'=>61,'gender'=>'Male',  'blood_group'=>'O-'],
    ['name'=>'Kavita Rao',      'email'=>'kavita6@Sanjeevani.com', 'phone'=>'9876543206','age'=>29,'gender'=>'Female','blood_group'=>'B-'],
    ['name'=>'Deepak Nair',     'email'=>'deepak7@Sanjeevani.com', 'phone'=>'9876543207','age'=>38,'gender'=>'Male',  'blood_group'=>'A-'],
    ['name'=>'Ananya Iyer',     'email'=>'ananya8@Sanjeevani.com', 'phone'=>'9876543208','age'=>23,'gender'=>'Female','blood_group'=>'AB-'],
    ['name'=>'Suresh Verma',    'email'=>'suresh9@Sanjeevani.com', 'phone'=>'9876543209','age'=>55,'gender'=>'Male',  'blood_group'=>'O+'],
    ['name'=>'Meera Joshi',     'email'=>'meera10@Sanjeevani.com', 'phone'=>'9876543210','age'=>41,'gender'=>'Female','blood_group'=>'A+'],
    ['name'=>'Rajesh Gupta',    'email'=>'rajesh11@Sanjeevani.com','phone'=>'9876543211','age'=>67,'gender'=>'Male',  'blood_group'=>'B+'],
    ['name'=>'Lakshmi Reddy',   'email'=>'laxmi12@Sanjeevani.com', 'phone'=>'9876543212','age'=>31,'gender'=>'Female','blood_group'=>'O-'],
    ['name'=>'Vikram Bose',     'email'=>'vikram13@Sanjeevani.com','phone'=>'9876543213','age'=>49,'gender'=>'Male',  'blood_group'=>'AB+'],
    ['name'=>'Nisha Chauhan',   'email'=>'nisha14@Sanjeevani.com', 'phone'=>'9876543214','age'=>37,'gender'=>'Female','blood_group'=>'A-'],
    ['name'=>'Arun Saxena',     'email'=>'arun15@Sanjeevani.com',  'phone'=>'9876543215','age'=>58,'gender'=>'Male',  'blood_group'=>'B-'],
    ['name'=>'Pooja Menon',     'email'=>'pooja16@Sanjeevani.com', 'phone'=>'9876543216','age'=>26,'gender'=>'Female','blood_group'=>'O+'],
    ['name'=>'Sandeep Khanna',  'email'=>'sandeep17@Sanjeevani.com','phone'=>'9876543217','age'=>43,'gender'=>'Male', 'blood_group'=>'A+'],
    ['name'=>'Divya Pillai',    'email'=>'divya18@Sanjeevani.com', 'phone'=>'9876543218','age'=>32,'gender'=>'Female','blood_group'=>'AB+'],
    ['name'=>'Rahul Aggarwal',  'email'=>'rahul19@Sanjeevani.com', 'phone'=>'9876543219','age'=>22,'gender'=>'Male',  'blood_group'=>'O-'],
    ['name'=>'Sushma Tiwari',   'email'=>'sushma20@Sanjeevani.com','phone'=>'9876543220','age'=>48,'gender'=>'Female','blood_group'=>'B+'],
];

// ── DOCTORS ───────────────────────────────────────────────────────────────────
$doctors = [
    ['name'=>'Dr. Meena Sharma',     'email'=>'doctor1@Sanjeevani.com',  'phone'=>'9123456701','specialization'=>'General Physician',   'experience'=>'6 years' ],
    ['name'=>'Dr. Arjun Kapoor',     'email'=>'doctor2@Sanjeevani.com',  'phone'=>'9123456702','specialization'=>'Cardiologist',         'experience'=>'12 years'],
    ['name'=>'Dr. Priya Nair',       'email'=>'doctor3@Sanjeevani.com',  'phone'=>'9123456703','specialization'=>'Neurologist',          'experience'=>'9 years' ],
    ['name'=>'Dr. Suresh Rajan',     'email'=>'doctor4@Sanjeevani.com',  'phone'=>'9123456704','specialization'=>'Orthopedic',           'experience'=>'15 years'],
    ['name'=>'Dr. Anita Desai',      'email'=>'doctor5@Sanjeevani.com',  'phone'=>'9123456705','specialization'=>'Dermatologist',        'experience'=>'7 years' ],
    ['name'=>'Dr. Vikram Pillai',    'email'=>'doctor6@Sanjeevani.com',  'phone'=>'9123456706','specialization'=>'Pediatrician',         'experience'=>'11 years'],
    ['name'=>'Dr. Kavita Iyer',      'email'=>'doctor7@Sanjeevani.com',  'phone'=>'9123456707','specialization'=>'ENT Specialist',       'experience'=>'8 years' ],
    ['name'=>'Dr. Rohit Mehta',      'email'=>'doctor8@Sanjeevani.com',  'phone'=>'9123456708','specialization'=>'Gynecologist',         'experience'=>'14 years'],
    ['name'=>'Dr. Shalini Rao',      'email'=>'doctor9@Sanjeevani.com',  'phone'=>'9123456709','specialization'=>'Psychiatrist',         'experience'=>'10 years'],
    ['name'=>'Dr. Amar Joshi',       'email'=>'doctor10@Sanjeevani.com', 'phone'=>'9123456710','specialization'=>'Radiologist',          'experience'=>'5 years' ],
    ['name'=>'Dr. Pooja Saxena',     'email'=>'doctor11@Sanjeevani.com', 'phone'=>'9123456711','specialization'=>'Ophthalmologist',      'experience'=>'13 years'],
    ['name'=>'Dr. Deepak Verma',     'email'=>'doctor12@Sanjeevani.com', 'phone'=>'9123456712','specialization'=>'Endocrinologist',      'experience'=>'8 years' ],
    ['name'=>'Dr. Sunita Khanna',    'email'=>'doctor13@Sanjeevani.com', 'phone'=>'9123456713','specialization'=>'Pulmonologist',        'experience'=>'11 years'],
    ['name'=>'Dr. Ajay Bose',        'email'=>'doctor14@Sanjeevani.com', 'phone'=>'9123456714','specialization'=>'Nephrologist',         'experience'=>'16 years'],
    ['name'=>'Dr. Lakshmi Reddy',    'email'=>'doctor15@Sanjeevani.com', 'phone'=>'9123456715','specialization'=>'Gastroenterologist',   'experience'=>'9 years' ],
];

$password_hash = password_hash('123456', PASSWORD_BCRYPT);

$pInserted = 0; $pSkipped = 0;
foreach ($patients as $p) {
    $existing = $db->patients->findOne(['email' => $p['email']]);
    if ($existing) { $pSkipped++; continue; }
    $db->patients->insertOne(array_merge($p, [
        'password'   => $password_hash,
        'created_at' => $now,
    ]));
    $pInserted++;
}

$dInserted = 0; $dSkipped = 0;
foreach ($doctors as $d) {
    $existing = $db->doctors->findOne(['email' => $d['email']]);
    if ($existing) { $dSkipped++; continue; }
    $db->doctors->insertOne(array_merge($d, [
        'password'   => $password_hash,
        'created_at' => $now,
    ]));
    $dInserted++;
}

// Pretty output
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<title>Sanjeevani — Seed Data</title>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap"/>
<style>
  body{font-family:'Plus Jakarta Sans',sans-serif;background:#0f172a;color:#e2e8f0;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0}
  .card{background:#1e293b;border:1px solid #334155;border-radius:18px;padding:40px 48px;max-width:520px;width:100%;box-shadow:0 20px 60px rgba(0,0,0,.5)}
  h1{font-size:1.5rem;font-weight:800;color:#fff;margin-bottom:6px}
  p.sub{font-size:.85rem;color:#94a3b8;margin-bottom:28px}
  .stat{display:flex;align-items:center;justify-content:space-between;padding:14px 18px;background:#0f172a;border-radius:12px;margin-bottom:12px}
  .stat-label{font-size:.82rem;color:#94a3b8}
  .stat-nums{display:flex;gap:16px}
  .num-badge{display:flex;align-items:center;gap:6px;font-size:.78rem;font-weight:700}
  .green{color:#34d399}.yellow{color:#fbbf24}
  .divider{border:none;border-top:1px solid #334155;margin:24px 0}
  .btn{display:inline-flex;align-items:center;gap:8px;padding:12px 24px;background:linear-gradient(135deg,#2563eb,#3b82f6);color:white;border-radius:99px;font-size:.85rem;font-weight:700;text-decoration:none;cursor:pointer;border:none;transition:.2s}
  .btn:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(37,99,235,.5)}
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
    <div class="logo-text">Sanjeevani<small>Seed Tool</small></div>
  </div>
  <h1>Database Seeded Successfully</h1>
  <p class="sub">Sample data has been inserted into MongoDB Atlas — Sanjeevani_hospital</p>

  <div class="stat">
    <div>
      <div class="stat-label">Patients Collection</div>
    </div>
    <div class="stat-nums">
      <div class="num-badge green">✓ <?= $pInserted ?> inserted</div>
      <div class="num-badge yellow">↷ <?= $pSkipped ?> skipped</div>
    </div>
  </div>

  <div class="stat">
    <div>
      <div class="stat-label">Doctors Collection</div>
    </div>
    <div class="stat-nums">
      <div class="num-badge green">✓ <?= $dInserted ?> inserted</div>
      <div class="num-badge yellow">↷ <?= $dSkipped ?> skipped</div>
    </div>
  </div>

  <hr class="divider"/>

  <div style="display:flex;gap:12px;flex-wrap:wrap">
    <a href="login.html" class="btn">🔐 Go to Login</a>
    <a href="register.html" class="btn" style="background:linear-gradient(135deg,#059669,#10b981)">📋 Register</a>
  </div>

  <p class="note">
    Default test password for all seeded accounts: <strong style="color:#e2e8f0">123456</strong><br/>
    Example patient: ravi1@Sanjeevani.com / 123456<br/>
    Example doctor: doctor1@Sanjeevani.com / 123456
  </p>
</div>
</body>
</html>
