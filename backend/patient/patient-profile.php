<?php
session_start();
require_once __DIR__ . '/../config.php';

// Check if patient session exists, otherwise redirect to login.html
if (empty($_SESSION['patient_id'])) {
    header("Location: login.html");
    exit;
}

// Fetch patient details from MongoDB
try {
    $db = getDB();
    $patient_email = $_SESSION['patient_email'];
    $patient = $db->patients->findOne(['email' => $patient_email]);
    
    if (!$patient) {
        // Fallback or error handling
        $patient = [
            'full_name' => $_SESSION['patient_name'] ?? '',
            'email'     => $patient_email,
            'phone'     => '',
            'gender'    => '',
            'age'       => '',
            'address'   => ''
        ];
    }
} catch (Exception $e) {
    die("Database connection failed.");
}

$fullName = htmlspecialchars($patient['full_name'] ?? $patient['name'] ?? '');
$email    = htmlspecialchars($patient['email'] ?? '');
$phone    = htmlspecialchars($patient['phone'] ?? '');
$gender   = htmlspecialchars($patient['gender'] ?? '');
$age      = htmlspecialchars($patient['age'] ?? '');
$address  = htmlspecialchars($patient['address'] ?? '');

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>Profile Settings — MediVita Patient Portal</title>
<link rel="stylesheet" href="portal.css"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
<style>
/* Additional minimal styles for the profile settings page */
.profile-card {
    background: #fff;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-sm);
    border: 1px solid var(--gray-200);
    padding: 30px;
    max-width: 600px;
    margin: 40px auto;
}
.profile-header {
    text-align: center;
    margin-bottom: 30px;
}
.profile-header h2 {
    font-size: 1.5rem;
    color: var(--gray-900);
    margin-bottom: 5px;
}
.profile-header p {
    color: var(--gray-500);
}
.form-group {
    margin-bottom: 20px;
}
.form-label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: var(--gray-700);
}
.form-control {
    width: 100%;
    padding: 12px;
    border: 1.5px solid var(--gray-300);
    border-radius: var(--radius-sm);
    font-size: 0.95rem;
    transition: var(--transition);
}
.form-control:focus {
    border-color: var(--blue-500);
    outline: none;
    box-shadow: 0 0 0 3px var(--blue-50);
}
.btn-save {
    background: var(--blue-600);
    color: #fff;
    border: none;
    padding: 12px 24px;
    border-radius: var(--radius-sm);
    font-weight: 600;
    cursor: pointer;
    width: 100%;
    margin-top: 10px;
    transition: var(--transition);
}
.btn-save:hover {
    background: var(--blue-700);
}
</style>
</head>
<body>
<div class="portal-wrap">
  <aside class="sidebar"></aside>
  <div class="sidebar-overlay"></div>

  <div class="main-area">
    <div class="topbar"></div>

    <div class="page-content">
      <div class="profile-card anim-fade-up">
        <div class="profile-header">
            <h2>Patient Profile Settings</h2>
            <p>Update your personal information below.</p>
        </div>
        
        <form id="patientProfileForm">
            <div class="form-group">
                <label class="form-label">Full Name</label>
                <input type="text" class="form-control" name="full_name" value="<?php echo $fullName; ?>" readonly>
                <small style="color:var(--gray-500)">Name changes must be requested through administration.</small>
            </div>
            
            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" class="form-control" name="email" value="<?php echo $email; ?>" readonly>
            </div>
            
            <div class="form-group">
                <label class="form-label">Phone Number</label>
                <input type="text" class="form-control" name="phone" value="<?php echo $phone; ?>">
            </div>
            
            <div class="form-group" style="display:flex; gap:20px;">
                <div style="flex:1;">
                    <label class="form-label">Gender</label>
                    <select class="form-control" name="gender">
                        <option value="Male" <?php echo ($gender === 'Male') ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo ($gender === 'Female') ? 'selected' : ''; ?>>Female</option>
                        <option value="Other" <?php echo ($gender === 'Other') ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
                <div style="flex:1;">
                    <label class="form-label">Age</label>
                    <input type="number" class="form-control" name="age" value="<?php echo $age; ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Address</label>
                <textarea class="form-control" name="address" rows="3"><?php echo $address; ?></textarea>
            </div>
            
            <button type="button" class="btn-save" onclick="alert('Profile updated successfully! (Demo)')">Save Changes</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="portal.js"></script>
<script>
  initPortal('profile', 'Profile Settings', 'Manage your personal details');
</script>
</body>
</html>
