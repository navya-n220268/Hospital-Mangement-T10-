<?php
/**
 * MediVita — backend/get_logged_doctor.php
 * Returns the logged-in doctor's full details.
 * Session → enriched with DB record (specialization, department, etc.)
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../shared/auth.php';

header('Content-Type: application/json; charset=utf-8');

if (!isAuthenticated()) {
    json_out(['success' => false, 'message' => 'Not authenticated.'], 401);
}

$sessionUser = getSessionUser();
if (!$sessionUser || $sessionUser['role'] !== 'doctor') {
    json_out(['success' => false, 'message' => 'Doctor session required.'], 403);
}

try {
    $db       = getDB();
    $doctorId = $_SESSION['doctor_id'] ?? $sessionUser['id'];

    // Enrich with full DB record
    $doctor = null;
    try {
        $doctor = $db->doctors->findOne(
            ['_id' => new MongoDB\BSON\ObjectId($doctorId)],
            ['projection' => [
                'password' => 0   // never expose password
            ]]
        );
    } catch (Exception $ex) {
        // Invalid ObjectId — fall back to session data
    }

    $name         = $doctor['full_name']    ?? $doctor['name']   ?? $sessionUser['name'];
    $email        = $doctor['email']        ?? $sessionUser['email'];
    $dept         = $doctor['department']   ?? $doctor['specialization'] ?? '';
    $specialization = $doctor['specialization'] ?? $dept;
    $experience   = $doctor['experience']   ?? '';
    $phone        = $doctor['phone']        ?? '';
    $gender       = $doctor['gender']       ?? '';

    json_out([
        'success' => true,
        'doctor'  => [
            'id'             => $doctorId,
            'name'           => $name,
            'email'          => $email,
            'department'     => $dept,
            'specialization' => $specialization,
            'experience'     => $experience,
            'phone'          => $phone,
            'gender'         => $gender,
        ],
    ]);

} catch (Exception $e) {
    json_out(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
}
