// ============================================================
// Sanjeevani — MongoDB Compass Insert Scripts
// ============================================================
// Use these scripts in MongoDB Compass:
//   Database: hospital_management
//   Collection: appointments (create if it doesn't exist)
// 
// STEP 1: First run seed_appointments.php via browser, OR
// STEP 2: Use these manual scripts in Compass > MongoShell
// ============================================================

// ── STEP A: Add 'department' field to doctors ─────────────────
// Run in the 'doctors' collection (MongoShell):

db.doctors.updateMany(
  { specialization: "General Physician", department: { $exists: false } },
  { $set: { department: "General Medicine" } }
);
db.doctors.updateMany(
  { specialization: "Cardiologist", department: { $exists: false } },
  { $set: { department: "Cardiology" } }
);
db.doctors.updateMany(
  { specialization: "Neurologist", department: { $exists: false } },
  { $set: { department: "Neurology" } }
);
db.doctors.updateMany(
  { specialization: "Orthopedic", department: { $exists: false } },
  { $set: { department: "Orthopedics" } }
);
db.doctors.updateMany(
  { specialization: "Dermatologist", department: { $exists: false } },
  { $set: { department: "Dermatology" } }
);
db.doctors.updateMany(
  { specialization: "Pediatrician", department: { $exists: false } },
  { $set: { department: "Pediatrics" } }
);
db.doctors.updateMany(
  { specialization: "ENT Specialist", department: { $exists: false } },
  { $set: { department: "ENT" } }
);
db.doctors.updateMany(
  { specialization: "Gynecologist", department: { $exists: false } },
  { $set: { department: "Gynecology" } }
);
db.doctors.updateMany(
  { specialization: "Psychiatrist", department: { $exists: false } },
  { $set: { department: "Psychiatry" } }
);
db.doctors.updateMany(
  { specialization: "Radiologist", department: { $exists: false } },
  { $set: { department: "Radiology" } }
);
db.doctors.updateMany(
  { specialization: "Ophthalmologist", department: { $exists: false } },
  { $set: { department: "Ophthalmology" } }
);
db.doctors.updateMany(
  { specialization: "Endocrinologist", department: { $exists: false } },
  { $set: { department: "Endocrinology" } }
);
db.doctors.updateMany(
  { specialization: "Pulmonologist", department: { $exists: false } },
  { $set: { department: "Pulmonology" } }
);
db.doctors.updateMany(
  { specialization: "Nephrologist", department: { $exists: false } },
  { $set: { department: "Nephrology" } }
);
db.doctors.updateMany(
  { specialization: "Gastroenterologist", department: { $exists: false } },
  { $set: { department: "Gastroenterology" } }
);


// ── STEP B: Insert sample appointments ───────────────────────
// Run in the 'appointments' collection.
//
// IMPORTANT: Replace the patient_id and doctor_id values below
// with REAL ObjectId values from your patients/doctors collections.
// You can find them by running:
//   db.patients.find({}, { _id: 1, name: 1 }).limit(5)
//   db.doctors.find({}, { _id: 1, name: 1, department: 1 }).limit(5)

db.appointments.insertMany([
  {
    patient_id:       "REPLACE_WITH_PATIENT_OBJECTID_1",
    patient_name:     "Ravi Kumar",
    doctor_id:        "REPLACE_WITH_DOCTOR_OBJECTID_1",
    doctor_name:      "Dr. Arjun Kapoor",
    department:       "Cardiology",
    appointment_date: "2026-03-20",
    appointment_time: "09:00 AM",
    appointment_type: "consultation",
    reason:           "Routine heart checkup and blood pressure monitoring.",
    status:           "pending",
    created_at:       new Date(),
    updated_at:       new Date()
  },
  {
    patient_id:       "REPLACE_WITH_PATIENT_OBJECTID_1",
    patient_name:     "Ravi Kumar",
    doctor_id:        "REPLACE_WITH_DOCTOR_OBJECTID_2",
    doctor_name:      "Dr. Priya Nair",
    department:       "Neurology",
    appointment_date: "2026-03-25",
    appointment_time: "11:00 AM",
    appointment_type: "followup",
    reason:           "Follow-up visit for headache and migraine review.",
    status:           "approved",
    created_at:       new Date(),
    updated_at:       new Date()
  },
  {
    patient_id:       "REPLACE_WITH_PATIENT_OBJECTID_2",
    patient_name:     "Priya Sharma",
    doctor_id:        "REPLACE_WITH_DOCTOR_OBJECTID_3",
    doctor_name:      "Dr. Suresh Rajan",
    department:       "Orthopedics",
    appointment_date: "2026-03-18",
    appointment_time: "02:00 PM",
    appointment_type: "consultation",
    reason:           "Knee pain evaluation and X-ray review.",
    status:           "pending",
    created_at:       new Date(),
    updated_at:       new Date()
  },
  {
    patient_id:       "REPLACE_WITH_PATIENT_OBJECTID_2",
    patient_name:     "Priya Sharma",
    doctor_id:        "REPLACE_WITH_DOCTOR_OBJECTID_4",
    doctor_name:      "Dr. Anita Desai",
    department:       "Dermatology",
    appointment_date: "2026-02-28",
    appointment_time: "10:30 AM",
    appointment_type: "consultation",
    reason:           "Skin rash and allergy assessment.",
    status:           "completed",
    created_at:       new Date(),
    updated_at:       new Date()
  },
  {
    patient_id:       "REPLACE_WITH_PATIENT_OBJECTID_3",
    patient_name:     "Arjun Singh",
    doctor_id:        "REPLACE_WITH_DOCTOR_OBJECTID_5",
    doctor_name:      "Dr. Meena Sharma",
    department:       "General Medicine",
    appointment_date: "2026-03-30",
    appointment_time: "03:30 PM",
    appointment_type: "teleconsult",
    reason:           "General health consultation — fever and fatigue.",
    status:           "pending",
    created_at:       new Date(),
    updated_at:       new Date()
  }
]);
