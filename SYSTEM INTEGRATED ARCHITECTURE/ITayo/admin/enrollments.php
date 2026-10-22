<?php
declare(strict_types=1);
require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/../includes/auth.php';
require_role('admin');
$user=current_user();
$page_title='Student Enrollments';
$pdo=db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = post('action');
    $studentId = (int)($_POST['student_id'] ?? 0);
    $courseId = (int)($_POST['course_id'] ?? 0);

    if ($action === 'enroll') {
        if (!$studentId || !$courseId) {
            flash('error', 'Please select both a student and a course.');
        } else {
            $studentStmt = $pdo->prepare("SELECT id FROM users WHERE id=? AND role_id=(SELECT id FROM roles WHERE name='student') AND status='active'");
            $studentStmt->execute([$studentId]);
            $courseStmt = $pdo->prepare("SELECT id FROM courses WHERE id=? AND status <> 'archived'");
            $courseStmt->execute([$courseId]);

            if (!$studentStmt->fetch()) {
                flash('error', 'The selected student is not available for enrollment.');
            } elseif (!$courseStmt->fetch()) {
                flash('error', 'The selected course is not available.');
            } else {
                $existing = $pdo->prepare('SELECT id,status FROM enrollments WHERE student_id=? AND course_id=? LIMIT 1');
                $existing->execute([$studentId, $courseId]);
                $enrollment = $existing->fetch();

                if ($enrollment) {
                    if ($enrollment['status'] === 'active') {
                        flash('error', 'This student is already actively enrolled in that course.');
                    } else {
                        $restore = $pdo->prepare("UPDATE enrollments SET status='active', enrolled_at=CURRENT_TIMESTAMP WHERE id=?");
                        $restore->execute([$enrollment['id']]);
                        flash('success', 'The existing enrollment was restored to active.');
                    }
                } else {
                    $insert = $pdo->prepare("INSERT INTO enrollments (student_id,course_id,status) VALUES (?,?, 'active')");
                    $insert->execute([$studentId, $courseId]);
                    flash('success', 'Student enrolled successfully.');
                }
            }
        }
        redirect('admin/enrollments.php');
    }

    if ($action === 'drop') {
        $enrollmentId = (int)($_POST['enrollment_id'] ?? 0);
        if ($enrollmentId) {
            $stmt = $pdo->prepare("UPDATE enrollments SET status='dropped' WHERE id=?");
            $stmt->execute([$enrollmentId]);
            flash('success', 'Enrollment marked as dropped.');
        }
        redirect('admin/enrollments.php');
    }
}

$q = trim((string)($_GET['q'] ?? ''));
$status = trim((string)($_GET['status'] ?? 'active'));
if (!in_array($status, ['active','completed','dropped','all'], true)) $status = 'active';

$students = $pdo->query("SELECT u.id,u.first_name,u.last_name,u.email FROM users u JOIN roles r ON r.id=u.role_id WHERE r.name='student' AND u.status='active' ORDER BY u.last_name,u.first_name")->fetchAll();
$courses = $pdo->query("SELECT c.id,c.title,c.course_code,sub.name subject_name,sec.name section_name FROM courses c LEFT JOIN subjects sub ON sub.id=c.subject_id LEFT JOIN sections sec ON sec.id=c.section_id WHERE c.status <> 'archived' ORDER BY c.title")->fetchAll();

$sql = "SELECT e.id,e.status,e.enrolled_at,u.id student_id,u.first_name,u.last_name,u.email,c.id course_id,c.title,c.course_code,sub.name subject_name,sec.name section_name
        FROM enrollments e
        JOIN users u ON u.id=e.student_id
        JOIN courses c ON c.id=e.course_id
        LEFT JOIN subjects sub ON sub.id=c.subject_id
        LEFT JOIN sections sec ON sec.id=c.section_id";
$params = [];
$where = [];
if ($q !== '') {
    $where[] = '(u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR c.title LIKE ? OR c.course_code LIKE ?)';
    $x = "%{$q}%";
    $params = [$x,$x,$x,$x,$x];
}
if ($status !== 'all') {
    $where[] = 'e.status=?';
    $params[] = $status;
}
if ($where) $sql .= ' WHERE '.implode(' AND ', $where);
$sql .= ' ORDER BY u.last_name,u.first_name,c.title';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();
?>
<?php require __DIR__.'/../includes/header.php'; ?>
<div class="app">
<aside class="sidebar">
<div class="brand"><div class="brand-mark"><i class="fa-solid fa-graduation-cap"></i></div><div><strong>ITayo</strong><span>Admin</span></div></div>
<nav class="nav">
<a href="dashboard.php"><i class="fa-solid fa-grid-2"></i>Dashboard</a>
<a href="users.php" class="active"><i class="fa-solid fa-users"></i>Users</a>
<a href="enrollments.php" class="active"><i class="fa-solid fa-user-plus"></i>Enrollments</a>
<a href="courses.php"><i class="fa-solid fa-book-open"></i>Courses</a>
<a href="subjects.php"><i class="fa-solid fa-book"></i>Subjects</a>
<a href="sections.php"><i class="fa-solid fa-layer-group"></i>Sections</a>
<a href="../google_account.php"><i class="fa-brands fa-google"></i>Google Account</a>
<div class="nav-sep"></div><a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i>Sign out</a>
</nav>
</aside>
<main class="main">
<header class="topbar"><div><span class="eyebrow">Admin portal</span><h1>Student Enrollments</h1></div><div class="user-chip"><div class="avatar"><?= e(strtoupper(substr($user['first_name'],0,1).substr($user['last_name'],0,1))) ?></div><div><b><?= e($user['first_name'].' '.$user['last_name']) ?></b><span>Admin</span></div></div></header>
<div class="content">
<section class="card card-pad" style="margin-bottom:18px">
<div class="section-head"><div><span class="eyebrow">Enrollment</span><h2>Enroll a Student</h2></div><i class="fa-solid fa-user-plus" style="color:var(--pink-600);font-size:22px"></i></div>
<form method="post">
<input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="enroll">
<div class="form-grid">
<div class="form-group"><label>Student</label><select class="select" name="student_id" required><option value="">Select student</option><?php foreach($students as $s): ?><option value="<?= $s['id'] ?>"><?= e($s['last_name'].', '.$s['first_name'].' — '.$s['email']) ?></option><?php endforeach; ?></select></div>
<div class="form-group"><label>Course</label><select class="select" name="course_id" required><option value="">Select course</option><?php foreach($courses as $c): ?><option value="<?= $c['id'] ?>"><?= e($c['course_code'].' — '.$c['title'].($c['section_name'] ? ' · '.$c['section_name'] : '')) ?></option><?php endforeach; ?></select></div>
</div>
<div class="form-actions"><button class="btn btn-primary" type="submit"><i class="fa-solid fa-user-plus"></i>Enroll Student</button></div>
</form>
</section>

<section class="card card-pad">
<div class="section-head"><div><span class="eyebrow">Records</span><h2>Enrollment Records</h2></div><span class="badge"><?= count($rows) ?> records</span></div>
<form class="actions" method="get" style="margin-bottom:15px">
<input class="input" name="q" style="max-width:390px" placeholder="Search student, email, course or code" value="<?= e($q) ?>">
<select class="select" name="status" style="max-width:160px"><option value="active" <?= $status==='active'?'selected':'' ?>>Active</option><option value="completed" <?= $status==='completed'?'selected':'' ?>>Completed</option><option value="dropped" <?= $status==='dropped'?'selected':'' ?>>Dropped</option><option value="all" <?= $status==='all'?'selected':'' ?>>All statuses</option></select>
<button class="btn btn-soft">Filter</button><a class="btn btn-soft" href="enrollments.php">Reset</a>
</form>
<div class="table-wrap"><table class="table"><thead><tr><th>Student</th><th>Course</th><th>Section</th><th>Enrolled</th><th>Status</th><th>Action</th></tr></thead><tbody>
<?php foreach($rows as $r): ?><tr>
<td><b><?= e($r['first_name'].' '.$r['last_name']) ?></b><br><small class="muted"><?= e($r['email']) ?></small></td>
<td><b><?= e($r['course_code']) ?></b><br><small class="muted"><?= e($r['title']) ?></small></td>
<td><?= e($r['section_name'] ?? '—') ?></td>
<td><?= e(date('M d, Y', strtotime($r['enrolled_at']))) ?></td>
<td><span class="badge <?= $r['status']==='active'?'green':($r['status']==='completed'?'purple':'gray') ?>"><?= e(ucfirst($r['status'])) ?></span></td>
<td><?php if($r['status']==='active'): ?><form method="post" style="display:inline"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="drop"><input type="hidden" name="enrollment_id" value="<?= $r['id'] ?>"><button class="btn btn-danger" type="submit" data-confirm="Mark this enrollment as dropped?"><i class="fa-solid fa-user-minus"></i>Drop</button></form><?php else: ?><span class="muted">No action</span><?php endif; ?></td>
</tr><?php endforeach; ?>
<?php if(!$rows): ?><tr><td colspan="6" class="empty"><i class="fa-solid fa-user-group"></i><br>No enrollment records found.</td></tr><?php endif; ?>
</tbody></table></div>
</section>
</div>
</main></div>
<?php require __DIR__.'/../includes/footer.php'; ?>
