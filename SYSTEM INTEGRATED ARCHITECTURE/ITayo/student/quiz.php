<?php
declare(strict_types=1);
require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/../includes/auth.php';
require_role('student');

$user = current_user();
$pdo = db();
$quizId = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

$q = $pdo->prepare("SELECT q.*, c.title AS course_title, c.course_code
    FROM quizzes q
    INNER JOIN courses c ON c.id=q.course_id
    WHERE q.id=?
      AND (EXISTS (SELECT 1 FROM enrollments e WHERE e.course_id=q.course_id AND e.student_id=? AND e.status IN ('active','completed'))
           OR EXISTS (SELECT 1 FROM course_enrollments ce WHERE ce.course_id=q.course_id AND ce.user_id=? AND ce.role='student' AND ce.status='accepted'))
    LIMIT 1");
$q->execute([$quizId, $user['id'], $user['id']]);
$quiz = $q->fetch();
if (!$quiz) exit('Quiz not found or you are not enrolled.');

$qs = $pdo->prepare("SELECT * FROM quiz_questions WHERE quiz_id=? ORDER BY sort_order,id");
$qs->execute([$quizId]);
$questions = $qs->fetchAll();

$choiceStmt = $pdo->prepare("SELECT * FROM quiz_choices WHERE question_id=? ORDER BY sort_order,id");

// Never allow an empty quiz to be submitted. This prevents the old behavior
// where a quiz with no stored questions immediately produced a zero-point result.
$questionProblems = [];
foreach ($questions as $question) {
    if ($question['question_type'] !== 'short_answer') {
        $choiceStmt->execute([(int)$question['id']]);
        $choicesCheck = $choiceStmt->fetchAll();
        if (!$choicesCheck) {
            $questionProblems[] = 'Question '.((int)$question['sort_order'] + 1).' has no answer choices.';
        }
    }
}

$attemptStmt = $pdo->prepare("SELECT * FROM quiz_attempts WHERE quiz_id=? AND student_id=? ORDER BY id DESC LIMIT 1");
$attemptStmt->execute([$quizId, $user['id']]);
$attempt = $attemptStmt->fetch();

$completedAttemptsStmt = $pdo->prepare("SELECT COUNT(*) FROM quiz_attempts WHERE quiz_id=? AND student_id=? AND submitted_at IS NOT NULL");
$completedAttemptsStmt->execute([$quizId, $user['id']]);
$completedAttempts = (int)$completedAttemptsStmt->fetchColumn();

// If the previous broken version submitted an empty attempt, reopen that same
// empty attempt instead of consuming the student's only allowed attempt.
$canReopenEmptyAttempt = false;
if ($attempt && $attempt['submitted_at'] !== null) {
    $hasAnswersJson = false;
    foreach ($pdo->query("SHOW COLUMNS FROM quiz_attempts")->fetchAll() as $col) {
        if ($col['Field'] === 'answers_json') {
            $hasAnswersJson = true;
            break;
        }
    }
    if ($hasAnswersJson && trim((string)($attempt['answers_json'] ?? '')) === '{}') {
        $canReopenEmptyAttempt = true;
    }
}

if (!$questions) {
    $page_title = $quiz['title'];
    require __DIR__.'/../includes/header.php';
    ?>
    <div class="app">
      <main class="main">
        <div class="content">
          <section class="card card-pad">
            <span class="eyebrow">Quiz · <?=e($quiz['course_code'])?></span>
            <h1><?=e($quiz['title'])?></h1>
            <div class="notice" style="margin-top:16px">
              <i class="fa-solid fa-circle-exclamation"></i>
              This quiz currently has <b>no questions</b>. It cannot be submitted until the teacher adds at least one question.
            </div>
            <a class="btn btn-soft" href="activity.php?id=<?=e((string)($_GET['activity_id'] ?? 0))?>" style="margin-top:12px">Back to Activity</a>
            <a class="btn btn-soft" href="activities.php" style="margin-top:12px">Back to Activities</a>
          </section>
        </div>
      </main>
    </div>
    <?php
    require __DIR__.'/../includes/footer.php';
    exit;
}

if ($questionProblems) {
    $page_title = $quiz['title'];
    require __DIR__.'/../includes/header.php';
    ?>
    <div class="app">
      <main class="main">
        <div class="content">
          <section class="card card-pad">
            <span class="eyebrow">Quiz · <?=e($quiz['course_code'])?></span>
            <h1><?=e($quiz['title'])?></h1>
            <div class="notice" style="margin-top:16px">
              <i class="fa-solid fa-triangle-exclamation"></i>
              This quiz has a question configuration problem. The teacher needs to add answer choices before students can submit it.
            </div>
            <a class="btn btn-soft" href="activities.php" style="margin-top:12px">Back to Activities</a>
          </section>
        </div>
      </main>
    </div>
    <?php
    require __DIR__.'/../includes/footer.php';
    exit;
}

if ($canReopenEmptyAttempt) {
    $reset = $pdo->prepare("UPDATE quiz_attempts SET score=NULL, submitted_at=NULL WHERE id=? AND student_id=?");
    $reset->execute([(int)$attempt['id'], $user['id']]);
    // status exists only on enhanced installations.
    try {
        $pdo->prepare("UPDATE quiz_attempts SET status='in_progress' WHERE id=? AND student_id=?")->execute([(int)$attempt['id'], $user['id']]);
    } catch (Throwable $ignored) {}
    $attemptStmt->execute([$quizId, $user['id']]);
    $attempt = $attemptStmt->fetch();
    $completedAttempts--;
}

if (!$attempt || $attempt['submitted_at'] !== null) {
    if ($completedAttempts >= (int)$quiz['attempts_allowed']) {
        $page_title = $quiz['title'];
        require __DIR__.'/../includes/header.php';
        echo '<div class="app"><main class="main"><div class="content"><section class="card card-pad"><span class="eyebrow">Quiz</span><h1>'.e($quiz['title']).'</h1><div class="notice"><i class="fa-solid fa-lock"></i> You have used all allowed attempts for this quiz.</div><a class="btn btn-soft" href="activities.php">Back to Activities</a></section></div></main></div>';
        require __DIR__.'/../includes/footer.php';
        exit;
    }

    $pdo->prepare("INSERT INTO quiz_attempts(quiz_id,student_id,started_at,status) VALUES(?,?,NOW(),'in_progress')")->execute([$quizId,$user['id']]);
    $attemptId = (int)$pdo->lastInsertId();
    $attemptStmt->execute([$quizId, $user['id']]);
    $attempt = $attemptStmt->fetch();
} else {
    $attemptId = (int)$attempt['id'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $answers = $_POST['answers'] ?? [];
    if (!is_array($answers)) $answers = [];

    $score = 0.0;
    $maxScore = 0.0;
    $hasManual = false;
    $normalized = [];

    foreach ($questions as $question) {
        $qid = (int)$question['id'];
        $points = (float)$question['points'];
        $maxScore += $points;
        $answer = isset($answers[$qid]) ? trim((string)$answers[$qid]) : '';
        $normalized[(string)$qid] = $answer;

        if ($question['question_type'] === 'short_answer') {
            $hasManual = true;
            continue;
        }

        $choiceStmt->execute([$qid]);
        $choices = $choiceStmt->fetchAll();
        $correct = [];
        foreach ($choices as $choice) {
            if ((int)$choice['is_correct'] === 1) $correct[] = (string)$choice['id'];
        }
        if (in_array($answer, $correct, true) && count($correct) === 1) {
            $score += $points;
        }
    }

    $status = $hasManual ? 'submitted' : 'graded';
    $payload = json_encode($normalized, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);

    $cols = $pdo->query("SHOW COLUMNS FROM quiz_attempts")->fetchAll();
    $hasAnswersJson = $hasMaxScore = $hasStatus = false;
    foreach ($cols as $col) {
        if ($col['Field'] === 'answers_json') $hasAnswersJson = true;
        if ($col['Field'] === 'max_score') $hasMaxScore = true;
        if ($col['Field'] === 'status') $hasStatus = true;
    }

    $set = ['score = ?', 'submitted_at = NOW()'];
    $params = [$score];
    if ($hasAnswersJson) { $set[] = 'answers_json = ?'; $params[] = $payload; }
    if ($hasMaxScore) { $set[] = 'max_score = ?'; $params[] = $maxScore; }
    if ($hasStatus) { $set[] = 'status = ?'; $params[] = $status; }
    $params[] = $attemptId;
    $params[] = $user['id'];
    $pdo->prepare('UPDATE quiz_attempts SET '.implode(', ', $set).' WHERE id=? AND student_id=?')->execute($params);

    flash('success', 'Quiz submitted successfully.'.($hasManual ? ' Short-answer questions require teacher review.' : ''));
    redirect('student/quiz_result.php?id='.$attemptId);
}

$page_title=$quiz['title'];
require __DIR__.'/../includes/header.php';
?>
<div class="app">
<aside class="sidebar"><div class="brand"><div class="brand-mark"><i class="fa-solid fa-graduation-cap"></i></div><div><strong>ITayo</strong><span>Student</span></div></div><nav class="nav"><a href="dashboard.php"><i class="fa-solid fa-grid-2"></i>Dashboard</a><a href="courses.php"><i class="fa-solid fa-book-open"></i>My Courses</a><a href="activities.php" class="active"><i class="fa-solid fa-list-check"></i>Activities</a><a href="invitations.php"><i class="fa-solid fa-envelope-open-text"></i>Course Invitations</a><a href="grades.php"><i class="fa-solid fa-chart-line"></i>Grades</a><a href="gamification.php"><i class="fa-solid fa-trophy"></i>Achievements</a><a href="announcements.php"><i class="fa-solid fa-bullhorn"></i>Announcements</a><a href="../google_account.php"><i class="fa-brands fa-google"></i>Google Account</a>
<div class="nav-sep"></div><a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i>Sign out</a></nav></aside>
<main class="main"><header class="topbar"><div><span class="eyebrow">Quiz · <?=e($quiz['course_code'])?></span><h1><?=e($quiz['title'])?></h1></div></header>
<div class="content"><section class="card card-pad">
<p class="muted"><?=e($quiz['description']??'')?></p>
<div class="notice"><i class="fa-solid fa-clock"></i> <?=(int)$quiz['time_limit_minutes']>0?'Time limit: '.e((string)$quiz['time_limit_minutes']).' minutes · ':''?>Attempts: <?=e((string)$quiz['attempts_allowed'])?></div>
<form method="post" action="quiz.php?id=<?=$quizId?>">
<input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="id" value="<?=$quizId?>">
<?php foreach($questions as $i=>$question):
    $choiceStmt->execute([(int)$question['id']]);
    $choices=$choiceStmt->fetchAll();
?>
<div class="card card-pad" style="margin-bottom:12px">
<b><?=($i+1)?>. <?=e($question['question_text'])?></b>
<small class="muted" style="display:block;margin:6px 0">Points: <?=e((string)$question['points'])?></small>
<?php if($question['question_type']==='short_answer'): ?>
<textarea class="textarea" name="answers[<?=$question['id']?>]" required></textarea>
<?php else: foreach($choices as $choice): ?>
<label style="display:block;padding:10px;border-radius:10px"><input type="radio" name="answers[<?=$question['id']?>]" value="<?=$choice['id']?>" required> <?=e($choice['choice_text'])?></label>
<?php endforeach; endif; ?>
</div>
<?php endforeach; ?>
<button class="btn btn-primary" type="submit"><i class="fa-solid fa-paper-plane"></i> Submit Quiz</button>
</form>
</section></div></main></div>
<?php require __DIR__.'/../includes/footer.php'; ?>
