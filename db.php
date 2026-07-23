<?php
require_once __DIR__.'/config.php';

/* احتياطي لو mbstring مش متفعلة على الاستضافة */
if (!function_exists('mb_substr')) { function mb_substr($s,$st,$len=null){ return $len===null?substr($s,$st):substr($s,$st,$len); } }

function db(): PDO {
  static $pdo = null;
  if ($pdo === null) {
    try {
      $pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4', DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
      ]);
    } catch (PDOException $e) {
      http_response_code(500);
      header('Content-Type: application/json; charset=utf-8');
      $hint = str_contains($e->getMessage(),'Access denied') ? 'اسم المستخدم أو كلمة المرور غلط في config.php'
        : (str_contains($e->getMessage(),'Unknown database') ? 'اسم قاعدة البيانات (DB_NAME) في config.php مش موجود — اتأكد من الاسم الكامل من hPanel'
        : 'اتأكد من قيم DB_HOST / DB_NAME / DB_USER / DB_PASS في ملف config.php');
      die(json_encode(['ok'=>false,'error'=>'فشل الاتصال بقاعدة البيانات: '.$hint,'details'=>$e->getMessage()], JSON_UNESCAPED_UNICODE));
    }
  }
  return $pdo;
}

/* ── Migration آمنة: CREATE IF NOT EXISTS + إضافة أعمدة ناقصة بدون مساس بالبيانات ── */
function db_install(): void {
  $q = fn($sql) => db()->exec($sql);

  $q("CREATE TABLE IF NOT EXISTS roles(
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(60) NOT NULL UNIQUE,
    label VARCHAR(120) NOT NULL,
    perms TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

  $q("CREATE TABLE IF NOT EXISTS users(
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(60) NOT NULL UNIQUE,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(160) DEFAULT '',
    phone VARCHAR(40) DEFAULT '',
    title VARCHAR(120) DEFAULT '',
    dept VARCHAR(120) DEFAULT '',
    role_id INT NOT NULL,
    extra_perms TEXT,
    pw_hash VARCHAR(255) NOT NULL,
    must_change_pw TINYINT DEFAULT 0,
    active TINYINT DEFAULT 1,
    device_exempt TINYINT DEFAULT 0,
    token_version INT DEFAULT 1,
    last_login DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL,
    FOREIGN KEY(role_id) REFERENCES roles(id)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

  $q("CREATE TABLE IF NOT EXISTS remember_tokens(
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token_hash VARCHAR(255) NOT NULL,
    token_version INT NOT NULL,
    expires DATETIME NOT NULL,
    INDEX(user_id),
    FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

  $q("CREATE TABLE IF NOT EXISTS devices(
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    device_uid VARCHAR(80) NOT NULL,
    name VARCHAR(160) DEFAULT '',
    ua VARCHAR(500) DEFAULT '',
    os VARCHAR(60) DEFAULT '',
    browser VARCHAR(60) DEFAULT '',
    approved TINYINT DEFAULT 0,
    last_used DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_dev(user_id, device_uid),
    FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

  $q("CREATE TABLE IF NOT EXISTS login_attempts(
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(120) DEFAULT '',
    ok TINYINT DEFAULT 0,
    reason VARCHAR(160) DEFAULT '',
    ip VARCHAR(60) DEFAULT '',
    ua VARCHAR(500) DEFAULT '',
    at DATETIME DEFAULT CURRENT_TIMESTAMP
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

  $q("CREATE TABLE IF NOT EXISTS settings(
    k VARCHAR(80) PRIMARY KEY,
    v TEXT
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

  $q("CREATE TABLE IF NOT EXISTS lists(
    id INT AUTO_INCREMENT PRIMARY KEY,
    kind VARCHAR(40) NOT NULL,
    label VARCHAR(160) NOT NULL,
    color VARCHAR(20) DEFAULT '#2563eb',
    sort INT DEFAULT 0,
    INDEX(kind)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

  $q("CREATE TABLE IF NOT EXISTS leaves(
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    kind ENUM('leave','permission') NOT NULL DEFAULT 'leave',
    date_from DATE NOT NULL,
    date_to DATE NULL,
    time_from VARCHAR(10) DEFAULT '',
    time_to VARCHAR(10) DEFAULT '',
    reason TEXT,
    status ENUM('pending','approved','rejected','cancelled') DEFAULT 'pending',
    decided_by INT NULL,
    decided_at DATETIME NULL,
    decision_note TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL,
    FOREIGN KEY(user_id) REFERENCES users(id)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

  $q("CREATE TABLE IF NOT EXISTS leave_history(
    id INT AUTO_INCREMENT PRIMARY KEY,
    leave_id INT NOT NULL,
    from_status VARCHAR(20), to_status VARCHAR(20),
    by_user VARCHAR(120), note TEXT,
    at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(leave_id) REFERENCES leaves(id) ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

  $q("CREATE TABLE IF NOT EXISTS notifications(
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    body VARCHAR(400) DEFAULT '',
    page VARCHAR(40) DEFAULT '',
    is_read TINYINT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX(user_id, is_read),
    FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

  $q("CREATE TABLE IF NOT EXISTS finance(
    id INT AUTO_INCREMENT PRIMARY KEY,
    kind ENUM('opening','income','expense') NOT NULL,
    category VARCHAR(120) DEFAULT '',
    amount DECIMAL(14,2) NOT NULL DEFAULT 0,
    currency VARCHAR(10) DEFAULT 'EGP',
    method VARCHAR(60) DEFAULT '',
    tx_date DATE NOT NULL,
    descr VARCHAR(300) DEFAULT '',
    note TEXT,
    client_id INT NULL,
    attachment VARCHAR(255) DEFAULT '',
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

  $q("CREATE TABLE IF NOT EXISTS attendance(
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    day DATE NOT NULL,
    in_time DATETIME NULL,
    out_time DATETIME NULL,
    status VARCHAR(30) DEFAULT 'present',
    ip VARCHAR(60) DEFAULT '',
    os VARCHAR(60) DEFAULT '',
    browser VARCHAR(60) DEFAULT '',
    device_uid VARCHAR(80) DEFAULT '',
    edited_by INT NULL,
    edit_reason VARCHAR(300) DEFAULT '',
    UNIQUE KEY uq_att(user_id, day),
    FOREIGN KEY(user_id) REFERENCES users(id)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

  $q("CREATE TABLE IF NOT EXISTS clients(
    id INT AUTO_INCREMENT PRIMARY KEY,
    name_ar VARCHAR(200) NOT NULL,
    name_en VARCHAR(200) DEFAULT '',
    phone VARCHAR(40) DEFAULT '',
    email VARCHAR(160) DEFAULT '',
    country VARCHAR(80) DEFAULT '',
    city VARCHAR(80) DEFAULT '',
    industry VARCHAR(120) DEFAULT '',
    services TEXT,
    depts TEXT,
    primary_assignee INT NULL,
    leader_id INT NULL,
    contract_value DECIMAL(14,2) DEFAULT 0,
    currency VARCHAR(10) DEFAULT 'EGP',
    pay_method VARCHAR(60) DEFAULT '',
    pay_cycle VARCHAR(20) DEFAULT 'monthly',
    start_date DATE NULL, end_date DATE NULL, renew_date DATE NULL,
    status_id INT NULL,
    notes_internal TEXT, notes_visible TEXT,
    files_link VARCHAR(300) DEFAULT '', wa_link VARCHAR(300) DEFAULT '',
    ad_link VARCHAR(300) DEFAULT '', site_link VARCHAR(300) DEFAULT '',
    last_contact DATE NULL,
    next_action VARCHAR(300) DEFAULT '', next_action_date DATE NULL,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

  $q("CREATE TABLE IF NOT EXISTS client_assignees(
    client_id INT NOT NULL, user_id INT NOT NULL,
    PRIMARY KEY(client_id, user_id),
    FOREIGN KEY(client_id) REFERENCES clients(id) ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

  $q("CREATE TABLE IF NOT EXISTS client_history(
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    kind ENUM('status','transfer','note') NOT NULL,
    from_v VARCHAR(200) DEFAULT '', to_v VARCHAR(200) DEFAULT '',
    note TEXT, by_user VARCHAR(120),
    at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(client_id) REFERENCES clients(id) ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

  $q("CREATE TABLE IF NOT EXISTS tasks(
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(300) NOT NULL,
    descr TEXT,
    client_id INT NULL,
    dept VARCHAR(120) DEFAULT '',
    status VARCHAR(30) DEFAULT 'new',
    priority ENUM('low','normal','high','urgent') DEFAULT 'normal',
    deadline DATE NULL,
    primary_assignee INT NULL,
    fin_value DECIMAL(12,2) NULL,
    points INT NULL,
    est_hours DECIMAL(6,1) NULL,
    sort INT DEFAULT 0,
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

  $q("CREATE TABLE IF NOT EXISTS task_assignees(
    task_id INT NOT NULL, user_id INT NOT NULL,
    PRIMARY KEY(task_id, user_id),
    FOREIGN KEY(task_id) REFERENCES tasks(id) ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

  $q("CREATE TABLE IF NOT EXISTS task_checklist(
    id INT AUTO_INCREMENT PRIMARY KEY,
    task_id INT NOT NULL, txt VARCHAR(300) NOT NULL, done TINYINT DEFAULT 0,
    FOREIGN KEY(task_id) REFERENCES tasks(id) ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

  $q("CREATE TABLE IF NOT EXISTS task_comments(
    id INT AUTO_INCREMENT PRIMARY KEY,
    task_id INT NOT NULL, user_id INT NOT NULL, txt TEXT NOT NULL,
    at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(task_id) REFERENCES tasks(id) ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

  $q("CREATE TABLE IF NOT EXISTS task_files(
    id INT AUTO_INCREMENT PRIMARY KEY,
    task_id INT NOT NULL, orig_name VARCHAR(255), path VARCHAR(255),
    by_user INT, at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(task_id) REFERENCES tasks(id) ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

  $q("CREATE TABLE IF NOT EXISTS leads(
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    phone VARCHAR(40) NOT NULL,
    wa VARCHAR(40) DEFAULT '',
    email VARCHAR(160) DEFAULT '',
    company VARCHAR(200) DEFAULT '',
    industry VARCHAR(120) DEFAULT '',
    country VARCHAR(80) DEFAULT '', city VARCHAR(80) DEFAULT '',
    source VARCHAR(120) DEFAULT '',
    campaign VARCHAR(160) DEFAULT '',
    service VARCHAR(160) DEFAULT '',
    assignee INT NULL,
    entered_at DATE NULL,
    last_contact DATE NULL,
    next_action VARCHAR(300) DEFAULT '',
    next_date DATE NULL,
    expected_value DECIMAL(14,2) NULL,
    status VARCHAR(30) DEFAULT 'new',
    lost_reason VARCHAR(200) DEFAULT '',
    notes TEXT,
    chat_link VARCHAR(300) DEFAULT '',
    converted_client_id INT NULL,
    converted_by INT NULL,
    converted_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL,
    UNIQUE KEY uq_lead_phone(phone)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

  $q("CREATE TABLE IF NOT EXISTS activity_log(
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    user_name VARCHAR(120) DEFAULT '',
    action VARCHAR(120) NOT NULL,
    entity VARCHAR(200) DEFAULT '',
    entity_id INT NULL,
    old_v VARCHAR(300) DEFAULT '',
    new_v VARCHAR(300) DEFAULT '',
    ip VARCHAR(60) DEFAULT '',
    at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX(user_id), INDEX(at)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");


  $q("CREATE TABLE IF NOT EXISTS client_payments(
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    amount DECIMAL(14,2) NOT NULL,
    currency VARCHAR(10) DEFAULT 'EGP',
    method VARCHAR(60) DEFAULT '',
    status ENUM('paid','due') DEFAULT 'paid',
    pay_date DATE NULL,
    due_date DATE NULL,
    note VARCHAR(300) DEFAULT '',
    cofin_id INT NULL,
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL,
    FOREIGN KEY(client_id) REFERENCES clients(id) ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

  $q("CREATE TABLE IF NOT EXISTS company_finance(
    id INT AUTO_INCREMENT PRIMARY KEY,
    kind ENUM('income','expense') NOT NULL,
    category VARCHAR(120) DEFAULT '',
    amount DECIMAL(14,2) NOT NULL,
    currency VARCHAR(10) DEFAULT 'EGP',
    method VARCHAR(60) DEFAULT '',
    tx_date DATE NOT NULL,
    descr VARCHAR(300) DEFAULT '',
    note TEXT,
    source ENUM('manual','payment','payroll') DEFAULT 'manual',
    ref_id INT NULL,
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

  $q("CREATE TABLE IF NOT EXISTS renewals(
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    phone VARCHAR(40) DEFAULT '',
    site_link VARCHAR(300) DEFAULT '',
    renew_date DATE NOT NULL,
    amount DECIMAL(12,2) NULL,
    notes VARCHAR(300) DEFAULT '',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

  /* Migration آمنة: إضافة عمود الراتب لو مش موجود */
  $col = $q2 = db()->query("SELECT COUNT(*) c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='users' AND COLUMN_NAME='salary'")->fetch();
  if(!(int)$col['c']) db()->exec("ALTER TABLE users ADD COLUMN salary DECIMAL(12,2) NULL AFTER dept");

  db_seed();
}

const ALL_PERMS = [
  'view_all_clients','view_assigned_clients','create_client','edit_client','delete_client',
  'view_financials','edit_financials','delete_financials',
  'view_all_tasks','view_assigned_tasks','create_task','edit_task','delete_task',
  'edit_task_value','view_task_value',
  'approve_leave','reject_leave',
  'manage_attendance','edit_attendance',
  'manage_users','reset_user_password','manage_roles','manage_permissions',
  'view_leads','manage_leads','convert_lead',
  'manage_whatsapp','manage_devices','manage_settings','company_finance','manage_renewals',
  'owner_finance','view_activity_log','manage_trash',
];

function db_seed(): void {
  $d = db();
  if (!$d->query("SELECT id FROM roles LIMIT 1")->fetch()) {
    $ins = $d->prepare("INSERT INTO roles(name,label,perms) VALUES(?,?,?)");
    $ins->execute(['super_admin','مدير رئيسي (Super Admin)', json_encode(ALL_PERMS)]);
    $ins->execute(['team_leader','مدير فريق', json_encode([
      'view_all_clients','create_client','edit_client','view_all_tasks','create_task','edit_task',
      'view_task_value','approve_leave','reject_leave','manage_attendance','view_leads','manage_leads','convert_lead','view_activity_log'])]);
    $ins->execute(['employee','موظف', json_encode(['view_assigned_clients','view_assigned_tasks','view_leads'])]);
    $ins->execute(['accountant','محاسب', json_encode(['view_all_clients','view_financials','view_assigned_tasks','view_activity_log'])]);
  }
  if (!$d->query("SELECT id FROM users LIMIT 1")->fetch()) {
    $rid = $d->query("SELECT id FROM roles WHERE name='super_admin'")->fetch()['id'];
    // حساب أولي واحد فقط - إجباري تغيير الباسورد أول دخول
    $d->prepare("INSERT INTO users(username,name,role_id,pw_hash,must_change_pw,device_exempt) VALUES(?,?,?,?,1,1)")
      ->execute(['owner','صاحب الشركة',$rid, password_hash('ChangeMe@123', PASSWORD_DEFAULT)]);
  }
  if (!$d->query("SELECT id FROM lists LIMIT 1")->fetch()) {
    $i = $d->prepare("INSERT INTO lists(kind,label,color,sort) VALUES(?,?,?,?)");
    $rows = [
      ['client_status','نشط','#16a34a',1], ['client_status','في انتظار البدء','#2563eb',2],
      ['client_status','متوقف مؤقتًا','#d97706',3], ['client_status','اقترب التجديد','#7c3aed',4],
      ['client_status','مغلق','#64748b',5], ['client_status','ملغي','#dc2626',6],
      ['department','Media Buying','#2563eb',1], ['department','Design','#9d174d',2],
      ['department','Content','#6d28d9',3], ['department','Web Development','#374151',4],
      ['department','Hosting','#065f46',5], ['department','Customer Service','#7c3aed',6],
      ['department','Account Management','#92400e',7],
      ['service','إدارة إعلانات ممولة','#2563eb',1], ['service','إدارة سوشيال ميديا','#16a34a',2],
      ['service','تصميم هوية بصرية','#9d174d',3], ['service','موشن جرافيك','#d97706',4],
      ['service','تطوير موقع','#374151',5], ['service','استضافة وصيانة','#065f46',6],
      ['service','محتوى شهري','#6d28d9',7], ['service','إدارة حساب كامل','#92400e',8],
      ['fin_cat','عقود عملاء','#16a34a',1], ['fin_cat','رواتب','#dc2626',2],
      ['fin_cat','إعلانات','#2563eb',3], ['fin_cat','استضافة وأدوات','#7c3aed',4],
      ['fin_cat','إيجار ومرافق','#d97706',5], ['fin_cat','مصروفات شخصية','#64748b',6], ['fin_cat','أخرى','#94a3b8',7],
      ['lead_source','إعلان فيسبوك','#2563eb',1], ['lead_source','واتساب','#16a34a',2],
      ['lead_source','ترشيح','#d97706',3], ['lead_source','الموقع','#7c3aed',4], ['lead_source','اتصال مباشر','#64748b',5],
    ];
    foreach ($rows as $r) $i->execute($r);
  }
  db()->prepare("UPDATE roles SET perms=? WHERE name='super_admin'")->execute([json_encode(ALL_PERMS)]);
  $defaults = [
    'block_mobile' => '1', 'block_tablet' => '0', 'device_approval' => '0',
    'attendance_ips' => '', 'work_start' => '10:00', 'late_after' => '10:15',
    'wa_verify_token' => bin2hex(random_bytes(8)),
  ];
  $ins = db()->prepare("INSERT IGNORE INTO settings(k,v) VALUES(?,?)");
  foreach ($defaults as $k=>$v) $ins->execute([$k,$v]);
}
