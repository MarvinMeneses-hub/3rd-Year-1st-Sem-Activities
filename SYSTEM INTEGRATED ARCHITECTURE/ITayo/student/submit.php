<?php
declare(strict_types=1);
require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/../includes/auth.php';
require_role('student');

$user = current_user();
$pdo = db();
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

$stmt = $pdo->prepare("SELECT ass.id AS assignment_id, ass.allow_late, a.id AS activity_id, a.title, a.instructions, a.points, a.due_at, a.course_id, c.title AS course_title, c.course_code
    FROM assignments ass
    INNER JOIN activities a ON a.id=ass.activity_id
    INNER JOIN courses c ON c.id=a.course_id
    WHERE ass.id=?
      AND EXISTS (SELECT 1 FROM enrollments e WHERE e.course_id=c.id AND e.student_id=? AND e.status IN ('active','completed'))
    LIMIT 1");
$stmt->execute([$id, $user['id']]);
$activity = $stmt->fetch();
if (!$activity) {
    exit('Assignment not found or you are not enrolled in this course.');
}

$uploadDir = __DIR__.'/../uploads/submissions';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0775, true);
}

$allowed = [
    'pdf'=>'application/pdf',
    'doc'=>'application/msword',
    'docx'=>'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'ppt'=>'application/vnd.ms-powerpoint',
    'pptx'=>'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    'xls'=>'application/vnd.ms-excel',
    'xlsx'=>'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'txt'=>'text/plain',
    'zip'=>'application/zip',
    'jpg'=>'image/jpeg',
    'jpeg'=>'image/jpeg',
    'png'=>'image/png'
];
$maxBytes = 10 * 1024 * 1024;

$existingStmt = $pdo->prepare("SELECT * FROM submissions WHERE assignment_id=? AND student_id=? LIMIT 1");
$existingStmt->execute([$id, $user['id']]);
$existing = $existingStmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $content = post('content');
    $newFilePath = $existing['file_path'] ?? null;
    $newPhysicalPath = null;

    if (!empty($_FILES['submission_file']['name'])) {
        $file = $_FILES['submission_file'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            flash('error', 'The file upload failed. Please try again.');
            redirect('student/submit.php?id='.$id);
        }
        if ((int)$file['size'] > $maxBytes) {
            flash('error', 'The file is too large. Maximum size is 10 MB.');
            redirect('student/submit.php?id='.$id);
        }

        $original = (string)$file['name'];
        $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
        if (!isset($allowed[$ext])) {
            flash('error', 'This file type is not allowed.');
            redirect('student/submit.php?id='.$id);
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']) ?: '';
        $mimeOk = ($mime === $allowed[$ext]);
        if (!$mimeOk && in_array($ext, ['doc','docx','ppt','pptx','xls','xlsx','zip'], true)) {
            $mimeOk = str_starts_with($mime, 'application/');
        }
        if (!$mimeOk) {
            flash('error', 'The uploaded file type could not be verified.');
            redirect('student/submit.php?id='.$id);
        }

        $stored = bin2hex(random_bytes(16)).'.'.$ext;
        $destination = $uploadDir.'/'.$stored;
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            flash('error', 'The server could not save the uploaded file.');
            redirect('student/submit.php?id='.$id);
        }
        $newPhysicalPath = $destination;
        $newFilePath = 'uploads/submissions/'.$stored;
    }

    if ($content === '' && !$newFilePath) {
        flash('error', 'Please enter a response or attach a file before submitting.');
        redirect('student/submit.php?id='.$id);
    }

    try {
        $pdo->beginTransaction();
        if ($existing) {
            $update = $pdo->prepare("UPDATE submissions SET content=?, file_path=?, submitted_at=NOW() WHERE id=?");
            $update->execute([$content, $newFilePath, $existing['id']]);
        } else {
            $insert = $pdo->prepare("INSERT INTO submissions(assignment_id,student_id,content,file_path,submitted_at) VALUES(?,?,?,?,NOW())");
            $insert->execute([$id, $user['id'], $content, $newFilePath]);
        }
        $pdo->commit();

        if ($newPhysicalPath && !empty($existing['file_path'])) {
            $old = __DIR__.'/../'.ltrim($existing['file_path'], '/');
            if (is_file($old)) @unlink($old);
        }
        flash('success', 'Your activity was submitted successfully.');
        redirect('student/activity.php?id='.$activity['activity_id']);
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        if ($newPhysicalPath && is_file($newPhysicalPath)) @unlink($newPhysicalPath);
        throw $e;
    }
}

$page_title='Submit Activity';
require __DIR__.'/../includes/header.php';
?>
<div class="app">
<aside class="sidebar"><div class="brand"><div class="brand-mark"><i class="fa-solid fa-graduation-cap"></i></div><div><strong>ITayo</strong><span>Student</span></div></div>
<nav class="nav"><a href="dashboard.php"><i class="fa-solid fa-grid-2"></i>Dashboard</a><a href="courses.php"><i class="fa-solid fa-book-open"></i>My Courses</a><a href="activities.php"><i class="fa-solid fa-list-check"></i>Activities</a><a href="invitations.php"><i class="fa-solid fa-envelope-open-text"></i>Course Invitations</a><a href="grades.php"><i class="fa-solid fa-chart-line"></i>Grades</a><a href="../google_account.php"><i class="fa-brands fa-google"></i>Google Account</a>
<div class="nav-sep"></div><a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i>Sign out</a></nav></aside>
<main class="main"><header class="topbar"><div><span class="eyebrow">Student portal</span><h1>Submit Activity</h1></div><div class="user-chip"><div class="avatar"><?=e(strtoupper(substr($user['first_name'],0,1).substr($user['last_name'],0,1)))?></div><div><b><?=e($user['first_name'].' '.$user['last_name'])?></b><span>Student</span></div></div></header>
<div class="content"><section class="card card-pad"><span class="eyebrow"><?=e($activity['course_code'])?></span><h2><?=e($activity['title'])?></h2><p class="muted"><?=nl2br(e($activity['instructions'] ?? ''))?></p>
<div class="notice"><i class="fa-solid fa-paperclip"></i> You may submit text, a file, or both. Maximum file size: 10 MB.</div>
<form method="post" enctype="multipart/form-data"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="id" value="<?=$id?>">
<div class="form-group"><label>Your response</label><textarea class="textarea" style="min-height:240px" name="content" placeholder="Write your answer or submission here..."><?=e($existing['content'] ?? '')?></textarea></div>
<div class="form-group"><label>Attach file</label><input class="input" type="file" name="submission_file" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.txt,.zip,.jpg,.jpeg,.png"><small class="muted">Allowed: PDF, Word, PowerPoint, Excel, TXT, ZIP, JPG, PNG.</small></div>
<?php if (!empty($existing['file_path'])): ?><div class="notice"><i class="fa-solid fa-file"></i> Current file: <a href="<?=e(APP_URL.'/'.$existing['file_path'])?>" target="_blank" rel="noopener">Open submitted file</a>. Uploading another file will replace it.</div><?php endif; ?>
<div class="form-actions"><button class="btn btn-primary" type="submit"><i class="fa-solid fa-paper-plane"></i> Submit Activity</button></div></form></section></div></main></div>
<?php require __DIR__.'/../includes/footer.php'; ?>
