<?php
/**
 * Sanjeevani – backend/admin/approve_doctor.php
 * POST { doctorId, action: "approved"|"rejected" }
 * Updates the doctor's approval_status, marks notification as read,
 * and sends an email to the doctor about the decision.
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../shared/auth.php';
require_once __DIR__ . '/../shared/mailer.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(['success' => false, 'message' => 'POST required.'], 405);
}
if (!isAuthenticated() || getSessionUser()['role'] !== 'admin') {
    json_out(['success' => false, 'message' => 'Unauthorised.'], 403);
}

$body     = json_decode(file_get_contents('php://input'), true) ?? [];
$doctorId = trim($body['doctorId'] ?? ($_POST['doctorId'] ?? ''));
$action   = trim($body['action']   ?? ($_POST['action']   ?? ''));

if (empty($doctorId) || !in_array($action, ['approved', 'rejected'], true)) {
    json_out(['success' => false, 'message' => 'doctorId and valid action required.'], 400);
}

try {
    $db  = getDB();
    $oid = new MongoDB\BSON\ObjectId($doctorId);

    $result = $db->doctors->updateOne(
        ['_id' => $oid],
        ['$set' => [
            'approval_status' => $action,
            'availability'    => $action === 'approved' ? 'Available' : 'Unavailable',
            'is_available'   => $action === 'approved' ? true : false
        ]]
    );

    if ($result->getMatchedCount() === 0) {
        json_out(['success' => false, 'message' => 'Doctor not found.'], 404);
    }

    // Mark related admin notification as read
    $db->notifications->updateOne(
        ['type' => 'doctor_registration', 'relatedId' => $doctorId, 'receiverType' => 'admin'],
        ['$set' => ['status' => 'read']]
    );

    // ── Send email notification to doctor ─────────────────────────────────────
    $doctor = $db->doctors->findOne(['_id' => $oid]);
    if ($doctor && !empty($doctor['email'])) {
        $docName = $doctor['full_name'] ?? $doctor['name'] ?? 'Doctor';
        if ($action === 'approved') {
            $emailBody = '<p>Dear <strong>' . $docName . '</strong>,</p>'
                . '<p>🎉 Congratulations! Your doctor registration with <strong>Sanjeevani Hospital</strong> has been <span style="color:#16a34a;font-weight:700;">approved</span>.</p>'
                . '<p>You can now log in to your doctor portal using your registered email and password:</p>'
                . '<a href="http://localhost/Hospital-Mangement-T10-/frontend/auth/login.html" class="btn">Log In to Portal</a>'
                . '<p>Welcome to the Sanjeevani team!</p>';
            $subject = 'Sanjeevani - Your Doctor Registration Has Been Approved!';
        } else {
            $emailBody = '<p>Dear <strong>' . $docName . '</strong>,</p>'
                . '<p>We regret to inform you that your doctor registration with <strong>Sanjeevani Hospital</strong> has been <span style="color:#dc2626;font-weight:700;">rejected</span>.</p>'
                . '<p>If you believe this is an error or would like to appeal, please contact the hospital administration directly.</p>';
            $subject = 'Sanjeevani - Doctor Registration Status Update';
        }
        sendMail($doctor['email'], $subject, $emailBody);
    }

    json_out(['success' => true, 'message' => 'Doctor ' . $action . ' successfully.']);
} catch (Exception $e) {
    json_out(['success' => false, 'message' => $e->getMessage()], 500);
}
