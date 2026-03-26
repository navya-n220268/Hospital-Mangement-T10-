<?php
/**
 * Sanjeevani — Google OAuth Simulation Handler
 * 
 * Since real Google OAuth requires a registered client ID and secret,
 * this file acts as a simulation wrapper to demonstrate the logic:
 * - Creates user if they don't exist
 * - Sets auth_type = 'google', is_verified = true
 * - Skips OTP entirely
 */
session_start();
require_once __DIR__ . '/../config.php';

// If POST request, we are receiving the simulated Google Account from our mock UI
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(sanitise($_POST['email'] ?? ''));
    $name = sanitise($_POST['name'] ?? 'Google User');
    $role = sanitise($_POST['role'] ?? 'patient');

    if (empty($email) || empty($role)) {
        die("Invalid simulated payload.");
    }

    try {
        $db = getDB();
        $collection = ($role === 'patient') ? $db->patients : $db->doctors;
        
        // 1. Check if user already exists
        $user = $collection->findOne(['email' => $email]);

        if (!$user) {
            // Auto Register via Google
            $now = new MongoDB\BSON\UTCDateTime();
            if ($role === 'patient') {
                $doc = [
                    'patient_id' => 'PAT-G' . strtoupper(substr(uniqid(), -5)),
                    'full_name'  => $name,
                    'email'      => $email,
                    'phone'      => '',
                    'gender'     => '',
                    'age'        => 0,
                    'auth_type'  => 'google',
                    'is_verified'=> true,
                    'created_at' => $now,
                    'role'       => 'patient'
                ];
            } else {
                $doc = [
                    'doctor_id'      => 'DOC-G' . strtoupper(substr(uniqid(), -5)),
                    'full_name'      => $name,
                    'email'          => $email,
                    'department'     => 'General',
                    'specialization' => 'General Physician',
                    'auth_type'      => 'google',
                    'is_verified'    => true,
                    'availability'   => 'Unavailable',
                    'approval_status'=> 'pending',
                    'created_at'     => $now,
                    'role'           => 'doctor'
                ];
            }
            $result = $collection->insertOne($doc);
            $user = $collection->findOne(['_id' => $result->getInsertedId()]);

            if ($role === 'doctor') {
                // Keep doctor as pending approval, but they bypassed OTP
                $db->notifications->insertOne([
                    'type'         => 'doctor_registration',
                    'message'      => "New doctor signed up via Google: {$name} ({$email}). Awaiting approval.",
                    'receiverType' => 'admin',
                    'relatedId'    => (string)$user['_id'],
                    'status'       => 'unread',
                    'createdAt'    => $now,
                ]);
            }
        }

        // 2. Perform Login Flow
        if ($role === 'doctor' && ($user['approval_status'] ?? 'pending') !== 'approved') {
            die("<h3>Account Pending</h3><p>Your Google Doctor profile was created, but requires Admin Approval before login.</p><a href='/Hospital-Mangement-T10-/frontend/auth/login.html'>Go Back</a>");
        }

        session_regenerate_id(true);
        $_SESSION['user_id']   = (string)$user['_id'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['user_email']= $user['email'];
        $_SESSION['user_role'] = $user['role'] ?? $role;
        $_SESSION['role']      = $user['role'] ?? $role;

        if ($role === 'patient') {
            $_SESSION['patient_id']   = (string)$user['_id'];
            $_SESSION['patient_name'] = $user['full_name'];
            $_SESSION['patient_email']= $user['email'];
            header("Location: /Hospital-Mangement-T10-/frontend/patient/dashboard.html");
        } else {
            $_SESSION['doctor_id']   = (string)$user['_id'];
            $_SESSION['doctor_name'] = $user['full_name'];
            header("Location: /Hospital-Mangement-T10-/frontend/doctor/doctor-dashboard.php");
        }
        exit;

    } catch (Exception $e) {
        die("Error processing Google Login: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Simulate Google Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; background: #f0f4ff; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .card { background: white; padding: 40px; border-radius: 16px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); text-align: center; width: 100%; max-width: 400px; }
        .card img { width: 48px; margin-bottom: 20px; }
        input, select { width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 8px; font-family: inherit; font-size: 1rem; box-sizing: border-box; }
        button { background: #4285F4; color: white; border: none; padding: 12px; width: 100%; border-radius: 8px; font-size: 1rem; cursor: pointer; font-weight: 600; }
        button:hover { background: #3367d6; }
    </style>
</head>
<body>
<div class="card">
    <svg viewBox="0 0 48 48" width="48" style="margin: 0 auto 20px; display: block;">
        <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z" />
        <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z" />
        <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z" />
        <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z" />
        <path fill="none" d="M0 0h48v48H0z" />
    </svg>
    <h2>Simulated Google Flow</h2>
    <p style="font-size: 0.9rem; color: #555; margin-bottom: 24px;">Bypass OTP. Auto-generates account if new.</p>
    
    <form method="POST" action="google_login.php">
        <input type="text" name="name" placeholder="Full Name" required value="Google Test User">
        <input type="email" name="email" placeholder="Gmail Address" required value="test@gmail.com">
        <select name="role">
            <option value="patient">Patient</option>
            <option value="doctor">Doctor</option>
        </select>
        <button type="submit">Complete OAuth Flow</button>
    </form>
</div>
</body>
</html>
