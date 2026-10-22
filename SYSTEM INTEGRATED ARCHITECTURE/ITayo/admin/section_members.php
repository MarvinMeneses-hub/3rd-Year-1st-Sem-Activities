<?php
declare(strict_types=1);
require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/../includes/auth.php';
require_role('admin');
$user=current_user(); $pdo=db();

if($_SERVER['REQUEST_METHOD']==='POST'){
    verify_csrf();
    $sectionId=(int)($_POST['section_id']??0);
    $role=post('role');
    $userIds=array_unique(array_map('intval', $_POST['user_ids']??[]));
    if(!$sectionId || !in_array($role,['teacher','student'],true) || !$userIds){
        flash('error','Select a section, role, and at least one user.');
        redirect('admin/section_members.php');
    }
    $section=$pdo->prepare('SELECT id,name,school_year FROM sections WHERE id=? LIMIT 1');
    $section->execute([$sectionId]);
    if(!$section->fetch()){ flash('error','Section not found.'); redirect('admin/section_members.php'); }
    $roleStmt=$pdo->prepare('SELECT id FROM roles WHERE name=? LIMIT 1');
    $roleStmt->execute([$role]); $roleId=(int)$roleStmt->fetchColumn();
    $check=$pdo->prepare('SELECT id FROM users WHERE id=? AND role_id=? AND status="active" LIMIT 1');
    $insert=$pdo->prepare('INSERT INTO section_members(section_id,user_id,role,status) VALUES(?,?,?,"active") ON DUPLICATE KEY UPDATE status="active"');
    $count=0;
    foreach($userIds as $uid){
        $check->execute([$uid,$roleId]);
        if(!$check->fetchColumn()) continue;
        $insert->execute([$sectionId,$uid,$role]);
        if($insert->rowCount()>0) $count++;
    }
    flash('success',$count.' user(s) added to the section.');
    redirect('admin/section_members.php?section_id='.$sectionId);
}

$sections=$pdo->query('SELECT s.id,s.name,s.school_year,COUNT(DISTINCT CASE WHEN sm.role="student" AND sm.status="active" THEN sm.user_id END) student_count,COUNT(DISTINCT CASE WHEN sm.role="teacher" AND sm.status="active" THEN sm.user_id END) teacher_count FROM sections s LEFT JOIN section_members sm ON sm.section_id=s.id GROUP BY s.id ORDER BY s.school_year DESC,s.name')->fetchAll();
$sectionId=(int)($_GET['section_id']??($sections[0]['id']??0));
$teachers=$pdo->prepare('SELECT u.id,u.first_name,u.last_name,u.email FROM users u JOIN roles r ON r.id=u.role_id WHERE r.name="teacher" AND u.status="active" AND NOT EXISTS(SELECT 1 FROM section_members sm WHERE sm.section_id=? AND sm.user_id=u.id AND sm.role="teacher" AND sm.status="active") ORDER BY u.last_name,u.first_name');
$teachers->execute([$sectionId]); $availableTeachers=$teachers->fetchAll();
$students=$pdo->prepare('SELECT u.id,u.first_name,u.last_name,u.email FROM users u JOIN roles r ON r.id=u.role_id WHERE r.name="student" AND u.status="active" AND NOT EXISTS(SELECT 1 FROM section_members sm WHERE sm.section_id=? AND sm.user_id=u.id AND sm.role="student" AND sm.status="active") ORDER BY u.last_name,u.first_name');
$students->execute([$sectionId]); $availableStudents=$students->fetchAll();
$members=$pdo->prepare('SELECT sm.id,sm.role,sm.status,sm.joined_at,CONCAT(u.first_name," ",u.last_name) user_name,u.email,s.name section_name,s.school_year FROM section_members sm JOIN users u ON u.id=sm.user_id JOIN sections s ON s.id=sm.section_id WHERE sm.section_id=? ORDER BY sm.role,u.last_name,u.first_name');
$members->execute([$sectionId]); $members=$members->fetchAll();
$page_title='Section Members'; require __DIR__.'/../includes/header.php';
?>
<div class="app"><aside class="sidebar"><div class="brand"><div class="brand-mark"><i class="fa-solid fa-graduation-cap"></i></div><div><strong>ITayo</strong><span>Admin</span></div></div><nav class="nav">
<a href="dashboard.php"><i class="fa-solid fa-grid-2"></i>Dashboard</a><a href="users.php"><i class="fa-solid fa-users"></i>Users</a><a href="courses.php"><i class="fa-solid fa-book-open"></i>Courses</a><a href="course_enrollments.php"><i class="fa-solid fa-user-plus"></i>Course Enrollment</a><a href="subjects.php"><i class="fa-solid fa-book"></i>Subjects</a><a href="sections.php" class="active"><i class="fa-solid fa-layer-group"></i>Sections</a><a href="section_members.php" class="active"><i class="fa-solid fa-users-rectangle"></i>Section Members</a><a href="../google_account.php"><i class="fa-brands fa-google"></i>Google Account</a>
<div class="nav-sep"></div><a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i>Sign out</a></nav></aside>
<main class="main"><header class="topbar"><div><span class="eyebrow">Admin portal</span><h1>Section Members</h1></div><div class="user-chip"><div class="avatar"><?=e(strtoupper(substr($user['first_name'],0,1).substr($user['last_name'],0,1)))?></div><div><b><?=e($user['first_name'].' '.$user['last_name'])?></b><span>Admin</span></div></div></header><div class="content">
<section class="card card-pad"><div class="section-head"><div><span class="eyebrow">Academic organization</span><h2>Build a Section Roster</h2><p class="muted">Add teachers and students to a section first. Course assignment will then only show members of the selected section.</p></div><a class="btn btn-soft" href="sections.php"><i class="fa-solid fa-layer-group"></i>Manage Sections</a></div>
<form method="get" class="form-grid"><div class="form-group"><label>Section</label><select class="select" name="section_id" onchange="this.form.submit()"><option value="">Select section</option><?php foreach($sections as $s):?><option value="<?=$s['id']?>" <?=$sectionId===$s['id']?'selected':''?>><?=e($s['name'].' · '.$s['school_year'])?></option><?php endforeach;?></select></div></form></section><br>
<section class="card card-pad"><div class="section-head"><div><span class="eyebrow">Add members</span><h3>Section Assignment</h3></div></div><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="section_id" value="<?=$sectionId?>"><div class="form-grid"><div class="form-group"><label>Role</label><select class="select" name="role" id="memberRole" required><option value="">Select role</option><option value="teacher">Teacher</option><option value="student">Student</option></select></div><div class="form-group"><label>Users</label><select class="select" name="user_ids[]" id="memberUsers" multiple size="7" required><option value="">Select a role first</option></select><small class="muted">Only users not already active in this section are shown.</small></div></div><div class="form-actions"><button class="btn btn-primary"><i class="fa-solid fa-user-plus"></i>Add to Section</button></div></form></section><br>
<section class="card card-pad"><div class="section-head"><div><span class="eyebrow">Roster</span><h3><?=e($sections[array_search($sectionId,array_column($sections,'id'))]['name']??'Selected Section')?></h3></div></div><div class="table-wrap"><table class="table"><thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Joined</th></tr></thead><tbody><?php if(!$members):?><tr><td colspan="5" class="empty">No members in this section yet.</td></tr><?php else:foreach($members as $m):?><tr><td><b><?=e($m['user_name'])?></b></td><td><?=e($m['email'])?></td><td><span class="badge"><?=e(ucfirst($m['role']))?></span></td><td><span class="badge green">Active</span></td><td><?=e(date('M d, Y',strtotime($m['joined_at'])))?></td></tr><?php endforeach;endif;?></tbody></table></div></section>
</div></main></div>
<script>const memberUsers={teacher:<?=json_encode($availableTeachers)?>,student:<?=json_encode($availableStudents)?>};const memberRole=document.getElementById('memberRole'),memberSelect=document.getElementById('memberUsers');memberRole.addEventListener('change',()=>{memberSelect.innerHTML='';(memberUsers[memberRole.value]||[]).forEach(u=>{const o=document.createElement('option');o.value=u.id;o.textContent=u.last_name+', '+u.first_name+' · '+u.email;memberSelect.appendChild(o)});if(!memberSelect.options.length){const o=document.createElement('option');o.textContent='No available users';o.disabled=true;memberSelect.appendChild(o)}});</script>
<?php require __DIR__.'/../includes/footer.php'; ?>
