<?php
declare(strict_types=1);
require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/../includes/auth.php';
require_role('teacher');

// Assignments are now an activity type and are managed from Activities.
// Keep this route only so old bookmarks/links continue to work.
redirect('teacher/activities.php');
