<?php
declare(strict_types=1);
require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/../includes/auth.php';
require_role('admin');
$user=current_user(); $pdo=db();

if($_SERVER['REQUEST_METHOD']==='POST'){
    verify_csrf();
    $sectionId=(int)($_POST['section_id']??0);
    $courseId=(int)($_POST['course_id']??0);
    $role=post('role');
    $userIds=array_unique(array_map('intval', $_POST['user_ids']??[]));

    if(!$sectionId || !$courseId || !in_array($role,['teacher','student'],true) || !$userIds){
        flash('error','Select a section, course, role, and at least one section member.');
        redirect('admin/course_enrollments.php');
    }

    $courseStmt=$pdo->prepare('SELECT c.id,c.title,c.course_code,c.section_id,s.name section_name,s.school_year FROM courses c LEFT JOIN sections s ON s.id=c.section_id WHERE c.id=? LIMIT 1');
    $courseStmt->execute([$courseId]); $course=$courseStmt->fetch();
    if(!$course || (int)$course['section_id']!==$sectionId){
        flash('error','The selected course does not belong to the selected section.');
        redirect('admin/course_enrollments.php');
    }

    $member=$pdo->prepare('SELECT sm.user_id FROM section_members sm JOIN users u ON u.id=sm.user_id WHERE sm.section_id=? AND sm.user_id=? AND sm.role=? AND sm.status="active" AND u.status="active" LIMIT 1');
    $insert=$pdo->prepare('INSERT INTO course_enrollments(course_id,user_id,role,status,invited_by) VALUES(?,?,?,"pending",?) ON DUPLICATE KEY UPDATE status=IF(status="declined","pending",status), invited_by=IF(status="accepted",invited_by,VALUES(invited_by)), invited_at=IF(status="accepted",invited_at, NOW()), responded_at=IF(status="accepted",responded_at,NULL)');
    $notice=$pdo->prepare('INSERT INTO notifications(user_id,title,message,type) VALUES(?,?,?,"course_invitation")');
    $count=0; $skipped=0;
    foreach($userIds as $uid){
        $member->execute([$sectionId,$uid,$role]);
        if(!$member->fetchColumn()){ $skipped++; continue; }
        $insert->execute([$courseId,$uid,$role,$user['id']]);
        if($insert->rowCount()>0){
            $notice->execute([$uid,'New course invitation','You have been invited to '.$course['title'].' ('.$course['course_code'].') for section '.$course['section_name'].' · '.$course['school_year'].'. Open Course Invitations to respond.']);
            $count++;
        }
    }
    $message=$count.' course invitation(s) sent.';
    if($skipped) $message.=' '.$skipped.' selected user(s) were skipped because they are not active members of this section.';
    flash('success',$message);
    redirect('admin/course_enrollments.php?section_id='.$sectionId);
}

$sections=$pdo->query('SELECT id,name,school_year FROM sections ORDER BY school_year DESC,name')->fetchAll();
$sectionId=(int)($_GET['section_id']??($sections[0]['id']??0));

$courseStmt=$pdo->prepare('SELECT c.id,c.title,c.course_code,c.status,c.section_id,s.name section_name,s.school_year,sub.name subject_name FROM courses c LEFT JOIN sections s ON s.id=c.section_id LEFT JOIN subjects sub ON sub.id=c.subject_id WHERE c.section_id=? ORDER BY c.title');
$courseStmt->execute([$sectionId]); $sectionCourses=$courseStmt->fetchAll();

$memberStmt=$pdo->prepare('SELECT sm.user_id,sm.role,u.first_name,u.last_name,u.email FROM section_members sm JOIN users u ON u.id=sm.user_id WHERE sm.section_id=? AND sm.status="active" AND u.status="active" ORDER BY sm.role,u.last_name,u.first_name');
$memberStmt->execute([$sectionId]); $sectionMembers=$memberStmt->fetchAll();
$membersByRole=['teacher'=>[],'student'=>[]]; foreach($sectionMembers as $m) $membersByRole[$m['role']][]=$m;

$allSectionData=[];
foreach($sections as $sec){
    $sid=(int)$sec['id'];
    $cs=$pdo->prepare('SELECT id,title,course_code,status FROM courses WHERE section_id=? ORDER BY title'); $cs->execute([$sid]);
    $ms=$pdo->prepare('SELECT sm.user_id,sm.role,u.first_name,u.last_name,u.email FROM section_members sm JOIN users u ON u.id=sm.user_id WHERE sm.section_id=? AND sm.status="active" AND u.status="active" ORDER BY sm.role,u.last_name,u.first_name'); $ms->execute([$sid]);
    $data=['courses'=>$cs->fetchAll(),'teacher'=>[],'student'=>[]];
    foreach($ms->fetchAll() as $m) $data[$m['role']][]=$m;
    $allSectionData[$sid]=$data;
}

$roleFilter=in_array($_GET['role']??'', ['teacher','student'],true)?$_GET['role']:'';
$sql='SELECT ce.*,c.title,c.course_code,s.name section_name,s.school_year,CONCAT(u.first_name," ",u.last_name) user_name,u.email,CONCAT(a.first_name," ",a.last_name) admin_name FROM course_enrollments ce JOIN courses c ON c.id=ce.course_id LEFT JOIN sections s ON s.id=c.section_id JOIN users u ON u.id=ce.user_id JOIN users a ON a.id=ce.invited_by';
$params=[]; $where=[];
if($sectionId){$where[]='c.section_id=?';$params[]=$sectionId;}
if($roleFilter){$where[]='ce.role=?';$params[]=$roleFilter;}
if($where)$sql.=' WHERE '.implode(' AND ',$where); $sql.=' ORDER BY s.school_year DESC,s.name,c.title,ce.invited_at DESC';
$s=$pdo->prepare($sql);$s->execute($params);$rows=$s->fetchAll();

$page_title='Course Enrollment'; require __DIR__.'/../includes/header.php';
?>
<div class="app"><aside class="sidebar"><div class="brand"><div class="brand-mark"><i class="fa-solid fa-graduation-cap"></i></div><div><strong>ITayo</strong>
<span>Admin</span></div></div><nav class="nav">
<a href="dashboard.php"><i class="fa-solid fa-grid-2"></i>Dashboard</a><a href="users.php"><i class="fa-solid fa-users"></i>Users</a><a href="courses.php"><i class="fa-solid fa-book-open"></i>Courses</a><a href="course_enrollments.php" class="active"><i class="fa-solid fa-user-plus"></i>Course Enrollment</a><a href="subjects.php"><i class="fa-solid fa-book"></i>Subjects</a><a href="sections.php"><i class="fa-solid fa-layer-group"></i>Sections</a><a href="section_members.php"><i class="fa-solid fa-users-rectangle"></i>Section Members</a><a href="../google_account.php"><i class="fa-brands fa-google"></i>Google Account</a>
<div class="nav-sep"></div><a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i>Sign out</a></nav></aside>
<main class="main"><header class="topbar"><div><span class="eyebrow">Admin portal</span><h1>Course Enrollment</h1></div><div class="user-chip"><div class="avatar"><?=e(strtoupper(substr($user['first_name'],0,1).substr($user['last_name'],0,1)))?></div><div><b><?=e($user['first_name'].' '.$user['last_name'])?></b><span>Admin</span></div></div></header><div class="content">
<section class="card card-pad"><div class="section-head"><div><span class="eyebrow">Organized enrollment</span><h2>Assign by Section</h2><p class="muted">Choose the academic section first. Only courses and section members belonging to that section can be assigned.</p></div><a class="btn btn-soft" href="section_members.php?section_id=<?=$sectionId?>"><i class="fa-solid fa-users-rectangle"></i>Manage Section Roster</a></div>
<form method="get" class="form-grid"><div class="form-group"><label>1. Section</label><select class="select" name="section_id" onchange="this.form.submit()"><option value="">Select section</option><?php foreach($sections as $sec):?><option value="<?=$sec['id']?>" <?=$sectionId===(int)$sec['id']?'selected':''?>><?=e($sec['name'].' · '.$sec['school_year'])?></option><?php endforeach;?></select></div></form></section><br>
<section class="card card-pad"><div class="section-head"><div><span class="eyebrow">Send invitation</span><h3>Enroll Students / Assign Teacher</h3></div><span class="badge purple"><?=count($sectionMembers)?> section member<?=count($sectionMembers)===1?'':'s'?></span></div>
<?php if(!$sectionId):?><div class="notice"><i class="fa-solid fa-circle-info"></i>Select a section above to continue.</div><?php elseif(!$sectionCourses):?><div class="notice"><i class="fa-solid fa-circle-info"></i>No courses are attached to this section yet. Create or edit a course and select this section.</div><?php elseif(!$sectionMembers):?><div class="notice"><i class="fa-solid fa-circle-info"></i>This section has no teacher/student members yet. Add them in Section Members first.</div><?php else:?>
<form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="section_id" value="<?=$sectionId?>"><div class="form-grid"><div class="form-group"><label>2. Course</label><select class="select" name="course_id" id="courseSelect" required><option value="">Select course</option><?php foreach($sectionCourses as $c):?><option value="<?=$c['id']?>"><?=e($c['title'].' · '.$c['course_code'])?><?= $c['status']!=='published'?' · '.ucfirst($c['status']):'' ?></option><?php endforeach;?></select></div><div class="form-group"><label>3. Assign as</label><select class="select" name="role" id="enrollRole" required><option value="">Select role</option><option value="teacher">Teacher</option><option value="student">Student</option></select></div><div class="form-group full"><label>4. Section Members</label><select class="select" name="user_ids[]" id="enrollUsers" multiple size="7" required><option value="">Select a role first</option></select><small class="muted">Only active members of the selected section appear here. You can select multiple students.</small></div></div><div class="form-actions"><button class="btn btn-primary" type="submit"><i class="fa-solid fa-paper-plane"></i>Send Course Invitation</button></div></form><?php endif;?></section><br>
<section class="card card-pad"><div class="section-head"><div><span class="eyebrow">Section history</span><h3>Invitations for <?=e($sections[array_search($sectionId,array_column($sections,'id'))]['name']??'Selected Section')?></h3></div><form><input type="hidden" name="section_id" value="<?=$sectionId?>"><select class="select" name="role" onchange="this.form.submit()"><option value="">All roles</option><option value="teacher" <?=$roleFilter==='teacher'?'selected':''?>>Teachers</option><option value="student" <?=$roleFilter==='student'?'selected':''?>>Students</option></select></form></div><div class="table-wrap"><table class="table"><thead><tr><th>Course</th><th>Section</th><th>User</th><th>Role</th><th>Status</th><th>Invited</th></tr></thead><tbody><?php if(!$rows):?><tr><td colspan="6" class="empty">No course invitations for this section yet.</td></tr><?php else:foreach($rows as $r):?><tr><td><b><?=e($r['title'])?></b><br><small class="muted"><?=e($r['course_code'])?></small></td><td><?=e($r['section_name'].' · '.$r['school_year'])?></td><td><b><?=e($r['user_name'])?></b><br><small class="muted"><?=e($r['email'])?></small></td><td><span class="badge"><?=e(ucfirst($r['role']))?></span></td><td><span class="badge <?=$r['status']==='accepted'?'green':($r['status']==='declined'?'gray':'purple')?>"><?=e(ucfirst($r['status']))?></span></td><td><?=e(date('M d, Y h:i A',strtotime($r['invited_at'])))?></td></tr><?php endforeach;endif;?></tbody></table></div></section>
</div></main></div>
<script>
const sectionData=<?=json_encode($allSectionData)?>; const roleSelect=document.getElementById('enrollRole'); const userSelect=document.getElementById('enrollUsers');
roleSelect?.addEventListener('change',()=>{userSelect.innerHTML='';const members=(sectionData[<?=json_encode((string)$sectionId)?>]||sectionData[<?=json_encode($sectionId)?>]||{})[roleSelect.value]||[];members.forEach(u=>{const o=document.createElement('option');o.value=u.user_id;o.textContent=u.last_name+', '+u.first_name+' · '+u.email;userSelect.appendChild(o)});if(!members.length){const o=document.createElement('option');o.textContent='No active members in this section for this role';o.disabled=true;userSelect.appendChild(o)}});
</script><?php require __DIR__.'/../includes/footer.php'; ?>
