<?php
/**
 * MediVita — backend/get_doctor_patients.php
 *
 * Returns a deduplicated list of patients who booked appointments
 * with the currently logged-in doctor. Each entry is enriched with
 * patient details (age, gender, phone) from the patients collection.
 *
 * Also returns the appointment_id for linking prescriptions.
 *
 * Response:
 *   { success: true, patients: [...] }
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../shared/auth.php';

header('Content-Type: application/json; charset=utf-8');

if (!isAuthenticated()) {
    json_out(['success' => false, 'message' => 'Not authenticated.'], 401);
}

$sessionUser = getSessionUser();
if (!$sessionUser || $sessionUser['role'] !== 'doctor') {
    json_out(['success' => false, 'message' => 'Doctor access only.'], 403);
}

$doctorId = $_SESSION['doctor_id'] ?? $sessionUser['id'];

try {
    $db = getDB();

    // Fetch all non-cancelled appointments for this doctor
    $appointments = $db->appointments->find(
        [
            'doctor_id' => $doctorId,
            'status'    => ['$ne' => 'cancelled'],
        ],
        [
            'sort'       => ['appointment_date' => -1],
            'projection' => [
                '_id'              => 1,
                'patient_id'       => 1,
                'patient_name'     => 1,
                'appointment_date' => 1,
                'appointment_time' => 1,
                'department'       => 1,
                'reason'           => 1,
                'status'           => 1,
            ],
        ]
    );

    // Deduplicate by patient_id — keep most recent appointment per patient
    $seen     = [];
    $patients = [];

    foreach ($appointments as $appt) {
        $pid = $appt['patient_id'] ?? '';
        if (empty($pid) || isset($seen[$pid])) continue;
        $seen[$pid] = true;

        // Fetch patient details from patients collection
        $patientName = $appt['patient_name'] ?? '';
        $patientAge  = null;
        $patientGender = '';
        $patientPhone  = '';
        $patientBlood  = '';

        try {
            $patObj = $db->patients->findOne(
                ['_id' => new MongoDB\BSON\ObjectId($pid)],
                ['projection' => [
                    'password'   => 0,
                    'name'       => 1,
                    'full_name'  => 1,
                    'age'        => 1,
                    'gender'     => 1,
                    'phone'      => 1,
                    'blood_group'=> 1,
                ]]
            );
            if ($patObj) {
                if (empty($patientName)) {
                    $patientName = $patObj['full_name'] ?? $patObj['name'] ?? 'Unknown';
                }
                $patientAge    = isset($patObj['age'])        ? (int)$patObj['age']        : null;
                $patientGender = $patObj['gender']            ?? '';
                $patientPhone  = $patObj['phone']             ?? '';
                $patientBlood  = $patObj['blood_group']       ?? '';
            }
        } catch (Exception $ex) { /* skip bad ObjectId */ }

        $patients[] = [
            'patient_id'       => $pid,
            'patient_name'     => $patientName ?: 'Unknown Patient',
            'patient_age'      => $patientAge,
            'patient_gender'   => $patientGender,
            'patient_phone'    => $patientPhone,
            'patient_blood'    => $patientBlood,
            'appointment_id'   => (string)$appt['_id'],
            'appointment_date' => $appt['appointment_date'] ?? '',
            'appointment_time' => $appt['appointment_time'] ?? '',
            'department'       => $appt['department']       ?? '',
            'reason'           => $appt['reason']           ?? '',
            'status'           => $appt['status']           ?? '',
        ];
    }

    json_out([
        'success'  => true,
        'patients' => $patients,
        'total'    => count($patients),
    ]);

} catch (Exception $e) {
    json_out(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
}
