<?php
declare(strict_types=1);
require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/../includes/auth.php';
require_role('admin');
$user=current_user();
$page_title='Course Form';
require __DIR__.'/../includes/header.php';
?>
<div class="app">
<aside class="sidebar">
<div class="brand"><div class="brand-mark"><i class="fa-solid fa-graduation-cap"></i></div><div><strong>EdubridgeAcademy</strong><span>Admin</span></div></div>
<nav class="nav"><a href="dashboard.php" class=""><i class="fa-solid fa-grid-2"></i>Dashboard</a>
<a href="users.php" class=""><i class="fa-solid fa-users"></i>Users</a>
<a href="courses.php" class="active"><i class="fa-solid fa-book-open"></i>Courses</a>
<a href="subjects.php" class=""><i class="fa-solid fa-book"></i>Subjects</a>
<a href="sections.php" class=""><i class="fa-solid fa-layer-group"></i>Sections</a><a href="../google_account.php"><i class="fa-brands fa-google"></i>Google Account</a>
<div class="nav-sep"></div><a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i>Sign out</a></nav>
</aside>
<main class="main">
<header class="topbar"><div><span class="eyebrow">Admin portal</span><h1>Course Form</h1></div><div class="user-chip"><div class="avatar"><?= e(strtoupper(substr($user["first_name"],0,1).substr($user["last_name"],0,1))) ?></div><div><b><?= e($user['first_name'].' '.$user['last_name']) ?></b><span>Admin</span></div></div></header>
<div class="content"><?php
$pdo=db();$id=(int)($_GET['id']??0);$row=['title'=>'','course_code'=>'','description'=>'','subject_id'=>'','section_id'=>'','status'=>'draft'];
if($id){$s=$pdo->prepare("SELECT * FROM courses WHERE id=?");$s->execute([$id]);$row=$s->fetch()?:$row;}
$subjects=$pdo->query("SELECT * FROM subjects ORDER BY name")->fetchAll();$sections=$pdo->query("SELECT * FROM sections ORDER BY name")->fetchAll();
if($_SERVER['REQUEST_METHOD']==='POST'){verify_csrf();$title=post('title');$code=post('course_code');$desc=post('description');$sub=(int)($_POST['subject_id']??0);$sec=(int)($_POST['section_id']??0);$status=post('status','draft');if($id){$pdo->prepare("UPDATE courses SET title=?,course_code=?,description=?,subject_id=?,section_id=?,status=? WHERE id=?")->execute([$title,$code,$desc,$sub?:null,$sec?:null,$status,$id]);flash('success','Course updated.');}else{$pdo->prepare("INSERT INTO courses(title,course_code,description,subject_id,section_id,status) VALUES(?,?,?,?,?,?)")->execute([$title,$code,$desc,$sub?:null,$sec?:null,$status]);flash('success','Course created.');}redirect('admin/courses.php');}
?>
<section class="card card-pad"><div class="section-head"><h2><?=$id?'Edit Course':'Create Course'?></h2><a class="btn btn-soft" href="courses.php">Back</a></div>
<form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><div class="form-grid"><div class="form-group"><label>Course title</label><input class="input" name="title" required value="<?=e($row['title'])?>"></div><div class="form-group"><label>Course code</label><input class="input" name="course_code" required value="<?=e($row['course_code'])?>"></div><div class="form-group"><label>Subject</label><select class="select" name="subject_id"><option value="">None</option><?php foreach($subjects as $s):?><option value="<?=$s['id']?>" <?=((int)$row['subject_id']==$s['id']?'selected':'')?>><?=e($s['name'])?></option><?php endforeach;?></select></div><div class="form-group"><label>Section</label><select class="select" name="section_id"><option value="">None</option><?php foreach($sections as $s):?><option value="<?=$s['id']?>" <?=((int)$row['section_id']==$s['id']?'selected':'')?>><?=e($s['name'])?></option><?php endforeach;?></select></div><div class="form-group"><label>Status</label><select class="select" name="status"><?php foreach(['draft','published','archived'] as $x):?><option <?=$row['status']===$x?'selected':''?>><?=$x?></option><?php endforeach;?></select></div><div class="form-group full"><label>Description</label><textarea class="textarea" name="description"><?=e($row['description'])?></textarea></div></div><div class="form-actions"><button class="btn btn-primary">Save Course</button></div></form></section>
</div>
</main></div>
<?php require __DIR__.'/../includes/footer.php'; ?>
