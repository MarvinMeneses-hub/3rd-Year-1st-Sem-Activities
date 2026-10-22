<?php
declare(strict_types=1);
require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/../includes/auth.php';
require_role('student');

$id = (int)($_GET['assignment_id'] ?? $_POST['assignment_id'] ?? 0);
if (!$id) {
    redirect('student/courses.php');
}
redirect('student/submit.php?id='.$id);
