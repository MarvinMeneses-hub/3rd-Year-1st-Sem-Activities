<?php
declare(strict_types=1);
require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/../includes/auth.php';
require_role('teacher');

$user = current_user();
$pdo = db();
$attemptId = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT
    qa.id AS attempt_id,
    qa.score,
    qa.answers_json,
    qa.started_at,
    qa.submitted_at,
    q.id AS quiz_id,
    q.title AS quiz_title,
    q.description,
    CONCAT(u.first_name,' ',u.last_name) AS student_name,
    u.email,
    a.id AS activity_id,
    a.title AS activity_title,
    a.points AS activity_points,
    c.title AS course_title,
    c.course_code
FROM quiz_attempts qa
INNER JOIN quizzes q ON q.id=qa.quiz_id
INNER JOIN users u ON u.id=qa.student_id
INNER JOIN courses c ON c.id=q.course_id
INNER JOIN activities a ON (
    q.activity_id=a.id
    OR (q.activity_id IS NULL AND a.course_id=q.course_id AND a.title=q.title AND a.activity_type='quiz')
)
INNER JOIN teacher_courses tc ON tc.course_id=c.id
WHERE qa.id=? AND tc.teacher_id=?
LIMIT 1");
$stmt->execute([$attemptId, $user['id']]);
$row = $stmt->fetch();
if (!$row) exit('Quiz submission not found or you do not have permission to view it.');

$maxStmt = $pdo->prepare('SELECT COALESCE(SUM(points), 0) FROM quiz_questions WHERE quiz_id=?');
$maxStmt->execute([(int)$row['quiz_id']]);
$row['max_score_display'] = (float)$maxStmt->fetchColumn();
if ($row['max_score_display'] <= 0) $row['max_score_display'] = (float)$row['activity_points'];

$answers = [];
if (!empty($row['answers_json'])) {
    $decoded = json_decode((string)$row['answers_json'], true);
    if (is_array($decoded)) $answers = $decoded;
}

$qStmt = $pdo->prepare("SELECT * FROM quiz_questions WHERE quiz_id=? ORDER BY sort_order,id");
$qStmt->execute([(int)$row['quiz_id']]);
$questions = $qStmt->fetchAll();
$choiceStmt = $pdo->prepare("SELECT * FROM quiz_choices WHERE question_id=? ORDER BY sort_order,id");

$page_title = 'Quiz Submission';
require __DIR__.'/../includes/header.php';
?>
<div class="app">
<aside class="sidebar"><div class="brand"><div class="brand-mark"><i class="fa-solid fa-graduation-cap"></i></div><div><strong>ITayo</strong><span>Teacher</span></div></div>
<nav class="nav">
<a href="dashboard.php"><i class="fa-solid fa-grid-2"></i>Dashboard</a>
<a href="courses.php"><i class="fa-solid fa-book-open"></i>My Courses</a>
<a href="activities.php"><i class="fa-solid fa-list-check"></i>Activities</a>
<a href="submissions.php" class="active"><i class="fa-solid fa-file-circle-check"></i>Submissions</a>
<a href="analytics.php"><i class="fa-solid fa-chart-line"></i>Analytics</a>
<a href="invitations.php"><i class="fa-solid fa-envelope-open-text"></i>Course Invitations</a>
<a href="students.php"><i class="fa-solid fa-user-graduate"></i>Students</a>
<a href="announcements.php"><i class="fa-solid fa-bullhorn"></i>Announcements</a>
<a href="../google_account.php"><i class="fa-brands fa-google"></i>Google Account</a>
<div class="nav-sep"></div><a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i>Sign out</a>
</nav></aside>
<main class="main">
<header class="topbar"><div><span class="eyebrow">Teacher portal · Quiz submission</span><h1><?=e($row['quiz_title'])?></h1></div><div class="user-chip"><div class="avatar"><?=e(strtoupper(substr($user['first_name'],0,1).substr($user['last_name'],0,1)))?></div><div><b><?=e($user['first_name'].' '.$user['last_name'])?></b><span>Teacher</span></div></div></header>
<div class="content">
<section class="card card-pad" style="margin-bottom:18px">
<div class="section-head"><div><span class="eyebrow"><?=e($row['course_code'])?></span><h2>Student Quiz Submission</h2><p class="muted"><?=e($row['student_name'])?> · <?=e($row['email'])?></p></div><a class="btn btn-soft" href="submissions.php"><i class="fa-solid fa-arrow-left"></i> Back to Submissions</a></div>
<div class="grid three">
<div class="card stat"><div class="stat-icon"><i class="fa-solid fa-user"></i></div><div><small>Student</small><strong><?=e($row['student_name'])?></strong></div></div>
<div class="card stat"><div class="stat-icon"><i class="fa-solid fa-clock"></i></div><div><small>Submitted</small><strong style="font-size:14px"><?=!empty($row['submitted_at'])?e(date('M d, Y h:i A',strtotime($row['submitted_at']))):'—'?></strong></div></div>
<div class="card stat"><div class="stat-icon"><i class="fa-solid fa-chart-simple"></i></div><div><small>Score</small><strong><?=($row['score']!==null?e((string)$row['score']):'Not graded')?><?=(' / '.e((string)$row['max_score_display']))?></strong></div></div>
</div>
</section>

<section class="card card-pad">
<div class="section-head"><div><span class="eyebrow">Answers</span><h2><?=e($row['activity_title'])?></h2></div></div>
<?php foreach($questions as $i=>$question):
    $qid=(string)$question['id'];
    $answer=(string)($answers[$qid] ?? '');
    $choiceStmt->execute([(int)$question['id']]);
    $choices=$choiceStmt->fetchAll();
    $answerLabel=$answer!==''?$answer:'No answer';
    foreach($choices as $choice){ if((string)$choice['id']===$answer){$answerLabel=$choice['choice_text'];break;} }
    $correctLabels=[];
    foreach($choices as $choice){ if((int)$choice['is_correct']===1)$correctLabels[]=$choice['choice_text']; }
?>
<div class="card card-pad" style="margin-bottom:12px">
    <div style="display:flex;justify-content:space-between;gap:16px;align-items:flex-start"><div><b><?=($i+1)?>. <?=e($question['question_text'])?></b><div class="muted" style="margin-top:5px"><?=e(ucwords(str_replace('_',' ',$question['question_type'])))?> · <?=e((string)$question['points'])?> points</div></div></div>
    <div class="notice" style="margin-top:12px"><strong>Student answer:</strong><br><?=e($answerLabel)?></div>
    <?php if($correctLabels): ?><div style="margin-top:10px"><small class="muted">Correct answer:</small><br><b><?=e(implode(', ', $correctLabels))?></b></div><?php endif; ?>
</div>
<?php endforeach; ?>
<?php if(!$questions): ?><div class="empty">This quiz has no questions.</div><?php endif; ?>
</section>
</div></main></div>
<?php require __DIR__.'/../includes/footer.php'; ?>
