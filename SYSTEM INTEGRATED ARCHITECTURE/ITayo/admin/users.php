<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

require_role('admin');

$user = current_user();

if (!$user) {
    redirect('login.php');
}

$page_title = 'User Management';
$pdo = db();

/*
 * Handle account actions securely through POST requests.
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $action = (string) ($_POST['action'] ?? '');
    $targetId = (int) ($_POST['user_id'] ?? 0);

    if ($targetId < 1) {
        flash('error', 'Invalid user selected.');
        redirect('admin/users.php');
    }

    if ($targetId === (int) $user['id']) {
        flash(
            'error',
            'You cannot deactivate or delete your own administrator account here.'
        );
        redirect('admin/users.php');
    }

    $stmt = $pdo->prepare(
        "SELECT u.id, u.first_name, u.last_name, u.status, r.name AS role
         FROM users u
         JOIN roles r ON r.id = u.role_id
         WHERE u.id = ?
         LIMIT 1"
    );
    $stmt->execute([$targetId]);
    $target = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$target) {
        flash('error', 'User not found.');
        redirect('admin/users.php');
    }

    if ($action === 'toggle_status') {
        $newStatus = $target['status'] === 'active'
            ? 'inactive'
            : 'active';

        /*
         * Prevent deactivating the last active administrator.
         */
        if (
            $newStatus === 'inactive' &&
            $target['role'] === 'admin'
        ) {
            $countStmt = $pdo->query(
                "SELECT COUNT(*)
                 FROM users u
                 JOIN roles r ON r.id = u.role_id
                 WHERE r.name = 'admin'
                   AND u.status = 'active'"
            );

            if ((int) $countStmt->fetchColumn() <= 1) {
                flash(
                    'error',
                    'The last active administrator cannot be deactivated.'
                );
                redirect('admin/users.php');
            }
        }

        $update = $pdo->prepare(
            'UPDATE users SET status = ? WHERE id = ?'
        );
        $update->execute([$newStatus, $targetId]);

        flash(
            'success',
            $newStatus === 'active'
                ? 'User activated successfully.'
                : 'User deactivated successfully.'
        );

        redirect('admin/users.php');
    }

    if ($action === 'delete_user') {
        /*
         * Permanent deletion can cascade to learning records.
         * Require the administrator to acknowledge the warning.
         */
        if (($_POST['confirm_delete'] ?? '') !== 'yes') {
            flash(
                'error',
                'Deletion was not confirmed. No changes were made.'
            );
            redirect('admin/users.php');
        }

        /*
         * Do not delete the last active administrator.
         */
        if (
            $target['role'] === 'admin' &&
            $target['status'] === 'active'
        ) {
            $countStmt = $pdo->query(
                "SELECT COUNT(*)
                 FROM users u
                 JOIN roles r ON r.id = u.role_id
                 WHERE r.name = 'admin'
                   AND u.status = 'active'"
            );

            if ((int) $countStmt->fetchColumn() <= 1) {
                flash(
                    'error',
                    'The last active administrator cannot be deleted.'
                );
                redirect('admin/users.php');
            }
        }

        try {
            $delete = $pdo->prepare('DELETE FROM users WHERE id = ?');
            $delete->execute([$targetId]);

            flash(
                'success',
                'User deleted. Related records governed by cascading foreign keys may also have been deleted.'
            );
        } catch (Throwable $exception) {
            error_log(
                'ITayo user deletion failed for user ID ' .
                $targetId . ': ' . $exception->getMessage()
            );

            flash(
                'error',
                'The user could not be deleted. Check the PHP error log.'
            );
        }

        redirect('admin/users.php');
    }

    flash('error', 'Unknown user action.');
    redirect('admin/users.php');
}

/*
 * Search and filter.
 */
$q = trim((string) ($_GET['q'] ?? ''));
$roleFilter = strtolower(trim((string) ($_GET['role'] ?? 'all')));
$statusFilter = strtolower(trim((string) ($_GET['status'] ?? 'all')));

$validRoles = ['all', 'admin', 'teacher', 'student'];
$validStatuses = ['all', 'active', 'inactive'];

if (!in_array($roleFilter, $validRoles, true)) {
    $roleFilter = 'all';
}

if (!in_array($statusFilter, $validStatuses, true)) {
    $statusFilter = 'all';
}

$where = [];
$params = [];

if ($q !== '') {
    $where[] = "(
        u.first_name LIKE ?
        OR u.last_name LIKE ?
        OR u.email LIKE ?
        OR r.name LIKE ?
    )";

    $term = '%' . $q . '%';
    array_push($params, $term, $term, $term, $term);
}

if ($roleFilter !== 'all') {
    $where[] = 'r.name = ?';
    $params[] = $roleFilter;
}

if ($statusFilter !== 'all') {
    $where[] = 'u.status = ?';
    $params[] = $statusFilter;
}

$sql = "
    SELECT
        u.id,
        u.first_name,
        u.last_name,
        u.email,
        u.status,
        u.created_at,
        r.name AS role
    FROM users u
    JOIN roles r ON r.id = u.role_id
";

if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}

/*
 * Active accounts appear before inactive accounts.
 * Within each status group: admins, teachers, students,
 * then names alphabetically.
 */
$sql .= "
    ORDER BY
        CASE WHEN u.status = 'active' THEN 0 ELSE 1 END,
        CASE r.name
            WHEN 'admin' THEN 1
            WHEN 'teacher' THEN 2
            WHEN 'student' THEN 3
            ELSE 4
        END,
        u.first_name ASC,
        u.last_name ASC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

/*
 * Counts are based on the complete users table, not the
 * current search or filter.
 */
$counts = [
    'active' => 0,
    'inactive' => 0,
    'admin' => 0,
    'teacher' => 0,
    'student' => 0,
];

$countRows = $pdo->query(
    "SELECT u.status, r.name AS role, COUNT(*) AS total
     FROM users u
     JOIN roles r ON r.id = u.role_id
     GROUP BY u.status, r.name"
)->fetchAll(PDO::FETCH_ASSOC);

foreach ($countRows as $countRow) {
    $status = strtolower((string) $countRow['status']);
    $role = strtolower((string) $countRow['role']);
    $total = (int) $countRow['total'];

    if (isset($counts[$status])) {
        $counts[$status] += $total;
    }

    if (isset($counts[$role])) {
        $counts[$role] += $total;
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
                <strong>ITayo</strong>
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
                <h1>User Management</h1>
            </div>

            <div class="user-chip">
                <div class="avatar">
                    <?= e(strtoupper(
                        substr((string) $user['first_name'], 0, 1) .
                        substr((string) $user['last_name'], 0, 1)
                    )) ?>
                </div>

                <div>
                    <b>
                        <?= e(
                            $user['first_name'] . ' ' . $user['last_name']
                        ) ?>
                    </b>
                    <span>Admin</span>
                </div>
            </div>
        </header>

        <div class="content">
            <div class="section-head">
                <div>
                    <span class="eyebrow">People</span>
                    <h2>Users</h2>
                </div>

                <a class="btn btn-primary" href="user_form.php">
                    <i class="fa-solid fa-user-plus"></i>
                    Add User
                </a>
            </div>

            <div class="grid" style="
                display:grid;
                grid-template-columns:repeat(auto-fit,minmax(150px,1fr));
                gap:14px;
                margin-bottom:20px;
            ">
                <section class="card card-pad">
                    <span class="eyebrow">Active</span>
                    <h2><?= $counts['active'] ?></h2>
                    <span class="badge green">Can sign in</span>
                </section>

                <section class="card card-pad">
                    <span class="eyebrow">Inactive</span>
                    <h2><?= $counts['inactive'] ?></h2>
                    <span class="badge gray">Access disabled</span>
                </section>

                <section class="card card-pad">
                    <span class="eyebrow">Administrators</span>
                    <h2><?= $counts['admin'] ?></h2>
                </section>

                <section class="card card-pad">
                    <span class="eyebrow">Teachers</span>
                    <h2><?= $counts['teacher'] ?></h2>
                </section>

                <section class="card card-pad">
                    <span class="eyebrow">Students</span>
                    <h2><?= $counts['student'] ?></h2>
                </section>
            </div>

            <section class="card card-pad">
                <form class="actions" method="get">
                    <input
                        class="input"
                        style="max-width:300px"
                        name="q"
                        placeholder="Search name, email or role"
                        value="<?= e($q) ?>"
                    >

                    <select class="select" name="role" style="max-width:180px">
                        <option value="all" <?= $roleFilter === 'all' ? 'selected' : '' ?>>
                            All roles
                        </option>
                        <option value="admin" <?= $roleFilter === 'admin' ? 'selected' : '' ?>>
                            Administrators
                        </option>
                        <option value="teacher" <?= $roleFilter === 'teacher' ? 'selected' : '' ?>>
                            Teachers
                        </option>
                        <option value="student" <?= $roleFilter === 'student' ? 'selected' : '' ?>>
                            Students
                        </option>
                    </select>

                    <select class="select" name="status" style="max-width:180px">
                        <option value="all" <?= $statusFilter === 'all' ? 'selected' : '' ?>>
                            All statuses
                        </option>
                        <option value="active" <?= $statusFilter === 'active' ? 'selected' : '' ?>>
                            Active first
                        </option>
                        <option value="inactive" <?= $statusFilter === 'inactive' ? 'selected' : '' ?>>
                            Inactive only
                        </option>
                    </select>

                    <button class="btn btn-soft" type="submit">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        Search
                    </button>

                    <a class="btn btn-soft" href="users.php">Reset</a>
                </form>
            </section>

            <?php
            $groups = [
                'active' => [
                    'admin' => [],
                    'teacher' => [],
                    'student' => [],
                    'other' => [],
                ],
                'inactive' => [
                    'admin' => [],
                    'teacher' => [],
                    'student' => [],
                    'other' => [],
                ],
            ];

            foreach ($rows as $row) {
                $status = strtolower((string) $row['status']) === 'active'
                    ? 'active'
                    : 'inactive';

                $role = strtolower((string) $row['role']);
                $roleKey = in_array(
                    $role,
                    ['admin', 'teacher', 'student'],
                    true
                ) ? $role : 'other';

                $groups[$status][$roleKey][] = $row;
            }

            $roleLabels = [
                'admin' => 'Administrators',
                'teacher' => 'Teachers',
                'student' => 'Students',
                'other' => 'Other roles',
            ];
            ?>

            <?php foreach (['active', 'inactive'] as $statusGroup): ?>
                <section class="card card-pad" style="margin-top:20px">
                    <div class="section-head">
                        <div>
                            <span class="eyebrow">
                                <?= $statusGroup === 'active'
                                    ? 'Access enabled'
                                    : 'Access disabled' ?>
                            </span>
                            <h2>
                                <?= ucfirst($statusGroup) ?> Users
                            </h2>
                        </div>

                        <span class="badge <?= $statusGroup === 'active' ? 'green' : 'gray' ?>">
                            <?= array_sum(array_map(
                                'count',
                                $groups[$statusGroup]
                            )) ?> users
                        </span>
                    </div>

                    <?php
                    $hasStatusUsers = array_sum(array_map(
                        'count',
                        $groups[$statusGroup]
                    )) > 0;
                    ?>

                    <?php if (!$hasStatusUsers): ?>
                        <p class="empty">
                            No <?= $statusGroup ?> users found for these filters.
                        </p>
                    <?php else: ?>
                        <?php foreach ($roleLabels as $roleKey => $roleLabel): ?>
                            <?php $roleRows = $groups[$statusGroup][$roleKey]; ?>

                            <?php if ($roleRows): ?>
                                <h3 style="margin:22px 0 12px">
                                    <?= e($roleLabel) ?>
                                    <span class="badge">
                                        <?= count($roleRows) ?>
                                    </span>
                                </h3>

                                <div class="table-wrap">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Role</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            <?php foreach ($roleRows as $r): ?>
                                                <tr>
                                                    <td>
                                                        <b>
                                                            <?= e(
                                                                $r['first_name'] . ' ' .
                                                                $r['last_name']
                                                            ) ?>
                                                        </b>
                                                    </td>

                                                    <td><?= e($r['email']) ?></td>

                                                    <td>
                                                        <span class="badge">
                                                            <?= e(ucfirst((string) $r['role'])) ?>
                                                        </span>
                                                    </td>

                                                    <td>
                                                        <span class="badge <?= $r['status'] === 'active' ? 'green' : 'gray' ?>">
                                                            <?= e(ucfirst((string) $r['status'])) ?>
                                                        </span>
                                                    </td>

                                                    <td>
                                                        <div class="actions">
                                                            <a
                                                                class="btn btn-soft"
                                                                href="user_form.php?id=<?= (int) $r['id'] ?>"
                                                            >
                                                                <i class="fa-solid fa-pen"></i>
                                                                Edit
                                                            </a>

                                                            <form
                                                                method="post"
                                                                style="display:inline"
                                                                onsubmit="return confirm('<?= $r['status'] === 'active' ? 'Deactivate' : 'Activate' ?> this user?');"
                                                            >
                                                                <input
                                                                    type="hidden"
                                                                    name="csrf"
                                                                    value="<?= e(csrf_token()) ?>"
                                                                >
                                                                <input
                                                                    type="hidden"
                                                                    name="action"
                                                                    value="toggle_status"
                                                                >
                                                                <input
                                                                    type="hidden"
                                                                    name="user_id"
                                                                    value="<?= (int) $r['id'] ?>"
                                                                >

                                                                <button
                                                                    type="submit"
                                                                    class="btn btn-dark"
                                                                >
                                                                    <?= $r['status'] === 'active'
                                                                        ? 'Deactivate'
                                                                        : 'Activate' ?>
                                                                </button>
                                                            </form>

                                                            <form
                                                                method="post"
                                                                style="display:inline"
                                                                onsubmit="return confirm('PERMANENT DELETE WARNING\n\nThis will permanently delete this user. Related enrollments, submissions, quiz attempts, messages, progress and other records may also be deleted.\n\nDeactivate the account instead if you need to preserve learning records.\n\nAre you absolutely sure you want to continue?');"
                                                            >
                                                                <input
                                                                    type="hidden"
                                                                    name="csrf"
                                                                    value="<?= e(csrf_token()) ?>"
                                                                >
                                                                <input
                                                                    type="hidden"
                                                                    name="action"
                                                                    value="delete_user"
                                                                >
                                                                <input
                                                                    type="hidden"
                                                                    name="user_id"
                                                                    value="<?= (int) $r['id'] ?>"
                                                                >
                                                                <input
                                                                    type="hidden"
                                                                    name="confirm_delete"
                                                                    value="yes"
                                                                >

                                                                <button
                                                                    type="submit"
                                                                    class="btn btn-soft"
                                                                    style="border:1px solid #c62828;color:#c62828"
                                                                >
                                                                    <i class="fa-solid fa-trash"></i>
                                                                    Delete
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </section>
            <?php endforeach; ?>
        </div>
    </main>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>