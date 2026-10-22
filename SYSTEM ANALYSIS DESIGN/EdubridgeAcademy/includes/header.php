<?php
declare(strict_types=1);
require_once __DIR__.'/../config/config.php';
$flashes = get_flashes();
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= e(($page_title ?? 'Dashboard').' · '.APP_NAME) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;750&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
<link rel="stylesheet" href="<?= APP_URL ?>/assets/edubridge.css?v=20260930r6">
</head>
<body class="<?= e($body_class ?? '') ?>">

<?php if (basename($_SERVER['PHP_SELF']) !== 'login.php'): ?>
<!-- Mobile/tablet navigation button for role pages only. -->
<button
    type="button"
    class="nav-toggle"
    id="mobileNavToggle"
    aria-controls="app-sidebar"
    aria-expanded="false"
    aria-label="Open navigation menu"
>
    <i class="fa-solid fa-bars" aria-hidden="true"></i>
</button>
<?php endif; ?>

<?php foreach ($flashes as $f): ?>
<div class="toast toast-<?= e($f['type']) ?>"><?= e($f['message']) ?></div>
<?php endforeach; ?>
