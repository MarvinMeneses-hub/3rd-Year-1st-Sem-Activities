<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/mail.php';

use PHPMailer\PHPMailer\PHPMailer;

require_role('admin');

$user = current_user();
$page_title = 'User Form';

$pdo = db();
$id = (int)($_GET['id'] ?? 0);

$row = [
    'first_name' => '',
    'last_name'  => '',
    'email'      => '',
    'role_id'    => '',
    'status'     => 'active'
];

if ($id) {
    $s = $pdo->prepare(
        "SELECT u.*, r.name AS role
         FROM users u
         JOIN roles r ON r.id = u.role_id
         WHERE u.id = ?"
    );
    $s->execute([$id]);
    $row = $s->fetch() ?: $row;
}

$roles = $pdo->query(
    "SELECT id, name FROM roles ORDER BY id"
)->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $first  = trim(post('first_name'));
    $last   = trim(post('last_name'));
    $email  = trim(post('email'));
    $role   = (int)($_POST['role_id'] ?? 0);
    $status = post('status', 'active');
    $pw     = (string)($_POST['password'] ?? '');

    if (
        !$first ||
        !$last ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        !$role ||
        !in_array($status, ['active', 'inactive'], true) ||
        (!$id && $pw === '')
    ) {
        flash('error', 'Please complete the required fields.');
    } else {
        if ($id) {
            // Update an existing user. No new-account email is sent.
            if ($pw !== '') {
                $s = $pdo->prepare(
                    "UPDATE users
                     SET first_name = ?, last_name = ?, email = ?,
                         role_id = ?, status = ?, password_hash = ?
                     WHERE id = ?"
                );

                $s->execute([
                    $first,
                    $last,
                    $email,
                    $role,
                    $status,
                    password_hash($pw, PASSWORD_DEFAULT),
                    $id
                ]);
            } else {
                $s = $pdo->prepare(
                    "UPDATE users
                     SET first_name = ?, last_name = ?, email = ?,
                         role_id = ?, status = ?
                     WHERE id = ?"
                );

                $s->execute([
                    $first,
                    $last,
                    $email,
                    $role,
                    $status,
                    $id
                ]);
            }

            flash('success', 'User updated.');
            redirect('admin/users.php');
        } else {
            // Create the account using the password entered by the admin.
            $s = $pdo->prepare(
                "INSERT INTO users
                    (role_id, first_name, last_name, email, password_hash, status)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );

            $s->execute([
                $role,
                $first,
                $last,
                $email,
                password_hash($pw, PASSWORD_DEFAULT),
                $status
            ]);

            // Determine the user's role for the welcome email.
            $roleName = 'User';

            foreach ($roles as $r) {
                if ((int)$r['id'] === $role) {
                    $roleName = ucfirst((string)$r['name']);
                    break;
                }
            }

            // Send a notification after the account has been saved.
            try {
                $mail = new PHPMailer(true);

                $mail->isSMTP();
                $mail->Host       = MAIL_HOST;
                $mail->SMTPAuth   = true;
                $mail->Username   = MAIL_USERNAME;
                $mail->Password   = MAIL_PASSWORD;
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = MAIL_PORT;
                $mail->CharSet    = 'UTF-8';

                $mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
                $mail->addAddress($email, $first . ' ' . $last);

                $mail->isHTML(true);
                $mail->Subject = 'Your EduBridgeAcademy Account Has Been Created';

                // Change this URL if your project uses a different address.
                $loginUrl = 'https://investigator-notify-gathering-therapist.trycloudflare.com/EdubridgeAcademy/login.php';

                $safeFirst = htmlspecialchars(
                    $first,
                    ENT_QUOTES | ENT_SUBSTITUTE,
                    'UTF-8'
                );

                $safeRole = htmlspecialchars(
                    $roleName,
                    ENT_QUOTES | ENT_SUBSTITUTE,
                    'UTF-8'
                );

                $safeLoginUrl = htmlspecialchars(
                    $loginUrl,
                    ENT_QUOTES | ENT_SUBSTITUTE,
                    'UTF-8'
                );

                $mail->Body = '
                    <div style="font-family:Arial,sans-serif;
                                max-width:600px;margin:auto;
                                border:1px solid #eadfd8;
                                border-radius:12px;overflow:hidden;">
                        <div style="background:#7d0b12;color:#ffffff;
                                    padding:25px;text-align:center;">
                            <h1 style="margin:0;">EduBridgeAcademy</h1>
                            <p style="margin:8px 0 0;">
                                Learning starts here
                            </p>
                        </div>

                        <div style="padding:28px;color:#333333;">
                            <h2>Welcome, ' . $safeFirst . '!</h2>

                            <p>
                                Your EduBridgeAcademy account has been
                                successfully created by the administrator.
                            </p>

                            <p>
                                <strong>Account role:</strong>
                                ' . $safeRole . '
                            </p>

                            <p>
                                You can now sign in using the email address
                                and initial password provided to you by
                                your administrator.
                            </p>

                            <p style="text-align:center;margin:28px 0;">
                                <a href="' . $safeLoginUrl . '"
                                   style="background:#7d0b12;color:#ffffff;
                                          padding:13px 24px;
                                          border-radius:7px;
                                          text-decoration:none;
                                          display:inline-block;">
                                    Go to Login
                                </a>
                            </p>

                            <p>
                                For your security, never share your password.
                                You may change it after signing in if your
                                LMS provides the password-change option.
                            </p>

                            <p>
                                If you have questions, please contact your
                                EduBridgeAcademy administrator.
                            </p>

                            <p>Thank you,<br>EduBridgeAcademy Team</p>
                        </div>
                    </div>
                ';

                $mail->AltBody =
                    "Welcome, {$first}!\n\n" .
                    "Your EduBridgeAcademy account has been created.\n" .
                    "Role: {$roleName}\n\n" .
                    "Sign in using the email address and initial password " .
                    "provided by your administrator.\n\n" .
                    "Login: {$loginUrl}\n\n" .
                    "Please keep your password private.";

                $mail->send();

                flash(
                    'success',
                    'User account created successfully. Welcome email sent.'
                );
            } catch (\Throwable $e) {
                // Keep the account even if email delivery fails.
                error_log(
                    'EduBridgeAcademy account notification failed: ' .
                    $e->getMessage()
                );

                flash(
                    'error',
                    'Account created, but the email notification could not be sent. Check the PHP error log.'
                );
            }

            redirect('admin/users.php');
        }
    }
}

require __DIR__ . '/../includes/header.php';
?>

<div class="app">
    <aside class="sidebar">
        <div class="brand">
            <div class="brand-mark">
                <i class="fa-solid fa-graduation-cap"></i>
            </div>
            <div>
                <strong>EdubridgeAcademy</strong>
                <span>Admin</span>
            </div>
        </div>

        <nav class="nav">
            <a href="dashboard.php">
                <i class="fa-solid fa-grid-2"></i>Dashboard
            </a>

            <a href="users.php" class="active">
                <i class="fa-solid fa-users"></i>Users
            </a>

            <a href="courses.php">
                <i class="fa-solid fa-book-open"></i>Courses
            </a>

            <a href="subjects.php">
                <i class="fa-solid fa-book"></i>Subjects
            </a>

            <a href="sections.php">
                <i class="fa-solid fa-layer-group"></i>Sections
            </a>

            <a href="../google_account.php">
                <i class="fa-brands fa-google"></i>Google Account
            </a>

            <div class="nav-sep"></div>

            <a href="../logout.php">
                <i class="fa-solid fa-right-from-bracket"></i>Sign out
            </a>
        </nav>
    </aside>

    <main class="main">
        <header class="topbar">
            <div>
                <span class="eyebrow">Admin portal</span>
                <h1>User Form</h1>
            </div>

            <div class="user-chip">
                <div class="avatar">
                    <?= e(strtoupper(
                        substr($user['first_name'], 0, 1) .
                        substr($user['last_name'], 0, 1)
                    )) ?>
                </div>

                <div>
                    <b>
                        <?= e($user['first_name'] . ' ' . $user['last_name']) ?>
                    </b>
                    <span>Admin</span>
                </div>
            </div>
        </header>

        <div class="content">
            <section class="card card-pad">
                <div class="section-head">
                    <h2><?= $id ? 'Edit User' : 'Add User' ?></h2>
                    <a class="btn btn-soft" href="users.php">Back</a>
                </div>

                <form method="post">
                    <input
                        type="hidden"
                        name="csrf"
                        value="<?= e(csrf_token()) ?>"
                    >

                    <div class="form-grid">
                        <div class="form-group">
                            <label>First name</label>
                            <input
                                class="input"
                                name="first_name"
                                required
                                value="<?= e($row['first_name']) ?>"
                            >
                        </div>

                        <div class="form-group">
                            <label>Last name</label>
                            <input
                                class="input"
                                name="last_name"
                                required
                                value="<?= e($row['last_name']) ?>"
                            >
                        </div>

                        <div class="form-group">
                            <label>Email</label>
                            <input
                                class="input"
                                type="email"
                                name="email"
                                required
                                value="<?= e($row['email']) ?>"
                            >
                        </div>

                        <div class="form-group">
                            <label>Role</label>
                            <select class="select" name="role_id" required>
                                <?php foreach ($roles as $r): ?>
                                    <option
                                        value="<?= (int)$r['id'] ?>"
                                        <?= (
                                            (int)($row['role_id'] ?? 0) ===
                                            (int)$r['id']
                                        ) ? 'selected' : '' ?>
                                    >
                                        <?= e(ucfirst($r['name'])) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Status</label>
                            <select class="select" name="status">
                                <option
                                    value="active"
                                    <?= $row['status'] === 'active'
                                        ? 'selected' : '' ?>
                                >
                                    Active
                                </option>

                                <option
                                    value="inactive"
                                    <?= $row['status'] === 'inactive'
                                        ? 'selected' : '' ?>
                                >
                                    Inactive
                                </option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>
                                Password
                                <?= $id
                                    ? '(leave blank to keep current)'
                                    : '' ?>
                            </label>

                            <input
                                class="input"
                                type="password"
                                name="password"
                                <?= $id ? '' : 'required' ?>
                                autocomplete="new-password"
                            >
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            Save User
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </main>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>