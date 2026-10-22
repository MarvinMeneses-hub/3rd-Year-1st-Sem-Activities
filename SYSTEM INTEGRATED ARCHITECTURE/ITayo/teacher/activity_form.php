<?php
declare(strict_types=1);
require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/../includes/auth.php';
require_role('teacher');

$user = current_user();
$pdo = db();
$course_id = (int)($_GET['course_id'] ?? $_POST['course_id'] ?? 0);

$stmt = $pdo->prepare("SELECT c.* FROM courses c INNER JOIN teacher_courses tc ON tc.course_id=c.id WHERE c.id=? AND tc.teacher_id=? LIMIT 1");
$stmt->execute([$course_id, $user['id']]);
$course = $stmt->fetch();
if (!$course) exit('Course not found or you are not assigned to this course.');

$modsStmt = $pdo->prepare("SELECT * FROM modules WHERE course_id=? ORDER BY sort_order,id");
$modsStmt->execute([$course_id]);
$modules = $modsStmt->fetchAll();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        verify_csrf();
        $type = post('activity_type', 'assignment');
        $allowed = ['assignment','quiz','project','practical','discussion'];
        if (!in_array($type, $allowed, true)) throw new RuntimeException('Invalid activity type.');

        $title = post('title');
        $instructions = post('instructions');
        $points = (float)($_POST['points'] ?? 0);
        $due = post('due_at') ?: null;
        $module = (int)($_POST['module_id'] ?? 0) ?: null;
        $allowLate = (int)($_POST['allow_late'] ?? 0);

        if ($title === '') throw new RuntimeException('Activity title is required.');

        $pdo->beginTransaction();

        $stmt = $pdo->prepare("INSERT INTO activities(course_id,module_id,activity_type,title,instructions,points,due_at) VALUES(?,?,?,?,?,?,?)");
        $stmt->execute([$course_id, $module, $type, $title, $instructions, $points, $due]);
        $activityId = (int)$pdo->lastInsertId();

        if ($type === 'quiz') {
            $timeLimit = (int)($_POST['time_limit_minutes'] ?? 0) ?: null;
            $attempts = max(1, (int)($_POST['attempts_allowed'] ?? 1));
            $qStmt = $pdo->prepare("INSERT INTO quizzes(course_id,module_id,title,description,time_limit_minutes,attempts_allowed) VALUES(?,?,?,?,?,?)");
            $qStmt->execute([$course_id, $module, $title, $instructions, $timeLimit, $attempts]);
            $quizId = (int)$pdo->lastInsertId();

            // Link the quiz to the activity when the enhanced column exists.
            $hasActivityId = false;
            foreach ($pdo->query("SHOW COLUMNS FROM quizzes")->fetchAll() as $col) {
                if ($col['Field'] === 'activity_id') { $hasActivityId = true; break; }
            }
            if ($hasActivityId) {
                $pdo->prepare('UPDATE quizzes SET activity_id=? WHERE id=?')->execute([$activityId, $quizId]);
            }

            $questions = $_POST['question_text'] ?? [];
            $types = $_POST['question_type'] ?? [];
            $qPoints = $_POST['question_points'] ?? [];
            $choices = $_POST['choices'] ?? [];
            $correct = $_POST['correct_choice'] ?? [];

            $questionStmt = $pdo->prepare("INSERT INTO quiz_questions(quiz_id,question_text,question_type,points,sort_order) VALUES(?,?,?,?,?)");
            $choiceStmt = $pdo->prepare("INSERT INTO quiz_choices(question_id,choice_text,is_correct,sort_order) VALUES(?,?,?,?)");
            $createdQuestions = 0;

            foreach ($questions as $i => $questionText) {
                $questionText = trim((string)$questionText);
                if ($questionText === '') continue;
                $questionType = $types[$i] ?? 'multiple_choice';
                if (!in_array($questionType, ['multiple_choice','true_false','short_answer'], true)) $questionType = 'multiple_choice';
                $qp = (float)($qPoints[$i] ?? 1);
                $questionStmt->execute([$quizId, $questionText, $questionType, $qp, $createdQuestions]);
                $questionId = (int)$pdo->lastInsertId();

                if ($questionType === 'true_false') {
                    $choiceStmt->execute([$questionId, 'True', (($correct[$i] ?? '') === 'true') ? 1 : 0, 0]);
                    $choiceStmt->execute([$questionId, 'False', (($correct[$i] ?? '') === 'false') ? 1 : 0, 1]);
                } elseif ($questionType === 'multiple_choice') {
                    $rowChoices = $choices[$i] ?? [];
                    $correctIndex = (string)($correct[$i] ?? '');
                    foreach ($rowChoices as $j => $choiceText) {
                        $choiceText = trim((string)$choiceText);
                        if ($choiceText === '') continue;
                        $choiceStmt->execute([$questionId, $choiceText, ((string)$j === $correctIndex) ? 1 : 0, $j]);
                    }
                }
                $createdQuestions++;
            }
            if ($createdQuestions === 0) throw new RuntimeException('Add at least one quiz question.');
        } else {
            // Projects and other teacher-created activities use the existing assignment/submission engine.
            $pdo->prepare("INSERT INTO assignments(activity_id,allow_late) VALUES(?,?)")->execute([$activityId, $allowLate]);
        }

        $pdo->commit();
        flash('success', ucfirst($type).' created successfully.');
        redirect('teacher/activities.php?course_id='.$course_id);
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $error = $e->getMessage();
    }
}

$page_title = 'Create Activity';
require __DIR__.'/../includes/header.php';
?>
<div class="app">
<aside class="sidebar"><div class="brand"><div class="brand-mark"><i class="fa-solid fa-graduation-cap"></i></div><div><strong>ITayo</strong><span>Teacher</span></div></div><nav class="nav">
<a href="dashboard.php"><i class="fa-solid fa-grid-2"></i>Dashboard</a><a href="courses.php"><i class="fa-solid fa-book-open"></i>My Courses</a><a href="activities.php" class="active"><i class="fa-solid fa-list-check"></i>Activities</a><a href="submissions.php"><i class="fa-solid fa-file-circle-check"></i>Submissions</a><a href="analytics.php"><i class="fa-solid fa-chart-line"></i>Analytics</a><a href="invitations.php"><i class="fa-solid fa-envelope-open-text"></i>Course Invitations</a><a href="students.php"><i class="fa-solid fa-user-graduate"></i>Students</a><a href="announcements.php"><i class="fa-solid fa-bullhorn"></i>Announcements</a><a href="../google_account.php"><i class="fa-brands fa-google"></i>Google Account</a>
<div class="nav-sep"></div><a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i>Sign out</a>
</nav></aside>
<main class="main"><header class="topbar"><div><span class="eyebrow">Teacher portal</span><h1>Create Activity</h1></div><div class="user-chip"><div class="avatar"><?=e(strtoupper(substr($user['first_name'],0,1).substr($user['last_name'],0,1)))?></div><div><b><?=e($user['first_name'].' '.$user['last_name'])?></b><span>Teacher</span></div></div></header>
<div class="content">
<?php if ($error): ?><div class="notice" style="background:#fae4e7;color:#9b4c58"><i class="fa-solid fa-circle-exclamation"></i> <?=e($error)?></div><?php endif; ?>
<section class="card card-pad"><div class="section-head"><div><span class="eyebrow"><?=e($course['course_code'])?></span><h2><?=e($course['title'])?></h2><p class="muted">Teachers can create assignments, quizzes, projects and other learning activities.</p></div><a class="btn btn-soft" href="activities.php?course_id=<?=$course_id?>">Back</a></div>
<form method="post" id="activityForm"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="course_id" value="<?=$course_id?>">
<div class="form-grid">
<div class="form-group"><label>Activity Type</label><select class="select" name="activity_type" id="activityType" required><option value="assignment">Assignment</option><option value="quiz">Quiz</option><option value="project">Project</option><option value="practical">Practical Activity</option><option value="discussion">Discussion / Task</option></select></div>
<div class="form-group"><label>Module</label><select class="select" name="module_id"><option value="">None</option><?php foreach($modules as $m):?><option value="<?=$m['id']?>"><?=e($m['title'])?></option><?php endforeach;?></select></div>
<div class="form-group full"><label>Title</label><input class="input" name="title" required placeholder="e.g. Network Design Project"></div>
<div class="form-group"><label>Points</label><input class="input" type="number" step="0.01" min="0" name="points" value="100"></div>
<div class="form-group"><label>Due Date</label><input class="input" type="datetime-local" name="due_at"></div>
<div class="form-group full"><label>Instructions</label><textarea class="textarea" name="instructions" placeholder="Explain what students need to do..."></textarea></div>
</div>
<div id="nonQuizOptions"><label><input type="checkbox" name="allow_late" value="1"> Allow late submissions</label></div>
<div id="quizOptions" style="display:none;margin-top:20px">
<div class="form-grid"><div class="form-group"><label>Time Limit (minutes)</label><input class="input" type="number" min="1" name="time_limit_minutes" value="30"></div><div class="form-group"><label>Attempts Allowed</label><input class="input" type="number" min="1" name="attempts_allowed" value="1"></div></div>
<br><div class="section-head"><h3>Quiz Questions</h3><button type="button" class="btn btn-soft" id="addQuestion"><i class="fa-solid fa-plus"></i>Add Question</button></div>
<div id="questions"></div>
</div>
<div class="form-actions"><button class="btn btn-primary"><i class="fa-solid fa-check"></i>Create Activity</button></div>
</form></section></div></main></div>
<script>
const typeEl=document.getElementById('activityType'), quizBox=document.getElementById('quizOptions'), nonQuiz=document.getElementById('nonQuizOptions'), questions=document.getElementById('questions');
function toggleType(){const quiz=typeEl.value==='quiz';quizBox.style.display=quiz?'block':'none';nonQuiz.style.display=quiz?'none':'block';if(quiz&&!questions.children.length)addQuestion();}
typeEl.addEventListener('change',toggleType);
function addQuestion(){const i=questions.children.length;const wrap=document.createElement('div');wrap.className='card card-pad';wrap.style.marginBottom='12px';wrap.innerHTML=`<div class="section-head"><h3>Question ${i+1}</h3><button type="button" class="btn btn-danger removeQ"><i class="fa-solid fa-trash"></i></button></div><div class="form-grid"><div class="form-group full"><label>Question</label><textarea class="textarea" name="question_text[${i}]" required></textarea></div><div class="form-group"><label>Type</label><select class="select qtype" name="question_type[${i}]"><option value="multiple_choice">Multiple Choice</option><option value="true_false">True / False</option><option value="short_answer">Short Answer</option></select></div><div class="form-group"><label>Points</label><input class="input" type="number" step="0.01" name="question_points[${i}]" value="1"></div></div><div class="choices" style="margin-top:12px"><label>Choices</label><div class="choiceRows"><div class="form-grid"><input class="input" name="choices[${i}][]" placeholder="Choice A"><label><input type="radio" name="correct_choice[${i}]" value="0"> Correct</label></div><div class="form-grid"><input class="input" name="choices[${i}][]" placeholder="Choice B"><label><input type="radio" name="correct_choice[${i}]" value="1"> Correct</label></div></div><button type="button" class="btn btn-soft addChoice" style="margin-top:8px"><i class="fa-solid fa-plus"></i>Add Choice</button></div>`;
wrap.querySelector('.removeQ').onclick=()=>wrap.remove();wrap.querySelector('.addChoice').onclick=()=>{const n=wrap.querySelector('.choiceRows').children.length;const row=document.createElement('div');row.className='form-grid';row.innerHTML=`<input class="input" name="choices[${i}][]" placeholder="Choice ${String.fromCharCode(65+n)}"><label><input type="radio" name="correct_choice[${i}]" value="${n}"> Correct</label>`;wrap.querySelector('.choiceRows').appendChild(row)};wrap.querySelector('.qtype').onchange=(e)=>{wrap.querySelector('.choices').style.display=e.target.value==='multiple_choice'?'block':e.target.value==='true_false'?'none':'none';};questions.appendChild(wrap);}
document.getElementById('addQuestion').onclick=addQuestion;toggleType();
</script>
<?php require __DIR__.'/../includes/footer.php'; ?>
