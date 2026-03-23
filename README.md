# 🏥 MediVita — Hospital Management System

> A complete, multi-role Hospital Management System built with **PHP**, **MongoDB Atlas**, and **vanilla HTML/CSS/JavaScript**. Designed for real-world hospital workflows with separate portals for Patients, Doctors, and Admins.

---

## 📋 Table of Contents

1. [Project Overview](#1-project-overview)
2. [Features](#2-features)
3. [Folder Structure](#3-folder-structure)
4. [File-by-File Explanation](#4-file-by-file-explanation)
5. [Technologies Used](#5-technologies-used)
6. [Installation Guide](#6-installation-guide)
7. [Environment Variables](#7-environment-variables)
8. [How the Project Works (Flow)](#8-how-the-project-works-flow)
9. [Pages Explanation](#9-pages-explanation)
10. [Troubleshooting](#10-troubleshooting)
11. [Conclusion](#11-conclusion)

---

## 1. Project Overview

**MediVita** is a full-stack Hospital Management System (HMS) that allows a hospital to manage its patients, doctors, appointments, prescriptions, and medical records — all through a single web application.

### Purpose
The goal of this project is to digitise hospital operations and replace paper-based processes with a clean, modern web portal. It covers the entire lifecycle — from patient registration to doctor prescription writing.

### Who Can Use It?

| Role | Description |
|------|-------------|
| 🧑‍🤝‍🧑 **Patient** | Registers, logs in, books appointments, views medical records, chats with doctors, uses the AI symptom checker |
| 👨‍⚕️ **Doctor** | Logs in, views their assigned patients, writes prescriptions, manages schedules, replies to patient messages |
| 🧑‍💼 **Admin** | Manages all doctors and patients, views appointment analytics with charts, controls the entire system |

---

## 2. Features

### 🔐 Authentication System
- **Standard Login & Registration** — patients and doctors can sign up with email and password
- **Google OAuth 2.0** — users can log in with their Google account using a single click
- **Role-based Access Control** — after login, every user is sent to the correct portal based on their role (patient / doctor / admin)
- **Session Management** — PHP sessions keep users logged in securely; sessions expire on logout
- **Profile Completion** — new Google users are sent to a page to enter their phone number and select their role before entering the system

### 🧑‍🤝‍🧑 Patient Portal
- **Dashboard** — shows upcoming appointments, recent activity, and a welcome summary
- **Book Appointment** — browse doctors by department, pick a date and time, and submit a booking
- **My Appointments** — view all past and upcoming appointments with status (pending / approved / completed / cancelled)
- **Cancel Appointment** — patients can cancel a pending appointment
- **Medical Records** — view prescriptions and diagnoses written by doctors
- **AI Symptom Checker** — select symptoms, set severity and duration, and get an AI-powered specialist recommendation with a direct "Book Appointment" button
- **Message a Doctor** — send messages directly to doctors for queries or follow-ups
- **Patient Profile** — update personal information
- **Notifications** — view system notifications
- **Settings** — account-level settings

### 👨‍⚕️ Doctor Portal
- **Dashboard** — PHP-rendered dashboard showing patient count, today's schedule, and key stats
- **Today's Schedule** — list of today's appointments sorted by time
- **My Patients** — full list of patients assigned based on appointments
- **Patient History** — view a specific patient's medical history and past visits
- **Add Prescription** — write prescriptions for patients (medicine, dosage, instructions)
- **Doctor Messages** — view and reply to messages sent by patients
- **Doctor Profile** — update professional information, specialisation, and contact details
- **Schedules** — manage availability and view upcoming appointments

### 🧑‍💼 Admin Portal
- **Admin Dashboard** — overview statistics: total patients, doctors, appointments, and recent activity
- **Manage Doctors** — view all doctors, toggle active/inactive status, update doctor details
- **Manage Patients** — view all registered patients, add new patients, delete patients
- **Appointment Analytics** — rich visual charts showing:
  - Total, completed, pending, and cancelled appointment counts
  - Department-wise appointment pie chart
  - Monthly appointment trend bar chart
  - Most active doctors ranking
  - Status breakdown donut chart
- **Settings** — system-level configuration

---

## 3. Folder Structure

```
medivita/
│
├── 📄 index.html                  ← Landing page (entry point of the website)
├── 📄 composer.json               ← PHP dependency configuration
├── 📄 composer.lock               ← Locked versions of installed packages
├── 📄 .env                        ← Your SECRET credentials (NOT pushed to GitHub)
├── 📄 .env.example                ← Template showing which variables to set
├── 📄 .gitignore                  ← Files Git should ignore
├── 📄 README.md                   ← This file
│
├── 📁 assets/                     ← Shared CSS and JavaScript files
│   ├── 📁 css/                    ← All stylesheets
│   │   ├── styles.css             ← Base styles (landing, login, register pages)
│   │   ├── portal.css             ← Patient portal layout and components
│   │   ├── medivita.css           ← Admin portal layout and components
│   │   ├── doctor.css             ← Doctor portal specific styles
│   │   ├── messages.css           ← Messaging UI styles
│   │   ├── shared.css             ← Reusable component styles
│   │   └── shared2.css            ← Additional shared styles
│   └── 📁 js/                     ← All JavaScript files
│       ├── script.js              ← Auth page logic (login, register forms)
│       ├── portal.js              ← Patient portal sidebar/topbar loader
│       ├── admin.js               ← Admin portal dynamic functionality
│       ├── doctor.js              ← Doctor portal dynamic functionality
│       ├── messages.js            ← Real-time messaging logic
│       ├── shared.js              ← Common utilities across portals
│       └── shared2.js             ← Additional shared utilities
│
├── 📁 frontend/                   ← All HTML/PHP pages users see
│   ├── 📁 auth/                   ← Authentication pages
│   │   ├── login.html
│   │   ├── register.html
│   │   └── complete-profile.html  ← For new Google OAuth users
│   ├── 📁 patient/                ← Patient portal pages
│   │   ├── dashboard.html
│   │   ├── book-appointment.html
│   │   ├── my-appointments.html
│   │   ├── medical-records.html
│   │   ├── symptom-checker.html
│   │   ├── message-to-doctor.html
│   │   ├── patient-messages.html
│   │   ├── patient-profile.html
│   │   ├── notifications.html
│   │   └── settings.html
│   ├── 📁 doctor/                 ← Doctor portal pages
│   │   ├── doctor-dashboard.php   ← PHP dashboard (session-protected)
│   │   ├── todays-schedule.html
│   │   ├── schedules.html
│   │   ├── patient-history.html
│   │   ├── add-prescription.html
│   │   ├── doctor-messages.html
│   │   └── doctor_profile.html
│   └── 📁 admin/                  ← Admin portal pages
│       ├── admin-dashboard.html
│       ├── manage-doctors.html
│       ├── manage-patients.html
│       ├── appointment-analytics.html
│       └── settings.html
│
├── 📁 backend/                    ← All PHP server-side logic
│   ├── 📄 config.php              ← MASTER config: DB connection + helper functions
│   ├── 📁 auth/                   ← Authentication PHP scripts
│   │   ├── login.php
│   │   ├── register.php
│   │   ├── logout.php
│   │   ├── google-login.php
│   │   ├── google-callback.php
│   │   └── save-profile.php
│   ├── 📁 patient/                ← Patient API endpoints
│   │   ├── get_patient.php
│   │   ├── get_patient_dashboard_data.php
│   │   ├── get_patient_appointments.php
│   │   ├── get_patient_medical_records.php
│   │   ├── get_patient_history.php
│   │   ├── get_doctors_for_patient.php
│   │   ├── patient-profile.php
│   │   ├── get_user.php
│   │   └── get-session-info.php
│   ├── 📁 doctor/                 ← Doctor API endpoints
│   │   ├── get_logged_doctor.php
│   │   ├── get_doctor_patients.php
│   │   ├── get_doctor_schedules.php
│   │   ├── get_doctor_messages.php
│   │   ├── get_patient_messages.php
│   │   ├── send_message.php
│   │   ├── reply_message.php
│   │   └── update_doctor_profile.php
│   ├── 📁 admin/                  ← Admin API endpoints
│   │   ├── admin-dashboard.php
│   │   ├── get_doctors.php
│   │   ├── get_patients.php
│   │   ├── add_doctor.php
│   │   ├── delete_doctor.php
│   │   ├── delete_patient.php
│   │   ├── toggle_doctor_status.php
│   │   ├── update_doctor.php
│   │   ├── get_appointment_analytics.php
│   │   ├── getDashboardStats.php
│   │   ├── getRecentAppointments.php
│   │   ├── getRecentPatients.php
│   │   └── getTopDoctors.php
│   └── 📁 shared/                 ← APIs used by multiple roles
│       ├── auth.php               ← Session middleware (requireAuth, getSessionUser)
│       ├── book_appointment.php
│       ├── cancel_appointment.php
│       ├── add_prescription.php
│       ├── get_doctors.php
│       ├── get_departments.php
│       ├── get_doctors_by_department.php
│       └── get_error.php
│
├── 📁 config/                     ← Legacy database connection files
│   ├── database.php               ← getDatabaseConnection() helper
│   └── db.php                     ← Raw $db variable for older scripts
│
├── 📁 database/                   ← Database setup and seeding
│   ├── config.php                 ← DB config (mirrors config/database.php)
│   ├── 📁 scripts/
│   │   └── mongodb_compass_scripts.js  ← MongoDB Compass queries for manual use
│   └── 📁 seeds/
│       ├── seed_data.php          ← Seeds doctors and patients
│       └── seed_appointments.php  ← Seeds sample appointments
│
└── 📁 vendor/                     ← Composer packages (NOT in Git, auto-generated)
```

---

## 4. File-by-File Explanation

### `index.html`
- **What it does:** The public-facing landing page of MediVita. It is the first page visitors see.
- **Why:** Introduces the hospital system, shows features, and provides links to Login and Register.
- **Connects to:** `frontend/auth/login.html` and `frontend/auth/register.html`

### `backend/config.php` ⭐ (Most Important File)
- **What it does:** This is the heart of the backend. It loads environment variables from `.env`, connects to MongoDB Atlas, and defines helper functions used everywhere:
  - `getDB()` — returns the MongoDB database connection
  - `json_out()` — sends a JSON response and stops the script
  - `redirect()` — sends an HTTP redirect
  - `sanitise()` — cleans user input (removes HTML tags, trims spaces)
- **Why:** Every backend PHP file includes this file so they all share the same database connection and utilities.
- **Connects to:** All files in `backend/auth/`, `backend/patient/`, `backend/doctor/`, `backend/admin/`, `backend/shared/`

### `backend/shared/auth.php`
- **What it does:** The authentication middleware. Provides functions to protect backend routes:
  - `requireAuth('patient')` — blocks access if user is not a patient
  - `requireAuth('doctor')` — blocks access if user is not a doctor
  - `getSessionUser()` — returns the current user's session data as an array
  - `isAuthenticated()` — returns true/false based on session
- **Why:** Prevents unauthorised users from accessing sensitive API endpoints
- **Connects to:** Included by all protected backend scripts

### `backend/auth/login.php`
- **What it does:** Handles the standard login form. Checks the email and password against MongoDB (`patients` and `doctors` collections), verifies the password hash, sets session variables, and redirects to the correct dashboard.
- **Connects to:** `frontend/auth/login.html` (submits form here)

### `backend/auth/register.php`
- **What it does:** Handles new patient registration. Validates all fields, hashes the password securely with `password_hash()`, and saves the new patient to MongoDB.
- **Connects to:** `frontend/auth/register.html`

### `backend/auth/google-login.php`
- **What it does:** Starts the Google OAuth flow. Generates a CSRF security token, builds the Google sign-in URL, and redirects the user to Google's login page.
- **Connects to:** The login page's "Continue with Google" button links here

### `backend/auth/google-callback.php`
- **What it does:** Google redirects back here after the user signs in. It:
  1. Validates the CSRF token for security
  2. Exchanges Google's code for an access token
  3. Fetches the user's name and email from Google
  4. Checks if the email exists in MongoDB
  5. If found → sets session and redirects to correct dashboard
  6. If new → redirects to `complete-profile.html` to collect phone/role
- **Connects to:** Google Cloud Console (authorised redirect URI)

### `backend/shared/book_appointment.php`
- **What it does:** Creates a new appointment in MongoDB. Validates all fields, checks for duplicate bookings, verifies the doctor exists, and saves the appointment with status `pending`.
- **Why:** Centralised booking logic used by the patient portal
- **Connects to:** `frontend/patient/book-appointment.html`

### `backend/shared/add_prescription.php`
- **What it does:** Allows a doctor to write a prescription for a patient. Saves medicine name, dosage, instructions, and diagnosis to the `prescriptions` collection in MongoDB.
- **Connects to:** `frontend/doctor/add-prescription.html`

### `backend/admin/get_appointment_analytics.php`
- **What it does:** Queries MongoDB for appointment statistics: total count, completed, pending, cancelled, monthly trend, department breakdown, and top doctors. Returns everything as JSON.
- **Connects to:** `frontend/admin/appointment-analytics.html` (powers all 5 charts)

### `assets/js/portal.js`
- **What it does:** Dynamically builds the patient portal's sidebar navigation and top bar on every patient page. Called with `initPortal('page-name', 'Page Title', 'subtitle')`.
- **Why:** Avoids copy-pasting the same sidebar HTML into 12 different pages. One JS file powers all of them.

### `assets/js/script.js`
- **What it does:** Handles login and registration form validation, password strength meter, password show/hide toggle, and form submission via `fetch()` API calls.

### `.env`
- **What it does:** Stores your real secret credentials (MongoDB URI, Google OAuth keys). Loaded by PHP using the `phpdotenv` library.
- **⚠️ NEVER commit this file to GitHub.** It is listed in `.gitignore`.

### `.env.example`
- **What it does:** A safe template copy of `.env` with placeholder values. This IS committed to GitHub so your teammates know what variables to configure.

---

## 5. Technologies Used

| Technology | Purpose |
|------------|---------|
| **HTML5** | Structure of all web pages |
| **CSS3** | Styling, animations, responsive layouts |
| **JavaScript (Vanilla)** | Dynamic page behaviour, API calls using `fetch()`, chart rendering |
| **PHP 8.1** | Backend server logic, session management, API endpoints |
| **MongoDB Atlas** | Cloud NoSQL database — stores patients, doctors, appointments, prescriptions |
| **Google OAuth 2.0** | Lets users sign in with their Google account |
| **Composer** | PHP package manager — installs all PHP libraries |
| **phpdotenv** | Loads `.env` file into PHP's `$_ENV` (keeps secrets out of code) |
| **mongodb/mongodb** | Official PHP driver for connecting to MongoDB Atlas |
| **google/apiclient** | Official Google PHP library for Google OAuth authentication |
| **Chart.js** | JavaScript charting library used in the Admin analytics page |
| **Font Awesome** | Icon library used throughout the UI |
| **XAMPP / LAMPP** | Local web server (Apache + PHP) for development |

---

## 6. Installation Guide

> ⚠️ **Prerequisites:** Make sure these are installed on your computer before starting.

### Prerequisites

| Tool | How to Get It |
|------|--------------|
| **XAMPP or LAMPP** | [xampp.apachefriends.org](https://www.apachefriends.org/) |
| **Composer** | [getcomposer.org](https://getcomposer.org/download/) |
| **Git** | [git-scm.com](https://git-scm.com/) |
| **MongoDB Atlas Account** | [cloud.mongodb.com](https://cloud.mongodb.com/) (free tier available) |
| **Google Cloud Console Account** | [console.cloud.google.com](https://console.cloud.google.com/) |
| **A web browser** | Chrome, Firefox, Edge, etc. |

---

### Step-by-Step Setup

#### Step 1 — Clone the Repository

Open a terminal and run:

```bash
git clone https://github.com/your-team/medivita.git
```

Then move the project into your XAMPP/LAMPP `htdocs` folder:

```bash
# For LAMPP (Linux):
mv medivita /opt/lampp/htdocs/medivita

# For XAMPP (Windows):
move medivita C:\xampp\htdocs\medivita
```

#### Step 2 — Install PHP Dependencies

Navigate into the project folder and run Composer:

```bash
cd /opt/lampp/htdocs/medivita
composer install
```

> This will create a `vendor/` folder with all required PHP packages (`mongodb`, `google/apiclient`, `phpdotenv`). This may take a minute.

**If Composer is not found, install it first:**
```bash
# Linux / Mac
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Then retry:
composer install
```

#### Step 3 — Set Up the `.env` File

Copy the example file:

```bash
cp .env.example .env
```

Now open `.env` in any text editor and fill in your real values:

```env
GOOGLE_CLIENT_ID="your-google-client-id.apps.googleusercontent.com"
GOOGLE_CLIENT_SECRET="your-google-client-secret"

MONGO_URI="mongodb+srv://username:password@cluster.mongodb.net/?appName=medivita"
MONGO_DB="hospital_management"
```

> See [Section 7 — Environment Variables](#7-environment-variables) for detailed explanations of each variable.

#### Step 4 — Configure Google OAuth (for Google Login)

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project (or select an existing one)
3. Go to **APIs & Services → Credentials**
4. Click **Create Credentials → OAuth 2.0 Client ID**
5. Set **Application type** to **Web application**
6. Under **Authorized redirect URIs**, add exactly:
   ```
   http://localhost/medivita/backend/auth/google-callback.php
   ```
7. Click **Create** and copy the **Client ID** and **Client Secret** into your `.env`

#### Step 5 — Start the Local Web Server

**Linux (LAMPP):**
```bash
sudo /opt/lampp/lampp start
```

**Windows (XAMPP):**
- Open the XAMPP Control Panel
- Click **Start** next to **Apache**

#### Step 6 — Open the Project in Your Browser

Visit:
```
http://localhost/medivita/
```

You should see the MediVita landing page. 🎉

---

## 7. Environment Variables

Your `.env` file must contain these four variables. Here is what each one means:

### `GOOGLE_CLIENT_ID`
- **What it is:** A unique identifier for your application registered in Google Cloud Console.
- **Example:** `1088109439203-abc123xyz.apps.googleusercontent.com`
- **Where to get it:** Google Cloud Console → APIs & Services → Credentials → your OAuth Client
- **Used in:** `backend/auth/google-login.php` and `backend/auth/google-callback.php`

### `GOOGLE_CLIENT_SECRET`
- **What it is:** The secret key that proves to Google that the request is coming from your app.
- **Example:** `GOCSPX-abcdefghijklmnop`
- **Where to get it:** Same place as Client ID (shown together)
- **⚠️ Keep this private.** Anyone with this key could impersonate your app.

### `MONGO_URI`
- **What it is:** The full connection string to your MongoDB Atlas database cluster.
- **Format:** `mongodb+srv://username:password@cluster-name.mongodb.net/?appName=medivita`
- **Where to get it:** MongoDB Atlas → Database → Connect → Connect your application → PHP driver → Copy the URI → Replace `<password>` with your actual password
- **⚠️ Keep this private.** This contains your database password.

### `MONGO_DB`
- **What it is:** The name of the database inside MongoDB Atlas that this project uses.
- **Value:** `hospital_management`
- **Why it exists:** Allows switching between databases easily (e.g., a test database vs. the live database).

---

## 8. How the Project Works (Flow)

### Registration & Login Flow

```
User visits index.html
      ↓
Clicks "Register"
      ↓
Fills out register.html form
      ↓
Form submits to backend/auth/register.php (POST)
      ↓
PHP validates input → hashes password → saves to MongoDB "patients" collection
      ↓
User is redirected to login.html
      ↓
User logs in with email + password
      ↓
backend/auth/login.php checks MongoDB → verifies password hash
      ↓
PHP sets session variables: user_id, user_name, user_role
      ↓
Redirect to correct dashboard:
  - patient  → /frontend/patient/dashboard.html
  - doctor   → /frontend/doctor/doctor-dashboard.php
  - admin    → /frontend/admin/admin-dashboard.html
```

### Google OAuth Flow

```
User clicks "Continue with Google" on login.html
      ↓
Browser goes to backend/auth/google-login.php
      ↓
PHP generates a security token (CSRF state) and builds a Google URL
      ↓
User is redirected to Google's login page
      ↓
User selects their Google account
      ↓
Google redirects back to backend/auth/google-callback.php
      ↓
PHP checks MongoDB for the user's email
  ├── Email exists as patient → set session → redirect to patient dashboard
  ├── Email exists as doctor  → set session → redirect to doctor dashboard
  └── Email not found         → redirect to complete-profile.html
             ↓
User fills in phone and role → saved to MongoDB → redirected to dashboard
```

### Booking an Appointment Flow

```
Patient visits book-appointment.html
      ↓
JavaScript calls /backend/shared/get_departments.php → populates department dropdown
      ↓
Patient selects department → JavaScript calls /backend/shared/get_doctors_by_department.php
      ↓
Patient selects doctor, date, time, reason → clicks "Book Appointment"
      ↓
JavaScript sends POST to /backend/shared/book_appointment.php
      ↓
PHP checks: Is user logged in? Is patient? Is doctor valid? Is slot already taken?
      ↓
MongoDB "appointments" collection gets new document with status = "pending"
      ↓
Patient sees success message
      ↓
Doctor can see it in their dashboard / schedule
```

### Data Storage in MongoDB

All data is stored in a MongoDB Atlas cloud database called `hospital_management`. It has these main collections:

| Collection | What It Stores |
|------------|---------------|
| `patients` | Registered patient accounts |
| `doctors` | Doctor accounts and professional details |
| `appointments` | All booked appointments with status |
| `prescriptions` | Prescriptions written by doctors |
| `messages` | Messages between patients and doctors |

---

## 9. Pages Explanation

### Authentication Pages (`frontend/auth/`)

| Page | Purpose |
|------|---------|
| `login.html` | Login form with email/password and Google OAuth button. Shows error messages from session if login fails. |
| `register.html` | Registration form for new patients. Includes real-time password strength meter, validation, and Google sign-up option. |
| `complete-profile.html` | Shown only to first-time Google login users. Collects phone number and role (Patient or Doctor) before proceeding. |

### Patient Pages (`frontend/patient/`)

| Page | Purpose |
|------|---------|
| `dashboard.html` | Patient home page. Shows upcoming appointments, recent activity summary, and quick-action buttons. Fetches data from `get_patient_dashboard_data.php`. |
| `book-appointment.html` | Multi-step appointment booking form. Filter by department, pick a doctor, choose date/time, add a reason. |
| `my-appointments.html` | Lists all patient appointments with colour-coded status badges (pending, approved, completed, cancelled). Has cancel button. |
| `medical-records.html` | Shows all prescriptions written by the patient's doctors. |
| `symptom-checker.html` | AI-powered tool. Patient selects symptoms from a grid, sets severity and duration, and the system recommends the right medical speciality and a direct booking link. |
| `message-to-doctor.html` | A form to send a message to a specific doctor. |
| `patient-messages.html` | Inbox showing all received and sent messages with doctors. |
| `patient-profile.html` | View and edit personal information (name, phone, address, blood group, etc.). |
| `notifications.html` | System notifications for the patient. |
| `settings.html` | Account settings. |

### Doctor Pages (`frontend/doctor/`)

| Page | Purpose |
|------|---------|
| `doctor-dashboard.php` | PHP-rendered dashboard (session checked server-side). Shows total patients, today's appointments, and quick stats. |
| `todays-schedule.html` | Lists all of today's appointments for the logged-in doctor, sorted by time. |
| `schedules.html` | Full schedule view — all upcoming appointments with filters. |
| `patient-history.html` | View complete medical history for a selected patient. |
| `add-prescription.html` | Form to write a new prescription for a patient (medicine, dosage, frequency, diagnosis). |
| `doctor-messages.html` | Doctor's inbox — view patient messages and reply to them. |
| `doctor_profile.html` | Doctor's profile page — update name, specialisation, department, contact, bio. |

### Admin Pages (`frontend/admin/`)

| Page | Purpose |
|------|---------|
| `admin-dashboard.html` | Overview of the whole hospital: total doctors, patients, appointments; recent patients and top doctors shown as tables. |
| `manage-doctors.html` | Full list of all doctors. Admins can toggle active/inactive status, edit details. |
| `manage-patients.html` | Full list of all patients. Admins can add new patients or delete existing ones. |
| `appointment-analytics.html` | Rich analytics dashboard with 5 interactive Chart.js charts: status overview, department breakdown, monthly trend, most active doctors, status donut. |
| `settings.html` | Admin-level settings page. |

### Root Level

| File | Purpose |
|------|---------|
| `index.html` | The public landing page — first thing visitors see. Contains hero section, features overview, and links to login/register. |

---

## 10. Troubleshooting

### ❌ "MONGO_URI is not set" error
- **Cause:** Your `.env` file is missing or not in the right place.
- **Fix:**
  1. Make sure `.env` exists at the root of the project (same folder as `composer.json`)
  2. Make sure `MONGO_URI` is correctly set inside it
  3. Run `composer install` if you haven't already (phpdotenv must be installed)

### ❌ MongoDB Atlas connection fails / timeout
- **Cause 1:** Your IP address is not whitelisted in Atlas
- **Fix:** Go to MongoDB Atlas → Network Access → Add IP Address → Add `0.0.0.0/0` (allow all, for development)
- **Cause 2:** Wrong username or password in the URI
- **Fix:** Double-check the URI in `.env`. The password must be URL-encoded (e.g. `@` → `%40`, `$` → `%24`)

### ❌ Google login shows "redirect_uri_mismatch" error
- **Cause:** The redirect URI in your Google Cloud Console does not exactly match what the code uses.
- **Fix:**
  1. Go to Google Cloud Console → Credentials → your OAuth Client
  2. Under **Authorized Redirect URIs**, make sure this exact URL is listed:
     ```
     http://localhost/medivita/backend/auth/google-callback.php
     ```
  3. Save and wait 1–2 minutes for Google to update

### ❌ Blank page after login (no redirect)
- **Cause:** PHP session could not be started (permissions issue or headers already sent).
- **Fix:**
  1. Make sure Apache is running
  2. Check the PHP error log at `/opt/lampp/logs/php_error_log`
  3. Ensure there is no whitespace or output before the `<?php` opening tag in any included file

### ❌ "composer: command not found"
- **Cause:** Composer is not installed or not in your system PATH.
- **Fix:** Download Composer from [getcomposer.org](https://getcomposer.org/download/) and follow the installation instructions for your OS.

### ❌ `vendor/` folder is missing
- **Cause:** Someone cloned the repo but didn't run `composer install`. The vendor folder is intentionally excluded from Git.
- **Fix:** Run `composer install` inside the project folder.

### ❌ Login works but patient dashboard shows no data
- **Cause:** The patient's `_id` in MongoDB doesn't match the session `user_id`.
- **Fix:** Log out, clear browser cookies, and log in again to get a fresh session.

### ❌ Charts not showing in Analytics page
- **Cause:** The browser couldn't load Chart.js from the CDN (no internet, or blocked).
- **Fix:** Check your internet connection. Chart.js is loaded via CDN — it requires internet access.

---

## 11. Conclusion

MediVita is a professional-grade, full-stack Hospital Management System that demonstrates the complete development lifecycle — from user authentication to data management and analytics.

### What the Project Covers

- ✅ **Multi-role authentication** — standard login + Google OAuth with role-based routing
- ✅ **Complete Patient Portal** — appointment booking, medical records, AI symptom checker, messaging
- ✅ **Complete Doctor Portal** — patient management, prescriptions, schedules, messaging
- ✅ **Complete Admin Portal** — user management, analytics with 5 interactive charts
- ✅ **Secure backend** — session middleware, input sanitisation, password hashing, CSRF protection
- ✅ **Cloud database** — MongoDB Atlas with a clean, documents-based data model
- ✅ **Environment-based configuration** — no hardcoded secrets; `.env` keeps credentials safe
- ✅ **Clean, modular code** — shared utilities, reusable PHP helpers, component-based JS

### Tech Stack Summary

```
Frontend  →  HTML5 + CSS3 + Vanilla JavaScript
Backend   →  PHP 8.1
Database  →  MongoDB Atlas (Cloud NoSQL)
Auth      →  PHP Sessions + Google OAuth 2.0
Libraries →  Composer (phpdotenv, mongodb, google/apiclient)
Charts    →  Chart.js (CDN)
Icons     →  Font Awesome (CDN)
Server    →  Apache via XAMPP / LAMPP
```

---

*Built by Team 10 — MediVita Hospital Management System*
