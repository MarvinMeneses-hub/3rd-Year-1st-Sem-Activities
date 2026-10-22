<?php
declare(strict_types=1);

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';

/*
 * Main domain entry:
 * Always begin at the login page.
 * This intentionally clears an existing browser session so that
 * opening "/" never jumps directly into a role dashboard.
 */
$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

session_destroy();

header('Location: login.php', true, 302);
exit;
