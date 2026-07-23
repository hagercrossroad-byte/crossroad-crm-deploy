<?php
/* ══════════════════════════════════════════════
   CrossRoad CRM — إعدادات قاعدة البيانات
   عدّل القيم دي ببيانات MySQL من لوحة Hostinger:
   (Databases → MySQL Databases)
   ══════════════════════════════════════════════ */
define('DB_HOST', 'localhost');
define('DB_NAME', 'u000000000_crm');   // اسم قاعدة البيانات
define('DB_USER', 'u000000000_crm');   // اسم المستخدم
define('DB_PASS', 'ضع-كلمة-المرور-هنا'); // كلمة المرور

define('APP_NAME', 'CrossRoad CRM');
define('SESSION_TIMEOUT_MIN', 480);   // مدة الجلسة بالدقائق (8 ساعات)
define('UPLOAD_DIR', __DIR__ . '/uploads');
define('MAX_UPLOAD_MB', 8);
