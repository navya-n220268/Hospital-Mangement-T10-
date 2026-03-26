<?php
session_set_cookie_params([
    'path' => '/Hospital-Mangement-T10-/',
    'samesite' => 'Lax'
]);
session_start();

if (isset($_GET['doctor'])) {
    $id = '69c2c912410daacbcb07f7e3';
    $_SESSION['user_id']     = $id;
    $_SESSION['doctor_id']   = $id;
    $_SESSION['user_name']   = 'Dr. Test';
    $_SESSION['doctor_name'] = 'Dr. Test';
    $_SESSION['user_role']   = 'doctor';
    $_SESSION['role']        = 'doctor';
    echo "Doctor session set for ID: $id";
} elseif (isset($_GET['patient'])) {
    $id = '69c2c911410daacbcb07f7e2';
    $_SESSION['user_id']      = $id;
    $_SESSION['patient_id']   = $id;
    $_SESSION['user_name']    = 'Patient Test';
    $_SESSION['patient_name'] = 'Patient Test';
    $_SESSION['user_role']    = 'patient';
    $_SESSION['role']         = 'patient';
    echo "Patient session set for ID: $id";
} elseif (isset($_GET['admin'])) {
    $id = '65f1a2b3c4d5e6f7a8b9c0d1';
    $_SESSION['user_id']   = $id;
    $_SESSION['user_name'] = 'Super Admin';
    $_SESSION['user_role'] = 'admin';
    $_SESSION['role']      = 'admin';
    echo "Admin session set for ID: $id";
} else {
    $_SESSION = [];
    session_destroy();
    echo "Session cleared.";
}
session_write_close();
