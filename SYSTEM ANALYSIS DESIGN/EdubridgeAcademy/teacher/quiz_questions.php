<?php
declare(strict_types=1);
require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/../includes/auth.php';
require_role('teacher');

$user = current_user();
$pdo = db();
$quizId = (int)($_GET['id'] ?? $_POST['quiz_id'] ?? 0);

$quizStmt = $pdo->prepare("SELECT q.*, c.title AS course_title, c.course_code
    FROM quizzes q
    INNER JOIN courses c ON c.id=q.course_id
    INNER JOIN teacher_courses tc ON tc.course_id=c.id
    WHERE q.id=? AND tc.teacher_id=? LIMIT 1");
$quizStmt->execute([$quizId, $user['id']]);
$quiz = $quizStmt->fetch();
if (!$quiz) exit('Quiz not found or you are not assigned to this course.');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        verify_csrf();
        $text = post('question_text');
        $type = post('question_type', 'multiple_choice');
        $points = (float)($_POST['points'] ?? 1);
        if ($text === '') throw new RuntimeException('Question text is required.');
        if (!in_array($type, ['multiple_choice','true_false','short_answer'], true)) $type='multiple_choice';
        if ($points < 0) $points = 0;

        $pdo->beginTransaction();
        $sortStmt = $pdo->prepare('SELECT COALESCE(MAX(sort_order),-1)+1 FROM quiz_questions WHERE quiz_id=?');
        $sortStmt->execute([$quizId]);
        $sort = (int)$sortStmt->fetchColumn();

        $qStmt = $pdo->prepare('INSERT INTO quiz_questions(quiz_id,question_text,question_type,points,sort_order) VALUES(?,?,?,?,?)');
        $qStmt->execute([$quizId,$text,$type,$points,$sort]);
        $questionId = (int)$pdo->lastInsertId();

        $choiceStmt = $pdo->prepare('INSERT INTO quiz_choices(question_id,choice_text,is_correct,sort_order) VALUES(?,?,?,?)');
        if ($type === 'true_false') {
            $correct = post('correct_tf', 'true');
            $choiceStmt->execute([$questionId,'True',$correct==='true'?1:0,0]);
            $choiceStmt->execute([$questionId,'False',$correct==='false'?1:0,1]);
        } elseif ($type === 'multiple_choice') {
            $choices = $_POST['choices'] ?? [];
            $correct = (string)($_POST['correct_choice'] ?? '');
            $added = 0;
            foreach ($choices as $i=>$choiceText) {
                $choiceText = trim((string)$choiceText);
                if ($choiceText === '') continue;
                $choiceStmt->execute([$questionId,$choiceText,((string)$i===$correct)?1:0,$added]);
                $added++;
            }
            if ($added < 2) throw new RuntimeException('Multiple-choice questions need at least two choices.');
            if ($correct === '') throw new RuntimeException('Select the correct choice.');
        }
        $pdo->commit();
        flash('success','Quiz question added successfully.');
        redirect('teacher/quiz_questions.php?id='.$quizId);
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $error=$e->getMessage();
    }
}

$questionsStmt=$pdo->prepare('SELECT * FROM quiz_questions WHERE quiz_id=? ORDER BY sort_order,id');
$questionsStmt->execute([$quizId]);
$questions=$questionsStmt->fetchAll();
$choiceStmt=$pdo->prepare('SELECT * FROM quiz_choices WHERE question_id=? ORDER BY sort_order,id');

$page_title='Quiz Questions';
require __DIR__.'/../includes/header.php';
?>
<div class="app">
<aside class="sidebar"><div class="brand"><div class="brand-mark"><i class="fa-solid fa-graduation-cap"></i></div><div><strong>EdubridgeAcademy</strong><span>Teacher</span></div></div><nav class="nav">
<a href="dashboard.php"><i class="fa-solid fa-grid-2"></i>Dashboard</a><a href="courses.php"><i class="fa-solid fa-book-open"></i>My Courses</a><a href="activities.php" class="active"><i class="fa-solid fa-list-check"></i>Activities</a><a href="submissions.php"><i class="fa-solid fa-file-circle-check"></i>Submissions</a><a href="analytics.php"><i class="fa-solid fa-chart-line"></i>Analytics</a><a href="invitations.php"><i class="fa-solid fa-envelope-open-text"></i>Course Invitations</a><a href="students.php"><i class="fa-solid fa-user-graduate"></i>Students</a><a href="announcements.php"><i class="fa-solid fa-bullhorn"></i>Announcements</a><a href="../google_account.php"><i class="fa-brands fa-google"></i>Google Account</a>
<div class="nav-sep"></div><a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i>Sign out</a>
</nav></aside>
<main class="main"><header class="topbar"><div><span class="eyebrow">Teacher portal · <?=e($quiz['course_code'])?></span><h1>Quiz Questions</h1></div><div class="user-chip"><div class="avatar"><?=e(strtoupper(substr($user['first_name'],0,1).substr($user['last_name'],0,1)))?></div><div><b><?=e($user['first_name'].' '.$user['last_name'])?></b><span>Teacher</span></div></div></header>
<div class="content">
<?php if($error): ?><div class="notice" style="background:#fae4e7;color:#9b4c58"><i class="fa-solid fa-circle-exclamation"></i> <?=e($error)?></div><?php endif; ?>
<section class="card card-pad"><div class="section-head"><div><span class="eyebrow">Quiz</span><h2><?=e($quiz['title'])?></h2><p class="muted"><?=e($quiz['course_title'])?> · Add or review questions before students take this quiz.</p></div><a class="btn btn-soft" href="activities.php?course_id=<?=$quiz['course_id']?>">Back to Activities</a></div></section>
<br>
<section class="card card-pad"><div class="section-head"><h2>Current Questions (<?=count($questions)?>)</h2></div>
<?php if(!$questions): ?><div class="empty"><i class="fa-solid fa-circle-exclamation"></i><br>No questions have been added yet.</div><?php else: ?>
<div class="list"><?php foreach($questions as $i=>$question): $choiceStmt->execute([(int)$question['id']]);$choices=$choiceStmt->fetchAll(); ?><div class="list-row"><div class="stat-icon"><i class="fa-solid fa-circle-question"></i></div><div class="list-main"><strong><?=($i+1)?>. <?=e($question['question_text'])?></strong><span><?=e(ucwords(str_replace('_',' ',$question['question_type'])))?> · <?=e((string)$question['points'])?> points<?php if($choices): ?> · <?=count($choices)?> choices<?php endif; ?></span></div></div><?php endforeach; ?></div>
<?php endif; ?></section>
<br>
<section class="card card-pad"><div class="section-head"><div><h2>Add Question</h2><p class="muted">New questions are immediately available to students.</p></div></div>
<form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="quiz_id" value="<?=$quizId?>">
<div class="form-grid"><div class="form-group full"><label>Question</label><textarea class="textarea" name="question_text" required placeholder="Enter the question..." style="min-height:110px"></textarea></div><div class="form-group"><label>Question Type</label><select class="select" name="question_type" id="qtype"><option value="multiple_choice">Multiple Choice</option><option value="true_false">True / False</option><option value="short_answer">Short Answer</option></select></div><div class="form-group"><label>Points</label><input class="input" type="number" step="0.01" min="0" name="points" value="1"></div></div>
<div id="mc"><label>Choices</label><div id="choiceRows"><div class="form-grid"><input class="input" name="choices[]" placeholder="Choice A"><label><input type="radio" name="correct_choice" value="0"> Correct</label></div><div class="form-grid"><input class="input" name="choices[]" placeholder="Choice B"><label><input type="radio" name="correct_choice" value="1"> Correct</label></div></div><button type="button" class="btn btn-soft" id="addChoice" style="margin-top:8px"><i class="fa-solid fa-plus"></i> Add Choice</button></div>
<div id="tf" style="display:none;margin-top:14px"><label>Correct Answer</label><select class="select" name="correct_tf"><option value="true">True</option><option value="false">False</option></select></div>
<div class="form-actions"><button class="btn btn-primary"><i class="fa-solid fa-plus"></i> Add Question</button></div>
</form></section>
</div></main></div>
<script>
const qtype=document.getElementById('qtype'), mc=document.getElementById('mc'), tf=document.getElementById('tf'), rows=document.getElementById('choiceRows');
function toggle(){const v=qtype.value;mc.style.display=v==='multiple_choice'?'block':'none';tf.style.display=v==='true_false'?'block':'none';}
qtype.addEventListener('change',toggle);
document.getElementById('addChoice').addEventListener('click',()=>{const n=rows.children.length;const row=document.createElement('div');row.className='form-grid';row.innerHTML='<input class="input" name="choices[]" placeholder="Choice '+String.fromCharCode(65+n)+'"><label><input type="radio" name="correct_choice" value="'+n+'"> Correct</label>';rows.appendChild(row);});
toggle();
</script>
<?php require __DIR__.'/../includes/footer.php'; ?>
