<?php
declare(strict_types=1);
require_once __DIR__.'/../config/config.php';

function is_logged_in(): bool {
    return isset($_SESSION['user_id'], $_SESSION['role']);
}
function require_login(): void {
    if (!is_logged_in()) redirect('login.php');
}
function require_role(string $role): void {
    require_login();
    if ($_SESSION['role'] !== $role) {
        http_response_code(403);
        exit('403 Forbidden');
    }
}
function current_user(): ?array {
    if (!is_logged_in()) return null;
    static $user = false;
    if ($user !== false) return $user;
    $s = db()->prepare("SELECT u.*, r.name role FROM users u JOIN roles r ON r.id=u.role_id WHERE u.id=? AND u.status='active' LIMIT 1");
    $s->execute([(int)$_SESSION['user_id']]);
    $user = $s->fetch() ?: null;
    return $user;
}
function role_home(string $role): string {
    return match($role) {
        'admin' => 'admin/dashboard.php',
        'teacher' => 'teacher/dashboard.php',
        'student' => 'student/dashboard.php',
        default => 'login.php'
    };
}
