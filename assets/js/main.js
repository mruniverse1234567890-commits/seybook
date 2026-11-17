/* SeyBook JavaScript: functions, events and form validation */

/* ---------- FUNCTION 1: welcome message after login ---------- */
function greetUser() {
  var el = document.getElementById('welcomeMsg');
  if (!el) return;
  var name = el.getAttribute('data-name');
  var hour = new Date().getHours();
  var greeting = hour < 12 ? 'Good morning' : (hour < 18 ? 'Good afternoon' : 'Good evening');
  el.textContent = greeting + ', ' + name + '! Ready to book your next visit?';
}

/* ---------- FUNCTION 2: count-up animation for the registered-users number ---------- */
function animateCount(el) {
  var target = parseInt(el.getAttribute('data-target'), 10) || 0;
  var current = 0;
  var step = Math.max(1, Math.ceil(target / 40));
  var timer = setInterval(function () {
    current += step;
    if (current >= target) { current = target; clearInterval(timer); }
    el.textContent = current;
  }, 30);
}

/* ---------- FUNCTION 3: price and end-time summary on the booking page ---------- */
function updateSummary() {
  var select = document.getElementById('service_id');
  var box = document.getElementById('summaryBox');
  if (!select || !box) return;
  var opt = select.options[select.selectedIndex];
  if (!opt.value) { box.textContent = 'Choose a service to see the price and duration.'; return; }
  var price = parseFloat(opt.getAttribute('data-price'));
  var minutes = parseInt(opt.getAttribute('data-duration'), 10);
  var text = 'Price: SCR ' + price.toFixed(2) + ' | Duration: ' + minutes + ' minutes';
  var time = document.getElementById('appt_time').value;
  if (time) {
    var parts = time.split(':');
    var end = new Date(2000, 0, 1, parseInt(parts[0], 10), parseInt(parts[1], 10) + minutes);
    text += ' | Ends about ' + String(end.getHours()).padStart(2, '0') + ':' + String(end.getMinutes()).padStart(2, '0');
  }
  box.textContent = text;
}

/* ---------- EVENT: onclick ---------- */
function showTip() {
  var tips = ['Arrive 10 minutes early with your ID.', 'Cancel at least a day ahead so someone else can use the slot.', 'Bring a list of your questions or medicines.'];
  document.getElementById('tipBox').textContent = tips[Math.floor(Math.random() * tips.length)];
}
function openCancel(id) {
  document.getElementById('confirmCancel').href = 'my_appointments.php?cancel=' + id;
  new bootstrap.Modal(document.getElementById('cancelModal')).show();
}

/* ---------- EVENTS: onmouseover / onmouseout ---------- */
function hoverOn(btn) { btn.classList.add('btn-lift'); }
function hoverOff(btn) { btn.classList.remove('btn-lift'); }

/* ---------- Validation helpers ---------- */
function showErrors(errors) {
  var box = document.getElementById('formErrors');
  if (errors.length === 0) { box.classList.add('d-none'); box.innerHTML = ''; return true; }
  box.classList.remove('d-none');
  box.innerHTML = '<ul class="mb-0"><li>' + errors.join('</li><li>') + '</li></ul>';
  box.scrollIntoView({ behavior: 'smooth', block: 'center' });
  return false;
}
function todayString() {
  var d = new Date();
  return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
}

/* ---------- FORM VALIDATION: registration (5 fields) ---------- */
function validateRegister() {
  var errors = [];
  var f = document.getElementById('registerForm');
  if (f.full_name.value.trim() === '') errors.push('Full name cannot be empty.');
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(f.email.value.trim())) errors.push('Enter a valid email address.');
  if (f.username.value.trim().length < 4) errors.push('Username must be at least 4 characters.');
  if (f.password.value.length < 8) errors.push('Password must be at least 8 characters.');
  if (f.password.value !== f.confirm_password.value) errors.push('Passwords do not match.');
  return showErrors(errors);
}

/* ---------- FORM VALIDATION: login (2 fields) ---------- */
function validateLogin() {
  var errors = [];
  var f = document.getElementById('loginForm');
  if (f.username.value.trim() === '') errors.push('Enter your username.');
  if (f.password.value === '') errors.push('Enter your password.');
  return showErrors(errors);
}

/* ---------- FORM VALIDATION: booking (3 fields) ---------- */
function validateBooking() {
  var errors = [];
  var f = document.getElementById('bookingForm');
  if (f.service_id.value === '') errors.push('Select a service.');
  if (f.appt_date.value === '') errors.push('Choose an appointment date.');
  else if (f.appt_date.value < todayString()) errors.push('The date cannot be in the past.');
  if (f.appt_time.value === '') errors.push('Choose a time slot.');
  return showErrors(errors);
}

/* ---------- Run when the page has loaded ---------- */
document.addEventListener('DOMContentLoaded', function () {
  greetUser();
  document.querySelectorAll('.count').forEach(animateCount);
  updateSummary();
});
