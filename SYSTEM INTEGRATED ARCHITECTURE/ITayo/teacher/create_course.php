<?php
declare(strict_types=1);
require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/../includes/auth.php';
require_role('teacher');

flash('success','Course creation is managed by the administrator so sectioning and course assignments remain organized.');
redirect('teacher/courses.php');
