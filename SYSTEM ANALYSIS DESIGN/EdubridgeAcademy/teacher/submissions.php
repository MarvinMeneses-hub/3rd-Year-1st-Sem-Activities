<?php
declare(strict_types=1);
require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/../includes/auth.php';
require_role('teacher');

$user = current_user();
$pdo = db();

/*
 * Teacher Submissions
 *
 * IMPORTANT:
 * - Assignments/activities are stored in submissions.
 * - Quizzes are stored in quiz_attempts.
 * Therefore this page intentionally reads BOTH sources and combines them.
 *
 * The Activities overview also lists activities even when nobody has submitted yet.
 */

// 1) Every activity owned by this teacher, including quizzes.
$activitySql = "
    SELECT
        a.id AS activity_id,
        a.title AS activity_title,
        a.activity_type,
        a.points,
        a.due_at,
        c.id AS course_id,
        c.title AS course_title,
        c.course_code,
        COALESCE(asub.submission_count, 0) AS submission_count,
        COALESCE(qsub.quiz_submission_count, 0) AS quiz_submission_count,
        CASE
            WHEN a.activity_type = 'quiz' THEN COALESCE(qsub.quiz_submission_count, 0)
            ELSE COALESCE(asub.submission_count, 0)
        END AS total_work_count
    FROM activities a
    INNER JOIN courses c ON c.id = a.course_id
    INNER JOIN teacher_courses tc ON tc.course_id = c.id
    LEFT JOIN (
        SELECT a2.id AS activity_id, COUNT(sub.id) AS submission_count
        FROM activities a2
        INNER JOIN assignments ass ON ass.activity_id = a2.id
        LEFT JOIN submissions sub ON sub.assignment_id = ass.id
        GROUP BY a2.id
    ) asub ON asub.activity_id = a.id
    LEFT JOIN (
        SELECT
            a3.id AS activity_id,
            COUNT(qa.id) AS quiz_submission_count
        FROM activities a3
        INNER JOIN quizzes q
            ON q.activity_id = a3.id
            OR (
                q.activity_id IS NULL
                AND q.course_id = a3.course_id
                AND q.title = a3.title
                AND a3.activity_type = 'quiz'
            )
        LEFT JOIN quiz_attempts qa
            ON qa.quiz_id = q.id
            AND qa.submitted_at IS NOT NULL
        GROUP BY a3.id
    ) qsub ON qsub.activity_id = a.id
    WHERE tc.teacher_id = ?
    ORDER BY c.title ASC, a.created_at DESC, a.id DESC
";
$activityStmt = $pdo->prepare($activitySql);
$activityStmt->execute([$user['id']]);
$activities = $activityStmt->fetchAll();

// 2) All actual student work: assignment submissions + submitted quiz attempts.
$workSql = "
    SELECT * FROM (
        SELECT
            'assignment' AS work_type,
            sub.id AS work_id,
            sub.student_id,
            CONCAT(u.first_name, ' ', u.last_name) AS student_name,
            u.email,
            a.id AS activity_id,
            a.title AS activity_title,
            a.activity_type,
            a.points AS max_points,
            sub.submitted_at,
            sub.score,
            sub.file_path,
            c.title AS course_title,
            c.course_code
        FROM submissions sub
        INNER JOIN users u ON u.id = sub.student_id
        INNER JOIN assignments ass ON ass.id = sub.assignment_id
        INNER JOIN activities a ON a.id = ass.activity_id
        INNER JOIN courses c ON c.id = a.course_id
        INNER JOIN teacher_courses tc ON tc.course_id = c.id
        WHERE tc.teacher_id = ?

        UNION ALL

        SELECT
            'quiz' AS work_type,
            qa.id AS work_id,
            qa.student_id,
            CONCAT(u2.first_name, ' ', u2.last_name) AS student_name,
            u2.email,
            a2.id AS activity_id,
            a2.title AS activity_title,
            'quiz' AS activity_type,
            COALESCE((SELECT SUM(qq.points) FROM quiz_questions qq WHERE qq.quiz_id = q.id), a2.points) AS max_points,
            qa.submitted_at,
            qa.score,
            NULL AS file_path,
            c2.title AS course_title,
            c2.course_code
        FROM quiz_attempts qa
        INNER JOIN quizzes q ON q.id = qa.quiz_id
        INNER JOIN users u2 ON u2.id = qa.student_id
        INNER JOIN courses c2 ON c2.id = q.course_id
        INNER JOIN activities a2
            ON (
                q.activity_id = a2.id
                OR (
                    q.activity_id IS NULL
                    AND a2.course_id = q.course_id
                    AND a2.title = q.title
                    AND a2.activity_type = 'quiz'
                )
            )
        INNER JOIN teacher_courses tc2 ON tc2.course_id = c2.id
        WHERE tc2.teacher_id = ?
          AND qa.submitted_at IS NOT NULL
    ) work
    ORDER BY submitted_at DESC, work_id DESC
";
$workStmt = $pdo->prepare($workSql);
$workStmt->execute([$user['id'], $user['id']]);
$workRows = $workStmt->fetchAll();

$labels = [
    'assignment' => 'Assignment',
    'quiz' => 'Quiz',
    'project' => 'Project',
    'practical' => 'Practical Activity',
    'discussion' => 'Discussion / Task',
];
$icons = [
    'assignment' => 'fa-file-pen',
    'quiz' => 'fa-circle-question',
    'project' => 'fa-diagram-project',
    'practical' => 'fa-flask',
    'discussion' => 'fa-comments',
];

$page_title = 'Submissions';
require __DIR__.'/../includes/header.php';
?>
<div class="app">
<aside class="sidebar">
    <div class="brand"><div class="brand-mark"><i class="fa-solid fa-graduation-cap"></i></div><div><strong>EdubridgeAcademy</strong><span>Teacher</span></div></div>
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
<div class="nav-sep"></div>
        <a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i>Sign out</a>
    </nav>
</aside>

<main class="main">
<header class="topbar">
    <div><span class="eyebrow">Teacher portal</span><h1>Submissions</h1></div>
    <div class="user-chip"><div class="avatar"><?=e(strtoupper(substr($user['first_name'],0,1).substr($user['last_name'],0,1)))?></div><div><b><?=e($user['first_name'].' '.$user['last_name'])?></b><span>Teacher</span></div></div>
</header>

<div class="content">
    <section class="card card-pad" style="margin-bottom:18px">
        <div class="section-head">
            <div>
                <span class="eyebrow">Coursework</span>
                <h2>All Activities</h2>
                <p class="muted">Every activity you created appears here, including quizzes. Submitted work is counted from the correct submission system.</p>
            </div>
            <a class="btn btn-soft" href="activities.php"><i class="fa-solid fa-list-check"></i> Manage Activities</a>
        </div>
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Activity</th><th>Course</th><th>Type</th><th>Points</th><th>Due</th><th>Student Work</th></tr></thead>
                <tbody>
                <?php foreach ($activities as $a): ?>
                    <tr>
                        <td><b><?=e($a['activity_title'])?></b></td>
                        <td><?=e($a['course_title'])?><br><small class="muted"><?=e($a['course_code'])?></small></td>
                        <td><span class="badge"><?=e($labels[$a['activity_type']] ?? ucfirst((string)$a['activity_type']))?></span></td>
                        <td><?=e((string)$a['points'])?></td>
                        <td><?=!empty($a['due_at']) ? e(date('M d, Y h:i A', strtotime($a['due_at']))) : 'No due date'?></td>
                        <td><span class="badge <?=((int)$a['total_work_count'] > 0 ? 'green' : 'purple')?>"><?=number_format((int)$a['total_work_count'])?> submitted</span></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$activities): ?><tr><td colspan="6" class="empty">No activities have been created yet.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="card card-pad">
        <div class="section-head">
            <div>
                <span class="eyebrow">Student work</span>
                <h2>Submitted Work</h2>
                <p class="muted">Assignments come from submissions; quizzes come from quiz attempts. Both are shown together below.</p>
            </div>
            <span class="badge purple"><?=number_format(count($workRows))?> submission<?=count($workRows)===1?'':'s'?></span>
        </div>
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Student</th><th>Activity</th><th>Course</th><th>Type</th><th>Submitted</th><th>Attachment</th><th>Grade</th><th>Action</th></tr></thead>
                <tbody>
                <?php foreach ($workRows as $r): ?>
                    <tr>
                        <td><b><?=e($r['student_name'])?></b><br><small class="muted"><?=e($r['email'])?></small></td>
                        <td><b><?=e($r['activity_title'])?></b></td>
                        <td><?=e($r['course_title'])?><br><small class="muted"><?=e($r['course_code'])?></small></td>
                        <td><span class="badge"><i class="fa-solid <?=e($icons[$r['activity_type']] ?? 'fa-list-check')?>"></i> <?=e($labels[$r['activity_type']] ?? ucfirst((string)$r['activity_type']))?></span></td>
                        <td><?=!empty($r['submitted_at']) ? e(date('M d, Y h:i A', strtotime($r['submitted_at']))) : '—'?></td>
                        <td>
                            <?php if (!empty($r['file_path'])): ?>
                                <a class="btn btn-soft" href="<?=e(APP_URL.'/'.$r['file_path'])?>" target="_blank" rel="noopener"><i class="fa-solid fa-paperclip"></i> Open file</a>
                            <?php else: ?><span class="muted">—</span><?php endif; ?>
                        </td>
                        <td>
                            <?php if ($r['score'] !== null): ?>
                                <span class="badge green"><?=e((string)$r['score'])?> / <?=e((string)$r['max_points'])?></span>
                            <?php else: ?><span class="badge purple">Not graded</span><?php endif; ?>
                        </td>
                        <td>
                            <?php if ($r['work_type'] === 'assignment'): ?>
                                <a class="btn btn-soft" href="grade_submission.php?id=<?=(int)$r['work_id']?>"><i class="fa-solid fa-pen-to-square"></i> Grade</a>
                            <?php else: ?>
                                <a class="btn btn-soft" href="quiz_submission.php?id=<?=(int)$r['work_id']?>"><i class="fa-solid fa-eye"></i> View</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$workRows): ?><tr><td colspan="8" class="empty"><i class="fa-solid fa-inbox"></i><br>No student submissions yet.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>
</main>
</div>
<?php require __DIR__.'/../includes/footer.php'; ?>
