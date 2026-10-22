<?php

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';

if (is_logged_in()) {
    redirect(role_home((string) $_SESSION['role']));
}

$error = '';

// Display an error returned by Google sign-in.
$googleLoginError = (string) ($_SESSION['google_login_error'] ?? '');
unset($_SESSION['google_login_error']);

// Handle the existing email and password login.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
        $error = 'Please enter a valid email address and password.';
    } else {
        $stmt = db()->prepare(
            "SELECT u.id, u.password_hash, u.status, r.name AS role
             FROM users u
             JOIN roles r ON r.id = u.role_id
             WHERE u.email = ?
             LIMIT 1"
        );

        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (
            !$user ||
            $user['status'] !== 'active' ||
            !password_verify($password, $user['password_hash'])
        ) {
            $error = 'Invalid email or password.';
        } else {
            session_regenerate_id(true);

            $_SESSION['user_id'] = (int) $user['id'];
            $_SESSION['role'] = (string) $user['role'];

            db()->prepare(
                "UPDATE users SET last_login_at = NOW() WHERE id = ?"
            )->execute([(int) $user['id']]);

            redirect(role_home((string) $user['role']));
        }
    }
}

$page_title = 'Sign in';
$body_class = 'login-page';

require __DIR__ . '/includes/header.php';
?>

<div class="auth login-auth">
    <section class="auth-card login-card">

        <div class="login-logo">
            <div class="login-logo-inner">
                <i class="fa-solid fa-graduation-cap"></i>
            </div>
        </div>

        <div class="auth-head login-head">
            <h1>EdubridgeAcademy</h1>
            <p>Learn · Grow · Achieve</p>
        </div>

        <?php if ($googleLoginError !== ''): ?>
            <div class="notice login-notice" role="alert">
                <?= e($googleLoginError) ?>
            </div>
        <?php endif; ?>

        <?php if ($error !== ''): ?>
            <div class="notice login-notice" role="alert">
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <form method="post" class="login-form">
            <div class="login-field">
                <i class="fa-regular fa-envelope"></i>
                <input
                    type="email"
                    name="email"
                    placeholder="email"
                    autocomplete="username"
                    required
                    value="<?= e($_POST['email'] ?? '') ?>"
                >
            </div>

            <div class="login-field">
                <i class="fa-solid fa-lock"></i>
                <input
                    id="loginPassword"
                    type="password"
                    name="password"
                    placeholder="password"
                    autocomplete="current-password"
                    required
                >
                <button
                    type="button"
                    class="password-toggle"
                    id="togglePassword"
                    aria-label="Show password"
                >
                    <i class="fa-regular fa-eye"></i>
                </button>
            </div>

            <div class="login-forgot">
                <a href="#" onclick="return false;">Forgot password?</a>
            </div>

            <button class="login-button" type="submit">
                Login
            </button>
        </form>

        <div style="
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 22px 0;
            color: #8b7d88;
            font-size: 12px;
        ">
            <span style="flex: 1; height: 1px; background: #eadde5;"></span>
            <span>OR</span>
            <span style="flex: 1; height: 1px; background: #eadde5;"></span>
        </div>

        <a
            href="<?= e(APP_URL) ?>/google_login.php"
            style="
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 12px;
                width: 100%;
                min-height: 48px;
                box-sizing: border-box;
                padding: 12px 16px;
                border: 1px solid #e4d7e0;
                border-radius: 999px;
                background: rgba(255, 255, 255, 0.78);
                color: #302733;
                font-size: 14px;
                font-weight: 600;
                text-decoration: none;
                transition: background 0.2s ease, transform 0.2s ease;
            "
            onmouseover="this.style.background='#fff';"
            onmouseout="this.style.background='rgba(255, 255, 255, 0.78)';"
        >
            <i class="fa-brands fa-google" aria-hidden="true"></i>
            Continue with Google
        </a>

        <div class="login-footer-text" style="margin-top: 20px;">
            Secure access to your learning portal
        </div>

    </section>
</div>

<script>
(function () {
    const toggle = document.getElementById('togglePassword');
    const input = document.getElementById('loginPassword');

    if (toggle && input) {
        toggle.addEventListener('click', function () {
            const hidden = input.type === 'password';

            input.type = hidden ? 'text' : 'password';

            this.innerHTML = hidden
                ? '<i class="fa-regular fa-eye-slash"></i>'
                : '<i class="fa-regular fa-eye"></i>';

            this.setAttribute(
                'aria-label',
                hidden ? 'Hide password' : 'Show password'
            );
        });
    }
})();
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>