/* =====================================================
   Sanjeevani — MESSAGES SHARED JAVASCRIPT
   Shared by: patient-messages.html & doctor-messages.html
   Storage: localStorage key "mv_messages"
   ===================================================== */

'use strict';

// ── STORAGE HELPERS ─────────────────────────────────────
const STORAGE_KEY = 'mv_messages';

function loadMessages() {
  try {
    return JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
  } catch { return []; }
}

function saveMessages(msgs) {
  localStorage.setItem(STORAGE_KEY, JSON.stringify(msgs));
}

function addMessage(msg) {
  const msgs = loadMessages();
  msgs.push(msg);
  saveMessages(msgs);
  return msg;
}

// ── DEPARTMENT → DOCTOR MAP ─────────────────────────────
const DEPT_DOCTORS = {
  'Cardiology':       [
    { id:'dr_sharma',  name:'Dr. Arjun Sharma',  initials:'AS', grad:'135deg,#667eea,#764ba2' },
    { id:'dr_reddy',   name:'Dr. Sunita Reddy',  initials:'SR', grad:'135deg,#f093fb,#f5576c' },
  ],
  'Neurology':        [
    { id:'dr_kumar',   name:'Dr. Priya Kumar',   initials:'PK', grad:'135deg,#4facfe,#00f2fe' },
    { id:'dr_mehta',   name:'Dr. Rajiv Mehta',   initials:'RM', grad:'135deg,#43e97b,#38f9d7' },
  ],
  'Orthopedics':      [
    { id:'dr_bose',    name:'Dr. Ananya Bose',   initials:'AB', grad:'135deg,#fa709a,#fee140' },
    { id:'dr_nair',    name:'Dr. Sanjay Nair',   initials:'SN', grad:'135deg,#a18cd1,#fbc2eb' },
  ],
  'Dermatology':      [
    { id:'dr_iyer',    name:'Dr. Kavitha Iyer',  initials:'KI', grad:'135deg,#f77062,#fe5196' },
    { id:'dr_singh',   name:'Dr. Harpal Singh',  initials:'HS', grad:'135deg,#ffecd2,#fcb69f' },
  ],
  'General Medicine': [
    { id:'dr_pillai',  name:'Dr. Rahul Pillai',  initials:'RP', grad:'135deg,#89f7fe,#66a6ff' },
    { id:'dr_thomas',  name:'Dr. Sarah Thomas',  initials:'ST', grad:'135deg,#fddb92,#d1fdff' },
  ],
  'Pulmonology':      [
    { id:'dr_verma',   name:'Dr. Deepa Verma',   initials:'DV', grad:'135deg,#30cfd0,#330867' },
    { id:'dr_rao',     name:'Dr. Suresh Rao',    initials:'SR2',grad:'135deg,#a1c4fd,#c2e9fb' },
  ],
  'Gastroenterology': [
    { id:'dr_gupta',   name:'Dr. Meera Gupta',   initials:'MG', grad:'135deg,#fbc2eb,#a6c1ee' },
    { id:'dr_kaur',    name:'Dr. Harleen Kaur',  initials:'HK', grad:'135deg,#84fab0,#8fd3f4' },
  ],
  'ENT':              [
    { id:'dr_joshi',   name:'Dr. Rohan Joshi',   initials:'RJ', grad:'135deg,#cfd9df,#e2ebf0' },
    { id:'dr_mishra',  name:'Dr. Priti Mishra',  initials:'PM', grad:'135deg,#ffeaa7,#dfe6e9' },
  ],
};

const DEPARTMENTS = Object.keys(DEPT_DOCTORS);

function getDoctorById(id) {
  for (const [dept, docs] of Object.entries(DEPT_DOCTORS)) {
    const d = docs.find(x => x.id === id);
    if (d) return { ...d, dept };
  }
  return null;
}

function getDoctorsByDept(dept) {
  return DEPT_DOCTORS[dept] || [];
}

// ── CONVERSATION KEY ────────────────────────────────────
// Thread is uniquely identified by patientId + doctorId
function threadKey(patientId, doctorId) {
  return `${patientId}::${doctorId}`;
}

function getThread(patientId, doctorId) {
  return loadMessages().filter(m =>
    (m.patientId === patientId && m.doctorId === doctorId)
  ).sort((a, b) => a.ts - b.ts);
}

// ── SEED DEMO DATA (first run only) ─────────────────────
function seedDemoMessages() {
  if (loadMessages().length > 0) return;
  const now = Date.now();
  const MIN = 60000;
  const HOUR = 3600000;
  const demos = [
    { id: 'msg_1', patientId:'patient_rahul', patientName:'Rahul Kumar', doctorId:'dr_sharma', doctorName:'Dr. Arjun Sharma', dept:'Cardiology', sender:'patient', text:'Good morning Doctor. I wanted to ask about the chest discomfort I have been experiencing after exercise. Should I be concerned?', ts: now - 2*HOUR - 10*MIN },
    { id: 'msg_2', patientId:'patient_rahul', patientName:'Rahul Kumar', doctorId:'dr_sharma', doctorName:'Dr. Arjun Sharma', dept:'Cardiology', sender:'doctor',  text:'Good morning Rahul. Thank you for reaching out. Post-exercise chest discomfort can have several causes. Could you describe the sensation — is it sharp, pressure-like, or burning? And does it radiate to your arm or jaw?', ts: now - 2*HOUR },
    { id: 'msg_3', patientId:'patient_rahul', patientName:'Rahul Kumar', doctorId:'dr_sharma', doctorName:'Dr. Arjun Sharma', dept:'Cardiology', sender:'patient', text:'It feels more like a tightness or pressure in the centre of my chest. It usually goes away within a few minutes of resting. No radiation to the arm.', ts: now - 1*HOUR - 45*MIN },
    { id: 'msg_4', patientId:'patient_rahul', patientName:'Rahul Kumar', doctorId:'dr_sharma', doctorName:'Dr. Arjun Sharma', dept:'Cardiology', sender:'doctor',  text:'That description warrants a proper evaluation. I would like you to come in for an ECG and a stress test. Please book an appointment at the earliest. In the meantime, avoid strenuous exercise.', ts: now - 1*HOUR - 30*MIN },
    { id: 'msg_5', patientId:'patient_rahul', patientName:'Rahul Kumar', doctorId:'dr_sharma', doctorName:'Dr. Arjun Sharma', dept:'Cardiology', sender:'patient', text:'Understood, Doctor. I will book the appointment right away. Thank you.', ts: now - 45*MIN },
    { id: 'msg_6', patientId:'patient_rahul', patientName:'Rahul Kumar', doctorId:'dr_kumar',  doctorName:'Dr. Priya Kumar',  dept:'Neurology', sender:'patient', text:'Hello Dr. Kumar, I have been having recurring headaches on the right side of my head for the past two weeks. Could this be a migraine?', ts: now - 3*HOUR },
    { id: 'msg_7', patientId:'patient_rahul', patientName:'Rahul Kumar', doctorId:'dr_kumar',  doctorName:'Dr. Priya Kumar',  dept:'Neurology', sender:'doctor',  text:'Hello Rahul. Unilateral headaches are indeed a common feature of migraines. Are the headaches accompanied by nausea, light sensitivity, or visual disturbances? How long do they typically last?', ts: now - 2*HOUR - 30*MIN },
    { id: 'msg_8', patientId:'patient_rahul', patientName:'Rahul Kumar', doctorId:'dr_kumar',  doctorName:'Dr. Priya Kumar',  dept:'Neurology', sender:'patient', text:'Yes, I get light sensitivity and sometimes nausea. They last about 4 to 6 hours. Paracetamol helps a little but not completely.', ts: now - 2*HOUR - 15*MIN },
  ];
  saveMessages(demos);
}

// ── DATE / TIME FORMATTING ───────────────────────────────
function formatTime(ts) {
  return new Date(ts).toLocaleTimeString('en-IN', { hour: '2-digit', minute: '2-digit' });
}

function formatDateSep(ts) {
  const d = new Date(ts);
  const today = new Date();
  const yesterday = new Date(today); yesterday.setDate(today.getDate() - 1);
  if (d.toDateString() === today.toDateString()) return 'Today';
  if (d.toDateString() === yesterday.toDateString()) return 'Yesterday';
  return d.toLocaleDateString('en-IN', { day: '2-digit', month: 'long', year: 'numeric' });
}

// ── RENDER MESSAGES ─────────────────────────────────────
function renderMessages(container, messages, myRole, myInitials, myGrad, otherInitials, otherGrad) {
  container.innerHTML = '';
  if (!messages.length) {
    container.innerHTML = `
      <div style="flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:10px;color:var(--text-4);text-align:center;padding:40px">
        <i class="fas fa-comment-medical" style="font-size:2.5rem;color:var(--blue-lt)"></i>
        <p style="font-size:.9rem;font-weight:600;color:var(--text-2)">Start the conversation</p>
        <p style="font-size:.82rem;max-width:260px;line-height:1.6">Type your message below to begin a secure consultation with your doctor.</p>
      </div>`;
    return;
  }

  let lastDate = '';
  messages.forEach(msg => {
    const msgDate = formatDateSep(msg.ts);
    if (msgDate !== lastDate) {
      lastDate = msgDate;
      const sep = document.createElement('div');
      sep.className = 'chat-date-sep';
      sep.innerHTML = `<span>${msgDate}</span>`;
      container.appendChild(sep);
    }

    const isSent = msg.sender === myRole;
    const wrap = document.createElement('div');
    wrap.className = `msg-bubble-wrap ${isSent ? 'sent' : 'received'}`;

    const avatarGrad  = isSent ? myGrad  : otherGrad;
    const avatarInit  = isSent ? myInitials : otherInitials;

    wrap.innerHTML = `
      <div class="bubble-avatar" style="background:linear-gradient(${avatarGrad})">${avatarInit}</div>
      <div>
        <div class="msg-bubble">${escapeHTML(msg.text)}</div>
        <span class="msg-time">${formatTime(msg.ts)}</span>
      </div>`;
    container.appendChild(wrap);
  });

  // Scroll to bottom
  requestAnimationFrame(() => {
    container.scrollTop = container.scrollHeight;
  });
}

function escapeHTML(str) {
  return str
    .replace(/&/g,'&amp;')
    .replace(/</g,'&lt;')
    .replace(/>/g,'&gt;')
    .replace(/"/g,'&quot;')
    .replace(/\n/g,'<br>');
}

// ── AUTO-GROWING TEXTAREA ───────────────────────────────
function autoGrow(el) {
  el.style.height = 'auto';
  el.style.height = Math.min(el.scrollHeight, 120) + 'px';
}

// ── TYPING INDICATOR ─────────────────────────────────────
let _typingTimer = null;
function showTypingIndicator(container, initials, grad) {
  removeTypingIndicator(container);
  const wrap = document.createElement('div');
  wrap.className = 'typing-indicator';
  wrap.id = 'typingIndicator';
  wrap.innerHTML = `
    <div class="bubble-avatar" style="background:linear-gradient(${grad});width:30px;height:30px;font-size:.65rem">${initials}</div>
    <div class="typing-dots"><span></span><span></span><span></span></div>`;
  container.appendChild(wrap);
  container.scrollTop = container.scrollHeight;
}
function removeTypingIndicator(container) {
  const el = document.getElementById('typingIndicator');
  if (el) el.remove();
}

// ── DOCTOR AUTO-REPLY ────────────────────────────────────
const AUTO_REPLIES = [
  "Thank you for letting me know. I will review this carefully.",
  "I understand your concern. Please do not worry — we will address this together.",
  "Thank you for the update. Can you tell me a bit more about when it started?",
  "I have noted your symptoms. It would be helpful to schedule a follow-up visit soon.",
  "Good to hear from you. Please continue with the prescribed medication and let me know if there is any change.",
  "Noted. Please ensure you are staying hydrated and getting adequate rest.",
  "I will check your records and get back to you shortly.",
  "Thank you for reaching out. Your health is our priority. We will take the best course of action.",
];

function getAutoReply() {
  return AUTO_REPLIES[Math.floor(Math.random() * AUTO_REPLIES.length)];
}

// ── UNREAD COUNT HELPERS ────────────────────────────────
function getUnreadCountForDoctor(doctorId) {
  return loadMessages().filter(m => m.doctorId === doctorId && m.sender === 'patient' && !m.readByDoctor).length;
}
function markThreadReadByDoctor(patientId, doctorId) {
  const msgs = loadMessages().map(m => {
    if (m.patientId === patientId && m.doctorId === doctorId && m.sender === 'patient') {
      return { ...m, readByDoctor: true };
    }
    return m;
  });
  saveMessages(msgs);
}
function markThreadReadByPatient(patientId, doctorId) {
  const msgs = loadMessages().map(m => {
    if (m.patientId === patientId && m.doctorId === doctorId && m.sender === 'doctor') {
      return { ...m, readByPatient: true };
    }
    return m;
  });
  saveMessages(msgs);
}

// ── GET ALL UNIQUE THREADS FOR DOCTOR ──────────────────
function getThreadsForDoctor(doctorId) {
  const msgs = loadMessages().filter(m => m.doctorId === doctorId);
  const map = {};
  msgs.forEach(m => {
    const key = m.patientId;
    if (!map[key] || m.ts > map[key].ts) map[key] = m;
  });
  return Object.values(map).sort((a, b) => b.ts - a.ts);
}

// ── GET ALL UNIQUE THREADS FOR PATIENT ─────────────────
function getThreadsForPatient(patientId) {
  const msgs = loadMessages().filter(m => m.patientId === patientId);
  const map = {};
  msgs.forEach(m => {
    const key = m.doctorId;
    if (!map[key] || m.ts > map[key].ts) map[key] = m;
  });
  return Object.values(map).sort((a, b) => b.ts - a.ts);
}
