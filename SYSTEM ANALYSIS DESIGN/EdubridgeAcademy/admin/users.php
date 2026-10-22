<?php
declare(strict_types=1);
require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/../includes/auth.php';
require_role('admin');
$user=current_user();
$page_title='User Management';
require __DIR__.'/../includes/header.php';
?>
<div class="app">
<aside class="sidebar">
<div class="brand"><div class="brand-mark"><i class="fa-solid fa-graduation-cap"></i></div><div><strong>EdubridgeAcademy</strong><span>Admin</span></div></div>
<nav class="nav"><a href="dashboard.php" class=""><i class="fa-solid fa-grid-2"></i>Dashboard</a>
<a href="users.php" class="active"><i class="fa-solid fa-users"></i>Users</a>
<a href="courses.php" class=""><i class="fa-solid fa-book-open"></i>Courses</a>
<a href="subjects.php" class=""><i class="fa-solid fa-book"></i>Subjects</a>
<a href="sections.php" class=""><i class="fa-solid fa-layer-group"></i>Sections</a><a href="../google_account.php"><i class="fa-brands fa-google"></i>Google Account</a>
<div class="nav-sep"></div><a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i>Sign out</a></nav>
</aside>
<main class="main">
<header class="topbar"><div><span class="eyebrow">Admin portal</span><h1>User Management</h1></div><div class="user-chip"><div class="avatar"><?= e(strtoupper(substr($user["first_name"],0,1).substr($user["last_name"],0,1))) ?></div><div><b><?= e($user['first_name'].' '.$user['last_name']) ?></b><span>Admin</span></div></div></header>
<div class="content"><?php
$pdo=db();
if(isset($_GET['toggle'])){
 $id=(int)$_GET['toggle']; $pdo->prepare("UPDATE users SET status=IF(status='active','inactive','active') WHERE id=?")->execute([$id]); flash('success','User status updated.'); redirect('admin/users.php');
}
$q=trim((string)($_GET['q']??''));
$sql="SELECT u.id,u.first_name,u.last_name,u.email,u.status,u.created_at,r.name role FROM users u JOIN roles r ON r.id=u.role_id";
$params=[];
if($q!==''){ $sql.=" WHERE u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR r.name LIKE ?"; $x="%$q%";$params=[$x,$x,$x,$x];}
$sql.=" ORDER BY u.created_at DESC"; $s=$pdo->prepare($sql);$s->execute($params);$rows=$s->fetchAll();
$roles=$pdo->query("SELECT id,name FROM roles ORDER BY id")->fetchAll();
?>
<div class="section-head"><div><span class="eyebrow">People</span><h2>Users</h2></div><a class="btn btn-primary" href="user_form.php"><i class="fa-solid fa-user-plus"></i>Add User</a></div>
<section class="card card-pad">
<form class="actions" method="get"><input class="input" style="max-width:360px" name="q" placeholder="Search name, email or role" value="<?=e($q)?>"><button class="btn btn-soft">Search</button></form><br>
<div class="table-wrap"><table class="table"><thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Action</th></tr></thead><tbody>
<?php foreach($rows as $r):?><tr><td><b><?=e($r['first_name'].' '.$r['last_name'])?></b></td><td><?=e($r['email'])?></td><td><span class="badge"><?=e(ucfirst($r['role']))?></span></td><td><span class="badge <?=$r['status']==='active'?'green':'gray'?>"><?=e(ucfirst($r['status']))?></span></td><td class="actions"><a class="btn btn-soft" href="user_form.php?id=<?=$r['id']?>">Edit</a><a class="btn btn-dark" data-confirm="Change this user's status?" href="?toggle=<?=$r['id']?>"><?= $r['status']==='active'?'Deactivate':'Activate' ?></a></td></tr><?php endforeach;?>
<?php if(!$rows):?><tr><td colspan="5" class="empty">No users found.</td></tr><?php endif;?></tbody></table></div></section>
</div>
</main></div>
<?php require __DIR__.'/../includes/footer.php'; ?>
