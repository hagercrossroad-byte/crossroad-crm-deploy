<?php
/* ══ CrossRoad CRM — API موحّد. كل التحقق من الصلاحيات هنا (Backend) ══ */
require_once __DIR__.'/db.php';
db_install();

ini_set('session.cookie_httponly','1');
ini_set('session.use_strict_mode','1');
session_start();
header('Content-Type: application/json; charset=utf-8');

function inp(): array { static $i=null; if($i===null){ $raw=file_get_contents('php://input'); $i=json_decode($raw,true)?:[]; $i=array_merge($_POST,$i);} return $i; }
function out($data=[], $ok=true, $code=200){ http_response_code($code); echo json_encode(['ok'=>$ok]+$data, JSON_UNESCAPED_UNICODE); exit; }
function fail($msg, $code=400){ out(['error'=>$msg], false, $code); }
function ip(){ return $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? ''; }
function ua(){ return substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 490); }
function now(){ return date('Y-m-d H:i:s'); }

function parse_ua($ua){
  $os='غير معروف'; $br='غير معروف';
  if(preg_match('/Windows/i',$ua)) $os='Windows';
  elseif(preg_match('/Mac OS X|Macintosh/i',$ua)) $os='macOS';
  elseif(preg_match('/Android/i',$ua)) $os='Android';
  elseif(preg_match('/iPhone|iPad|iOS/i',$ua)) $os='iOS';
  elseif(preg_match('/Linux/i',$ua)) $os='Linux';
  if(preg_match('/Edg\//i',$ua)) $br='Edge';
  elseif(preg_match('/OPR|Opera/i',$ua)) $br='Opera';
  elseif(preg_match('/Chrome/i',$ua)) $br='Chrome';
  elseif(preg_match('/Safari/i',$ua)) $br='Safari';
  elseif(preg_match('/Firefox/i',$ua)) $br='Firefox';
  return [$os,$br];
}
function is_mobile_ua($ua){ return (bool)preg_match('/Mobile|Android.*Mobile|iPhone|iPod|Opera Mini|IEMobile/i',$ua) && !preg_match('/iPad|Tablet/i',$ua); }
function is_tablet_ua($ua){ return (bool)preg_match('/iPad|Tablet|Android(?!.*Mobile)/i',$ua); }

function setting($k, $def=''){ $r=db()->prepare("SELECT v FROM settings WHERE k=?"); $r->execute([$k]); $v=$r->fetch(); return $v?$v['v']:$def; }
function set_setting($k,$v){ db()->prepare("INSERT INTO settings(k,v) VALUES(?,?) ON DUPLICATE KEY UPDATE v=VALUES(v)")->execute([$k,$v]); }

function log_it($action,$entity='',$eid=null,$old='',$new='',$uid=null,$uname=null){
  $u = $GLOBALS['ME'] ?? null;
  db()->prepare("INSERT INTO activity_log(user_id,user_name,action,entity,entity_id,old_v,new_v,ip) VALUES(?,?,?,?,?,?,?,?)")
    ->execute([$uid ?? ($u['id']??null), $uname ?? ($u['name']??'النظام'), $action, mb_substr($entity,0,190), $eid, mb_substr((string)$old,0,290), mb_substr((string)$new,0,290), ip()]);
}
function notify($uid,$title,$body='',$page=''){
  db()->prepare("INSERT INTO notifications(user_id,title,body,page) VALUES(?,?,?,?)")->execute([$uid,$title,$body,$page]);
}

/* ── جلسة المستخدم الحالي + الصلاحيات ── */
$ME = null;
function load_me(){
  global $ME;
  if(isset($_SESSION['uid'], $_SESSION['tv'], $_SESSION['last'])){
    if(time() - $_SESSION['last'] > SESSION_TIMEOUT_MIN*60){ session_destroy(); return; }
    $_SESSION['last'] = time();
    $s = db()->prepare("SELECT u.*, r.name role_name, r.label role_label, r.perms role_perms FROM users u JOIN roles r ON r.id=u.role_id WHERE u.id=? AND u.active=1 AND u.deleted_at IS NULL");
    $s->execute([$_SESSION['uid']]);
    $u = $s->fetch();
    if($u && (int)$u['token_version'] === (int)$_SESSION['tv']){
      $u['perms'] = array_values(array_unique(array_merge(json_decode($u['role_perms'],true)?:[], json_decode($u['extra_perms']??'[]',true)?:[])));
      unset($u['pw_hash']);
      $ME = $u;
    } else session_destroy();
  }
}
function can($p){ global $ME; return $ME && in_array($p, $ME['perms']); }
function need($p){ if(!can($p)) fail('غير مصرح لك بهذه العملية ('.$p.')', 403); }
function need_login(){ global $ME; if(!$ME) fail('انتهت الجلسة، سجّل الدخول من جديد', 401); }
function csrf_check(){
  $t = $_SERVER['HTTP_X_CSRF'] ?? '';
  if(!isset($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $t)) fail('طلب غير صالح (CSRF)', 403);
}
load_me();

$action = $_GET['a'] ?? inp()['a'] ?? '';
$MUTATING = !in_array($action, ['login','me','ping']) && ($_SERVER['REQUEST_METHOD']==='POST');
if($ME && $MUTATING) csrf_check();

/* ═══════════ AUTH ═══════════ */
if($action==='ping') out(['pong'=>1]);

if($action==='login'){
  $i = inp();
  $un = trim($i['username']??''); $pw = $i['password']??'';
  $device_uid = substr(preg_replace('/[^a-zA-Z0-9_-]/','', $i['device_uid']??''),0,70);
  $uAgent = ua(); [$os,$br] = parse_ua($uAgent);
  $deny = function($reason) use($un,$uAgent){ 
    db()->prepare("INSERT INTO login_attempts(username,ok,reason,ip,ua) VALUES(?,0,?,?,?)")->execute([$un,$reason,ip(),$uAgent]);
    log_it('فشل تسجيل دخول', $un, null, '', $reason, null, $un?:'مجهول');
    fail($reason, 403);
  };
  if(!$un || !$pw) $deny('اكتب اسم المستخدم وكلمة المرور');

  $s = db()->prepare("SELECT u.*, r.perms role_perms, r.label role_label, r.name role_name FROM users u JOIN roles r ON r.id=u.role_id WHERE u.username=? AND u.deleted_at IS NULL");
  $s->execute([$un]); $u = $s->fetch();
  if(!$u || !password_verify($pw, $u['pw_hash'])) $deny('اسم المستخدم أو كلمة المرور غير صحيحة');
  if(!$u['active']) $deny('الحساب معطّل، راجع الإدارة');

  /* منع الموبايل/التابلت من الـ Backend */
  if(!$u['device_exempt']){
    if(setting('block_mobile')==='1' && is_mobile_ua($uAgent)) $deny('الدخول إلى النظام متاح من أجهزة الكمبيوتر فقط');
    if(setting('block_tablet')==='1' && is_tablet_ua($uAgent)) $deny('الدخول من التابلت غير مسموح');
  }

  /* الأجهزة المعتمدة */
  if($device_uid){
    $ds = db()->prepare("SELECT * FROM devices WHERE user_id=? AND device_uid=?");
    $ds->execute([$u['id'],$device_uid]); $dev = $ds->fetch();
    if(!$dev){
      db()->prepare("INSERT INTO devices(user_id,device_uid,name,ua,os,browser,approved,last_used) VALUES(?,?,?,?,?,?,?,NOW())")
        ->execute([$u['id'],$device_uid,$br.' على '.$os,$uAgent,$os,$br, ($u['device_exempt']||setting('device_approval')!=='1')?1:0]);
      log_it('تسجيل جهاز جديد', $br.' على '.$os, null,'','',$u['id'],$u['name']);
      $ds->execute([$u['id'],$device_uid]); $dev=$ds->fetch();
    } else {
      db()->prepare("UPDATE devices SET last_used=NOW(), ua=?, os=?, browser=? WHERE id=?")->execute([$uAgent,$os,$br,$dev['id']]);
    }
    if(setting('device_approval')==='1' && !$u['device_exempt'] && !$dev['approved']){
      // إشعار للإدارة
      foreach(db()->query("SELECT u2.id FROM users u2 JOIN roles r2 ON r2.id=u2.role_id WHERE r2.name='super_admin' AND u2.active=1 AND u2.deleted_at IS NULL") as $adm)
        notify($adm['id'],'جهاز جديد بانتظار الاعتماد',$u['name'].' حاول الدخول من '.$br.' على '.$os,'devices');
      $deny('الجهاز ده لسه معتمدش من الإدارة. اطلب من المدير اعتماده من قسم الأجهزة');
    }
  } elseif(setting('device_approval')==='1' && !$u['device_exempt']) $deny('تعذر التعرف على الجهاز');

  session_regenerate_id(true);
  $_SESSION['uid'] = $u['id'];
  $_SESSION['tv'] = $u['token_version'];
  $_SESSION['last'] = time();
  $_SESSION['csrf'] = bin2hex(random_bytes(24));
  db()->prepare("UPDATE users SET last_login=NOW() WHERE id=?")->execute([$u['id']]);
  db()->prepare("INSERT INTO login_attempts(username,ok,reason,ip,ua) VALUES(?,1,'',?,?)")->execute([$un,ip(),$uAgent]);
  log_it('تسجيل دخول','', null,'','',$u['id'],$u['name']);

  /* Remember Me */
  if(!empty($i['remember'])){
    $tok = bin2hex(random_bytes(32));
    db()->prepare("INSERT INTO remember_tokens(user_id,token_hash,token_version,expires) VALUES(?,?,?,DATE_ADD(NOW(),INTERVAL 30 DAY))")
      ->execute([$u['id'], hash('sha256',$tok), $u['token_version']]);
    setcookie('crm_remember', $u['id'].':'.$tok, time()+86400*30, '/', '', isset($_SERVER['HTTPS']), true);
  }
  load_me();
  out(['user'=>me_payload(), 'csrf'=>$_SESSION['csrf']]);
}

if($action==='auto_login' && !$ME && isset($_COOKIE['crm_remember'])){
  [$uid,$tok] = array_pad(explode(':',$_COOKIE['crm_remember'],2),2,'');
  $s = db()->prepare("SELECT rt.*, u.token_version utv, u.active, u.deleted_at FROM remember_tokens rt JOIN users u ON u.id=rt.user_id WHERE rt.user_id=? AND rt.token_hash=? AND rt.expires>NOW()");
  $s->execute([(int)$uid, hash('sha256',$tok)]);
  $r = $s->fetch();
  if($r && $r['active'] && !$r['deleted_at'] && (int)$r['token_version']===(int)$r['utv']){
    session_regenerate_id(true);
    $_SESSION['uid']=(int)$uid; $_SESSION['tv']=(int)$r['utv']; $_SESSION['last']=time(); $_SESSION['csrf']=bin2hex(random_bytes(24));
    load_me();
    out(['user'=>me_payload(),'csrf'=>$_SESSION['csrf']]);
  }
  out(['user'=>null]);
}

function me_payload(){
  global $ME; if(!$ME) return null;
  return ['id'=>(int)$ME['id'],'username'=>$ME['username'],'name'=>$ME['name'],'title'=>$ME['title'],'dept'=>$ME['dept'],
    'role'=>$ME['role_name'],'role_label'=>$ME['role_label'],'perms'=>$ME['perms'],'must_change_pw'=>(int)$ME['must_change_pw']];
}

if($action==='me'){ out(['user'=>me_payload(), 'csrf'=>$_SESSION['csrf']??null]); }

if($action==='logout'){ need_login(); log_it('تسجيل خروج'); setcookie('crm_remember','',time()-3600,'/'); session_destroy(); out(); }

if($action==='logout_all'){
  need_login();
  $target = (int)(inp()['user_id'] ?? $ME['id']);
  if($target !== (int)$ME['id']) need('manage_users');
  db()->prepare("UPDATE users SET token_version=token_version+1 WHERE id=?")->execute([$target]);
  db()->prepare("DELETE FROM remember_tokens WHERE user_id=?")->execute([$target]);
  log_it('تسجيل خروج من جميع الأجهزة', 'user#'.$target, $target);
  out();
}

if($action==='change_my_pw'){
  need_login(); $i=inp();
  $new = $i['new']??'';
  if(strlen($new)<8) fail('كلمة المرور لازم تكون 8 أحرف على الأقل');
  if($new !== ($i['confirm']??'')) fail('تأكيد كلمة المرور غير مطابق');
  if(!(int)$ME['must_change_pw']){
    $s=db()->prepare("SELECT pw_hash FROM users WHERE id=?"); $s->execute([$ME['id']]);
    if(!password_verify($i['old']??'', $s->fetch()['pw_hash'])) fail('كلمة المرور الحالية غير صحيحة');
  }
  db()->prepare("UPDATE users SET pw_hash=?, must_change_pw=0 WHERE id=?")->execute([password_hash($new,PASSWORD_DEFAULT), $ME['id']]);
  log_it('تغيير كلمة المرور','حسابه الشخصي',$ME['id']);
  out();
}

need_login(); /* ─── كل اللي بعد كده محتاج جلسة سارية ─── */

/* ═══════════ USERS & ROLES ═══════════ */
if($action==='users.list'){
  need('manage_users');
  $rows = db()->query("SELECT u.id,u.username,u.name,u.email,u.phone,u.title,u.dept,".(can('company_finance')?'u.salary,':'')."u.role_id,u.extra_perms,u.active,u.device_exempt,u.must_change_pw,u.last_login,u.created_at,r.label role_label,r.name role_name
    FROM users u JOIN roles r ON r.id=u.role_id WHERE u.deleted_at IS NULL ORDER BY u.id")->fetchAll();
  foreach($rows as &$r) $r['extra_perms']=json_decode($r['extra_perms']??'[]',true)?:[];
  out(['users'=>$rows]);
}
if($action==='users.save'){
  need('manage_users'); $i=inp();
  $id=(int)($i['id']??0);
  $un=trim($i['username']??''); $nm=trim($i['name']??'');
  if(!$un||!$nm) fail('اسم المستخدم والاسم مطلوبين');
  $role_id=(int)($i['role_id']??0);
  if(!db()->query("SELECT id FROM roles WHERE id=".$role_id)->fetch()) fail('اختر دورًا صحيحًا');
  $extra = json_encode(array_values(array_intersect((array)($i['extra_perms']??[]), ALL_PERMS)));
  if(!can('manage_permissions')) $extra = null; // بدون صلاحية إدارة الصلاحيات ميقدرش يدي صلاحيات إضافية
  $dup = db()->prepare("SELECT id FROM users WHERE username=? AND id!=?"); $dup->execute([$un,$id]);
  if($dup->fetch()) fail('اسم المستخدم مستخدم بالفعل');
  if($id){
    $old = db()->prepare("SELECT * FROM users WHERE id=? AND deleted_at IS NULL"); $old->execute([$id]); $old=$old->fetch();
    if(!$old) fail('المستخدم غير موجود');
    if((int)$old['role_id']!==$role_id){
      if($old['role_id']==role_id_of('super_admin') && count_supers()<=1 && $role_id!=role_id_of('super_admin')) fail('لا يمكن تغيير دور آخر Super Admin في النظام');
      log_it('تغيير الدور',$nm,$id, role_label($old['role_id']), role_label($role_id));
    }
    db()->prepare("UPDATE users SET username=?,name=?,email=?,phone=?,title=?,dept=?,role_id=?".($extra!==null?",extra_perms=?":"").(can('company_finance')&&isset($i['salary'])?",salary=".((float)$i['salary']?:'NULL'):"")." WHERE id=?")
      ->execute(array_merge([$un,$nm,trim($i['email']??''),trim($i['phone']??''),trim($i['title']??''),trim($i['dept']??''),$role_id], $extra!==null?[$extra]:[], [$id]));
    log_it('تعديل مستخدم',$nm,$id);
  } else {
    $pw = $i['password']??'';
    if(strlen($pw)<8) fail('كلمة مرور المستخدم الجديد لازم 8 أحرف على الأقل');
    db()->prepare("INSERT INTO users(username,name,email,phone,title,dept,role_id,extra_perms,pw_hash,must_change_pw) VALUES(?,?,?,?,?,?,?,?,?,1)")
      ->execute([$un,$nm,trim($i['email']??''),trim($i['phone']??''),trim($i['title']??''),trim($i['dept']??''),$role_id,$extra??'[]',password_hash($pw,PASSWORD_DEFAULT)]);
    $id = (int)db()->lastInsertId();
    if(can('company_finance') && isset($i['salary']) && (float)$i['salary']>0)
      db()->prepare("UPDATE users SET salary=? WHERE id=?")->execute([(float)$i['salary'],$id]);
    log_it('إضافة مستخدم',$nm,$id);
  }
  out(['id'=>$id]);
}
function role_id_of($name){ $s=db()->prepare("SELECT id FROM roles WHERE name=?"); $s->execute([$name]); return (int)($s->fetch()['id']??0); }
function role_label($id){ $s=db()->prepare("SELECT label FROM roles WHERE id=?"); $s->execute([$id]); return $s->fetch()['label']??''; }
function count_supers(){ return (int)db()->query("SELECT COUNT(*) c FROM users u JOIN roles r ON r.id=u.role_id WHERE r.name='super_admin' AND u.active=1 AND u.deleted_at IS NULL")->fetch()['c']; }

if($action==='users.set_pw'){
  need('reset_user_password'); $i=inp();
  $id=(int)$i['user_id']; $pw=$i['password']??'';
  if(strlen($pw)<8) fail('كلمة المرور 8 أحرف على الأقل');
  if($pw !== ($i['confirm']??'')) fail('التأكيد غير مطابق');
  $u=db()->prepare("SELECT name FROM users WHERE id=? AND deleted_at IS NULL"); $u->execute([$id]); $u=$u->fetch();
  if(!$u) fail('المستخدم غير موجود');
  db()->prepare("UPDATE users SET pw_hash=?, must_change_pw=?, token_version=token_version+1 WHERE id=?")
    ->execute([password_hash($pw,PASSWORD_DEFAULT), empty($i['force_change'])?0:1, $id]);
  db()->prepare("DELETE FROM remember_tokens WHERE user_id=?")->execute([$id]);
  log_it('إعادة تعيين كلمة مرور',$u['name'],$id); // بدون تسجيل القيمة
  notify($id,'تم تغيير كلمة المرور','قام المدير بتعيين كلمة مرور جديدة لحسابك');
  out();
}
if($action==='users.toggle'){
  need('manage_users'); $id=(int)inp()['user_id'];
  if($id===(int)$ME['id']) fail('لا يمكنك تعطيل حسابك بنفسك');
  $u=db()->prepare("SELECT u.*,r.name rn FROM users u JOIN roles r ON r.id=u.role_id WHERE u.id=? AND u.deleted_at IS NULL"); $u->execute([$id]); $u=$u->fetch();
  if(!$u) fail('غير موجود');
  if($u['rn']==='super_admin' && $u['active'] && count_supers()<=1) fail('لا يمكن تعطيل آخر Super Admin');
  db()->prepare("UPDATE users SET active=1-active, token_version=token_version+1 WHERE id=?")->execute([$id]);
  log_it($u['active']?'تعطيل مستخدم':'تفعيل مستخدم',$u['name'],$id);
  out();
}
if($action==='users.delete'){
  need('manage_users'); $id=(int)inp()['user_id'];
  if($id===(int)$ME['id']) fail('لا يمكنك حذف حسابك بنفسك');
  $u=db()->prepare("SELECT u.*,r.name rn FROM users u JOIN roles r ON r.id=u.role_id WHERE u.id=? AND u.deleted_at IS NULL"); $u->execute([$id]); $u=$u->fetch();
  if(!$u) fail('غير موجود');
  if($u['rn']==='super_admin' && count_supers()<=1) fail('لا يمكن حذف آخر Super Admin في النظام');
  if((inp()['confirm_name']??'') !== $u['name']) fail('اكتب اسم المستخدم "'.$u['name'].'" للتأكيد');
  db()->prepare("UPDATE users SET deleted_at=NOW(), active=0, token_version=token_version+1 WHERE id=?")->execute([$id]);
  log_it('حذف مستخدم (سلة)',$u['name'],$id);
  out();
}
if($action==='roles.list'){
  need_login();
  $rows = db()->query("SELECT * FROM roles ORDER BY id")->fetchAll();
  foreach($rows as &$r){ $r['perms']=json_decode($r['perms'],true); $r['users']=(int)db()->query("SELECT COUNT(*) c FROM users WHERE role_id=".$r['id']." AND deleted_at IS NULL")->fetch()['c']; }
  out(['roles'=>$rows,'all_perms'=>ALL_PERMS]);
}
if($action==='roles.save'){
  need('manage_roles'); $i=inp();
  $id=(int)($i['id']??0); $label=trim($i['label']??''); if(!$label) fail('اكتب اسم الدور');
  $perms=json_encode(array_values(array_intersect((array)($i['perms']??[]),ALL_PERMS)));
  if($id){
    $r=db()->prepare("SELECT * FROM roles WHERE id=?"); $r->execute([$id]); $r=$r->fetch();
    if(!$r) fail('غير موجود');
    if($r['name']==='super_admin' && json_decode($perms,true)!==ALL_PERMS && count(json_decode($perms,true))<count(ALL_PERMS)) fail('لا يمكن تقليل صلاحيات دور Super Admin');
    db()->prepare("UPDATE roles SET label=?, perms=? WHERE id=?")->execute([$label,$perms,$id]);
    log_it('تعديل دور وصلاحيات',$label,$id);
  } else {
    db()->prepare("INSERT INTO roles(name,label,perms) VALUES(?,?,?)")->execute(['r'.time(),$label,$perms]);
    $id=(int)db()->lastInsertId(); log_it('إضافة دور',$label,$id);
  }
  out(['id'=>$id]);
}
if($action==='roles.delete'){
  need('manage_roles'); $id=(int)inp()['id'];
  $r=db()->prepare("SELECT * FROM roles WHERE id=?"); $r->execute([$id]); $r=$r->fetch();
  if(!$r) fail('غير موجود');
  if($r['name']==='super_admin') fail('لا يمكن حذف دور Super Admin');
  $c=(int)db()->query("SELECT COUNT(*) c FROM users WHERE role_id=$id AND deleted_at IS NULL")->fetch()['c'];
  if($c) fail("لا يمكن الحذف: في $c مستخدم مرتبط بالدور ده. انقلهم لدور تاني الأول");
  db()->prepare("DELETE FROM roles WHERE id=?")->execute([$id]);
  log_it('حذف دور',$r['label'],$id); out();
}

/* ═══════════ DEVICES ═══════════ */
if($action==='devices.list'){
  need('manage_devices');
  out(['devices'=>db()->query("SELECT d.*, u.name user_name FROM devices d JOIN users u ON u.id=d.user_id ORDER BY d.approved, d.last_used DESC")->fetchAll()]);
}
if($action==='devices.approve'){
  need('manage_devices'); $id=(int)inp()['id'];
  $d=db()->prepare("SELECT d.*,u.name un,u.id uid FROM devices d JOIN users u ON u.id=d.user_id WHERE d.id=?"); $d->execute([$id]); $d=$d->fetch();
  if(!$d) fail('غير موجود');
  db()->prepare("UPDATE devices SET approved=1 WHERE id=?")->execute([$id]);
  log_it('اعتماد جهاز',$d['un'].' — '.$d['name'],$id);
  notify($d['uid'],'تم اعتماد جهازك','تقدر تسجل الدخول من '.$d['name'].' دلوقتي');
  out();
}
if($action==='devices.revoke'){
  need('manage_devices'); $id=(int)inp()['id'];
  db()->prepare("UPDATE devices SET approved=0 WHERE id=?")->execute([$id]);
  log_it('تعطيل جهاز','device#'.$id,$id); out();
}
if($action==='devices.delete'){
  need('manage_devices'); $id=(int)inp()['id'];
  db()->prepare("DELETE FROM devices WHERE id=?")->execute([$id]);
  log_it('حذف جهاز','device#'.$id,$id); out();
}

/* ═══════════ SETTINGS & LISTS ═══════════ */
if($action==='settings.get'){
  $keys=['block_mobile','block_tablet','device_approval','attendance_ips','work_start','late_after','wa_verify_token'];
  $o=[]; foreach($keys as $k) $o[$k]=setting($k);
  if(!can('manage_settings')) $o=['work_start'=>$o['work_start']]; // الموظف مايشوفش الإعدادات الأمنية
  out(['settings'=>$o]);
}
if($action==='settings.set'){
  need('manage_settings'); $i=inp();
  foreach(['block_mobile','block_tablet','device_approval','attendance_ips','work_start','late_after'] as $k)
    if(isset($i[$k])) set_setting($k, trim((string)$i[$k]));
  log_it('تعديل إعدادات النظام','Settings');
  out();
}
if($action==='lists.get'){
  $k=preg_replace('/[^a-z_]/','',inp()['kind']??$_GET['kind']??'');
  $s=db()->prepare("SELECT * FROM lists WHERE kind=? ORDER BY sort,id");
  $s->execute([$k]);
  out(['items'=>$s->fetchAll()]);
}
if($action==='lists.all'){
  $rows=db()->query("SELECT * FROM lists ORDER BY kind,sort,id")->fetchAll();
  $o=[]; foreach($rows as $r) $o[$r['kind']][]=$r;
  out(['lists'=>$o]);
}
if($action==='lists.save'){
  need('manage_settings'); $i=inp();
  $id=(int)($i['id']??0); $kind=preg_replace('/[^a-z_]/','',$i['kind']??''); $label=trim($i['label']??'');
  if(!$kind||!$label) fail('بيانات ناقصة');
  if($id){ db()->prepare("UPDATE lists SET label=?, color=? WHERE id=?")->execute([$label,$i['color']??'#2563eb',$id]); }
  else { db()->prepare("INSERT INTO lists(kind,label,color,sort) VALUES(?,?,?,99)")->execute([$kind,$label,$i['color']??'#2563eb']); $id=(int)db()->lastInsertId(); }
  out(['id'=>$id]);
}
if($action==='lists.delete'){
  need('manage_settings'); $id=(int)inp()['id'];
  $l=db()->prepare("SELECT * FROM lists WHERE id=?"); $l->execute([$id]); $l=$l->fetch();
  if(!$l) fail('غير موجود');
  if($l['kind']==='client_status'){
    $c=(int)db()->query("SELECT COUNT(*) c FROM clients WHERE status_id=$id AND deleted_at IS NULL")->fetch()['c'];
    if($c) fail("لا يمكن الحذف: في $c عميل بالحالة دي");
  }
  db()->prepare("DELETE FROM lists WHERE id=?")->execute([$id]);
  log_it('حذف عنصر قائمة',$l['kind'].': '.$l['label'],$id); out();
}

/* ═══════════ LEAVES ═══════════ */
if($action==='leaves.my'){
  $s=db()->prepare("SELECT * FROM leaves WHERE user_id=? AND deleted_at IS NULL ORDER BY id DESC LIMIT 100");
  $s->execute([$ME['id']]); out(['leaves'=>$s->fetchAll()]);
}
if($action==='leaves.submit'){
  $i=inp();
  $kind = $i['kind']==='permission'?'permission':'leave';
  $df=$i['date_from']??''; if(!$df) fail('حدد التاريخ');
  db()->prepare("INSERT INTO leaves(user_id,kind,date_from,date_to,time_from,time_to,reason) VALUES(?,?,?,?,?,?,?)")
    ->execute([$ME['id'],$kind,$df,$i['date_to']?:null,trim($i['time_from']??''),trim($i['time_to']??''),trim($i['reason']??'')]);
  $id=(int)db()->lastInsertId();
  db()->prepare("INSERT INTO leave_history(leave_id,from_status,to_status,by_user,note) VALUES(?,?,?,?,?)")->execute([$id,'','pending',$ME['name'],'إنشاء الطلب']);
  log_it($kind==='leave'?'طلب إجازة':'طلب إذن', $ME['name'].' — '.$df, $id);
  foreach(db()->query("SELECT DISTINCT u.id FROM users u JOIN roles r ON r.id=u.role_id WHERE u.active=1 AND u.deleted_at IS NULL AND (JSON_SEARCH(r.perms,'one','approve_leave') IS NOT NULL)") as $m)
    if((int)$m['id']!==(int)$ME['id']) notify($m['id'],'طلب '.($kind==='leave'?'إجازة':'إذن').' جديد','من '.$ME['name'].' بتاريخ '.$df,'leaves');
  out(['id'=>$id]);
}
if($action==='leaves.cancel'){
  $id=(int)inp()['id'];
  $l=db()->prepare("SELECT * FROM leaves WHERE id=? AND user_id=? AND deleted_at IS NULL"); $l->execute([$id,$ME['id']]); $l=$l->fetch();
  if(!$l) fail('غير موجود');
  if($l['status']!=='pending') fail('لا يمكن إلغاء طلب تم البت فيه');
  db()->prepare("UPDATE leaves SET status='cancelled' WHERE id=?")->execute([$id]);
  db()->prepare("INSERT INTO leave_history(leave_id,from_status,to_status,by_user,note) VALUES(?,?,?,?,?)")->execute([$id,'pending','cancelled',$ME['name'],'إلغاء بواسطة صاحب الطلب']);
  log_it('إلغاء طلب إجازة/إذن','#'.$id,$id,'pending','cancelled'); out();
}
if($action==='leaves.pending'){
  if(!can('approve_leave') && !can('reject_leave')) fail('غير مصرح',403);
  out(['leaves'=>db()->query("SELECT l.*, u.name user_name, u.dept FROM leaves l JOIN users u ON u.id=l.user_id WHERE l.deleted_at IS NULL ORDER BY (l.status='pending') DESC, l.id DESC LIMIT 200")->fetchAll()]);
}
if($action==='leaves.decide'){
  $i=inp(); $id=(int)$i['id']; $dec=$i['decision']==='approve'?'approved':'rejected';
  need($dec==='approved'?'approve_leave':'reject_leave');
  $l=db()->prepare("SELECT l.*,u.name un FROM leaves l JOIN users u ON u.id=l.user_id WHERE l.id=? AND l.deleted_at IS NULL"); $l->execute([$id]); $l=$l->fetch();
  if(!$l) fail('الطلب غير موجود');
  if((int)$l['user_id']===(int)$ME['id']) fail('لا يمكنك اعتماد طلبك بنفسك');
  if($l['status']!=='pending') fail('تم البت في الطلب بالفعل ('.$l['status'].') — لا يمكن تكرار القرار');
  $note=trim($i['note']??'');
  db()->prepare("UPDATE leaves SET status=?, decided_by=?, decided_at=NOW(), decision_note=? WHERE id=? AND status='pending'")->execute([$dec,$ME['id'],$note,$id]);
  db()->prepare("INSERT INTO leave_history(leave_id,from_status,to_status,by_user,note) VALUES(?,?,?,?,?)")->execute([$id,'pending',$dec,$ME['name'],$note]);
  log_it($dec==='approved'?'الموافقة على طلب':'رفض طلب', $l['un'].' — '.$l['date_from'], $id, 'pending', $dec);
  notify($l['user_id'], $dec==='approved'?'✅ تمت الموافقة على طلبك':'❌ تم رفض طلبك', ($l['kind']==='leave'?'إجازة ':'إذن ').$l['date_from'].($note?' — '.$note:''), 'leaves');
  out();
}
if($action==='leaves.history'){
  $id=(int)($_GET['id']??inp()['id']);
  $l=db()->prepare("SELECT user_id FROM leaves WHERE id=?"); $l->execute([$id]); $l=$l->fetch();
  if(!$l) fail('غير موجود');
  if((int)$l['user_id']!==(int)$ME['id'] && !can('approve_leave')) fail('غير مصرح',403);
  $s=db()->prepare("SELECT * FROM leave_history WHERE leave_id=? ORDER BY id"); $s->execute([$id]);
  out(['history'=>$s->fetchAll()]);
}

/* ═══════════ NOTIFICATIONS ═══════════ */
if($action==='notifs.list'){
  $s=db()->prepare("SELECT * FROM notifications WHERE user_id=? ORDER BY id DESC LIMIT 50"); $s->execute([$ME['id']]);
  $c=db()->prepare("SELECT COUNT(*) c FROM notifications WHERE user_id=? AND is_read=0"); $c->execute([$ME['id']]);
  out(['notifs'=>$s->fetchAll(),'unread'=>(int)$c->fetch()['c']]);
}
if($action==='notifs.read_all'){ db()->prepare("UPDATE notifications SET is_read=1 WHERE user_id=?")->execute([$ME['id']]); out(); }

/* ═══════════ OWNER FINANCE ═══════════ */
if(str_starts_with($action,'fin.')) need('owner_finance');
if($action==='fin.list'){
  $i=inp()+$_GET;
  $w="deleted_at IS NULL"; $p=[];
  if(!empty($i['from'])){ $w.=" AND tx_date>=?"; $p[]=$i['from']; }
  if(!empty($i['to'])){ $w.=" AND tx_date<=?"; $p[]=$i['to']; }
  if(!empty($i['kind'])){ $w.=" AND kind=?"; $p[]=$i['kind']; }
  $s=db()->prepare("SELECT f.*, c.name_ar client_name FROM finance f LEFT JOIN clients c ON c.id=f.client_id WHERE $w ORDER BY tx_date DESC, id DESC LIMIT 500");
  $s->execute($p);
  out(['rows'=>$s->fetchAll()]);
}
if($action==='fin.stats'){
  $g = fn($sql)=>(float)db()->query($sql)->fetch()['s'];
  $inc=$g("SELECT COALESCE(SUM(amount),0) s FROM finance WHERE kind IN('income','opening') AND deleted_at IS NULL");
  $exp=$g("SELECT COALESCE(SUM(amount),0) s FROM finance WHERE kind='expense' AND deleted_at IS NULL");
  $m=date('Y-m'); $pm=date('Y-m',strtotime('-1 month'));
  $mi=$g("SELECT COALESCE(SUM(amount),0) s FROM finance WHERE kind='income' AND tx_date LIKE '$m%' AND deleted_at IS NULL");
  $mx=$g("SELECT COALESCE(SUM(amount),0) s FROM finance WHERE kind='expense' AND tx_date LIKE '$m%' AND deleted_at IS NULL");
  $pi=$g("SELECT COALESCE(SUM(amount),0) s FROM finance WHERE kind='income' AND tx_date LIKE '$pm%' AND deleted_at IS NULL");
  $px=$g("SELECT COALESCE(SUM(amount),0) s FROM finance WHERE kind='expense' AND tx_date LIKE '$pm%' AND deleted_at IS NULL");
  $months=[]; 
  for($k=5;$k>=0;$k--){ $mm=date('Y-m',strtotime("-$k month"));
    $months[]=['m'=>$mm,
      'inc'=>$g("SELECT COALESCE(SUM(amount),0) s FROM finance WHERE kind='income' AND tx_date LIKE '$mm%' AND deleted_at IS NULL"),
      'exp'=>$g("SELECT COALESCE(SUM(amount),0) s FROM finance WHERE kind='expense' AND tx_date LIKE '$mm%' AND deleted_at IS NULL")]; }
  $cats=db()->query("SELECT category, SUM(amount) s FROM finance WHERE kind='expense' AND deleted_at IS NULL GROUP BY category ORDER BY s DESC LIMIT 10")->fetchAll();
  out(['balance'=>$inc-$exp,'income'=>$inc,'expense'=>$exp,'net'=>$inc-$exp,
    'month_income'=>$mi,'month_expense'=>$mx,'prev_income'=>$pi,'prev_expense'=>$px,'months'=>$months,'by_cat'=>$cats]);
}
if($action==='fin.save'){
  $i=inp(); $id=(int)($i['id']??0);
  $kind=in_array($i['kind']??'',['opening','income','expense'])?$i['kind']:'expense';
  $amount=(float)($i['amount']??0); if($amount<=0) fail('اكتب مبلغًا صحيحًا');
  $date=$i['tx_date']??date('Y-m-d');
  $vals=[$kind,trim($i['category']??''),$amount,trim($i['currency']??'EGP'),trim($i['method']??''),$date,trim($i['descr']??''),trim($i['note']??''),(int)($i['client_id']??0)?:null];
  if($id){
    $old=db()->prepare("SELECT * FROM finance WHERE id=? AND deleted_at IS NULL"); $old->execute([$id]); $old=$old->fetch();
    if(!$old) fail('الحركة غير موجودة');
    db()->prepare("UPDATE finance SET kind=?,category=?,amount=?,currency=?,method=?,tx_date=?,descr=?,note=?,client_id=? WHERE id=?")->execute(array_merge($vals,[$id]));
    log_it('تعديل حركة مالية',$old['descr']?:('#'.$id),$id, $old['kind'].' '.$old['amount'], $kind.' '.$amount);
  } else {
    db()->prepare("INSERT INTO finance(kind,category,amount,currency,method,tx_date,descr,note,client_id,created_by) VALUES(?,?,?,?,?,?,?,?,?,?)")->execute(array_merge($vals,[$ME['id']]));
    $id=(int)db()->lastInsertId();
    log_it($kind==='income'?'إضافة إيراد':($kind==='expense'?'إضافة مصروف':'رصيد افتتاحي'), trim($i['descr']??'').' '.$amount, $id);
  }
  out(['id'=>$id]);
}
if($action==='fin.delete'){
  $id=(int)inp()['id'];
  $f=db()->prepare("SELECT * FROM finance WHERE id=? AND deleted_at IS NULL"); $f->execute([$id]); $f=$f->fetch();
  if(!$f) fail('غير موجودة');
  need('delete_financials');
  db()->prepare("UPDATE finance SET deleted_at=NOW() WHERE id=?")->execute([$id]);
  log_it('حذف حركة مالية (سلة)',$f['descr']?:('#'.$id),$id,$f['kind'].' '.$f['amount']);
  out();
}

/* ═══════════ ATTENDANCE ═══════════ */
function att_ip_ok(){
  $list = array_filter(array_map('trim', explode(',', setting('attendance_ips'))));
  if(!$list) return true;
  $my = ip();
  foreach($list as $x){ if($x!=='' && str_starts_with($my,$x)) return true; }
  return false;
}
if($action==='att.today'){
  $s=db()->prepare("SELECT * FROM attendance WHERE user_id=? AND day=CURDATE()"); $s->execute([$ME['id']]);
  out(['today'=>$s->fetch()?:null,'ip_ok'=>att_ip_ok()]);
}
if($action==='att.checkin'){
  if(!att_ip_ok()) fail('تسجيل الحضور متاح من شبكة الشركة فقط');
  $ex=db()->prepare("SELECT id FROM attendance WHERE user_id=? AND day=CURDATE()"); $ex->execute([$ME['id']]);
  if($ex->fetch()) fail('سجلت حضورك النهارده بالفعل');
  [$os,$br]=parse_ua(ua());
  $late = date('H:i') > setting('late_after','10:15');
  db()->prepare("INSERT INTO attendance(user_id,day,in_time,status,ip,os,browser,device_uid) VALUES(?,CURDATE(),NOW(),?,?,?,?,?)")
    ->execute([$ME['id'], $late?'late':'present', ip(), $os, $br, substr(inp()['device_uid']??'',0,70)]);
  log_it('تسجيل حضور', $late?'متأخر':'في الميعاد');
  out(['status'=>$late?'late':'present','time'=>date('H:i')]);
}
if($action==='att.checkout'){
  $s=db()->prepare("SELECT * FROM attendance WHERE user_id=? AND day=CURDATE()"); $s->execute([$ME['id']]); $a=$s->fetch();
  if(!$a) fail('سجّل حضورك الأول');
  if($a['out_time']) fail('سجلت انصرافك بالفعل');
  db()->prepare("UPDATE attendance SET out_time=NOW() WHERE id=?")->execute([$a['id']]);
  log_it('تسجيل انصراف','');
  out(['time'=>date('H:i')]);
}
if($action==='att.report'){
  need('manage_attendance'); $i=inp()+$_GET;
  $from=$i['from']??date('Y-m-d'); $to=$i['to']??date('Y-m-d');
  $s=db()->prepare("SELECT a.*, u.name user_name, u.dept, TIMESTAMPDIFF(MINUTE,a.in_time,COALESCE(a.out_time,NOW())) mins
    FROM attendance a JOIN users u ON u.id=a.user_id WHERE a.day BETWEEN ? AND ? ORDER BY a.day DESC, u.name");
  $s->execute([$from,$to]);
  $emps=db()->query("SELECT id,name,dept FROM users WHERE active=1 AND deleted_at IS NULL")->fetchAll();
  out(['rows'=>$s->fetchAll(),'employees'=>$emps]);
}
if($action==='att.edit'){
  need('edit_attendance'); $i=inp();
  $reason=trim($i['reason']??''); if(!$reason) fail('اكتب سبب التعديل');
  $id=(int)($i['id']??0);
  if($id){
    $old=db()->prepare("SELECT a.*,u.name un FROM attendance a JOIN users u ON u.id=a.user_id WHERE a.id=?"); $old->execute([$id]); $old=$old->fetch();
    if(!$old) fail('غير موجود');
    db()->prepare("UPDATE attendance SET in_time=?, out_time=?, status=?, edited_by=?, edit_reason=? WHERE id=?")
      ->execute([$i['in_time']?:null,$i['out_time']?:null,$i['status']?:'present',$ME['id'],$reason,$id]);
    log_it('تعديل سجل حضور',$old['un'].' — '.$old['day'],$id, $old['status'].' '.$old['in_time'], ($i['status']??'').' '.($i['in_time']??''));
  } else {
    // إضافة سجل يدوي (غياب/إجازة/من المنزل)
    db()->prepare("INSERT INTO attendance(user_id,day,in_time,out_time,status,edited_by,edit_reason) VALUES(?,?,?,?,?,?,?)
ON DUPLICATE KEY UPDATE
in_time = VALUES(in_time),
out_time = VALUES(out_time),
status = VALUES(status),
edited_by = VALUES(edited_by),
edit_reason = VALUES(edit_reason)      ->execute([(int)$i['user_id'],$i['day']?:date('Y-m-d'),$i['in_time']?:null,$i['out_time']?:null,$i['status']?:'absent',$ME['id'],$reason]);
    log_it('إضافة سجل حضور يدوي','user#'.$i['user_id'].' — '.($i['day']??''));
  }
  out();
}

/* ═══════════ CLIENTS ═══════════ */
function client_strip($c){
  if(!can('view_financials')) unset($c['contract_value'],$c['currency'],$c['pay_method'],$c['pay_cycle']);
  if(!can('manage_users') && !can('view_financials')) unset($c['notes_internal']);
  return $c;
}
function client_visible($c){
  if(can('view_all_clients')) return true;
  if(!can('view_assigned_clients')) return false;
  global $ME;
  $s=db()->prepare("SELECT 1 FROM client_assignees WHERE client_id=? AND user_id=?"); $s->execute([$c['id'],$ME['id']]);
  return (bool)$s->fetch();
}
if($action==='clients.list'){
  if(!can('view_all_clients') && !can('view_assigned_clients')) fail('غير مصرح',403);
  if(can('view_all_clients')){
    $rows=db()->query("SELECT * FROM clients WHERE deleted_at IS NULL ORDER BY updated_at DESC")->fetchAll();
  } else {
    $s=db()->prepare("SELECT c.* FROM clients c JOIN client_assignees ca ON ca.client_id=c.id WHERE ca.user_id=? AND c.deleted_at IS NULL ORDER BY c.updated_at DESC");
    $s->execute([$ME['id']]); $rows=$s->fetchAll();
  }
  $asg=db()->query("SELECT * FROM client_assignees")->fetchAll();
  $map=[]; foreach($asg as $a) $map[$a['client_id']][]=(int)$a['user_id'];
  foreach($rows as &$r){ $r['assignees']=$map[$r['id']]??[]; $r['services']=json_decode($r['services']??'[]',true)?:[]; $r['depts']=json_decode($r['depts']??'[]',true)?:[]; $r=client_strip($r); }
  out(['clients'=>$rows]);
}
if($action==='clients.save'){
  $i=inp(); $id=(int)($i['id']??0);
  need($id?'edit_client':'create_client');
  $nm=trim($i['name_ar']??''); if(!$nm) fail('اكتب اسم العميل');
  $assignees=array_map('intval',(array)($i['assignees']??[]));
  $primary=(int)($i['primary_assignee']??0);
  if($primary && !in_array($primary,$assignees)) $assignees[]=$primary;
  $fin = can('edit_financials');
  $fields=['name_ar'=>$nm,'name_en'=>trim($i['name_en']??''),'phone'=>trim($i['phone']??''),'email'=>trim($i['email']??''),
    'country'=>trim($i['country']??''),'city'=>trim($i['city']??''),'industry'=>trim($i['industry']??''),
    'services'=>json_encode((array)($i['services']??[]),JSON_UNESCAPED_UNICODE),'depts'=>json_encode((array)($i['depts']??[]),JSON_UNESCAPED_UNICODE),
    'primary_assignee'=>$primary?:null,'leader_id'=>(int)($i['leader_id']??0)?:null,
    'start_date'=>$i['start_date']?:null,'end_date'=>$i['end_date']?:null,'renew_date'=>$i['renew_date']?:null,
    'status_id'=>(int)($i['status_id']??0)?:null,
    'notes_visible'=>trim($i['notes_visible']??''),
    'files_link'=>trim($i['files_link']??''),'wa_link'=>trim($i['wa_link']??''),'ad_link'=>trim($i['ad_link']??''),'site_link'=>trim($i['site_link']??''),
    'last_contact'=>$i['last_contact']?:null,'next_action'=>trim($i['next_action']??''),'next_action_date'=>$i['next_action_date']?:null];
  if($fin){ $fields+=['contract_value'=>(float)($i['contract_value']??0),'currency'=>trim($i['currency']??'EGP'),'pay_method'=>trim($i['pay_method']??''),'pay_cycle'=>trim($i['pay_cycle']??'monthly')]; }
  if(can('manage_users')||can('edit_financials')) $fields['notes_internal']=trim($i['notes_internal']??'');
  if($id){
    $old=db()->prepare("SELECT * FROM clients WHERE id=? AND deleted_at IS NULL"); $old->execute([$id]); $old=$old->fetch();
    if(!$old) fail('العميل غير موجود');
    if(!client_visible($old)) fail('غير مصرح',403);
    $set=implode(',',array_map(fn($k)=>"$k=?",array_keys($fields)));
    db()->prepare("UPDATE clients SET $set, updated_at=NOW() WHERE id=?")->execute(array_merge(array_values($fields),[$id]));
    if($fin && (float)$old['contract_value']!==(float)($fields['contract_value']??$old['contract_value']))
      log_it('تعديل قيمة العقد',$nm,$id,$old['contract_value'],$fields['contract_value']);
    if($old['status_id']!=$fields['status_id'])
      db()->prepare("INSERT INTO client_history(client_id,kind,from_v,to_v,by_user,note) VALUES(?,?,?,?,?,?)")->execute([$id,'status',list_label($old['status_id']),list_label($fields['status_id']),$ME['name'],'من نموذج التعديل']);
    if($old['primary_assignee']!=$fields['primary_assignee'])
      db()->prepare("INSERT INTO client_history(client_id,kind,from_v,to_v,by_user,note) VALUES(?,?,?,?,?,?)")->execute([$id,'transfer',user_name($old['primary_assignee']),user_name($fields['primary_assignee']),$ME['name'],'نقل من نموذج التعديل']);
    log_it('تعديل عميل',$nm,$id);
  } else {
    $cols=implode(',',array_keys($fields)); $ph=implode(',',array_fill(0,count($fields),'?'));
    db()->prepare("INSERT INTO clients($cols) VALUES($ph)")->execute(array_values($fields));
    $id=(int)db()->lastInsertId();
    log_it('إضافة عميل',$nm,$id);
  }
  db()->prepare("DELETE FROM client_assignees WHERE client_id=?")->execute([$id]);
  $ia=db()->prepare("INSERT IGNORE INTO client_assignees(client_id,user_id) VALUES(?,?)");
  foreach(array_unique($assignees) as $a) if($a) $ia->execute([$id,$a]);
  out(['id'=>$id]);
}
function list_label($id){ if(!$id) return ''; $s=db()->prepare("SELECT label FROM lists WHERE id=?"); $s->execute([$id]); return $s->fetch()['label']??''; }
function user_name($id){ if(!$id) return '—'; $s=db()->prepare("SELECT name FROM users WHERE id=?"); $s->execute([$id]); return $s->fetch()['name']??'—'; }
if($action==='clients.status'){
  $i=inp(); $id=(int)$i['id'];
  $c=db()->prepare("SELECT * FROM clients WHERE id=? AND deleted_at IS NULL"); $c->execute([$id]); $c=$c->fetch();
  if(!$c) fail('غير موجود');
  if(!client_visible($c)) fail('غير مصرح',403);
  $to=(int)$i['status_id'];
  if($to===(int)$c['status_id']) fail('اختر حالة مختلفة');
  db()->prepare("UPDATE clients SET status_id=?, updated_at=NOW() WHERE id=?")->execute([$to,$id]);
  db()->prepare("INSERT INTO client_history(client_id,kind,from_v,to_v,by_user,note) VALUES(?,?,?,?,?,?)")
    ->execute([$id,'status',list_label($c['status_id']),list_label($to),$ME['name'],trim($i['note']??'')]);
  log_it('تغيير حالة عميل',$c['name_ar'],$id,list_label($c['status_id']),list_label($to));
  if($c['primary_assignee'] && (int)$c['primary_assignee']!==(int)$ME['id'])
    notify($c['primary_assignee'],'تغيّرت حالة عميلك '.$c['name_ar'], list_label($c['status_id']).' ← '.list_label($to),'clients');
  out();
}
if($action==='clients.note'){
  $i=inp(); $id=(int)$i['id'];
  $c=db()->prepare("SELECT * FROM clients WHERE id=? AND deleted_at IS NULL"); $c->execute([$id]); $c=$c->fetch();
  if(!$c) fail('غير موجود'); if(!client_visible($c)) fail('غير مصرح',403);
  $txt=trim($i['text']??''); if(!$txt) fail('اكتب الملاحظة');
  db()->prepare("INSERT INTO client_history(client_id,kind,note,by_user) VALUES(?,?,?,?)")->execute([$id,'note',$txt,$ME['name']]);
  db()->prepare("UPDATE clients SET updated_at=NOW(), last_contact=CURDATE() WHERE id=?")->execute([$id]);
  log_it('إضافة ملاحظة',$c['name_ar'],$id,'',mb_substr($txt,0,40)); out();
}
if($action==='clients.history'){
  $id=(int)($_GET['id']??inp()['id']);
  $c=db()->prepare("SELECT * FROM clients WHERE id=?"); $c->execute([$id]); $c=$c->fetch();
  if(!$c) fail('غير موجود'); if(!client_visible($c)) fail('غير مصرح',403);
  $s=db()->prepare("SELECT * FROM client_history WHERE client_id=? ORDER BY id DESC LIMIT 60"); $s->execute([$id]);
  out(['history'=>$s->fetchAll()]);
}
if($action==='clients.delete'){
  need('delete_client'); $id=(int)inp()['id'];
  $c=db()->prepare("SELECT * FROM clients WHERE id=? AND deleted_at IS NULL"); $c->execute([$id]); $c=$c->fetch();
  if(!$c) fail('غير موجود');
  if((inp()['confirm_name']??'')!==$c['name_ar']) fail('اكتب اسم العميل "'.$c['name_ar'].'" للتأكيد');
  db()->prepare("UPDATE clients SET deleted_at=NOW() WHERE id=?")->execute([$id]);
  log_it('حذف عميل (سلة)',$c['name_ar'],$id); out();
}

/* ═══════════ TASKS ═══════════ */
const TASK_STATUSES = ['new','in_progress','waiting_review','revision','completed','cancelled'];
function task_visible($t){
  if(can('view_all_tasks')) return true;
  if(!can('view_assigned_tasks')) return false;
  global $ME;
  $s=db()->prepare("SELECT 1 FROM task_assignees WHERE task_id=? AND user_id=?"); $s->execute([$t['id'],$ME['id']]);
  return (bool)$s->fetch() || (int)$t['created_by']===(int)$ME['id'];
}
function task_strip($t){
  global $ME;
  $mine = in_array((int)$ME['id'], $t['assignees']??[]) || (int)$t['created_by']===(int)$ME['id'];
  if(!can('view_task_value') && !$mine){ $t['fin_value']=null; $t['points']=null; $t['est_hours']=null; }
  if(!can('view_task_value') && !can('view_financials')) $t['fin_value']=null; // القيمة المالية أعلى حساسية: محتاجة صلاحية صريحة
  return $t;
}
if($action==='tasks.board'){
  if(!can('view_all_tasks') && !can('view_assigned_tasks')) fail('غير مصرح',403);
  if(can('view_all_tasks')) $rows=db()->query("SELECT * FROM tasks WHERE deleted_at IS NULL ORDER BY sort,id DESC")->fetchAll();
  else { $s=db()->prepare("SELECT DISTINCT t.* FROM tasks t LEFT JOIN task_assignees ta ON ta.task_id=t.id WHERE t.deleted_at IS NULL AND (ta.user_id=? OR t.created_by=?) ORDER BY t.sort,t.id DESC"); $s->execute([$ME['id'],$ME['id']]); $rows=$s->fetchAll(); }
  $ids=array_column($rows,'id');
  $asg=[]; $chk=[]; $cmt=[]; $fls=[];
  if($ids){
    $in=implode(',',array_map('intval',$ids));
    foreach(db()->query("SELECT * FROM task_assignees WHERE task_id IN($in)") as $a) $asg[$a['task_id']][]=(int)$a['user_id'];
    foreach(db()->query("SELECT task_id, COUNT(*) t, SUM(done) d FROM task_checklist WHERE task_id IN($in) GROUP BY task_id") as $c) $chk[$c['task_id']]=['t'=>(int)$c['t'],'d'=>(int)$c['d']];
    foreach(db()->query("SELECT task_id, COUNT(*) c FROM task_comments WHERE task_id IN($in) GROUP BY task_id") as $c) $cmt[$c['task_id']]=(int)$c['c'];
    foreach(db()->query("SELECT task_id, COUNT(*) c FROM task_files WHERE task_id IN($in) GROUP BY task_id") as $c) $fls[$c['task_id']]=(int)$c['c'];
  }
  foreach($rows as &$r){
    $r['assignees']=$asg[$r['id']]??[];
    $r['checklist']=$chk[$r['id']]??['t'=>0,'d'=>0];
    $r['comments']=$cmt[$r['id']]??0; $r['files']=$fls[$r['id']]??0;
    $r=task_strip($r);
  }
  out(['tasks'=>$rows,'statuses'=>TASK_STATUSES]);
}
if($action==='tasks.get'){
  $id=(int)($_GET['id']??inp()['id']);
  $t=db()->prepare("SELECT * FROM tasks WHERE id=? AND deleted_at IS NULL"); $t->execute([$id]); $t=$t->fetch();
  if(!$t) fail('غير موجودة'); if(!task_visible($t)) fail('غير مصرح',403);
  $a=db()->prepare("SELECT user_id FROM task_assignees WHERE task_id=?"); $a->execute([$id]);
  $t['assignees']=array_map(fn($x)=>(int)$x['user_id'],$a->fetchAll());
  $c=db()->prepare("SELECT * FROM task_checklist WHERE task_id=? ORDER BY id"); $c->execute([$id]);
  $m=db()->prepare("SELECT tc.*, u.name user_name FROM task_comments tc JOIN users u ON u.id=tc.user_id WHERE tc.task_id=? ORDER BY tc.id"); $m->execute([$id]);
  $f=db()->prepare("SELECT tf.*, u.name user_name FROM task_files tf LEFT JOIN users u ON u.id=tf.by_user WHERE tf.task_id=? ORDER BY tf.id"); $f->execute([$id]);
  $t=task_strip($t);
  out(['task'=>$t,'checklist'=>$c->fetchAll(),'comments'=>$m->fetchAll(),'files'=>$f->fetchAll()]);
}
if($action==='tasks.save'){
  $i=inp(); $id=(int)($i['id']??0);
  need($id?'edit_task':'create_task');
  $title=trim($i['title']??''); if(!$title) fail('اكتب اسم المهمة');
  $st=in_array($i['status']??'',TASK_STATUSES)?$i['status']:'new';
  $pri=in_array($i['priority']??'',['low','normal','high','urgent'])?$i['priority']:'normal';
  $assignees=array_map('intval',(array)($i['assignees']??[]));
  $primary=(int)($i['primary_assignee']??0);
  if($primary && !in_array($primary,$assignees)) $assignees[]=$primary;
  if($id){
    $old=db()->prepare("SELECT * FROM tasks WHERE id=? AND deleted_at IS NULL"); $old->execute([$id]); $old=$old->fetch();
    if(!$old) fail('غير موجودة'); if(!task_visible($old)) fail('غير مصرح',403);
    db()->prepare("UPDATE tasks SET title=?,descr=?,client_id=?,dept=?,status=?,priority=?,deadline=?,primary_assignee=?,updated_at=NOW() WHERE id=?")
      ->execute([$title,trim($i['descr']??''),(int)($i['client_id']??0)?:null,trim($i['dept']??''),$st,$pri,$i['deadline']?:null,$primary?:null,$id]);
    log_it('تعديل مهمة',$title,$id);
  } else {
    db()->prepare("INSERT INTO tasks(title,descr,client_id,dept,status,priority,deadline,primary_assignee,created_by) VALUES(?,?,?,?,?,?,?,?,?)")
      ->execute([$title,trim($i['descr']??''),(int)($i['client_id']??0)?:null,trim($i['dept']??''),$st,$pri,$i['deadline']?:null,$primary?:null,$ME['id']]);
    $id=(int)db()->lastInsertId();
    log_it('إضافة مهمة',$title,$id);
  }
  db()->prepare("DELETE FROM task_assignees WHERE task_id=?")->execute([$id]);
  $ia=db()->prepare("INSERT IGNORE INTO task_assignees(task_id,user_id) VALUES(?,?)");
  foreach(array_unique($assignees) as $a) if($a){ $ia->execute([$id,$a]); if($a!=(int)$ME['id']) notify($a,'مهمة جديدة مسندة إليك',$title,'tasks'); }
  out(['id'=>$id]);
}
if($action==='tasks.move'){
  $i=inp(); $id=(int)$i['id'];
  $t=db()->prepare("SELECT * FROM tasks WHERE id=? AND deleted_at IS NULL"); $t->execute([$id]); $t=$t->fetch();
  if(!$t) fail('غير موجودة'); if(!task_visible($t)) fail('غير مصرح',403);
  $st=in_array($i['status']??'',TASK_STATUSES)?$i['status']:$t['status'];
  db()->prepare("UPDATE tasks SET status=?, sort=?, updated_at=NOW() WHERE id=?")->execute([$st,(int)($i['sort']??0),$id]);
  if($st!==$t['status']) log_it('نقل مهمة',$t['title'],$id,$t['status'],$st);
  out();
}
if($action==='tasks.value'){
  need('edit_task_value'); $i=inp(); $id=(int)$i['id'];
  $t=db()->prepare("SELECT * FROM tasks WHERE id=? AND deleted_at IS NULL"); $t->execute([$id]); $t=$t->fetch();
  if(!$t) fail('غير موجودة');
  $fv=$i['fin_value']!==''&&$i['fin_value']!==null?(float)$i['fin_value']:null;
  $pt=$i['points']!==''&&$i['points']!==null?(int)$i['points']:null;
  $eh=$i['est_hours']!==''&&$i['est_hours']!==null?(float)$i['est_hours']:null;
  db()->prepare("UPDATE tasks SET fin_value=?, points=?, est_hours=?, updated_at=NOW() WHERE id=?")->execute([$fv,$pt,$eh,$id]);
  log_it('تعديل قيمة مهمة',$t['title'],$id, "مالي:{$t['fin_value']} نقاط:{$t['points']} ساعات:{$t['est_hours']}", "مالي:$fv نقاط:$pt ساعات:$eh");
  out();
}
if($action==='tasks.check.save'){
  $i=inp(); $tid=(int)$i['task_id'];
  $t=db()->prepare("SELECT * FROM tasks WHERE id=? AND deleted_at IS NULL"); $t->execute([$tid]); $t=$t->fetch();
  if(!$t||!task_visible($t)) fail('غير مصرح',403);
  if(!empty($i['toggle_id'])){ db()->prepare("UPDATE task_checklist SET done=1-done WHERE id=? AND task_id=?")->execute([(int)$i['toggle_id'],$tid]); out(); }
  if(!empty($i['del_id'])){ db()->prepare("DELETE FROM task_checklist WHERE id=? AND task_id=?")->execute([(int)$i['del_id'],$tid]); out(); }
  $txt=trim($i['txt']??''); if(!$txt) fail('اكتب البند');
  db()->prepare("INSERT INTO task_checklist(task_id,txt) VALUES(?,?)")->execute([$tid,$txt]); out();
}
if($action==='tasks.comment'){
  $i=inp(); $tid=(int)$i['task_id'];
  $t=db()->prepare("SELECT * FROM tasks WHERE id=? AND deleted_at IS NULL"); $t->execute([$tid]); $t=$t->fetch();
  if(!$t||!task_visible($t)) fail('غير مصرح',403);
  $txt=trim($i['txt']??''); if(!$txt) fail('اكتب التعليق');
  db()->prepare("INSERT INTO task_comments(task_id,user_id,txt) VALUES(?,?,?)")->execute([$tid,$ME['id'],$txt]);
  out();
}
if($action==='tasks.upload'){
  $tid=(int)($_POST['task_id']??0);
  $t=db()->prepare("SELECT * FROM tasks WHERE id=? AND deleted_at IS NULL"); $t->execute([$tid]); $t=$t->fetch();
  if(!$t||!task_visible($t)) fail('غير مصرح',403);
  if(empty($_FILES['file'])) fail('اختر ملفًا');
  $f=$_FILES['file'];
  if($f['size'] > MAX_UPLOAD_MB*1024*1024) fail('أقصى حجم '.MAX_UPLOAD_MB.'MB');
  $ext=strtolower(pathinfo($f['name'],PATHINFO_EXTENSION));
  if(!in_array($ext,['jpg','jpeg','png','gif','webp','pdf','doc','docx','xls','xlsx','zip','txt','csv'])) fail('نوع الملف غير مسموح');
  $name='t'.$tid.'_'.bin2hex(random_bytes(6)).'.'.$ext;
  if(!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR,0755,true);
  if(!move_uploaded_file($f['tmp_name'], UPLOAD_DIR.'/'.$name)) fail('فشل الرفع');
  db()->prepare("INSERT INTO task_files(task_id,orig_name,path,by_user) VALUES(?,?,?,?)")->execute([$tid,$f['name'],'uploads/'.$name,$ME['id']]);
  log_it('رفع مرفق',$t['title'],$tid,'',$f['name']);
  out(['path'=>'uploads/'.$name]);
}
if($action==='tasks.delete'){
  need('delete_task'); $id=(int)inp()['id'];
  $t=db()->prepare("SELECT * FROM tasks WHERE id=? AND deleted_at IS NULL"); $t->execute([$id]); $t=$t->fetch();
  if(!$t) fail('غير موجودة');
  db()->prepare("UPDATE tasks SET deleted_at=NOW() WHERE id=?")->execute([$id]);
  log_it('حذف مهمة (سلة)',$t['title'],$id); out();
}

/* ═══════════ LEADS ═══════════ */
const LEAD_STATUSES=['new','not_contacted','contacted','interested','follow_up','meeting','proposal','negotiation','won','lost','no_answer','not_qualified','postponed'];
if($action==='leads.list'){
  need('view_leads');
  $rows=db()->query("SELECT l.*, u.name assignee_name FROM leads l LEFT JOIN users u ON u.id=l.assignee WHERE l.deleted_at IS NULL ORDER BY l.id DESC LIMIT 500")->fetchAll();
  out(['leads'=>$rows,'statuses'=>LEAD_STATUSES]);
}
if($action==='leads.save'){
  need('manage_leads'); $i=inp(); $id=(int)($i['id']??0);
  $name=trim($i['name']??''); $phone=preg_replace('/[^0-9+]/','',$i['phone']??'');
  if(!$name||!$phone) fail('الاسم ورقم الهاتف مطلوبين');
  $dup=db()->prepare("SELECT id,name FROM leads WHERE phone=? AND id!=? AND deleted_at IS NULL"); $dup->execute([$phone,$id]);
  if($d=$dup->fetch()) fail('الرقم ده مسجل بالفعل باسم: '.$d['name'].' (Lead #'.$d['id'].')');
  $cd=db()->prepare("SELECT id,name_ar FROM clients WHERE phone=? AND deleted_at IS NULL"); $cd->execute([$phone]);
  if($c=$cd->fetch() and !$id) fail('الرقم ده عميل حالي بالفعل: '.$c['name_ar']);
  $st=in_array($i['status']??'',LEAD_STATUSES)?$i['status']:'new';
  $vals=[$name,$phone,trim($i['wa']??''),trim($i['email']??''),trim($i['company']??''),trim($i['industry']??''),trim($i['country']??''),trim($i['city']??''),
    trim($i['source']??''),trim($i['campaign']??''),trim($i['service']??''),(int)($i['assignee']??0)?:null,
    $i['entered_at']?:date('Y-m-d'),$i['last_contact']?:null,trim($i['next_action']??''),$i['next_date']?:null,
    $i['expected_value']!==''?(float)$i['expected_value']:null,$st,trim($i['lost_reason']??''),trim($i['notes']??''),trim($i['chat_link']??'')];
  if($id){
    $old=db()->prepare("SELECT * FROM leads WHERE id=? AND deleted_at IS NULL"); $old->execute([$id]); $old=$old->fetch();
    if(!$old) fail('غير موجود');
    db()->prepare("UPDATE leads SET name=?,phone=?,wa=?,email=?,company=?,industry=?,country=?,city=?,source=?,campaign=?,service=?,assignee=?,entered_at=?,last_contact=?,next_action=?,next_date=?,expected_value=?,status=?,lost_reason=?,notes=?,chat_link=? WHERE id=?")
      ->execute(array_merge($vals,[$id]));
    if($old['status']!==$st) log_it('تغيير حالة Lead',$name,$id,$old['status'],$st);
    else log_it('تعديل Lead',$name,$id);
  } else {
    db()->prepare("INSERT INTO leads(name,phone,wa,email,company,industry,country,city,source,campaign,service,assignee,entered_at,last_contact,next_action,next_date,expected_value,status,lost_reason,notes,chat_link) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)")
      ->execute($vals);
    $id=(int)db()->lastInsertId();
    log_it('إضافة Lead',$name,$id);
    if(!empty($i['assignee']) && (int)$i['assignee']!==(int)$ME['id']) notify((int)$i['assignee'],'Lead جديد مسند إليك',$name.' — '.$phone,'leads');
  }
  out(['id'=>$id]);
}
if($action==='leads.convert'){
  need('convert_lead'); $i=inp(); $id=(int)$i['id'];
  $l=db()->prepare("SELECT * FROM leads WHERE id=? AND deleted_at IS NULL"); $l->execute([$id]); $l=$l->fetch();
  if(!$l) fail('غير موجود');
  if($l['converted_client_id']) fail('تم تحويل هذا الـ Lead بالفعل');
  $dup=db()->prepare("SELECT id,name_ar FROM clients WHERE phone=? AND phone!='' AND deleted_at IS NULL"); $dup->execute([$l['phone']]);
  if($d=$dup->fetch()) fail('يوجد عميل حالي بنفس الرقم: '.$d['name_ar']);
  $stActive=db()->query("SELECT id FROM lists WHERE kind='client_status' ORDER BY sort LIMIT 1")->fetch()['id']??null;
  db()->prepare("INSERT INTO clients(name_ar,phone,email,country,city,industry,services,primary_assignee,contract_value,currency,start_date,status_id,notes_visible,wa_link,last_contact)
    VALUES(?,?,?,?,?,?,?,?,?,?,CURDATE(),?,?,?,CURDATE())")
    ->execute([$l['company']?:$l['name'],$l['phone'],$l['email'],$l['country'],$l['city'],$l['industry'],
      json_encode($l['service']?[$l['service']]:[],JSON_UNESCAPED_UNICODE),$l['assignee'],
      can('edit_financials')?(float)($i['contract_value']??$l['expected_value']??0):0,'EGP',$stActive,
      'محوّل من Lead #'.$id.' — '.$l['name'],$l['chat_link']]);
  $cid=(int)db()->lastInsertId();
  if($l['assignee']) db()->prepare("INSERT IGNORE INTO client_assignees(client_id,user_id) VALUES(?,?)")->execute([$cid,$l['assignee']]);
  db()->prepare("UPDATE leads SET status='won', converted_client_id=?, converted_by=?, converted_at=NOW() WHERE id=?")->execute([$cid,$ME['id'],$id]);
  db()->prepare("INSERT INTO client_history(client_id,kind,note,by_user) VALUES(?,?,?,?)")->execute([$cid,'note','تم إنشاء العميل بالتحويل من Lead #'.$id,$ME['name']]);
  log_it('تحويل Lead إلى عميل',$l['name'],$id,'lead','client#'.$cid);
  out(['client_id'=>$cid]);
}
if($action==='leads.delete'){
  need('manage_leads'); $id=(int)inp()['id'];
  $l=db()->prepare("SELECT * FROM leads WHERE id=? AND deleted_at IS NULL"); $l->execute([$id]); $l=$l->fetch();
  if(!$l) fail('غير موجود');
  db()->prepare("UPDATE leads SET deleted_at=NOW() WHERE id=?")->execute([$id]);
  log_it('حذف Lead (سلة)',$l['name'],$id); out();
}
if($action==='leads.stats'){
  need('view_leads');
  $g=fn($sql)=>db()->query($sql)->fetchAll();
  $tot=(int)db()->query("SELECT COUNT(*) c FROM leads WHERE deleted_at IS NULL")->fetch()['c'];
  $won=(int)db()->query("SELECT COUNT(*) c FROM leads WHERE status='won' AND deleted_at IS NULL")->fetch()['c'];
  out([
    'total'=>$tot,'won'=>$won,
    'new'=>(int)db()->query("SELECT COUNT(*) c FROM leads WHERE status='new' AND deleted_at IS NULL")->fetch()['c'],
    'not_contacted'=>(int)db()->query("SELECT COUNT(*) c FROM leads WHERE status IN('new','not_contacted') AND deleted_at IS NULL")->fetch()['c'],
    'today_followups'=>(int)db()->query("SELECT COUNT(*) c FROM leads WHERE next_date=CURDATE() AND deleted_at IS NULL")->fetch()['c'],
    'lost'=>(int)db()->query("SELECT COUNT(*) c FROM leads WHERE status='lost' AND deleted_at IS NULL")->fetch()['c'],
    'conv_rate'=>$tot?round($won*100/$tot,1):0,
    'pipeline'=>(float)db()->query("SELECT COALESCE(SUM(expected_value),0) s FROM leads WHERE status NOT IN('won','lost','not_qualified') AND deleted_at IS NULL")->fetch()['s'],
    'by_emp'=>$g("SELECT u.name, COUNT(*) c, SUM(l.status='won') w FROM leads l JOIN users u ON u.id=l.assignee WHERE l.deleted_at IS NULL GROUP BY u.name ORDER BY c DESC"),
    'by_source'=>$g("SELECT source, COUNT(*) c FROM leads WHERE deleted_at IS NULL AND source!='' GROUP BY source ORDER BY c DESC"),
    'lost_reasons'=>$g("SELECT lost_reason, COUNT(*) c FROM leads WHERE status='lost' AND lost_reason!='' AND deleted_at IS NULL GROUP BY lost_reason ORDER BY c DESC"),
  ]);
}

/* ═══════════ TRASH ═══════════ */
if($action==='trash.list'){
  need('manage_trash');
  out(['trash'=>[
    'clients'=>db()->query("SELECT id,name_ar label,deleted_at FROM clients WHERE deleted_at IS NOT NULL ORDER BY deleted_at DESC")->fetchAll(),
    'users'=>db()->query("SELECT id,name label,deleted_at FROM users WHERE deleted_at IS NOT NULL ORDER BY deleted_at DESC")->fetchAll(),
    'tasks'=>db()->query("SELECT id,title label,deleted_at FROM tasks WHERE deleted_at IS NOT NULL ORDER BY deleted_at DESC")->fetchAll(),
    'finance'=>db()->query("SELECT id,CONCAT(kind,' — ',amount,' — ',descr) label,deleted_at FROM finance WHERE deleted_at IS NOT NULL ORDER BY deleted_at DESC")->fetchAll(),
    'leads'=>db()->query("SELECT id,name label,deleted_at FROM leads WHERE deleted_at IS NOT NULL ORDER BY deleted_at DESC")->fetchAll(),
  ]]);
}
if($action==='trash.restore' || $action==='trash.purge'){
  need('manage_trash');
  $i=inp(); $type=$i['type']??''; $id=(int)$i['id'];
  $tables=['clients'=>'clients','users'=>'users','tasks'=>'tasks','finance'=>'finance','leads'=>'leads'];
  if(!isset($tables[$type])) fail('نوع غير صحيح');
  $tb=$tables[$type];
  if($action==='trash.restore'){
    db()->prepare("UPDATE $tb SET deleted_at=NULL".($type==='users'?', active=1':'')." WHERE id=?")->execute([$id]);
    log_it('استرجاع من السلة',$type.'#'.$id,$id);
  } else {
    if($ME['role_name']!=='super_admin') fail('الحذف النهائي متاح للـ Super Admin فقط',403);
    db()->prepare("DELETE FROM $tb WHERE id=? AND deleted_at IS NOT NULL")->execute([$id]);
    log_it('حذف نهائي',$type.'#'.$id,$id);
  }
  out();
}

/* ═══════════ ACTIVITY LOG & DASHBOARD ═══════════ */
if($action==='log.list'){
  if(can('view_activity_log')) $rows=db()->query("SELECT * FROM activity_log ORDER BY id DESC LIMIT 300")->fetchAll();
  else { $s=db()->prepare("SELECT * FROM activity_log WHERE user_id=? ORDER BY id DESC LIMIT 100"); $s->execute([$ME['id']]); $rows=$s->fetchAll(); }
  out(['log'=>$rows]);
}
if($action==='dash.summary'){
  $o=['name'=>$ME['name']];
  $att=db()->prepare("SELECT * FROM attendance WHERE user_id=? AND day=CURDATE()"); $att->execute([$ME['id']]);
  $o['attendance']=$att->fetch()?:null;
  $mp=db()->prepare("SELECT COUNT(*) c FROM leaves WHERE user_id=? AND status='pending'"); $mp->execute([$ME['id']]);
  $o['my_pending_leaves']=(int)$mp->fetch()['c'];
  $mt=db()->prepare("SELECT COUNT(DISTINCT t.id) c FROM tasks t LEFT JOIN task_assignees ta ON ta.task_id=t.id WHERE t.deleted_at IS NULL AND t.status NOT IN('completed','cancelled') AND (ta.user_id=? OR t.created_by=?)"); $mt->execute([$ME['id'],$ME['id']]);
  $o['my_open_tasks']=(int)$mt->fetch()['c'];
  if(can('approve_leave')) $o['pending_approvals']=(int)db()->query("SELECT COUNT(*) c FROM leaves WHERE status='pending' AND deleted_at IS NULL")->fetch()['c'];
  if(can('view_all_clients')) $o['clients']=(int)db()->query("SELECT COUNT(*) c FROM clients WHERE deleted_at IS NULL")->fetch()['c'];
  if(can('view_leads')) $o['leads_new']=(int)db()->query("SELECT COUNT(*) c FROM leads WHERE status IN('new','not_contacted') AND deleted_at IS NULL")->fetch()['c'];
  if(can('manage_attendance')) $o['present_today']=(int)db()->query("SELECT COUNT(*) c FROM attendance WHERE day=CURDATE() AND in_time IS NOT NULL")->fetch()['c'];
  if(can('manage_devices')) $o['pending_devices']=(int)db()->query("SELECT COUNT(*) c FROM devices WHERE approved=0")->fetch()['c'];
  if(can('manage_renewals')) $o['renewals_soon']=db()->query("SELECT name, phone, site_link, renew_date, DATEDIFF(renew_date,CURDATE()) days_left FROM renewals WHERE deleted_at IS NULL AND renew_date<=DATE_ADD(CURDATE(),INTERVAL 30 DAY) ORDER BY renew_date LIMIT 10")->fetchAll();
  if(can('view_financials')){
    $o['client_renewals']=db()->query("SELECT name_ar, renew_date, DATEDIFF(renew_date,CURDATE()) days_left FROM clients WHERE deleted_at IS NULL AND renew_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(),INTERVAL 30 DAY) ORDER BY renew_date LIMIT 8")->fetchAll();
    $o['overdue_payments']=db()->query("SELECT cp.amount, cp.due_date, c.name_ar FROM client_payments cp JOIN clients c ON c.id=cp.client_id WHERE cp.status='due' AND cp.due_date<CURDATE() AND cp.deleted_at IS NULL ORDER BY cp.due_date LIMIT 8")->fetchAll();
  }
  if(can('company_finance')){
    $m=date('Y-m');
    $o['co_month_income']=(float)db()->query("SELECT COALESCE(SUM(amount),0) s FROM company_finance WHERE kind='income' AND tx_date LIKE '$m%' AND deleted_at IS NULL")->fetch()['s'];
    $o['co_month_expense']=(float)db()->query("SELECT COALESCE(SUM(amount),0) s FROM company_finance WHERE kind='expense' AND tx_date LIKE '$m%' AND deleted_at IS NULL")->fetch()['s'];
  }
  if(can('view_all_tasks')||can('view_assigned_tasks')){
    if(can('view_all_tasks')) $tq=db()->query("SELECT status, COUNT(*) c FROM tasks WHERE deleted_at IS NULL GROUP BY status")->fetchAll();
    else { $st=db()->prepare("SELECT t.status, COUNT(DISTINCT t.id) c FROM tasks t LEFT JOIN task_assignees ta ON ta.task_id=t.id WHERE t.deleted_at IS NULL AND (ta.user_id=? OR t.created_by=?) GROUP BY t.status"); $st->execute([$ME['id'],$ME['id']]); $tq=$st->fetchAll(); }
    $o['tasks_by_status']=array_column($tq,'c','status');
    $mt2=db()->prepare("SELECT DISTINCT t.id,t.title,t.deadline,t.priority FROM tasks t LEFT JOIN task_assignees ta ON ta.task_id=t.id WHERE t.deleted_at IS NULL AND t.status NOT IN('completed','cancelled') AND t.deadline IS NOT NULL AND (ta.user_id=? OR t.created_by=?) ORDER BY t.deadline LIMIT 6");
    $mt2->execute([$ME['id'],$ME['id']]); $o['my_upcoming']=$mt2->fetchAll();
  }
  if(can('view_leads')) $o['today_followups']=(int)db()->query("SELECT COUNT(*) c FROM leads WHERE next_date=CURDATE() AND deleted_at IS NULL")->fetch()['c'];
  out($o);
}
if($action==='users.options'){ // قائمة مختصرة لكل مستخدم نشط - للإسناد والاختيار
  out(['users'=>db()->query("SELECT id,name,dept,title FROM users WHERE active=1 AND deleted_at IS NULL ORDER BY name")->fetchAll()]);
}

/* ═══════════ CLIENT PAYMENTS ═══════════ */
if($action==='clients.payments'){
  need('view_financials');
  $id=(int)($_GET['id']??inp()['id']);
  $s=db()->prepare("SELECT * FROM client_payments WHERE client_id=? AND deleted_at IS NULL ORDER BY COALESCE(pay_date,due_date) DESC, id DESC"); $s->execute([$id]);
  out(['payments'=>$s->fetchAll()]);
}
if($action==='clients.payment.save'){
  need('edit_financials'); $i=inp();
  $cid=(int)$i['client_id']; $amount=(float)($i['amount']??0);
  if($amount<=0) fail('اكتب مبلغًا صحيحًا');
  $c=db()->prepare("SELECT * FROM clients WHERE id=? AND deleted_at IS NULL"); $c->execute([$cid]); $c=$c->fetch();
  if(!$c) fail('العميل غير موجود');
  $status=($i['status']??'paid')==='due'?'due':'paid';
  $id=(int)($i['id']??0);
  if($id){
    $old=db()->prepare("SELECT * FROM client_payments WHERE id=? AND deleted_at IS NULL"); $old->execute([$id]); $old=$old->fetch();
    if(!$old) fail('غير موجودة');
    db()->prepare("UPDATE client_payments SET amount=?,currency=?,method=?,status=?,pay_date=?,due_date=?,note=? WHERE id=?")
      ->execute([$amount,trim($i['currency']??'EGP'),trim($i['method']??''),$status,$i['pay_date']?:null,$i['due_date']?:null,trim($i['note']??''),$id]);
    if($status==='paid'){
      if($old['cofin_id']){
        db()->prepare("UPDATE company_finance SET amount=?, tx_date=?, method=?, descr=?, deleted_at=NULL WHERE id=?")
          ->execute([$amount,$i['pay_date']?:date('Y-m-d'),trim($i['method']??''),'دفعة من '.$c['name_ar'],$old['cofin_id']]);
      } else {
        db()->prepare("INSERT INTO company_finance(kind,category,amount,currency,method,tx_date,descr,source,ref_id,created_by) VALUES('income','عقود عملاء',?,?,?,?,?,'payment',?,?)")
          ->execute([$amount,trim($i['currency']??'EGP'),trim($i['method']??''),$i['pay_date']?:date('Y-m-d'),'دفعة من '.$c['name_ar'],$id,$ME['id']]);
        db()->prepare("UPDATE client_payments SET cofin_id=? WHERE id=?")->execute([(int)db()->lastInsertId(),$id]);
      }
      log_it('تحصيل دفعة',$c['name_ar'],$cid,'',$amount.' '.($i['currency']??'EGP'));
    } elseif($old['cofin_id']){
      db()->prepare("UPDATE company_finance SET deleted_at=NOW() WHERE id=?")->execute([$old['cofin_id']]);
      db()->prepare("UPDATE client_payments SET cofin_id=NULL WHERE id=?")->execute([$id]);
    }
  } else {
    db()->prepare("INSERT INTO client_payments(client_id,amount,currency,method,status,pay_date,due_date,note,created_by) VALUES(?,?,?,?,?,?,?,?,?)")
      ->execute([$cid,$amount,trim($i['currency']??'EGP'),trim($i['method']??''),$status,$i['pay_date']?:($status==='paid'?date('Y-m-d'):null),$i['due_date']?:null,trim($i['note']??''),$ME['id']]);
    $id=(int)db()->lastInsertId();
    if($status==='paid'){
      db()->prepare("INSERT INTO company_finance(kind,category,amount,currency,method,tx_date,descr,source,ref_id,created_by) VALUES('income','عقود عملاء',?,?,?,?,?,'payment',?,?)")
        ->execute([$amount,trim($i['currency']??'EGP'),trim($i['method']??''),$i['pay_date']?:date('Y-m-d'),'دفعة من '.$c['name_ar'],$id,$ME['id']]);
      db()->prepare("UPDATE client_payments SET cofin_id=? WHERE id=?")->execute([(int)db()->lastInsertId(),$id]);
      log_it('تحصيل دفعة',$c['name_ar'],$cid,'',$amount.' '.($i['currency']??'EGP'));
    } else {
      log_it('تسجيل دفعة مستحقة',$c['name_ar'],$cid,'',$amount.' — استحقاق '.($i['due_date']??''));
    }
  }
  db()->prepare("UPDATE clients SET updated_at=NOW() WHERE id=?")->execute([$cid]);
  out(['id'=>$id]);
}
if($action==='clients.payment.delete'){
  need('delete_financials'); $id=(int)inp()['id'];
  $p=db()->prepare("SELECT * FROM client_payments WHERE id=? AND deleted_at IS NULL"); $p->execute([$id]); $p=$p->fetch();
  if(!$p) fail('غير موجودة');
  db()->prepare("UPDATE client_payments SET deleted_at=NOW() WHERE id=?")->execute([$id]);
  if($p['cofin_id']) db()->prepare("UPDATE company_finance SET deleted_at=NOW() WHERE id=?")->execute([$p['cofin_id']]);
  log_it('حذف دفعة','client#'.$p['client_id'],$id,$p['amount']);
  out();
}
if($action==='crm.stats'){
  if(!can('view_all_clients') && !can('view_assigned_clients')) fail('غير مصرح',403);
  $fin = can('view_financials');
  $tot=(int)db()->query("SELECT COUNT(*) c FROM clients WHERE deleted_at IS NULL")->fetch()['c'];
  $stActive=(int)(db()->query("SELECT id FROM lists WHERE kind='client_status' ORDER BY sort LIMIT 1")->fetch()['id']??0);
  $active=(int)db()->query("SELECT COUNT(*) c FROM clients WHERE status_id=$stActive AND deleted_at IS NULL")->fetch()['c'];
  $renew30=(int)db()->query("SELECT COUNT(*) c FROM clients WHERE renew_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(),INTERVAL 30 DAY) AND deleted_at IS NULL")->fetch()['c'];
  $o=['total'=>$tot,'active'=>$active,'renew30'=>$renew30];
  if($fin){
    $mrr=0.0;
    foreach(db()->query("SELECT contract_value,currency,pay_cycle FROM clients WHERE deleted_at IS NULL AND status_id=$stActive") as $c){
      $mult=['monthly'=>1,'quarterly'=>1/3,'semiannual'=>1/6,'annual'=>1/12,'once'=>0][$c['pay_cycle']]??0;
      $fx=['EGP'=>1,'SAR'=>13,'USD'=>49,'AED'=>13.3][$c['currency']]??1;
      $mrr += (float)$c['contract_value']*$mult*$fx;
    }
    $o['mrr']=round($mrr);
    $o['collected']=(float)db()->query("SELECT COALESCE(SUM(amount),0) s FROM client_payments WHERE status='paid' AND deleted_at IS NULL")->fetch()['s'];
    $o['due']=(float)db()->query("SELECT COALESCE(SUM(amount),0) s FROM client_payments WHERE status='due' AND deleted_at IS NULL")->fetch()['s'];
    $o['overdue']=(int)db()->query("SELECT COUNT(*) c FROM client_payments WHERE status='due' AND due_date<CURDATE() AND deleted_at IS NULL")->fetch()['c'];
    $paid=db()->query("SELECT client_id, SUM(amount) s FROM client_payments WHERE status='paid' AND deleted_at IS NULL GROUP BY client_id")->fetchAll();
    $o['paid_by_client']=array_column($paid,'s','client_id');
  }
  out($o);
}

/* ═══════════ COMPANY FINANCE & PAYROLL ═══════════ */
if(str_starts_with($action,'cofin.') || str_starts_with($action,'payroll.')) need('company_finance');
if($action==='cofin.list'){
  $i=inp()+$_GET; $w="deleted_at IS NULL"; $p=[];
  if(!empty($i['kind'])){ $w.=" AND kind=?"; $p[]=$i['kind']; }
  if(!empty($i['from'])){ $w.=" AND tx_date>=?"; $p[]=$i['from']; }
  if(!empty($i['to'])){ $w.=" AND tx_date<=?"; $p[]=$i['to']; }
  $s=db()->prepare("SELECT * FROM company_finance WHERE $w ORDER BY tx_date DESC, id DESC LIMIT 500"); $s->execute($p);
  out(['rows'=>$s->fetchAll()]);
}
if($action==='cofin.stats'){
  $g=fn($sql)=>(float)db()->query($sql)->fetch()['s'];
  $inc=$g("SELECT COALESCE(SUM(amount),0) s FROM company_finance WHERE kind='income' AND deleted_at IS NULL");
  $exp=$g("SELECT COALESCE(SUM(amount),0) s FROM company_finance WHERE kind='expense' AND deleted_at IS NULL");
  $m=date('Y-m');
  $months=[];
  for($k=5;$k>=0;$k--){ $mm=date('Y-m',strtotime("-$k month"));
    $months[]=['m'=>$mm,
      'inc'=>$g("SELECT COALESCE(SUM(amount),0) s FROM company_finance WHERE kind='income' AND tx_date LIKE '$mm%' AND deleted_at IS NULL"),
      'exp'=>$g("SELECT COALESCE(SUM(amount),0) s FROM company_finance WHERE kind='expense' AND tx_date LIKE '$mm%' AND deleted_at IS NULL")]; }
  $cats=db()->query("SELECT category, SUM(amount) s FROM company_finance WHERE kind='expense' AND deleted_at IS NULL GROUP BY category ORDER BY s DESC LIMIT 10")->fetchAll();
  $expected=$g("SELECT COALESCE(SUM(amount),0) s FROM client_payments WHERE deleted_at IS NULL");
  $collected=$g("SELECT COALESCE(SUM(amount),0) s FROM client_payments WHERE status='paid' AND deleted_at IS NULL");
  out(['income'=>$inc,'expense'=>$exp,'net'=>$inc-$exp,
    'month_income'=>$g("SELECT COALESCE(SUM(amount),0) s FROM company_finance WHERE kind='income' AND tx_date LIKE '$m%' AND deleted_at IS NULL"),
    'month_expense'=>$g("SELECT COALESCE(SUM(amount),0) s FROM company_finance WHERE kind='expense' AND tx_date LIKE '$m%' AND deleted_at IS NULL"),
    'months'=>$months,'by_cat'=>$cats,
    'collection_rate'=>$expected>0?round($collected*100/$expected,1):100]);
}
if($action==='cofin.save'){
  $i=inp(); $id=(int)($i['id']??0);
  $kind=in_array($i['kind']??'',['income','expense'])?$i['kind']:'expense';
  $amount=(float)($i['amount']??0); if($amount<=0) fail('اكتب مبلغًا صحيحًا');
  $vals=[$kind,trim($i['category']??''),$amount,trim($i['currency']??'EGP'),trim($i['method']??''),$i['tx_date']?:date('Y-m-d'),trim($i['descr']??''),trim($i['note']??'')];
  if($id){
    $old=db()->prepare("SELECT * FROM company_finance WHERE id=? AND deleted_at IS NULL"); $old->execute([$id]); $old=$old->fetch();
    if(!$old) fail('غير موجودة');
    db()->prepare("UPDATE company_finance SET kind=?,category=?,amount=?,currency=?,method=?,tx_date=?,descr=?,note=? WHERE id=?")->execute(array_merge($vals,[$id]));
    log_it('تعديل حركة (حسابات الشركة)',$old['descr']?:('#'.$id),$id,$old['kind'].' '.$old['amount'],$kind.' '.$amount);
  } else {
    db()->prepare("INSERT INTO company_finance(kind,category,amount,currency,method,tx_date,descr,note,created_by) VALUES(?,?,?,?,?,?,?,?,?)")->execute(array_merge($vals,[$ME['id']]));
    $id=(int)db()->lastInsertId();
    log_it($kind==='income'?'إضافة إيراد (شركة)':'إضافة مصروف (شركة)',trim($i['descr']??'').' '.$amount,$id);
  }
  out(['id'=>$id]);
}
if($action==='cofin.delete'){
  $id=(int)inp()['id'];
  $f=db()->prepare("SELECT * FROM company_finance WHERE id=? AND deleted_at IS NULL"); $f->execute([$id]); $f=$f->fetch();
  if(!$f) fail('غير موجودة');
  db()->prepare("UPDATE company_finance SET deleted_at=NOW() WHERE id=?")->execute([$id]);
  log_it('حذف حركة (شركة)',$f['descr']?:('#'.$id),$id,$f['kind'].' '.$f['amount']);
  out();
}
if($action==='payroll.preview'){
  $m=preg_replace('/[^0-9-]/','',inp()['month']??date('Y-m'));
  $done=db()->prepare("SELECT COUNT(*) c FROM company_finance WHERE source='payroll' AND tx_date LIKE ? AND deleted_at IS NULL");
  $done->execute([$m.'%']);
  $emps=db()->query("SELECT id,name,dept,title,salary FROM users WHERE active=1 AND deleted_at IS NULL AND salary IS NOT NULL AND salary>0 ORDER BY name")->fetchAll();
  out(['employees'=>$emps,'already_run'=>(int)$done->fetch()['c']>0,'month'=>$m]);
}
if($action==='payroll.commit'){
  $i=inp(); $m=preg_replace('/[^0-9-]/','',$i['month']??date('Y-m'));
  $rows=(array)($i['rows']??[]); if(!$rows) fail('لا توجد رواتب للصرف');
  $done=db()->prepare("SELECT COUNT(*) c FROM company_finance WHERE source='payroll' AND tx_date LIKE ? AND deleted_at IS NULL");
  $done->execute([$m.'%']);
  if((int)$done->fetch()['c']>0) fail('رواتب شهر '.$m.' اتصرفت بالفعل — لو محتاج تعديل عدّل البنود من جدول الحركات');
  $ins=db()->prepare("INSERT INTO company_finance(kind,category,amount,currency,method,tx_date,descr,note,source,ref_id,created_by) VALUES('expense','رواتب',?,?,?,?,?,?,'payroll',?,?)");
  $n=0;
  foreach($rows as $r){
    $uid=(int)($r['user_id']??0); $amt=(float)($r['amount']??0);
    if($uid<=0||$amt<=0) continue;
    $u=db()->prepare("SELECT name FROM users WHERE id=?"); $u->execute([$uid]); $u=$u->fetch();
    if(!$u) continue;
    $ins->execute([$amt,'EGP',trim($r['method']??'إنستاباي'),$m.'-28','راتب '.$u['name'].' — '.$m,trim($r['note']??''),$uid,$ME['id']]);
    $n++;
  }
  log_it('صرف رواتب شهر '.$m,'', null,'', $n.' موظف');
  out(['count'=>$n]);
}

/* ═══════════ RENEWALS (عملاء تجديد) ═══════════ */
if(str_starts_with($action,'renewals.')) need('manage_renewals');
if($action==='renewals.list'){
  out(['renewals'=>db()->query("SELECT *, DATEDIFF(renew_date, CURDATE()) days_left FROM renewals WHERE deleted_at IS NULL ORDER BY renew_date")->fetchAll()]);
}
if($action==='renewals.save'){
  $i=inp(); $id=(int)($i['id']??0);
  $name=trim($i['name']??''); $date=$i['renew_date']??'';
  if(!$name||!$date) fail('الاسم وتاريخ التجديد مطلوبين');
  $vals=[$name,trim($i['phone']??''),trim($i['site_link']??''),$date,($i['amount']??'')!==''?(float)$i['amount']:null,trim($i['notes']??'')];
  if($id){
    db()->prepare("UPDATE renewals SET name=?,phone=?,site_link=?,renew_date=?,amount=?,notes=? WHERE id=? AND deleted_at IS NULL")->execute(array_merge($vals,[$id]));
    log_it('تعديل عميل تجديد',$name,$id);
  } else {
    db()->prepare("INSERT INTO renewals(name,phone,site_link,renew_date,amount,notes) VALUES(?,?,?,?,?,?)")->execute($vals);
    $id=(int)db()->lastInsertId();
    log_it('إضافة عميل تجديد',$name.' — '.$date,$id);
  }
  out(['id'=>$id]);
}
if($action==='renewals.delete'){
  $id=(int)inp()['id'];
  $r=db()->prepare("SELECT * FROM renewals WHERE id=? AND deleted_at IS NULL"); $r->execute([$id]); $r=$r->fetch();
  if(!$r) fail('غير موجود');
  db()->prepare("UPDATE renewals SET deleted_at=NOW() WHERE id=?")->execute([$id]);
  log_it('حذف عميل تجديد',$r['name'],$id);
  out();
}

fail('إجراء غير معروف: '.$action, 404);
