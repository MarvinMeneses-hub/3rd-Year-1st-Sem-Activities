<?php
declare(strict_types=1);
require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/../includes/auth.php';
require_role('student');
$user=current_user(); $pdo=db(); $id=(int)($_GET['id']??0);
$s=$pdo->prepare("SELECT qa.*,q.title,q.course_id,c.title course_title,c.course_code FROM quiz_attempts qa JOIN quizzes q ON q.id=qa.quiz_id JOIN courses c ON c.id=q.course_id WHERE qa.id=? AND qa.student_id=? LIMIT 1");
$s->execute([$id,$user['id']]); $r=$s->fetch(); if(!$r) exit('Quiz attempt not found.');
$page_title='Quiz Result'; require __DIR__.'/../includes/header.php';
$score=(float)($r['score']??0);
// max_score/status are optional columns in newer installations.
// Calculate the maximum directly from the quiz questions when max_score is absent.
if (array_key_exists('max_score', $r) && $r['max_score'] !== null) {
    $max=(float)$r['max_score'];
} else {
    $m=$pdo->prepare('SELECT COALESCE(SUM(points),0) FROM quiz_questions WHERE quiz_id=?');
    $m->execute([(int)$r['quiz_id']]);
    $max=(float)$m->fetchColumn();
}
if (array_key_exists('status', $r) && $r['status']) {
    $status=(string)$r['status'];
} else {
    $status='graded';
    $manual=$pdo->prepare("SELECT COUNT(*) FROM quiz_questions WHERE quiz_id=? AND question_type='short_answer'");
    $manual->execute([(int)$r['quiz_id']]);
    if ((int)$manual->fetchColumn()>0) $status='submitted';
}
$pct=$max>0?round(($score/$max)*100,2):0;
?><div class="app"><main class="main"><div class="content"><section class="card card-pad"><span class="eyebrow"><?=e($r['course_code'])?> · Quiz</span><h1><?=e($r['title'])?></h1><div class="grid three"><div class="card stat"><small>Score</small><strong><?=e((string)$score)?> / <?=e((string)$max)?></strong></div><div class="card stat"><small>Percentage</small><strong><?=e((string)$pct)?>%</strong></div><div class="card stat"><small>Status</small><strong><?=e(ucfirst($status))?></strong></div></div><br><?php if($status==='submitted'): ?><div class="notice"><i class="fa-solid fa-clock"></i> Short-answer responses are awaiting teacher review.</div><?php else: ?><div class="notice"><i class="fa-solid fa-circle-check"></i> Your quiz has been graded.</div><?php endif; ?><a class="btn btn-soft" href="activities.php">Back to Activities</a></section></div></main></div><?php require __DIR__.'/../includes/footer.php'; ?>
