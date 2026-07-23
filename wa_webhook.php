<?php
/* WhatsApp Cloud API Webhook — استقبال رسمي للرسائل وتحويلها لـ Leads */
require_once __DIR__.'/db.php';
db_install();
$verify = '';
$r = db()->prepare("SELECT v FROM settings WHERE k='wa_verify_token'"); $r->execute();
$verify = $r->fetch()['v'] ?? '';

if($_SERVER['REQUEST_METHOD']==='GET'){
  // خطوة التحقق من Meta
  if(($_GET['hub_verify_token'] ?? $_GET['hub.verify_token'] ?? '') === $verify){
    echo $_GET['hub_challenge'] ?? $_GET['hub.challenge'] ?? ''; exit;
  }
  http_response_code(403); exit('bad token');
}

$payload = json_decode(file_get_contents('php://input'), true) ?: [];
$entries = $payload['entry'] ?? [];
foreach($entries as $e){
  foreach(($e['changes'] ?? []) as $ch){
    $v = $ch['value'] ?? [];
    foreach(($v['messages'] ?? []) as $msg){
      $phone = preg_replace('/[^0-9+]/','', $msg['from'] ?? '');
      if(!$phone) continue;
      $name = $v['contacts'][0]['profile']['name'] ?? 'واتساب '.$phone;
      $text = mb_substr($msg['text']['body'] ?? '['.$msg['type'].']', 0, 300);
      // منع التكرار: لو الرقم موجود كـ Lead أو كعميل نحدّث آخر تواصل فقط
      $ex = db()->prepare("SELECT id FROM leads WHERE phone=?"); $ex->execute([$phone]);
      if($l = $ex->fetch()){
        db()->prepare("UPDATE leads SET last_contact=CURDATE(), notes=CONCAT(COALESCE(notes,''),'\n[WA ',NOW(),'] ',?) WHERE id=?")->execute([$text,$l['id']]);
      } else {
        $cx = db()->prepare("SELECT id FROM clients WHERE phone=? AND deleted_at IS NULL"); $cx->execute([$phone]);
        if(!$cx->fetch()){
          db()->prepare("INSERT INTO leads(name,phone,wa,source,entered_at,last_contact,status,notes,chat_link) VALUES(?,?,?,?,CURDATE(),CURDATE(),'new',?,?)")
            ->execute([$name,$phone,$phone,'واتساب','أول رسالة: '.$text,'https://wa.me/'.$phone]);
          db()->prepare("INSERT INTO activity_log(user_name,action,entity) VALUES('WhatsApp Webhook','إضافة Lead تلقائي',?)")->execute([$name]);
        }
      }
    }
  }
}
echo json_encode(['ok'=>true]);
