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
$body_class = 'ig-login-page';

require __DIR__ . '/includes/header.php';
?>

<main class="ig-login">

    <section class="ig-login-card" aria-labelledby="loginTitle">

        <div class="ig-brand">
            <span class="ig-brand-mark" aria-hidden="true"></span>
            <span>
                <b>ITayo</b>
                <small>GO GO GOOO · TODO NATO!</small>
            </span>
        </div>

        <div class="ig-login-copy">
            <p class="ig-eyebrow">YOUR LEARNING SPACE</p>
            <h1 id="loginTitle">Welcome back.</h1>
            <p>Sign in to continue your learning journey.</p>
        </div>

        <?php if ($googleLoginError !== ''): ?>
            <div class="ig-alert" role="alert">
                <?= e($googleLoginError) ?>
            </div>
        <?php endif; ?>

        <?php if ($error !== ''): ?>
            <div class="ig-alert" role="alert">
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <form method="post" class="ig-form">
            <div class="ig-field">
                <label class="ig-label" for="loginEmail">Email</label>
                <div class="ig-input">
                    <i class="fa-regular fa-envelope" aria-hidden="true"></i>
                    <input
                        id="loginEmail"
                        type="email"
                        name="email"
                        placeholder="Enter your school email"
                        autocomplete="username"
                        required
                        value="<?= e($_POST['email'] ?? '') ?>"
                    >
                </div>
            </div>

            <div class="ig-field">
                <label class="ig-label" for="loginPassword">Password</label>
                <div class="ig-input">
                    <i class="fa-solid fa-lock" aria-hidden="true"></i>
                    <input
                        id="loginPassword"
                        type="password"
                        name="password"
                        placeholder="Enter your password"
                        autocomplete="current-password"
                        required
                    >
                    <button
                        type="button"
                        class="ig-toggle"
                        id="togglePassword"
                        aria-label="Show password"
                    >
                        <i class="fa-regular fa-eye" aria-hidden="true"></i>
                    </button>
                </div>
            </div>

            <div class="ig-forgot">
                <a href="#" onclick="return false;">Forgot password?</a>
            </div>

            <button class="ig-submit" type="submit">Sign in</button>
        </form>

        <div class="ig-or" aria-hidden="true">OR</div>

        <a class="ig-google" href="<?= e(APP_URL) ?>/google_login.php">
            <i class="fa-brands fa-google" aria-hidden="true"></i>
            Continue with Google
        </a>

        <p class="ig-note">Secure access to your learning portal</p>

    </section>

    <aside class="ig-art">
        <div class="ig-orbit a" aria-hidden="true"></div>
        <div class="ig-orbit b" aria-hidden="true"></div>

        <div class="ig-glass">
            <span class="ig-glass-icon" aria-hidden="true"><i class="fa-solid fa-book-open"></i></span>
            <p class="ig-eyebrow">GO GO GOOO · TODO NATO!</p>
            <h2>Better learning.<br><em>Brighter futures.</em></h2>
            <p>A thoughtful space for students and teachers to grow together.</p>
            <ul class="ig-pills">
                <li><i class="fa-solid fa-check" aria-hidden="true"></i> Courses</li>
                <li><i class="fa-solid fa-check" aria-hidden="true"></i> Activities</li>
                <li><i class="fa-solid fa-check" aria-hidden="true"></i> Progress</li>
            </ul>
        </div>

        <div class="ig-caption">Made for every step of your learning journey.</div>
    </aside>

</main>

<script>
(function () {
    const toggle = document.getElementById('togglePassword');
    const input = document.getElementById('loginPassword');

    if (toggle && input) {
        toggle.addEventListener('click', function () {
            const hidden = input.type === 'password';

            input.type = hidden ? 'text' : 'password';

            this.innerHTML = hidden
                ? '<i class="fa-regular fa-eye-slash" aria-hidden="true"></i>'
                : '<i class="fa-regular fa-eye" aria-hidden="true"></i>';

            this.setAttribute(
                'aria-label',
                hidden ? 'Hide password' : 'Show password'
            );
        });
    }
})();
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
