document.addEventListener('DOMContentLoaded', () => {
  'use strict';

  /* ---------- Toasts: auto-dismiss (unchanged timing) with an exit animation ---------- */
  const dismissToast = (toast) => {
    if (!toast.isConnected || toast.classList.contains('is-leaving')) return;
    toast.classList.add('is-leaving');
    const remove = () => toast.remove();
    toast.addEventListener('animationend', remove, { once: true });
    setTimeout(remove, 450); // safety net if animations are disabled
  };
  setTimeout(() => document.querySelectorAll('.toast').forEach(dismissToast), 3500);

  /* ---------- Confirm-before-action links/buttons (unchanged) ---------- */
  document.querySelectorAll('[data-confirm]').forEach((el) => {
    el.addEventListener('click', (e) => { if (!confirm(el.dataset.confirm)) e.preventDefault(); });
  });

  /* ---------- Sidebar: highlight the current page ---------- */
  const sidebar = document.querySelector('.sidebar');
  if (!sidebar) return;

  // Sub-pages that should light up their parent link when the page has no link of its own.
  const PARENT = {
    'activity.php': 'activities.php', 'quiz.php': 'activities.php', 'quiz_result.php': 'activities.php',
    'submit.php': 'activities.php', 'submit_assignment.php': 'activities.php',
    'activity_form.php': 'activities.php', 'quiz_questions.php': 'activities.php',
    'assignments.php': 'activities.php', 'create_assignment.php': 'activities.php',
    'edit_assignment.php': 'activities.php', 'delete_assignment.php': 'activities.php',
    'course.php': 'courses.php', 'lesson.php': 'courses.php', 'lesson_form.php': 'courses.php',
    'create_course.php': 'courses.php', 'course_form.php': 'courses.php',
    'grade_submission.php': 'submissions.php', 'quiz_attempt.php': 'submissions.php',
    'quiz_submission.php': 'submissions.php',
    'edit_announcement.php': 'announcements.php', 'delete_announcement.php': 'announcements.php',
    'create_announcement.php': 'announcements.php',
    'enroll.php': 'invitations.php', 'user_form.php': 'users.php', 'section_members.php': 'sections.php'
  };
  const file = (a) => a.pathname.split('/').pop();
  const navLinks = [...sidebar.querySelectorAll('.nav a[href]')].filter((a) => file(a) !== 'logout.php');
  let current = navLinks.find((a) => a.pathname === location.pathname);
  if (!current && !sidebar.querySelector('.nav a.active')) {
    const parent = PARENT[location.pathname.split('/').pop()];
    if (parent) current = navLinks.find((a) => file(a) === parent);
  }
  if (current) {
    navLinks.forEach((a) => { a.classList.remove('active'); a.removeAttribute('aria-current'); });
    current.classList.add('active');
    current.setAttribute('aria-current', 'page');
  }

  /* ---------- Mobile navigation drawer (<= 760px) ---------- */
  const topbar = document.querySelector('.topbar');
  if (!topbar || !topbar.firstElementChild) return;

  if (!sidebar.id) sidebar.id = 'app-sidebar';

  const toggle = document.createElement('button');
  toggle.type = 'button';
  toggle.className = 'nav-toggle';
  toggle.setAttribute('aria-controls', sidebar.id);
  toggle.setAttribute('aria-expanded', 'false');
  toggle.setAttribute('aria-label', 'Open menu');
  toggle.innerHTML = '<i class="fa-solid fa-bars" aria-hidden="true"></i>';

  // display:contents on desktop, so the topbar layout there is unchanged
  const lead = document.createElement('div');
  lead.className = 'topbar-lead';
  topbar.insertBefore(lead, topbar.firstElementChild);
  lead.append(toggle, lead.nextElementSibling);

  const close = document.createElement('button');
  close.type = 'button';
  close.className = 'nav-close';
  close.setAttribute('aria-label', 'Close menu');
  close.innerHTML = '<i class="fa-solid fa-xmark" aria-hidden="true"></i>';
  sidebar.prepend(close);

  const backdrop = document.createElement('div');
  backdrop.className = 'nav-backdrop';
  document.body.appendChild(backdrop);

  const isOpen = () => document.body.classList.contains('nav-open');
  const setOpen = (open, restoreFocus = true) => {
    if (open === isOpen()) return;
    document.body.classList.toggle('nav-open', open);
    toggle.setAttribute('aria-expanded', String(open));
    toggle.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
    if (open) close.focus();
    else if (restoreFocus && sidebar.contains(document.activeElement)) toggle.focus();
  };

  toggle.addEventListener('click', () => setOpen(!isOpen()));
  close.addEventListener('click', () => setOpen(false));
  backdrop.addEventListener('click', () => setOpen(false));
  sidebar.addEventListener('click', (e) => { if (e.target.closest('a')) setOpen(false, false); });

  document.addEventListener('keydown', (e) => {
    if (!isOpen()) return;
    if (e.key === 'Escape') { setOpen(false); return; }
    if (e.key !== 'Tab') return;
    const items = [...sidebar.querySelectorAll('button, a[href]')];
    const first = items[0], last = items[items.length - 1];
    if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
    else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
  });

  window.matchMedia('(max-width: 760px)').addEventListener('change', (m) => { if (!m.matches) setOpen(false, false); });
});
