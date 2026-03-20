/* ============================================================
   MediVita Hospital — Auth Pages JavaScript
   Handles AJAX Login and Registration with role-based redirection.
   ============================================================ */

'use strict';

/* ── UTILITY FUNCTIONS ─────────────────────────────────────── */

function showAlert(elId, msg, type) {
  const el = document.getElementById(elId);
  if (!el) return;
  el.className = 'alert alert-' + type + ' visible';
  const msgEl = el.querySelector('.alert-msg');
  if (msgEl) msgEl.textContent = msg;
  const icon = el.querySelector('i');
  if (icon) {
    icon.className = type === 'success' ? 'fas fa-circle-check' : 'fas fa-circle-exclamation';
  }
  // Scroll alert into view
  el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function hideAlert(elId) {
  const el = document.getElementById(elId);
  if (el) el.classList.remove('visible');
}

function setError(input, msg) {
  if (!input) return;
  input.classList.add('is-invalid');
  const errEl = input.closest('.form-group')?.querySelector('.field-err');
  if (errEl) errEl.textContent = msg;
}

function clearError(input) {
  if (!input) return;
  input.classList.remove('is-invalid', 'is-valid');
  const errEl = input.closest('.form-group')?.querySelector('.field-err');
  if (errEl) errEl.textContent = '';
}

function setLoading(btn, loadingText) {
  const original = btn.innerHTML;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' + loadingText;
  btn.disabled = true;
  return () => {
    btn.innerHTML = original;
    btn.disabled = false;
  };
}

/* ── ROLE TOGGLE (Register page) ───────────────────────────── */
(function initRoleToggle() {
  const roleSelect = document.getElementById('reg-role');
  if (!roleSelect) return;
  const applyRole = (val) => {
    document.getElementById('patientFields')?.classList.toggle('visible', val === 'patient');
    document.getElementById('doctorFields')?.classList.toggle('visible', val === 'doctor');
  };
  roleSelect.addEventListener('change', (e) => applyRole(e.target.value));
})();

/* ── PASSWORD TOGGLE (Show / Hide) ─────────────────────────── */
(function initPasswordToggles() {
  document.querySelectorAll('[data-pw-toggle]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      const targetId = btn.dataset.target;
      const input = document.getElementById(targetId);
      if (!input) return;
      const isHidden = input.type === 'password';
      input.type = isHidden ? 'text' : 'password';
      const icon = btn.querySelector('i');
      if (icon) icon.className = isHidden ? 'fas fa-eye-slash' : 'fas fa-eye';
      btn.title = isHidden ? 'Hide password' : 'Show password';
    });
  });
})();

/* ── LOGIN HANDLER ─────────────────────────────────────────── */
(function initLogin() {
  const form = document.getElementById('loginForm');
  if (!form) return;

  form.addEventListener('submit', async function (e) {
    e.preventDefault();
    hideAlert('login-alert');

    const emailEl = document.getElementById('login-email');
    const passEl  = document.getElementById('login-password');
    const roleEl  = document.getElementById('login-role');

    // Clear previous field errors
    [emailEl, passEl, roleEl].forEach(clearError);

    // Client-side validation
    let hasError = false;
    if (!emailEl.value.trim()) {
      setError(emailEl, 'Email address is required.');
      hasError = true;
    }
    if (!passEl.value) {
      setError(passEl, 'Password is required.');
      hasError = true;
    }
    if (!roleEl.value) {
      setError(roleEl, 'Please select your role.');
      hasError = true;
    }
    if (hasError) {
      showAlert('login-alert', 'Please fill in all required fields.', 'error');
      return;
    }

    const restore = setLoading(document.getElementById('loginSubmitBtn'), 'Verifying...');

    try {
      const formData = new FormData(this);
      const resp = await fetch('/medivita/backend/auth/login.php', {
        method: 'POST',
        body: formData,
      });

      // Parse JSON response from backend
      let result;
      try {
        result = await resp.json();
      } catch (parseErr) {
        showAlert('login-alert', 'Unexpected server response. Please try again.', 'error');
        restore();
        return;
      }

      if (result.success) {
        showAlert('login-alert', result.message || 'Login successful! Redirecting...', 'success');
        // Redirect after brief delay so the user sees the success message
        setTimeout(() => {
          window.location.href = result.redirect;
        }, 800);
      } else {
        showAlert('login-alert', result.message || 'Login failed. Please try again.', 'error');
        restore();
      }

    } catch (networkErr) {
      showAlert('login-alert', 'Network error. Please check your connection and try again.', 'error');
      restore();
    }
  });
})();

/* ── REGISTER HANDLER ──────────────────────────────────────── */
(function initRegister() {
  const form = document.getElementById('registerForm');
  if (!form) return;

  form.addEventListener('submit', async function (e) {
    e.preventDefault();
    hideAlert('reg-alert');

    const restore = setLoading(document.getElementById('regSubmitBtn'), 'Creating account...');

    try {
      const formData = new FormData(this);
      const resp = await fetch('/medivita/backend/auth/register.php', {
        method: 'POST',
        body: formData,
      });

      // Parse JSON response from backend
      let result;
      try {
        result = await resp.json();
      } catch (parseErr) {
        showAlert('reg-alert', 'Unexpected server response. Please try again.', 'error');
        restore();
        return;
      }

      if (result.success) {
        showAlert('reg-alert', result.message || 'Account created! Redirecting...', 'success');
        setTimeout(() => {
          window.location.href = result.redirect;
        }, 800);
      } else {
        showAlert('reg-alert', result.message || 'Registration failed. Please try again.', 'error');
        restore();
      }

    } catch (networkErr) {
      showAlert('reg-alert', 'Network error. Please check your connection and try again.', 'error');
      restore();
    }
  });
})();

/* ── PASSWORD STRENGTH METER (Register page) ─────────────────*/
(function initPasswordStrength() {
  const pwInput = document.getElementById('reg-password');
  if (!pwInput) return;

  const segments = document.querySelectorAll('.pw-seg');
  const label    = document.querySelector('.pw-strength-lbl');
  if (!segments.length) return;

  const levels = [
    { label: '',        color: '' },
    { label: 'Weak',    color: '#ef4444' },
    { label: 'Fair',    color: '#f97316' },
    { label: 'Good',    color: '#eab308' },
    { label: 'Strong',  color: '#22c55e' },
  ];

  function getStrength(pw) {
    if (!pw) return 0;
    let score = 0;
    if (pw.length >= 8)  score++;
    if (/[A-Z]/.test(pw)) score++;
    if (/[0-9]/.test(pw)) score++;
    if (/[^A-Za-z0-9]/.test(pw)) score++;
    return score;
  }

  pwInput.addEventListener('input', function () {
    const score = getStrength(this.value);
    segments.forEach((seg, i) => {
      seg.style.background = i < score ? levels[score].color : '';
    });
    if (label) {
      label.textContent = this.value ? levels[score].label : '';
      label.style.color = levels[score].color;
    }
  });
})();
