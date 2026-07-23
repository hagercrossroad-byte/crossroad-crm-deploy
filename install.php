<?php
/* ══ CrossRoad CRM — معالج التثبيت ══
   افتح الصفحة دي في المتصفح وهي هتظبط كل حاجة بنفسها.
   بتتقفل تلقائيًا بعد اكتمال التثبيت. */
error_reporting(E_ALL); ini_set('display_errors','0');
header('Content-Type: text/html; charset=utf-8');

$CONFIG = __DIR__.'/config.php';

function cfg_read(){ return file_exists($GLOBALS['CONFIG']) ? file_get_contents($GLOBALS['CONFIG']) : ''; }
function cfg_get($k){ preg_match("/define\('$k',\s*'([^']*)'\)/", cfg_read(), $m); return $m[1] ?? ''; }
function try_db($h,$n,$u,$p){
  try { return [new PDO("mysql:host=$h;dbname=$n;charset=utf8mb4",$u,$p,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]), '']; }
  catch(PDOException $e){
    $msg = $e->getMessage();
    if (str_contains($msg,'Access denied')) $hint='اسم المستخدم أو كلمة المرور غلط. انسخهم من hPanel → Databases بالحرف، ولو مش متأكد من الباسورد اعمل لها Change password هناك وجرب الجديدة.';
    elseif (str_contains($msg,'Unknown database')) $hint='اسم قاعدة البيانات مش موجود. لازم الاسم الكامل بالبادئة (مثال: u123456789_crm) زي ما هو مكتوب في hPanel.';
    elseif (str_contains($msg,'getaddrinfo')||str_contains($msg,'Connection refused')) $hint='الـ Host غلط — في Hostinger سيبه localhost.';
    else $hint=$msg;
    return [null,$hint];
  }
}
function installed(){
  [$pdo,] = try_db(cfg_get('DB_HOST')?:'localhost', cfg_get('DB_NAME'), cfg_get('DB_USER'), cfg_get('DB_PASS'));
  if(!$pdo) return false;
  try { $u=$pdo->query("SELECT COUNT(*) c FROM users WHERE last_login IS NOT NULL")->fetch(); return ((int)($u['c']??0))>0; }
  catch(Exception $e){ return false; }
}

$step = $_GET['step'] ?? '1';
$err=''; $okmsg='';

/* ── حماية: لو النظام مستخدم بالفعل، المعالج يقفل ── */
if (installed() && $step!=='done'){
  die('<!DOCTYPE html><html dir="rtl"><meta charset="utf-8"><body style="font-family:sans-serif;text-align:center;padding:60px">
  <h2>✅ النظام مثبّت وشغال بالفعل</h2><p>معالج التثبيت اتقفل للأمان. <b>احذف ملف install.php من السيرفر.</b></p>
  <a href="index.php">فتح النظام ←</a></body></html>');
}

/* ── خطوة 1: بيانات قاعدة البيانات ── */
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['do']??'')==='db'){
  $h=trim($_POST['host']?:'localhost'); $n=trim($_POST['name']??''); $u=trim($_POST['user']??''); $p=$_POST['pass']??'';
  if(!$n||!$u) $err='اكتب اسم القاعدة واسم المستخدم';
  else {
    [$pdo,$hint] = try_db($h,$n,$u,$p);
    if(!$pdo) $err=$hint;
    else {
      $tpl = cfg_read();
      foreach(['DB_HOST'=>$h,'DB_NAME'=>$n,'DB_USER'=>$u,'DB_PASS'=>$p] as $k=>$v){
        $v2 = str_replace(["\\","'"], ["\\\\","\\'"], $v);
        $tpl = preg_replace("/define\('$k',\s*'[^']*'\)/", "define('$k', '$v2')", $tpl);
      }
      if(@file_put_contents($CONFIG,$tpl)===false) $err='مش قادر أكتب في config.php — من File Manager خلي Permissions بتاعته 644 وجرب تاني';
      else { header('Location: install.php?step=2'); exit; }
    }
  }
}

/* ── خطوة 2: إنشاء الجداول + حساب المدير باختيارك ── */
if ($step==='2' || ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['do']??'')==='owner')){
  require_once __DIR__.'/db.php';
  try { db_install(); } catch(Exception $e){ $err='خطأ في إنشاء الجداول: '.$e->getMessage(); $step='1'; }
}
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['do']??'')==='owner' && !$err){
  $name=trim($_POST['name']??'صاحب الشركة'); $un=trim($_POST['username']??''); $pw=$_POST['password']??''; $pw2=$_POST['confirm']??'';
  if(!preg_match('/^[a-zA-Z0-9_.-]{3,30}$/',$un)) $err='اسم المستخدم: حروف إنجليزية وأرقام فقط (3 أحرف على الأقل)';
  elseif(strlen($pw)<8) $err='كلمة المرور 8 أحرف على الأقل';
  elseif($pw!==$pw2) $err='تأكيد كلمة المرور غير مطابق';
  else {
    $rid = db()->query("SELECT id FROM roles WHERE name='super_admin'")->fetch()['id'];
    $seed = db()->query("SELECT id FROM users WHERE last_login IS NULL ORDER BY id LIMIT 1")->fetch();
    if($seed) db()->prepare("UPDATE users SET username=?, name=?, pw_hash=?, must_change_pw=0, active=1, device_exempt=1, role_id=? WHERE id=?")
      ->execute([$un,$name?:'صاحب الشركة',password_hash($pw,PASSWORD_DEFAULT),$rid,$seed['id']]);
    else db()->prepare("INSERT INTO users(username,name,role_id,pw_hash,must_change_pw,device_exempt) VALUES(?,?,?,?,0,1)")
      ->execute([$un,$name?:'صاحب الشركة',$rid,password_hash($pw,PASSWORD_DEFAULT)]);
    @unlink(__FILE__); // المعالج يحذف نفسه بعد النجاح
    die('<!DOCTYPE html><html dir="rtl"><meta charset="utf-8"><body style="font-family:sans-serif;text-align:center;padding:60px;background:#f0f4fa">
      <div style="background:#fff;max-width:440px;margin:auto;border-radius:18px;padding:30px;box-shadow:0 4px 20px rgba(0,0,0,.08)">
      <h2>🎉 تم التثبيت بنجاح</h2>
      <p>حسابك جاهز: <b>'.htmlspecialchars($un).'</b> بكلمة المرور اللي اخترتها.</p>
      <p style="color:#888;font-size:13px">ملف التثبيت اتحذف تلقائيًا للأمان.</p>
      <a href="index.php" style="display:inline-block;background:#2563eb;color:#fff;padding:12px 30px;border-radius:10px;text-decoration:none;font-weight:bold">دخول النظام ←</a>
      </div></body></html>');
  }
  $step='2';
}
?>
<!DOCTYPE html><html lang="ar" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>تثبيت CrossRoad CRM</title>
<style>
@import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;700;900&display=swap');
body{font-family:'Cairo',sans-serif;background:#f0f4fa;margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
.card{background:#fff;border-radius:20px;padding:30px;max-width:470px;width:100%;box-shadow:0 6px 30px rgba(13,37,64,.1)}
h2{margin:0 0 4px}.sub{color:#5a7a9a;font-size:12.5px;margin-bottom:18px}
label{font-size:11.5px;font-weight:700;color:#5a7a9a;display:block;margin-bottom:5px}
input{width:100%;box-sizing:border-box;padding:11px 13px;border:1.5px solid #dde5f0;border-radius:11px;font-family:'Cairo';font-size:13px;margin-bottom:12px}
input:focus{outline:none;border-color:#2563eb}
button{width:100%;background:linear-gradient(135deg,#1e3a5f,#2563eb);color:#fff;border:none;padding:12px;border-radius:11px;font-family:'Cairo';font-size:14px;font-weight:700;cursor:pointer}
.err{background:#fef2f2;color:#dc2626;font-size:12.5px;font-weight:700;border-radius:10px;padding:11px;margin-bottom:12px;line-height:1.7}
.steps{display:flex;gap:6px;margin-bottom:18px}
.st{flex:1;height:5px;border-radius:3px;background:#dde5f0}.st.on{background:#2563eb}
.hint{background:#f8fafd;border:1px dashed #dde5f0;border-radius:10px;padding:10px;font-size:11.5px;color:#5a7a9a;margin-bottom:12px;line-height:1.8}
</style></head><body>
<div class="card">
<?php if($step==='1'): ?>
  <h2>⚙️ تثبيت النظام — خطوة 1 من 2</h2>
  <div class="sub">بيانات قاعدة البيانات من حسابك في Hostinger</div>
  <div class="steps"><div class="st on"></div><div class="st"></div></div>
  <?php if($err) echo '<div class="err">⚠️ '.$err.'</div>'; ?>
  <div class="hint">📍 من hPanel افتح <b>Databases → Management</b> — لو لسه معملتش قاعدة، اعمل Create وهتلاقي الاسم واليوزر بيبدأوا بـ <b dir="ltr">u123456789_</b>. انسخهم من هناك زي ما هم.</div>
  <form method="post">
    <input type="hidden" name="do" value="db">
    <label>اسم قاعدة البيانات (Database name)</label>
    <input name="name" dir="ltr" placeholder="u123456789_crm" value="<?=htmlspecialchars($_POST['name']??'')?>" required>
    <label>اسم المستخدم (Username)</label>
    <input name="user" dir="ltr" placeholder="u123456789_crm" value="<?=htmlspecialchars($_POST['user']??'')?>" required>
    <label>كلمة مرور القاعدة</label>
    <input name="pass" dir="ltr" type="text" value="<?=htmlspecialchars($_POST['pass']??'')?>">
    <label>Host (سيبها زي ما هي)</label>
    <input name="host" dir="ltr" value="<?=htmlspecialchars($_POST['host']??'localhost')?>">
    <button>اختبار الاتصال والمتابعة ←</button>
  </form>
<?php else: ?>
  <h2>👑 خطوة 2 من 2 — حسابك أنت</h2>
  <div class="sub">✅ قاعدة البيانات اتوصلت والجداول اتعملت. اختار بيانات دخولك:</div>
  <div class="steps"><div class="st on"></div><div class="st on"></div></div>
  <?php if($err) echo '<div class="err">⚠️ '.$err.'</div>'; ?>
  <form method="post" action="install.php">
    <input type="hidden" name="do" value="owner">
    <label>اسمك (هيظهر في النظام)</label>
    <input name="name" value="<?=htmlspecialchars($_POST['name']??'صاحب الشركة')?>">
    <label>اسم المستخدم للدخول (إنجليزي)</label>
    <input name="username" dir="ltr" placeholder="ahmed" value="<?=htmlspecialchars($_POST['username']??'')?>" required>
    <label>كلمة المرور (8 أحرف على الأقل)</label>
    <input name="password" dir="ltr" type="text" required>
    <label>تأكيد كلمة المرور</label>
    <input name="confirm" dir="ltr" type="text" required>
    <button>✅ إنهاء التثبيت والدخول</button>
  </form>
<?php endif; ?>
</div></body></html>
