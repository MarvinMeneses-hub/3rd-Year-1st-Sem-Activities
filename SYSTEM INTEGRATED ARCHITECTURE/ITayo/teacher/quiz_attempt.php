<?php
declare(strict_types=1);
require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/../includes/auth.php';
require_role('teacher');

$user=current_user();
$pdo=db();
$attemptId=(int)($_GET['id']??0);

$hasQuizActivityId=false;
try {
    foreach($pdo->query("SHOW COLUMNS FROM quizzes")->fetchAll() as $col){
        if($col['Field']==='activity_id'){$hasQuizActivityId=true;break;}
    }
}catch(Throwable $e){}

if($hasQuizActivityId){
    $sql="SELECT qa.*,q.title AS quiz_title,q.id AS quiz_id,c.title AS course_title,c.course_code,
                 CONCAT(u.first_name,' ',u.last_name) AS student_name,u.email
          FROM quiz_attempts qa
          INNER JOIN quizzes q ON q.id=qa.quiz_id
          INNER JOIN courses c ON c.id=q.course_id
          INNER JOIN teacher_courses tc ON tc.course_id=c.id
          INNER JOIN users u ON u.id=qa.student_id
          WHERE qa.id=? AND tc.teacher_id=? LIMIT 1";
}else{
    $sql="SELECT qa.*,q.title AS quiz_title,q.id AS quiz_id,c.title AS course_title,c.course_code,
                 CONCAT(u.first_name,' ',u.last_name) AS student_name,u.email
          FROM quiz_attempts qa
          INNER JOIN quizzes q ON q.id=qa.quiz_id
          INNER JOIN courses c ON c.id=q.course_id
          INNER JOIN teacher_courses tc ON tc.course_id=c.id
          INNER JOIN users u ON u.id=qa.student_id
          WHERE qa.id=? AND tc.teacher_id=? LIMIT 1";
}
$s=$pdo->prepare($sql);$s->execute([$attemptId,$user['id']]);$attempt=$s->fetch();
if(!$attempt) exit('Quiz attempt not found.');

$answers=[];
if(isset($attempt['answers_json']) && trim((string)$attempt['answers_json'])!==''){
    $decoded=json_decode((string)$attempt['answers_json'],true);
    if(is_array($decoded)) $answers=$decoded;
}

$qStmt=$pdo->prepare("SELECT * FROM quiz_questions WHERE quiz_id=? ORDER BY sort_order,id");
$qStmt->execute([(int)$attempt['quiz_id']]);$questions=$qStmt->fetchAll();
$cStmt=$pdo->prepare("SELECT * FROM quiz_choices WHERE question_id=? ORDER BY sort_order,id");

$page_title='Quiz Attempt';require __DIR__.'/../includes/header.php';
?>
<div class="app">
<main class="main"><div class="content">
<section class="card card-pad">
<span class="eyebrow">Quiz submission · <?=e($attempt['course_code'])?></span>
<h1><?=e($attempt['quiz_title'])?></h1>
<p class="muted">Student: <b><?=e($attempt['student_name'])?></b> · <?=e($attempt['email'])?></p>
<div class="grid three" style="margin-top:18px"><div class="card stat"><small>Score</small><strong><?=e((string)($attempt['score']??0))?></strong></div><div class="card stat"><small>Submitted</small><strong><?=e($attempt['submitted_at']?date('M d, Y h:i A',strtotime($attempt['submitted_at'])):'—')?></strong></div><div class="card stat"><small>Questions</small><strong><?=count($questions)?></strong></div></div>
</section>
<section class="card card-pad" style="margin-top:18px"><div class="section-head"><div><span class="eyebrow">Student responses</span><h2>Answers</h2></div></div>
<?php foreach($questions as $i=>$q): $cStmt->execute([(int)$q['id']]);$choices=$cStmt->fetchAll();$answer=(string)($answers[(string)$q['id']]??'');$answerLabel='No answer';foreach($choices as $c){if((string)$c['id']===$answer){$answerLabel=$c['choice_text'];break;}} ?>
<div class="card card-pad" style="margin-bottom:12px"><b><?=($i+1)?>. <?=e($q['question_text'])?></b><p style="margin:10px 0 0"><span class="muted">Student answer:</span> <strong><?=e($answerLabel)?></strong></p><?php foreach($choices as $c): ?><?php if((int)$c['is_correct']===1): ?><small class="muted">Correct answer: <?=e($c['choice_text'])?></small><?php endif; ?><?php endforeach; ?></div>
<?php endforeach; ?>
<?php if(!$answers): ?><div class="notice"><i class="fa-solid fa-circle-info"></i> This attempt does not contain stored answer details. The score and submission time are still available.</div><?php endif; ?>
<a class="btn btn-soft" href="submissions.php">Back to Submissions</a>
</section>
</div></main></div>
<?php require __DIR__.'/../includes/footer.php'; ?>
