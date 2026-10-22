<?php
declare(strict_types=1);
require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/../includes/auth.php';
require_role('student');
flash('success','Course enrollment is controlled by the administrator. Review your pending course invitations to accept a course.');
redirect('student/invitations.php');
