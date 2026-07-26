<!DOCTYPE html>
<html lang="ar" dir="rtl" data-theme="light">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CrossRoad CRM</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.44.0/tabler-icons.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<style>
@import url('https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;900&family=Barlow:wght@700;900&display=swap');
:root{--bg:#f0f4fa;--card:#fff;--sidebar:#0a1628;--border:#dde5f0;--text:#0d2540;--sub:#5a7a9a;--muted:#9ab0c4;--accent:#2563eb;--input:#f8fafd;--hover:#f4f7fb;--shadow:0 2px 12px rgba(13,37,64,.08);--green:#16a34a;--red:#dc2626;--amber:#d97706;--purple:#7c3aed}
[data-theme="dark"]{--bg:#0f172a;--card:#1e293b;--sidebar:#080f1e;--border:#2d3a50;--text:#e2e8f0;--sub:#94a3b8;--muted:#64748b;--accent:#3b82f6;--input:#1a2540;--hover:#253047;--shadow:0 2px 12px rgba(0,0,0,.3)}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Cairo',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;font-size:13px}
.hide{display:none!important}
a{color:var(--accent)}
/* LOGIN */
#login-screen{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
.login-card{background:var(--card);border-radius:22px;border:1px solid var(--border);padding:34px;box-shadow:var(--shadow);width:100%;max-width:420px}
.logo-box{width:56px;height:56px;background:linear-gradient(135deg,#1e3a5f,#2563eb);border-radius:15px;display:flex;align-items:center;justify-content:center;margin:0 auto 10px}
.logo-txt{font-family:'Barlow',sans-serif;font-weight:900;font-size:19px;color:#fff}
.form-lbl{font-size:11px;color:var(--sub);font-weight:700;display:block;margin-bottom:5px}
input,select,textarea{width:100%;padding:10px 12px;border:1.5px solid var(--border);border-radius:11px;font-size:13px;font-family:'Cairo',sans-serif;color:var(--text);background:var(--input);outline:none;margin-bottom:11px}
input:focus,select:focus,textarea:focus{border-color:var(--accent);box-shadow:0 0 0 3px rgba(37,99,235,.1)}
textarea{resize:vertical;min-height:70px}
input[type=checkbox]{width:auto;margin:0}
.btn{padding:9px 16px;border-radius:10px;font-size:13px;font-family:'Cairo',sans-serif;font-weight:700;cursor:pointer;border:none;display:inline-flex;align-items:center;gap:6px;justify-content:center}
.btn:disabled{opacity:.5;cursor:wait}
.btn-p{background:linear-gradient(135deg,#1e3a5f,#2563eb);color:#fff}
.btn-g{background:rgba(22,163,74,.12);color:#16a34a}
.btn-r{background:rgba(220,38,38,.1);color:#dc2626}
.btn-gh{background:var(--hover);color:var(--sub);border:1.5px solid var(--border)}
.btn-sm{padding:6px 11px;font-size:12px;border-radius:8px}
.btn-full{width:100%}
/* APP SHELL */
#app{min-height:100vh;display:flex}
.sidebar{width:218px;background:var(--sidebar);display:flex;flex-direction:column;flex-shrink:0;position:sticky;top:0;height:100vh;overflow-y:auto;z-index:5}
.sb-top{padding:16px 13px;border-bottom:1px solid rgba(255,255,255,.07);display:flex;align-items:center;gap:9px}
.sb-av{width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,#1e3a5f,#2563eb);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:14px}
.sb-name{color:#fff;font-size:12.5px;font-weight:700}
.sb-role{color:rgba(255,255,255,.45);font-size:10px}
.sb-nav{flex:1;padding:10px 8px}
.sb-item{width:100%;padding:9px 12px;border-radius:9px;border:none;background:transparent;color:rgba(255,255,255,.6);font-family:'Cairo',sans-serif;font-size:12.5px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:9px;margin-bottom:2px;text-align:right}
.sb-item:hover{background:rgba(255,255,255,.06);color:#fff}
.sb-item.active{background:var(--accent);color:#fff}
.sb-item .bdg{margin-right:auto;background:#dc2626;color:#fff;font-size:9.5px;padding:1px 6px;border-radius:8px}
.sb-bottom{padding:10px 8px;border-top:1px solid rgba(255,255,255,.07)}
.main{flex:1;min-width:0;display:flex;flex-direction:column}
.topbar{background:var(--card);border-bottom:1.5px solid var(--border);padding:11px 20px;display:flex;align-items:center;gap:12px;position:sticky;top:0;z-index:4}
.topbar h1{font-size:15px;font-weight:900}
.top-actions{margin-right:auto;display:flex;gap:8px;align-items:center}
.ic-btn{width:34px;height:34px;border-radius:9px;background:var(--hover);border:1.5px solid var(--border);cursor:pointer;display:flex;align-items:center;justify-content:center;color:var(--sub);font-size:15px;position:relative}
.ndot{position:absolute;top:6px;left:6px;min-width:15px;height:15px;background:#dc2626;border-radius:8px;color:#fff;font-size:9px;font-weight:700;display:flex;align-items:center;justify-content:center;padding:0 3px}
.content{padding:18px 20px;flex:1}
/* CARDS & TABLES */
.stats{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:10px;margin-bottom:16px}
.stat{background:var(--card);border:1.5px solid var(--border);border-radius:13px;padding:14px}
.stat-lbl{font-size:11px;color:var(--sub);font-weight:700;display:flex;gap:6px;align-items:center}
.stat-val{font-size:20px;font-weight:900;margin-top:5px;font-family:'Barlow','Cairo',sans-serif}
.card{background:var(--card);border:1.5px solid var(--border);border-radius:13px;padding:16px;margin-bottom:14px}
.card h3{font-size:13px;font-weight:900;margin-bottom:12px;display:flex;align-items:center;gap:7px}
.tbl-wrap{background:var(--card);border:1.5px solid var(--border);border-radius:13px;overflow:auto}
table{width:100%;border-collapse:collapse}
th{font-size:11px;color:var(--sub);font-weight:700;text-align:right;padding:10px 12px;border-bottom:1.5px solid var(--border);white-space:nowrap;background:var(--hover)}
td{font-size:12px;padding:9px 12px;border-bottom:1px solid var(--border);white-space:nowrap;vertical-align:middle}
tbody tr:hover{background:var(--hover)}
tr:last-child td{border-bottom:none}
.pill{display:inline-flex;align-items:center;gap:4px;font-size:11px;font-weight:700;padding:3px 10px;border-radius:8px;color:#fff}
.tag{display:inline-block;font-size:10px;background:var(--hover);color:var(--sub);padding:2px 8px;border-radius:6px;margin:1px;font-weight:700}
.toolbar{display:flex;gap:8px;flex-wrap:wrap;align-items:center;margin-bottom:12px}
.toolbar input,.toolbar select{width:auto;min-width:130px;margin:0;padding:8px 11px;font-size:12px}
.toolbar .grow{flex:1;min-width:170px}
.empty{text-align:center;padding:40px;color:var(--muted)}
.empty i{font-size:34px;display:block;margin-bottom:8px;opacity:.4}
/* MODAL */
.overlay{position:fixed;inset:0;background:rgba(10,22,40,.55);display:none;align-items:center;justify-content:center;padding:18px;z-index:50}
.overlay.open{display:flex}
.modal{background:var(--card);border-radius:17px;padding:22px;width:100%;max-width:520px;max-height:92vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.3)}
.modal.lg{max-width:780px}
.modal-hd{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px}
.modal-title{font-size:14px;font-weight:900}
.mclose{width:28px;height:28px;border-radius:7px;background:var(--hover);border:none;cursor:pointer;color:var(--sub)}
.grid2{display:grid;grid-template-columns:1fr 1fr;gap:0 10px}
@media(max-width:620px){.grid2{grid-template-columns:1fr}}
.chk-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:6px;margin-bottom:11px}
.chk{display:flex;align-items:center;gap:6px;font-size:11.5px;font-weight:600;background:var(--input);border:1.5px solid var(--border);border-radius:9px;padding:7px 9px;cursor:pointer}
.chk.on{border-color:var(--accent);background:rgba(37,99,235,.07)}
.sec{font-size:11px;font-weight:900;color:var(--accent);margin:12px 0 8px;display:flex;gap:6px;align-items:center}
.kv{display:flex;justify-content:space-between;gap:10px;font-size:12px;padding:6px 0;border-bottom:1px dashed var(--border)}
.kv b{color:var(--sub);flex-shrink:0}
.kv span{text-align:left;word-break:break-word}
.hist{display:flex;gap:9px;padding:8px 0;border-bottom:1px dashed var(--border);font-size:12px}
.hist:last-child{border-bottom:none}
.hdot{width:8px;height:8px;border-radius:50%;margin-top:5px;flex-shrink:0;background:var(--accent)}
.hmeta{font-size:10px;color:var(--muted)}
/* TOAST */
#toast{position:fixed;bottom:22px;left:50%;transform:translateX(-50%) translateY(80px);background:#0d2540;color:#fff;padding:11px 20px;border-radius:12px;font-size:12.5px;font-weight:700;transition:transform .3s;z-index:99;max-width:90vw}
/* KANBAN */
.kanban{display:flex;gap:12px;overflow-x:auto;padding-bottom:10px;align-items:flex-start}
.kcol{background:var(--hover);border:1.5px solid var(--border);border-radius:13px;min-width:250px;width:250px;flex-shrink:0}
.kcol-hd{padding:10px 12px;font-size:12px;font-weight:900;display:flex;align-items:center;gap:7px;border-bottom:1.5px solid var(--border)}
.kcol-cnt{margin-right:auto;background:var(--card);border-radius:7px;padding:1px 7px;font-size:10px;color:var(--sub)}
.kcards{padding:8px;min-height:60px;display:flex;flex-direction:column;gap:8px}
.kcard{background:var(--card);border:1.5px solid var(--border);border-radius:11px;padding:10px;cursor:grab;box-shadow:var(--shadow)}
.kcard:active{cursor:grabbing}
.kcard.dragging{opacity:.4}
.kcol.dragover .kcards{background:rgba(37,99,235,.06);border-radius:0 0 11px 11px}
.kcard-title{font-size:12.5px;font-weight:700;margin-bottom:5px}
.kmeta{display:flex;flex-wrap:wrap;gap:4px;font-size:10px;color:var(--muted);align-items:center}
.kpri{font-size:9.5px;font-weight:900;padding:1px 7px;border-radius:6px}
.kval{background:rgba(22,163,74,.1);color:#16a34a;font-size:10px;font-weight:800;padding:2px 7px;border-radius:6px}
/* NOTIF PANEL */
.notif-item{display:flex;gap:10px;padding:10px 4px;border-bottom:1px dashed var(--border);font-size:12px}
.notif-item.unread{background:rgba(37,99,235,.05)}
/* ATTENDANCE BIG BUTTON */
.att-hero{background:linear-gradient(135deg,#0a1628,#1e3a5f);color:#fff;border-radius:16px;padding:24px;text-align:center;margin-bottom:16px}
.att-time{font-family:'Barlow',sans-serif;font-size:38px;font-weight:900}
.att-btn{margin-top:12px;padding:13px 36px;font-size:15px;border-radius:13px}
@media(max-width:860px){.sidebar{position:fixed;right:-230px;transition:right .25s}.sidebar.open{right:0}.main{width:100%}}
@media print{.sidebar,.topbar,.toolbar{display:none!important}}
</style>
</head>
<body>

<!-- ══ LOGIN ══ -->
<div id="login-screen">
  <div class="login-card">
    <div style="text-align:center;margin-bottom:22px">
      <div class="logo-box"><span class="logo-txt">CR</span></div>
      <div style="font-family:'Barlow',sans-serif;font-size:19px;font-weight:900">CROSS<span style="color:var(--accent)">ROAD</span></div>
      <div style="font-size:11px;color:var(--muted)">نظام إدارة الشركة — تسجيل الدخول</div>
    </div>
    <form id="login-form" autocomplete="on">
      <label class="form-lbl">اسم المستخدم</label>
      <input id="lg-user" name="username" autocomplete="username" required>
      <label class="form-lbl">كلمة المرور</label>
      <div style="position:relative">
        <input id="lg-pass" name="password" type="password" autocomplete="current-password" required style="padding-left:38px">
        <button type="button" onclick="togglePw()" style="position:absolute;left:8px;top:9px;background:none;border:none;cursor:pointer;color:var(--muted)"><i class="ti ti-eye" id="pw-eye"></i></button>
      </div>
      <label style="display:flex;align-items:center;gap:7px;font-size:12px;color:var(--sub);margin-bottom:12px;cursor:pointer">
        <input type="checkbox" id="lg-remember" style="margin:0"> تذكرني على الجهاز ده
      </label>
      <button class="btn btn-p btn-full" id="lg-btn" type="submit"><i class="ti ti-login"></i> دخول</button>
      <div id="lg-err" style="color:#dc2626;font-size:12px;font-weight:700;margin-top:10px;text-align:center"></div>
    </form>
  </div>
</div>

<!-- ══ FORCE PASSWORD CHANGE ══ -->
<div id="forcepw-screen" class="hide" style="min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px">
  <div class="login-card">
    <h3 style="margin-bottom:6px">🔐 تغيير كلمة المرور مطلوب</h3>
    <p style="font-size:12px;color:var(--sub);margin-bottom:14px">لأمان حسابك، لازم تحدد كلمة مرور جديدة قبل استخدام النظام.</p>
    <label class="form-lbl">كلمة المرور الجديدة (8 أحرف على الأقل)</label>
    <input id="fp-new" type="password" autocomplete="new-password">
    <label class="form-lbl">تأكيد كلمة المرور</label>
    <input id="fp-confirm" type="password" autocomplete="new-password">
    <button class="btn btn-p btn-full" onclick="doForcePw(this)"><i class="ti ti-check"></i> حفظ والمتابعة</button>
  </div>
</div>

<!-- ══ APP ══ -->
<div id="app" class="hide">
  <div class="sidebar" id="sidebar">
    <div class="sb-top">
      <div class="sb-av" id="sb-av">؟</div>
      <div><div class="sb-name" id="sb-name"></div><div class="sb-role" id="sb-role"></div></div>
    </div>
    <div class="sb-nav" id="sb-nav"></div>
    <div class="sb-bottom">
      <button class="sb-item" onclick="openMyPw()"><i class="ti ti-key"></i> تغيير كلمة مروري</button>
      <button class="sb-item" onclick="logout()"><i class="ti ti-logout"></i> تسجيل خروج</button>
    </div>
  </div>
  <div class="main">
    <div class="topbar">
      <button class="ic-btn" style="display:none" id="menu-btn" onclick="document.getElementById('sidebar').classList.toggle('open')"><i class="ti ti-menu-2"></i></button>
      <h1 id="page-title">الداشبورد</h1>
      <div class="top-actions">
        <button class="ic-btn" onclick="openNotifs()" title="التنبيهات"><i class="ti ti-bell"></i><div class="ndot hide" id="notif-dot">0</div></button>
        <button class="ic-btn" onclick="toggleTheme()" id="theme-btn"><i class="ti ti-moon-stars"></i></button>
      </div>
    </div>
    <div class="content" id="content"></div>
  </div>
</div>

<!-- generic modal container -->
<div class="overlay" id="modal-ov"><div class="modal" id="modal-box"></div></div>
<div id="toast"></div>
<script>
/* ══ CORE ══ */
let ME=null, CSRF=null, USERS_OPT=[], LISTS={};
const $=id=>document.getElementById(id);
const esc=s=>String(s==null?'':s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
const fD=d=>{if(!d)return '—';const p=String(d).split(' ')[0].split('T')[0].split('-');return p.length===3?`${p[2]}/${p[1]}/${p[0]}`:d};
const fDT=d=>{if(!d)return '—';const t=String(d).replace('T',' ').split(' ');return fD(t[0])+(t[1]?' '+t[1].slice(0,5):'')};
const fM=n=>n==null?'—':Number(n).toLocaleString('en-US');
let _memUid=null;
function deviceUid(){try{let u=localStorage.getItem('crm_device_uid');if(!u){u='d'+Date.now().toString(36)+Math.random().toString(36).slice(2,12);localStorage.setItem('crm_device_uid',u)}return u}catch(e){if(!_memUid)_memUid='d'+Date.now().toString(36)+Math.random().toString(36).slice(2,12);return _memUid}}
function toast(m){const t=$('toast');t.textContent=m;t.style.transform='translateX(-50%) translateY(0)';clearTimeout(t._t);t._t=setTimeout(()=>t.style.transform='translateX(-50%) translateY(80px)',2800)}

async function api(a, data=null, opts={}){
  const isForm = data instanceof FormData;
  const res = await fetch('api.php?a='+a, {
    method: data!==null?'POST':'GET',
    headers: Object.assign(data!==null&&!isForm?{'Content-Type':'application/json'}:{}, CSRF?{'X-CSRF':CSRF}:{}),
    body: data===null?undefined:(isForm?data:JSON.stringify(data)),
    credentials:'same-origin'
  });
  const j = await res.json().catch(()=>({ok:false,error:'خطأ في الاتصال بالسيرفر — افتح api.php?a=ping في المتصفح لمعرفة التفاصيل'}));
  if(!j.ok){
    if(res.status===401 && a!=='me' && a!=='auto_login'){ showLogin(); toast('⚠️ انتهت الجلسة'); }
    throw new Error(j.error||'حصل خطأ');
  }
  return j;
}
async function tryApi(a,data,btn){ // زر بيتقفل أثناء التنفيذ + رسالة خطأ واضحة
  if(btn){btn.disabled=true}
  try{ return await api(a,data); }
  catch(e){ toast('⚠️ '+e.message); throw e; }
  finally{ if(btn) btn.disabled=false; }
}

/* ══ AUTH FLOW ══ */
function togglePw(){const p=$('lg-pass');p.type=p.type==='password'?'text':'password';$('pw-eye').className='ti '+(p.type==='password'?'ti-eye':'ti-eye-off')}
function showLogin(){ $('app').classList.add('hide'); $('forcepw-screen').classList.add('hide'); $('login-screen').classList.remove('hide'); }
async function boot(){
  try{
    let r = await api('me');
    if(!r.user) r = await api('auto_login',{device_uid:deviceUid()}).catch(()=>({user:null}));
    if(r.user){ ME=r.user; CSRF=r.csrf; return enter(); }
  }catch(e){}
  showLogin();
}
$('login-form').addEventListener('submit', async ev=>{
  ev.preventDefault();
  const btn=$('lg-btn'); btn.disabled=true; $('lg-err').textContent='';
  try{
    const r = await api('login',{username:$('lg-user').value.trim(),password:$('lg-pass').value,remember:$('lg-remember').checked?1:0,device_uid:deviceUid()});
    ME=r.user; CSRF=r.csrf; $('lg-pass').value='';
    enter();
  }catch(e){ $('lg-err').textContent=e.message; }
  btn.disabled=false;
});
function enter(){
  $('login-screen').classList.add('hide');
  if(ME.must_change_pw){ $('forcepw-screen').classList.remove('hide'); $('forcepw-screen').style.display='flex'; return; }
  $('forcepw-screen').classList.add('hide');
  $('app').classList.remove('hide');
  $('sb-av').textContent = ME.name.trim()[0];
  $('sb-name').textContent = ME.name;
  $('sb-role').textContent = ME.role_label + (ME.title?' · '+ME.title:'');
  if(window.innerWidth<860) $('menu-btn').style.display='flex';
  buildNav(); loadCommon(); go('dashboard'); pollNotifs();
}
async function doForcePw(btn){
  const n=$('fp-new').value, c=$('fp-confirm').value;
  try{ await tryApi('change_my_pw',{new:n,confirm:c},btn); toast('✓ تم حفظ كلمة المرور'); ME.must_change_pw=0; $('forcepw-screen').style.display=''; enter(); }catch(e){}
}
async function logout(){ try{await api('logout',{})}catch(e){} location.reload(); }
function can(p){ return ME && ME.perms.includes(p); }
function toggleTheme(){const d=document.documentElement;const dark=d.getAttribute('data-theme')!=='dark';d.setAttribute('data-theme',dark?'dark':'light');$('theme-btn').innerHTML=dark?'<i class="ti ti-sun"></i>':'<i class="ti ti-moon-stars"></i>'}

async function loadCommon(){
  try{ USERS_OPT=(await api('users.options')).users; }catch(e){USERS_OPT=[]}
  try{ LISTS=(await api('lists.all')).lists; }catch(e){LISTS={}}
}
const uname=id=>{const u=USERS_OPT.find(x=>x.id==id);return u?u.name:'—'};
const listBy=k=>LISTS[k]||[];
const listLabel=(k,id)=>{const l=listBy(k).find(x=>x.id==id);return l?l.label:'—'};
const listColor=(k,id)=>{const l=listBy(k).find(x=>x.id==id);return l?l.color:'#64748b'};

/* ══ NAV ══ */
const PAGES=[
  {id:'dashboard', ic:'ti-layout-dashboard', lb:'الداشبورد', show:()=>true},
  {id:'attendance', ic:'ti-calendar-stats', lb:'الحضور', show:()=>true},
  {id:'leaves', ic:'ti-beach', lb:'الإجازات والأذونات', show:()=>true},
  {id:'tasks', ic:'ti-checklist', lb:'المهام', show:()=>can('view_all_tasks')||can('view_assigned_tasks')},
  {id:'clients', ic:'ti-building-store', lb:'العملاء الحاليين', show:()=>can('view_all_clients')||can('view_assigned_clients')},
  {id:'leads', ic:'ti-target-arrow', lb:'Leads العملاء المحتملين', show:()=>can('view_leads')},
  {id:'cofinance', ic:'ti-building-bank', lb:'حسابات الشركة', show:()=>can('company_finance')},
  {id:'renewals', ic:'ti-refresh-alert', lb:'عملاء تجديد', show:()=>can('manage_renewals'), badge:'renewBadge'},
  {id:'finance', ic:'ti-lock', lb:'فلوسي الخاصة 🔒', show:()=>can('owner_finance')},
  {id:'users', ic:'ti-users', lb:'المستخدمون والصلاحيات', show:()=>can('manage_users')},
  {id:'devices', ic:'ti-devices', lb:'الأجهزة المعتمدة', show:()=>can('manage_devices')},
  {id:'log', ic:'ti-history', lb:'سجل النشاط', show:()=>true},
  {id:'trash', ic:'ti-trash', lb:'سلة المهملات', show:()=>can('manage_trash')},
  {id:'settings', ic:'ti-settings', lb:'الإعدادات', show:()=>can('manage_settings')},
];
let CUR='dashboard';
function buildNav(){
  $('sb-nav').innerHTML = PAGES.filter(p=>p.show()).map(p=>
    `<button class="sb-item" id="nav-${p.id}" onclick="go('${p.id}')"><i class="ti ${p.ic}"></i> ${p.lb}${p.badge?`<span class="bdg hide" id="bdg-${p.id}"></span>`:''}</button>`).join('');
  updateRenewBadge();
}
function go(id){
  CUR=id;
  document.querySelectorAll('.sb-item').forEach(b=>b.classList.remove('active'));
  const nb=$('nav-'+id); if(nb) nb.classList.add('active');
  const pg=PAGES.find(p=>p.id===id);
  $('page-title').textContent=pg?pg.lb:'';
  $('sidebar').classList.remove('open');
  $('content').innerHTML='<div class="empty"><i class="ti ti-loader-2"></i>جارِ التحميل...</div>';
  ({dashboard:pgDash, attendance:pgAtt, leaves:pgLeaves, tasks:pgTasks, clients:pgClients, leads:pgLeads,
    finance:pgFin, cofinance:pgCoFin, renewals:pgRenewals, users:pgUsers, devices:pgDevices, log:pgLog, trash:pgTrash, settings:pgSettings}[id]||pgDash)();
}

/* ══ MODAL HELPERS ══ */
function openModal(html, lg=false){ $('modal-box').className='modal'+(lg?' lg':''); $('modal-box').innerHTML=html; $('modal-ov').classList.add('open'); }
function closeModal(){ $('modal-ov').classList.remove('open'); }
$('modal-ov').addEventListener('click',e=>{if(e.target.id==='modal-ov')closeModal()});
function mHead(t){ return `<div class="modal-hd"><div class="modal-title">${t}</div><button class="mclose" onclick="closeModal()">✕</button></div>`; }
function confirmModal(title, msg, onOk, typeName=null){
  openModal(mHead(title)+`<p style="font-size:12.5px;color:var(--sub);margin-bottom:12px">${msg}</p>
    ${typeName?`<label class="form-lbl">اكتب "${esc(typeName)}" للتأكيد</label><input id="cf-name">`:''}
    <div style="display:flex;gap:8px"><button class="btn btn-r" style="flex:1" id="cf-ok"><i class="ti ti-trash"></i> تأكيد</button>
    <button class="btn btn-gh" onclick="closeModal()">إلغاء</button></div>`);
  $('cf-ok').onclick=async function(){ await onOk(this, typeName?$('cf-name').value:null); };
}
function chkGrid(items, selected, name, valKey='id', lbKey='label'){
  return `<div class="chk-grid">`+items.map(it=>{
    const v=typeof it==='object'?it[valKey]:it, l=typeof it==='object'?it[lbKey]:it;
    const on=selected.map(String).includes(String(v));
    return `<label class="chk${on?' on':''}" onclick="this.classList.toggle('on')"><input type="checkbox" name="${name}" value="${esc(v)}" ${on?'checked':''} style="margin:0">${esc(l)}</label>`;
  }).join('')+`</div>`;
}
const chkVals=name=>[...document.querySelectorAll(`input[name="${name}"]:checked`)].map(i=>i.value);
function csv(name, rows){
  const c='\ufeff'+rows.map(r=>r.map(v=>`"${String(v==null?'':v).replace(/"/g,'""')}"`).join(',')).join('\n');
  const a=document.createElement('a'); a.href=URL.createObjectURL(new Blob([c],{type:'text/csv;charset=utf-8'})); a.download=name; a.click();
}

/* ══ NOTIFICATIONS ══ */
async function pollNotifs(){
  try{ const r=await api('notifs.list'); const d=$('notif-dot');
    if(r.unread>0){d.textContent=r.unread;d.classList.remove('hide')} else d.classList.add('hide');
  }catch(e){}
  setTimeout(pollNotifs, 60000);
}
async function openNotifs(){
  const r=await api('notifs.list');
  openModal(mHead('🔔 التنبيهات')+(r.notifs.length?r.notifs.map(n=>
    `<div class="notif-item ${n.is_read?'':'unread'}"><div><div style="font-weight:700">${esc(n.title)}</div>
     <div style="color:var(--sub)">${esc(n.body)}</div><div class="hmeta">${fDT(n.created_at)}</div></div></div>`).join('')
    :'<div class="empty"><i class="ti ti-bell-off"></i>لا توجد تنبيهات</div>'));
  await api('notifs.read_all',{}); $('notif-dot').classList.add('hide');
}
function openMyPw(){
  openModal(mHead('🔐 تغيير كلمة مروري')+`
    <label class="form-lbl">كلمة المرور الحالية</label><input id="mp-old" type="password" autocomplete="current-password">
    <label class="form-lbl">الجديدة (8+ أحرف)</label><input id="mp-new" type="password" autocomplete="new-password">
    <label class="form-lbl">تأكيد الجديدة</label><input id="mp-cf" type="password" autocomplete="new-password">
    <button class="btn btn-p btn-full" onclick="tryApi('change_my_pw',{old:$('mp-old').value,new:$('mp-new').value,confirm:$('mp-cf').value},this).then(()=>{toast('✓ تم التغيير');closeModal()})">حفظ</button>`);
}

/* ══ DASHBOARD ══ */
async function pgDash(){
  const r=await api('dash.summary');
  let cards=[];
  cards.push({lb:'مهامي المفتوحة',v:r.my_open_tasks,ic:'ti-checklist',go:'tasks'});
  cards.push({lb:'طلباتي المعلقة',v:r.my_pending_leaves,ic:'ti-beach',go:'leaves'});
  if(r.pending_approvals!=null) cards.push({lb:'طلبات تنتظر قرارك',v:r.pending_approvals,ic:'ti-inbox',go:'leaves',hot:r.pending_approvals>0});
  if(r.clients!=null) cards.push({lb:'العملاء',v:r.clients,ic:'ti-building-store',go:'clients'});
  if(r.leads_new!=null) cards.push({lb:'Leads جديدة',v:r.leads_new,ic:'ti-target-arrow',go:'leads'});
  if(r.present_today!=null) cards.push({lb:'الحاضرون اليوم',v:r.present_today,ic:'ti-user-check',go:'attendance'});
  if(r.pending_devices!=null && r.pending_devices>0) cards.push({lb:'أجهزة تنتظر الاعتماد',v:r.pending_devices,ic:'ti-devices',go:'devices',hot:1});
  const att=r.attendance;
  $('content').innerHTML=`
    <div class="att-hero">
      <div style="font-size:12px;opacity:.7">أهلًا ${esc(ME.name)} 👋</div>
      <div class="att-time" id="clock">--:--</div>
      <div style="font-size:11px;opacity:.6" id="today-date"></div>
      ${!att? `<button class="btn btn-g att-btn" onclick="checkIn(this)"><i class="ti ti-fingerprint"></i> تسجيل الحضور</button>`
       : !att.out_time? `<div style="margin-top:8px;font-size:12px">✅ حضرت الساعة ${att.in_time.slice(11,16)} ${att.status==='late'?'(متأخر)':''}</div>
          <button class="btn btn-r att-btn" onclick="checkOut(this)"><i class="ti ti-logout"></i> تسجيل الانصراف</button>`
       : `<div style="margin-top:8px;font-size:12px">✅ يوم مكتمل: ${att.in_time.slice(11,16)} → ${att.out_time.slice(11,16)}</div>`}
    </div>
    <div class="stats">${cards.map(c=>`<div class="stat" style="cursor:pointer${c.hot?';border-color:#dc2626':''}" onclick="go('${c.go}')">
      <div class="stat-lbl"><i class="ti ${c.ic}"></i> ${c.lb}</div><div class="stat-val">${c.v}</div></div>`).join('')}</div>`;
  clockTick();
}
function clockTick(){ const el=$('clock'); if(!el) return;
  const n=new Date(); el.textContent=n.toTimeString().slice(0,5);
  const days=['الأحد','الاثنين','الثلاثاء','الأربعاء','الخميس','الجمعة','السبت'];
  if($('today-date')) $('today-date').textContent=days[n.getDay()]+'، '+n.toLocaleDateString('ar-EG');
  setTimeout(clockTick,20000);
}
async function checkIn(b){ const r=await tryApi('att.checkin',{device_uid:deviceUid()},b); toast(r.status==='late'?'⚠️ تم تسجيل حضورك (متأخر) '+r.time:'✓ تم تسجيل حضورك '+r.time); go(CUR); }
async function checkOut(b){ const r=await tryApi('att.checkout',{},b); toast('✓ تم تسجيل انصرافك '+r.time); go(CUR); }

/* ══ ATTENDANCE ══ */
const ATT_ST={present:['حاضر','#16a34a'],late:['متأخر','#d97706'],absent:['غائب','#dc2626'],leave:['إجازة','#7c3aed'],permission:['إذن','#2563eb'],wfh:['من المنزل','#0ea5e9']};
async function pgAtt(){
  const t=await api('att.today');
  let html=`<div class="card"><h3><i class="ti ti-fingerprint"></i> حضوري اليوم</h3>
    ${!t.today? (t.ip_ok?`<button class="btn btn-g" onclick="checkIn(this)"><i class="ti ti-fingerprint"></i> تسجيل الحضور الآن</button>`
      :`<div style="color:#dc2626;font-weight:700">⚠️ تسجيل الحضور متاح من شبكة الشركة فقط</div>`)
    : `<div class="kv"><b>الحضور</b><span>${t.today.in_time?t.today.in_time.slice(11,16):'—'} ${t.today.status==='late'?'<span class="tag" style="color:#d97706">متأخر</span>':''}</span></div>
       <div class="kv"><b>الانصراف</b><span>${t.today.out_time?t.today.out_time.slice(11,16):'لسه'}</span></div>
       ${!t.today.out_time?`<button class="btn btn-r btn-sm" style="margin-top:10px" onclick="checkOut(this)">تسجيل الانصراف</button>`:''}`}
  </div>`;
  if(can('manage_attendance')){
    const today=new Date().toISOString().slice(0,10);
    html+=`<div class="card"><h3><i class="ti ti-calendar-stats"></i> لوحة الحضور (الإدارة)</h3>
      <div class="toolbar">
        <input type="date" id="att-from" value="${today}"><input type="date" id="att-to" value="${today}">
        <button class="btn btn-p btn-sm" onclick="loadAttReport()">عرض</button>
        <button class="btn btn-gh btn-sm" onclick="attCSV()"><i class="ti ti-file-spreadsheet"></i> CSV</button>
        ${can('edit_attendance')?`<button class="btn btn-gh btn-sm" onclick="attManual()"><i class="ti ti-plus"></i> سجل يدوي</button>`:''}
      </div>
      <div id="att-summary" class="stats"></div>
      <div class="tbl-wrap"><table><thead><tr><th>الموظف</th><th>اليوم</th><th>حضور</th><th>انصراف</th><th>ساعات</th><th>الحالة</th><th>الجهاز</th><th>IP</th>${can('edit_attendance')?'<th></th>':''}</tr></thead><tbody id="att-tbody"></tbody></table></div>
    </div>`;
  }
  $('content').innerHTML=html;
  if(can('manage_attendance')) loadAttReport();
}
let ATT_ROWS=[];
async function loadAttReport(){
  const r=await api('att.report',{from:$('att-from').value,to:$('att-to').value});
  ATT_ROWS=r.rows;
  const today=new Date().toISOString().slice(0,10);
  const isToday = $('att-from').value===today && $('att-to').value===today;
  if(isToday){
    const present=r.rows.filter(x=>x.in_time).length;
    const late=r.rows.filter(x=>x.status==='late').length;
    const noOut=r.rows.filter(x=>x.in_time&&!x.out_time).length;
    const absent=r.employees.length - r.rows.filter(x=>x.in_time||['leave','permission','wfh'].includes(x.status)).length;
    $('att-summary').innerHTML=[['حاضر اليوم',present,'#16a34a'],['متأخر',late,'#d97706'],['بدون انصراف',noOut,'#2563eb'],['غائب',Math.max(0,absent),'#dc2626']]
      .map(s=>`<div class="stat"><div class="stat-lbl">${s[0]}</div><div class="stat-val" style="color:${s[2]}">${s[1]}</div></div>`).join('');
  } else $('att-summary').innerHTML='';
  $('att-tbody').innerHTML=r.rows.length?r.rows.map(x=>{
    const st=ATT_ST[x.status]||[x.status,'#64748b'];
    const hrs=x.in_time?(x.mins/60).toFixed(1):'—';
    return `<tr><td style="font-weight:700">${esc(x.user_name)}</td><td>${fD(x.day)}</td>
      <td>${x.in_time?x.in_time.slice(11,16):'—'}</td><td>${x.out_time?x.out_time.slice(11,16):'—'}</td><td>${hrs}</td>
      <td><span class="pill" style="background:${st[1]}">${st[0]}</span>${x.edit_reason?` <span class="tag" title="${esc(x.edit_reason)}">معدّل</span>`:''}</td>
      <td style="color:var(--muted)">${esc(x.browser||'')} ${esc(x.os||'')}</td><td style="color:var(--muted)" dir="ltr">${esc(x.ip||'')}</td>
      ${can('edit_attendance')?`<td><button class="mclose" onclick="attEdit(${x.id})"><i class="ti ti-edit"></i></button></td>`:''}</tr>`;
  }).join(''):`<tr><td colspan="9"><div class="empty">لا توجد سجلات في الفترة دي</div></td></tr>`;
}
function attCSV(){
  csv('attendance.csv',[['الموظف','اليوم','حضور','انصراف','ساعات','الحالة','IP'],
    ...ATT_ROWS.map(x=>[x.user_name,x.day,x.in_time?.slice(11,16)||'',x.out_time?.slice(11,16)||'',x.in_time?(x.mins/60).toFixed(1):'',(ATT_ST[x.status]||[x.status])[0],x.ip])]);
}
function attStOpts(sel){ return Object.entries(ATT_ST).map(([k,v])=>`<option value="${k}" ${k===sel?'selected':''}>${v[0]}</option>`).join(''); }
function attEdit(id){
  const x=ATT_ROWS.find(r=>r.id==id); if(!x) return;
  openModal(mHead('✏️ تعديل سجل حضور — '+esc(x.user_name))+`
    <div class="grid2"><div><label class="form-lbl">حضور</label><input id="ae-in" value="${x.in_time||''}" placeholder="YYYY-MM-DD HH:MM:SS"></div>
    <div><label class="form-lbl">انصراف</label><input id="ae-out" value="${x.out_time||''}" placeholder="YYYY-MM-DD HH:MM:SS"></div></div>
    <label class="form-lbl">الحالة</label><select id="ae-st">${attStOpts(x.status)}</select>
    <label class="form-lbl">سبب التعديل (إجباري)</label><input id="ae-reason">
    <button class="btn btn-p btn-full" onclick="tryApi('att.edit',{id:${id},in_time:$('ae-in').value,out_time:$('ae-out').value,status:$('ae-st').value,reason:$('ae-reason').value},this).then(()=>{toast('✓ تم التعديل');closeModal();loadAttReport()})">حفظ</button>`);
}
function attManual(){
  openModal(mHead('➕ سجل حضور يدوي')+`
    <label class="form-lbl">الموظف</label>
    <select id="am-u">
      ${USERS_OPT.map(u=>`<option value="${u.id}">${esc(u.name)}</option>`).join('')}
    </select>

    <label class="form-lbl">اليوم</label>
    <input type="date" id="am-d" value="${new Date().toISOString().slice(0,10)}">

    <div class="grid2">
      <div>
        <label class="form-lbl">وقت الحضور</label>
        <input type="time" id="am-in">
      </div>

      <div>
        <label class="form-lbl">وقت الانصراف</label>
        <input type="time" id="am-out">
      </div>
    </div>

    <label class="form-lbl">الحالة</label>
    <select id="am-st">
      ${attStOpts('absent')}
    </select>

    <label class="form-lbl">السبب (إجباري)</label>
    <input id="am-r">

    <button class="btn btn-p btn-full"
      onclick="tryApi('att.edit',{
        user_id:$('am-u').value,
        day:$('am-d').value,

        in_time:$('am-in').value
          ? $('am-d').value+' '+$('am-in').value+':00'
          : '',

        out_time:$('am-out').value
          ? $('am-d').value+' '+$('am-out').value+':00'
          : '',

        status:$('am-st').value,
        reason:$('am-r').value
      },this).then(()=>{
        toast('✓ تم');
        closeModal();
        loadAttReport();
      })">

      حفظ
    </button>
  `);
}

/* ══ LEAVES ══ */
const LV_ST={pending:['قيد الانتظار','#d97706'],approved:['تمت الموافقة','#16a34a'],rejected:['مرفوض','#dc2626'],cancelled:['ملغي','#64748b']};
async function pgLeaves(){
  const my=await api('leaves.my');
  let html=`<div class="toolbar"><button class="btn btn-p" onclick="leaveNew()"><i class="ti ti-plus"></i> طلب إجازة / إذن جديد</button></div>
  <div class="card"><h3><i class="ti ti-user"></i> طلباتي</h3><div class="tbl-wrap"><table>
    <thead><tr><th>النوع</th><th>من</th><th>إلى</th><th>السبب</th><th>الحالة</th><th>القرار</th><th></th></tr></thead>
    <tbody>${my.leaves.length?my.leaves.map(l=>{
      const st=LV_ST[l.status];
      return `<tr><td>${l.kind==='leave'?'🏖 إجازة':'⏱ إذن'}</td><td>${fD(l.date_from)} ${l.time_from||''}</td><td>${l.date_to?fD(l.date_to):(l.time_to||'—')}</td>
        <td>${esc(l.reason||'—')}</td><td><span class="pill" style="background:${st[1]}">${st[0]}</span></td>
        <td style="color:var(--muted)">${l.decided_at?fDT(l.decided_at)+(l.decision_note?' — '+esc(l.decision_note):''):'—'}</td>
        <td>${l.status==='pending'?`<button class="btn btn-gh btn-sm" onclick="tryApi('leaves.cancel',{id:${l.id}},this).then(()=>{toast('تم الإلغاء');pgLeaves()})">إلغاء</button>`:''}</td></tr>`;
    }).join(''):'<tr><td colspan="7"><div class="empty">مفيش طلبات لسه</div></td></tr>'}</tbody></table></div></div>`;

  if(can('approve_leave')||can('reject_leave')){
    const all=await api('leaves.pending');
    html+=`<div class="card"><h3><i class="ti ti-inbox"></i> طلبات الفريق (${all.leaves.filter(l=>l.status==='pending').length} معلق)</h3>
      <div class="tbl-wrap"><table><thead><tr><th>الموظف</th><th>النوع</th><th>التاريخ</th><th>السبب</th><th>الحالة</th><th>إجراء</th></tr></thead>
      <tbody>${all.leaves.length?all.leaves.map(l=>{
        const st=LV_ST[l.status]; const mine=false;
        return `<tr><td style="font-weight:700">${esc(l.user_name)}<div class="hmeta">${esc(l.dept||'')}</div></td>
          <td>${l.kind==='leave'?'🏖 إجازة':'⏱ إذن'}</td>
          <td>${fD(l.date_from)}${l.date_to?' → '+fD(l.date_to):''} ${l.time_from?l.time_from+'-'+(l.time_to||''):''}</td>
          <td>${esc(l.reason||'—')}</td><td><span class="pill" style="background:${st[1]}">${st[0]}</span>
          ${l.decided_at?`<div class="hmeta">${fDT(l.decided_at)}</div>`:''}</td>
          <td>${l.status==='pending'?`
            <button class="btn btn-g btn-sm" onclick="leaveDecide(${l.id},'approve','${esc(l.user_name)}')"><i class="ti ti-check"></i> موافقة</button>
            <button class="btn btn-r btn-sm" onclick="leaveDecide(${l.id},'reject','${esc(l.user_name)}')"><i class="ti ti-x"></i> رفض</button>`
            :`<button class="btn btn-gh btn-sm" onclick="leaveHist(${l.id})"><i class="ti ti-history"></i></button>`}</td></tr>`;
      }).join(''):'<tr><td colspan="6"><div class="empty">لا توجد طلبات</div></td></tr>'}</tbody></table></div></div>`;
  }
  $('content').innerHTML=html;
}
function leaveNew(){
  openModal(mHead('➕ طلب جديد')+`
    <label class="form-lbl">النوع</label>
    <select id="ln-kind" onchange="$('ln-times').style.display=this.value==='permission'?'grid':'none';$('ln-dateto-w').style.display=this.value==='leave'?'block':'none'">
      <option value="leave">إجازة</option><option value="permission">إذن (ساعات)</option></select>
    <div class="grid2"><div><label class="form-lbl">التاريخ من</label><input type="date" id="ln-from"></div>
    <div id="ln-dateto-w"><label class="form-lbl">إلى (اختياري)</label><input type="date" id="ln-to"></div></div>
    <div class="grid2" id="ln-times" style="display:none"><div><label class="form-lbl">من ساعة</label><input type="time" id="ln-tf"></div>
    <div><label class="form-lbl">إلى ساعة</label><input type="time" id="ln-tt"></div></div>
    <label class="form-lbl">السبب</label><textarea id="ln-reason"></textarea>
    <button class="btn btn-p btn-full" onclick="tryApi('leaves.submit',{kind:$('ln-kind').value,date_from:$('ln-from').value,date_to:$('ln-to').value,time_from:$('ln-tf').value,time_to:$('ln-tt').value,reason:$('ln-reason').value},this).then(()=>{toast('✓ تم إرسال الطلب للإدارة');closeModal();pgLeaves()})">إرسال الطلب</button>`);
}
function leaveDecide(id, dec, name){
  openModal(mHead((dec==='approve'?'✅ الموافقة على':'❌ رفض')+' طلب '+name)+`
    <label class="form-lbl">ملاحظة (اختياري)</label><textarea id="ld-note"></textarea>
    <button class="btn ${dec==='approve'?'btn-g':'btn-r'} btn-full" onclick="tryApi('leaves.decide',{id:${id},decision:'${dec}',note:$('ld-note').value},this).then(()=>{toast('✓ تم ${dec==='approve'?'الموافقة':'الرفض'} وإخطار الموظف');closeModal();pgLeaves()})">تأكيد</button>`);
}
async function leaveHist(id){
  const r=await api('leaves.history',{id});
  openModal(mHead('📜 سجل الطلب #'+id)+r.history.map(h=>
    `<div class="hist"><div class="hdot"></div><div><div>${esc(h.from_status||'—')} ← <b>${esc(h.to_status)}</b> ${h.note?'— '+esc(h.note):''}</div>
    <div class="hmeta">${esc(h.by_user)} · ${fDT(h.at)}</div></div></div>`).join(''));
}
</script>
<script>
/* ══ TASKS (Trello-style) ══ */
const T_ST={new:['مهام جديدة','#2563eb'],in_progress:['جاري التنفيذ','#d97706'],waiting_review:['في انتظار المراجعة','#7c3aed'],revision:['تعديلات','#e11d48'],completed:['مكتملة','#16a34a'],cancelled:['ملغاة','#64748b']};
const T_PRI={low:['منخفضة','#64748b'],normal:['عادية','#2563eb'],high:['مرتفعة','#d97706'],urgent:['عاجلة 🔥','#dc2626']};
let T_ROWS=[], T_CLIENTS=[];
async function pgTasks(){
  $('content').innerHTML=`
    <div class="toolbar">
      <input class="grow" id="tk-q" placeholder="🔍 بحث باسم المهمة..." oninput="renderBoard()">
      <select id="tk-emp" onchange="renderBoard()"><option value="">كل الموظفين</option>${USERS_OPT.map(u=>`<option value="${u.id}">${esc(u.name)}</option>`).join('')}</select>
      <select id="tk-client" onchange="renderBoard()"><option value="">كل العملاء</option></select>
      <select id="tk-dept" onchange="renderBoard()"><option value="">كل الأقسام</option>${listBy('department').map(d=>`<option>${esc(d.label)}</option>`).join('')}</select>
      <input type="date" id="tk-date" onchange="renderBoard()" title="الاستحقاق قبل">
      ${can('create_task')?`<button class="btn btn-p" onclick="taskModal()"><i class="ti ti-plus"></i> مهمة جديدة</button>`:''}
    </div>
    <div class="kanban" id="kanban"></div>`;
  // restore filters
  try{ const f=JSON.parse(localStorage.getItem('tk_filters')||'{}'); ['tk-q','tk-emp','tk-client','tk-dept','tk-date'].forEach(k=>{if(f[k]&&$(k))$(k).value=f[k]}); }catch(e){}
  if(can('view_all_clients')||can('view_assigned_clients')){
    try{ T_CLIENTS=(await api('clients.list')).clients; $('tk-client').innerHTML='<option value="">كل العملاء</option>'+T_CLIENTS.map(c=>`<option value="${c.id}">${esc(c.name_ar)}</option>`).join(''); }catch(e){}
  }
  await loadBoard();
}
async function loadBoard(){ const r=await api('tasks.board'); T_ROWS=r.tasks; renderBoard(); }
function tkFilters(){ const f={}; ['tk-q','tk-emp','tk-client','tk-dept','tk-date'].forEach(k=>f[k]=$(k)?$(k).value:''); try{localStorage.setItem('tk_filters',JSON.stringify(f))}catch(e){} return f; }
function renderBoard(){
  const f=tkFilters();
  const q=(f['tk-q']||'').toLowerCase();
  const list=T_ROWS.filter(t=>{
    if(q && !t.title.toLowerCase().includes(q)) return false;
    if(f['tk-emp'] && !t.assignees.map(String).includes(f['tk-emp']) && String(t.primary_assignee)!==f['tk-emp']) return false;
    if(f['tk-client'] && String(t.client_id)!==f['tk-client']) return false;
    if(f['tk-dept'] && t.dept!==f['tk-dept']) return false;
    if(f['tk-date'] && (!t.deadline || t.deadline>f['tk-date'])) return false;
    return true;
  });
  $('kanban').innerHTML=Object.entries(T_ST).map(([st,[lb,color]])=>{
    const cards=list.filter(t=>t.status===st);
    return `<div class="kcol" data-st="${st}" ondragover="kDragOver(event)" ondragleave="this.classList.remove('dragover')" ondrop="kDrop(event,'${st}')">
      <div class="kcol-hd"><span style="width:9px;height:9px;border-radius:50%;background:${color}"></span> ${lb}<span class="kcol-cnt">${cards.length}</span></div>
      <div class="kcards">${cards.map(t=>kCard(t)).join('')||'<div style="text-align:center;font-size:11px;color:var(--muted);padding:12px">فاضي</div>'}</div></div>`;
  }).join('');
}
function kCard(t){
  const pri=T_PRI[t.priority]||T_PRI.normal;
  const cl=T_CLIENTS.find(c=>c.id==t.client_id);
  const overdue=t.deadline && t.deadline<new Date().toISOString().slice(0,10) && !['completed','cancelled'].includes(t.status);
  const vals=[];
  if(t.fin_value!=null) vals.push('💰 '+fM(t.fin_value));
  if(t.points!=null) vals.push('⭐ '+t.points+' نقطة');
  if(t.est_hours!=null) vals.push('⏱ '+t.est_hours+' س');
  return `<div class="kcard" draggable="true" ondragstart="kDragStart(event,${t.id})" onclick="taskView(${t.id})">
    <div style="display:flex;justify-content:space-between;gap:6px"><div class="kcard-title">${esc(t.title)}</div>
    <span class="kpri" style="background:${pri[1]}22;color:${pri[1]}">${pri[0]}</span></div>
    ${cl?`<div class="hmeta">🏢 ${esc(cl.name_ar)}</div>`:''}
    ${vals.length?`<div style="display:flex;gap:4px;flex-wrap:wrap;margin:4px 0">${vals.map(v=>`<span class="kval">${v}</span>`).join('')}</div>`:''}
    <div class="kmeta">
      ${t.primary_assignee?`<span>👤 ${esc(uname(t.primary_assignee))}${t.assignees.length>1?' +'+(t.assignees.length-1):''}</span>`:''}
      ${t.dept?`<span class="tag">${esc(t.dept)}</span>`:''}
      ${t.deadline?`<span style="${overdue?'color:#dc2626;font-weight:900':''}">📅 ${fD(t.deadline)}${overdue?' متأخرة!':''}</span>`:''}
      ${t.checklist.t?`<span>☑ ${t.checklist.d}/${t.checklist.t}</span>`:''}
      ${t.comments?`<span>💬 ${t.comments}</span>`:''}
      ${t.files?`<span>📎 ${t.files}</span>`:''}
    </div></div>`;
}
let DRAG_ID=null;
function kDragStart(e,id){ DRAG_ID=id; e.target.classList.add('dragging'); }
function kDragOver(e){ e.preventDefault(); e.currentTarget.classList.add('dragover'); }
async function kDrop(e,st){
  e.preventDefault(); e.currentTarget.classList.remove('dragover');
  document.querySelectorAll('.kcard.dragging').forEach(c=>c.classList.remove('dragging'));
  const t=T_ROWS.find(x=>x.id==DRAG_ID); if(!t||t.status===st) return;
  const old=t.status; t.status=st; renderBoard();
  try{ await api('tasks.move',{id:DRAG_ID,status:st}); toast('✓ اتنقلت إلى: '+T_ST[st][0]); }
  catch(err){ t.status=old; renderBoard(); toast('⚠️ '+err.message); }
}
function taskModal(t=null){
  const cliOpts='<option value="">بدون عميل</option>'+T_CLIENTS.map(c=>`<option value="${c.id}" ${t&&t.client_id==c.id?'selected':''}>${esc(c.name_ar)}</option>`).join('');
  openModal(mHead(t?'✏️ تعديل المهمة':'➕ مهمة جديدة')+`
    <label class="form-lbl">اسم المهمة *</label><input id="tm-title" value="${t?esc(t.title):''}">
    <label class="form-lbl">الوصف</label><textarea id="tm-descr">${t?esc(t.descr||''):''}</textarea>
    <div class="grid2">
      <div><label class="form-lbl">العميل</label><select id="tm-client">${cliOpts}</select></div>
      <div><label class="form-lbl">القسم</label><select id="tm-dept"><option value="">—</option>${listBy('department').map(d=>`<option ${t&&t.dept===d.label?'selected':''}>${esc(d.label)}</option>`).join('')}</select></div>
      <div><label class="form-lbl">الأولوية</label><select id="tm-pri">${Object.entries(T_PRI).map(([k,v])=>`<option value="${k}" ${t&&t.priority===k?'selected':''}>${v[0]}</option>`).join('')}</select></div>
      <div><label class="form-lbl">الاستحقاق</label><input type="date" id="tm-deadline" value="${t?.deadline||''}"></div>
    </div>
    <label class="form-lbl">الموظفون</label>${chkGrid(USERS_OPT, t?t.assignees:[], 'tm-asg','id','name')}
    <label class="form-lbl">الموظف الرئيسي</label><select id="tm-primary">${USERS_OPT.map(u=>`<option value="${u.id}" ${t&&t.primary_assignee==u.id?'selected':''}>${esc(u.name)}</option>`).join('')}</select>
    <button class="btn btn-p btn-full" onclick="saveTask(this,${t?t.id:0})">حفظ المهمة</button>`,true);
}
async function saveTask(b,id){
  await tryApi('tasks.save',{id:id||undefined,title:$('tm-title').value,descr:$('tm-descr').value,client_id:$('tm-client').value,
    dept:$('tm-dept').value,priority:$('tm-pri').value,deadline:$('tm-deadline').value,
    assignees:chkVals('tm-asg'),primary_assignee:$('tm-primary').value,status:id?(T_ROWS.find(t=>t.id==id)||{}).status:'new'},b);
  toast('✓ تم الحفظ'); closeModal(); loadBoard();
}
async function taskView(id){
  const r=await api('tasks.get',{id});
  const t=r.task; const pri=T_PRI[t.priority]||T_PRI.normal;
  const canVal = can('edit_task_value');
  const vals=`<div class="sec"><i class="ti ti-coin"></i> قيمة المهمة</div>
    ${t.fin_value==null&&t.points==null&&t.est_hours==null&&!canVal?'<div style="font-size:12px;color:var(--muted)">لا توجد قيم ظاهرة لك</div>':''}
    <div class="grid2" style="grid-template-columns:1fr 1fr 1fr">
      <div><label class="form-lbl">💰 قيمة مالية</label><input id="tv-fin" type="number" value="${t.fin_value??''}" ${canVal?'':'disabled'}></div>
      <div><label class="form-lbl">⭐ نقاط</label><input id="tv-pts" type="number" value="${t.points??''}" ${canVal?'':'disabled'}></div>
      <div><label class="form-lbl">⏱ ساعات متوقعة</label><input id="tv-hrs" type="number" step="0.5" value="${t.est_hours??''}" ${canVal?'':'disabled'}></div>
    </div>
    ${canVal?`<button class="btn btn-gh btn-sm" onclick="tryApi('tasks.value',{id:${id},fin_value:$('tv-fin').value,points:$('tv-pts').value,est_hours:$('tv-hrs').value},this).then(()=>{toast('✓ تم حفظ القيم');loadBoard()})">حفظ القيم</button>`:''}`;
  openModal(mHead('📋 '+esc(t.title))+`
    <div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:10px">
      <span class="pill" style="background:${T_ST[t.status][1]}">${T_ST[t.status][0]}</span>
      <span class="kpri" style="background:${pri[1]}22;color:${pri[1]};padding:4px 10px">${pri[0]}</span>
      ${can('edit_task')?`<button class="btn btn-gh btn-sm" onclick="closeModal();taskModal(T_ROWS.find(x=>x.id==${id}))"><i class="ti ti-edit"></i> تعديل</button>`:''}
      ${can('delete_task')?`<button class="btn btn-r btn-sm" onclick="confirmModal('حذف المهمة','هتتنقل لسلة المهملات وتقدر تسترجعها.',async b=>{await tryApi('tasks.delete',{id:${id}},b);toast('اتنقلت للسلة');closeModal();loadBoard()})"><i class="ti ti-trash"></i></button>`:''}
    </div>
    ${t.descr?`<p style="font-size:12.5px;color:var(--sub);margin-bottom:8px">${esc(t.descr)}</p>`:''}
    <div class="kv"><b>الموظفون</b><span>${t.assignees.map(a=>`<span class="tag">${esc(uname(a))}${a==t.primary_assignee?' ⭐':''}</span>`).join('')||'—'}</span></div>
    <div class="kv"><b>العميل / القسم</b><span>${esc((T_CLIENTS.find(c=>c.id==t.client_id)||{}).name_ar||'—')} · ${esc(t.dept||'—')}</span></div>
    <div class="kv"><b>الاستحقاق</b><span>${fD(t.deadline)}</span></div>
    <div class="kv"><b>أنشأها</b><span>${esc(uname(t.created_by))} · ${fDT(t.created_at)} — آخر تحديث ${fDT(t.updated_at)}</span></div>
    ${vals}
    <div class="sec"><i class="ti ti-list-check"></i> Checklist</div>
    <div id="tv-checks">${r.checklist.map(c=>`
      <label style="display:flex;gap:8px;align-items:center;padding:4px 0;font-size:12px;cursor:pointer">
        <input type="checkbox" ${c.done==1?'checked':''} onchange="tryApi('tasks.check.save',{task_id:${id},toggle_id:${c.id}},null)" style="margin:0">
        <span style="${c.done==1?'text-decoration:line-through;color:var(--muted)':''}">${esc(c.txt)}</span>
        <button class="mclose" style="margin-right:auto" onclick="tryApi('tasks.check.save',{task_id:${id},del_id:${c.id}},this).then(()=>taskView(${id}))"><i class="ti ti-x"></i></button>
      </label>`).join('')}</div>
    <div style="display:flex;gap:6px"><input id="tv-newchk" placeholder="بند جديد..." style="margin:0">
      <button class="btn btn-gh btn-sm" onclick="tryApi('tasks.check.save',{task_id:${id},txt:$('tv-newchk').value},this).then(()=>taskView(${id}))">+</button></div>
    <div class="sec"><i class="ti ti-paperclip"></i> المرفقات</div>
    ${r.files.map(f=>`<div class="kv"><b><a href="${esc(f.path)}" target="_blank">${esc(f.orig_name)}</a></b><span class="hmeta">${esc(f.user_name||'')} · ${fDT(f.at)}</span></div>`).join('')||''}
    <div style="display:flex;gap:6px;margin-top:6px"><input type="file" id="tv-file" style="margin:0">
      <button class="btn btn-gh btn-sm" onclick="taskUpload(this,${id})">رفع</button></div>
    <div class="sec"><i class="ti ti-messages"></i> التعليقات</div>
    ${r.comments.map(c=>`<div class="hist"><div class="hdot"></div><div><div>${esc(c.txt)}</div><div class="hmeta">${esc(c.user_name)} · ${fDT(c.at)}</div></div></div>`).join('')}
    <div style="display:flex;gap:6px;margin-top:6px"><input id="tv-newcmt" placeholder="اكتب تعليق..." style="margin:0">
      <button class="btn btn-p btn-sm" onclick="tryApi('tasks.comment',{task_id:${id},txt:$('tv-newcmt').value},this).then(()=>taskView(${id}))">إرسال</button></div>
    `,true);
}
async function taskUpload(b,id){
  const f=$('tv-file').files[0]; if(!f){toast('اختر ملفًا');return}
  const fd=new FormData(); fd.append('a','tasks.upload'); fd.append('task_id',id); fd.append('file',f);
  await tryApi('tasks.upload',fd,b); toast('✓ تم الرفع'); taskView(id);
}

/* ══ CLIENTS ══ */
let CL_ROWS=[], CL_PAGE=1, CL_SORT={k:'updated_at',d:-1};
async function pgClients(){
  $('content').innerHTML=`
    <div class="stats" id="crm-stats-row"></div>
    <div class="toolbar">
      <input class="grow" id="cl-q" placeholder="🔍 بحث بالاسم / الموظف / التليفون..." oninput="CL_PAGE=1;renderClients()">
      <select id="cl-st" onchange="CL_PAGE=1;renderClients()"><option value="">كل الحالات</option>${listBy('client_status').map(s=>`<option value="${s.id}">${esc(s.label)}</option>`).join('')}</select>
      <select id="cl-emp" onchange="CL_PAGE=1;renderClients()"><option value="">كل الموظفين</option>${USERS_OPT.map(u=>`<option value="${u.id}">${esc(u.name)}</option>`).join('')}</select>
      <button class="btn btn-gh btn-sm" onclick="clientsCSV()"><i class="ti ti-file-spreadsheet"></i> CSV</button>
      <button class="btn btn-gh btn-sm" onclick="window.print()"><i class="ti ti-printer"></i></button>
      ${can('create_client')?`<button class="btn btn-p" onclick="clientModal()"><i class="ti ti-plus"></i> عميل جديد</button>`:''}
    </div>
    <div class="tbl-wrap"><table><thead><tr id="cl-thead"></tr></thead><tbody id="cl-tbody"></tbody></table>
    <div style="display:flex;gap:5px;justify-content:center;padding:12px" id="cl-pag"></div></div>`;
  CL_ROWS=(await api('clients.list')).clients;
  renderClients();
  try{
    const s=await api('crm.stats');
    CL_PAID=s.paid_by_client||{};
    const cards=[['إجمالي العملاء',s.total,'#2563eb'],['النشطين',s.active,'#16a34a'],['تجديد خلال 30 يوم',s.renew30,'#7c3aed']];
    if(s.mrr!=null){
      cards.push(['الإيراد الشهري المتوقع MRR',fM(s.mrr)+' ج','#0ea5e9'],['المحصَّل فعليًا',fM(s.collected),'#16a34a'],['مستحق لم يُحصَّل',fM(s.due),'#d97706']);
      if(s.overdue>0) cards.push(['دفعات متأخرة ⚠️',s.overdue,'#dc2626']);
    }
    $('crm-stats-row').innerHTML=cards.map(c=>`<div class="stat"><div class="stat-lbl">${c[0]}</div><div class="stat-val" style="color:${c[2]};font-size:${String(c[1]).length>9?'15':'20'}px">${c[1]}</div></div>`).join('');
    renderClients();
  }catch(e){}
}
let CL_PAID={};
function clSort(k){ if(CL_SORT.k===k) CL_SORT.d*=-1; else CL_SORT={k,d:1}; renderClients(); }
function renderClients(){
  const fin=can('view_financials');
  const q=($('cl-q').value||'').toLowerCase(), fs=$('cl-st').value, fe=$('cl-emp').value;
  let list=CL_ROWS.filter(c=>{
    if(fs && String(c.status_id)!==fs) return false;
    if(fe && !c.assignees.map(String).includes(fe)) return false;
    if(q){ const hay=[c.name_ar,c.name_en,c.phone,...(c.assignees||[]).map(uname)].join(' ').toLowerCase(); if(!hay.includes(q)) return false; }
    return true;
  });
  list.sort((a,b)=>{let va=a[CL_SORT.k]??'',vb=b[CL_SORT.k]??''; return String(va).localeCompare(String(vb),'ar')*CL_SORT.d});
  const cols=[['name_ar','العميل'],['services','الخدمات',1],['primary_assignee','المسؤول'],...(fin?[['contract_value','قيمة العقد'],['_paid','المحصَّل',1]]:[]),['status_id','الحالة'],['renew_date','التجديد'],['updated_at','آخر تحديث'],['_','',1]];
  $('cl-thead').innerHTML=cols.map(c=>`<th ${c[2]?'':`onclick="clSort('${c[0]}')" style="cursor:pointer"`}>${c[1]}${CL_SORT.k===c[0]?(CL_SORT.d>0?' ▲':' ▼'):''}</th>`).join('');
  const per=10, pages=Math.max(1,Math.ceil(list.length/per));
  if(CL_PAGE>pages) CL_PAGE=pages;
  const rows=list.slice((CL_PAGE-1)*per,CL_PAGE*per);
  $('cl-tbody').innerHTML=rows.length?rows.map(c=>`<tr>
    <td style="font-weight:700;cursor:pointer" onclick="clientView(${c.id})">${esc(c.name_ar)}<div class="hmeta" dir="ltr">${esc(c.phone||'')}</div></td>
    <td>${(c.services||[]).slice(0,2).map(s=>`<span class="tag">${esc(s)}</span>`).join('')}${(c.services||[]).length>2?`<span class="tag">+${c.services.length-2}</span>`:''}</td>
    <td>${esc(uname(c.primary_assignee))}${(c.assignees||[]).length>1?` <span class="tag">+${c.assignees.length-1}</span>`:''}</td>
    ${fin?`<td style="font-weight:700">${fM(c.contract_value)} ${esc(c.currency||'')}</td>
    <td>${CL_PAID[c.id]?`<span style="color:#16a34a;font-weight:700">${fM(CL_PAID[c.id])}</span>`:'<span style="color:var(--muted)">0</span>'}</td>`:''}
    <td><span class="pill" style="background:${listColor('client_status',c.status_id)};cursor:pointer" onclick="clientStatus(${c.id})">${esc(listLabel('client_status',c.status_id))}</span></td>
    <td>${fD(c.renew_date)}</td><td style="color:var(--muted)">${fD(c.updated_at)}</td>
    <td><button class="mclose" onclick="clientView(${c.id})"><i class="ti ti-eye"></i></button>
      ${can('edit_client')?`<button class="mclose" onclick="clientModal(CL_ROWS.find(x=>x.id==${c.id}))"><i class="ti ti-edit"></i></button>`:''}
      ${can('delete_client')?`<button class="mclose" style="color:#dc2626" onclick="confirmModal('حذف العميل','هيتنقل لسلة المهملات.',async(b,nm)=>{await tryApi('clients.delete',{id:${c.id},confirm_name:nm},b);toast('اتنقل للسلة');closeModal();pgClients()},'${esc(c.name_ar)}')"><i class="ti ti-trash"></i></button>`:''}</td>
  </tr>`).join(''):`<tr><td colspan="9"><div class="empty"><i class="ti ti-mood-empty"></i>لا يوجد عملاء</div></td></tr>`;
  $('cl-pag').innerHTML=pages<=1?'':Array.from({length:pages},(_,i)=>`<button class="btn btn-gh btn-sm ${i+1===CL_PAGE?'btn-p':''}" onclick="CL_PAGE=${i+1};renderClients()">${i+1}</button>`).join('');
}
function clientsCSV(){
  const fin=can('view_financials');
  const head=['العميل','بالإنجليزية','الهاتف','الخدمات','المسؤول','الحالة','البداية','التجديد'];
  if(fin) head.push('قيمة العقد','العملة');
  csv('clients.csv',[head,...CL_ROWS.map(c=>{
    const r=[c.name_ar,c.name_en,c.phone,(c.services||[]).join(' | '),uname(c.primary_assignee),listLabel('client_status',c.status_id),c.start_date,c.renew_date];
    if(fin) r.push(c.contract_value,c.currency);
    return r;})]);
}
function clientModal(c=null){
  const fin=can('edit_financials');
  openModal(mHead(c?'✏️ تعديل: '+esc(c.name_ar):'➕ عميل جديد')+`
    <div class="sec"><i class="ti ti-id"></i> أساسي</div>
    <div class="grid2">
      <div><label class="form-lbl">الاسم بالعربية *</label><input id="cm-name_ar" value="${c?esc(c.name_ar):''}"></div>
      <div><label class="form-lbl">بالإنجليزية</label><input id="cm-name_en" dir="ltr" value="${c?esc(c.name_en):''}"></div>
      <div><label class="form-lbl">الهاتف</label><input id="cm-phone" dir="ltr" value="${c?esc(c.phone):''}"></div>
      <div><label class="form-lbl">الإيميل</label><input id="cm-email" dir="ltr" value="${c?esc(c.email):''}"></div>
      <div><label class="form-lbl">الدولة</label><input id="cm-country" value="${c?esc(c.country):'مصر'}"></div>
      <div><label class="form-lbl">المدينة</label><input id="cm-city" value="${c?esc(c.city):''}"></div>
      <div><label class="form-lbl">النشاط</label><input id="cm-industry" value="${c?esc(c.industry):''}"></div>
      <div><label class="form-lbl">الحالة</label><select id="cm-status_id">${listBy('client_status').map(s=>`<option value="${s.id}" ${c&&c.status_id==s.id?'selected':''}>${esc(s.label)}</option>`).join('')}</select></div>
    </div>
    <div class="sec"><i class="ti ti-briefcase"></i> الخدمات والأقسام والفريق</div>
    ${chkGrid(listBy('service'), c?(c.services||[]):[], 'cm-srv','label','label')}
    ${chkGrid(listBy('department'), c?(c.depts||[]):[], 'cm-dep','label','label')}
    ${chkGrid(USERS_OPT, c?(c.assignees||[]):[], 'cm-asg','id','name')}
    <div class="grid2">
      <div><label class="form-lbl">الموظف الرئيسي *</label><select id="cm-primary">${USERS_OPT.map(u=>`<option value="${u.id}" ${c&&c.primary_assignee==u.id?'selected':''}>${esc(u.name)}</option>`).join('')}</select></div>
      <div><label class="form-lbl">مدير الفريق</label><select id="cm-leader"><option value="">بدون</option>${USERS_OPT.map(u=>`<option value="${u.id}" ${c&&c.leader_id==u.id?'selected':''}>${esc(u.name)}</option>`).join('')}</select></div>
    </div>
    ${fin?`<div class="sec"><i class="ti ti-cash"></i> مالي (للإدارة)</div>
    <div class="grid2">
      <div><label class="form-lbl">قيمة العقد</label><input id="cm-contract_value" type="number" value="${c?.contract_value??''}"></div>
      <div><label class="form-lbl">العملة</label><select id="cm-currency">${['EGP','SAR','USD','AED'].map(x=>`<option ${c&&c.currency===x?'selected':''}>${x}</option>`).join('')}</select></div>
      <div><label class="form-lbl">طريقة الدفع</label><select id="cm-pay_method">${['تحويل بنكي','إنستاباي','فودافون كاش','كاش','شيك'].map(x=>`<option ${c&&c.pay_method===x?'selected':''}>${x}</option>`).join('')}</select></div>
      <div><label class="form-lbl">الدورية</label><select id="cm-pay_cycle">${[['monthly','شهري'],['quarterly','ربع سنوي'],['semiannual','نصف سنوي'],['annual','سنوي'],['once','دفعة واحدة']].map(x=>`<option value="${x[0]}" ${c&&c.pay_cycle===x[0]?'selected':''}>${x[1]}</option>`).join('')}</select></div>
    </div>`:''}
    <div class="sec"><i class="ti ti-calendar"></i> تواريخ وروابط</div>
    <div class="grid2">
      <div><label class="form-lbl">بداية التعاقد</label><input type="date" id="cm-start_date" value="${c?.start_date||''}"></div>
      <div><label class="form-lbl">نهاية التعاقد</label><input type="date" id="cm-end_date" value="${c?.end_date||''}"></div>
      <div><label class="form-lbl">التجديد</label><input type="date" id="cm-renew_date" value="${c?.renew_date||''}"></div>
      <div><label class="form-lbl">آخر تواصل</label><input type="date" id="cm-last_contact" value="${c?.last_contact||''}"></div>
      <div><label class="form-lbl">الإجراء القادم</label><input id="cm-next_action" value="${c?esc(c.next_action):''}"></div>
      <div><label class="form-lbl">موعده</label><input type="date" id="cm-next_action_date" value="${c?.next_action_date||''}"></div>
      <div><label class="form-lbl">ملفات العميل</label><input id="cm-files_link" dir="ltr" value="${c?esc(c.files_link):''}"></div>
      <div><label class="form-lbl">جروب واتساب</label><input id="cm-wa_link" dir="ltr" value="${c?esc(c.wa_link):''}"></div>
      <div><label class="form-lbl">حساب الإعلانات</label><input id="cm-ad_link" dir="ltr" value="${c?esc(c.ad_link):''}"></div>
      <div><label class="form-lbl">الموقع</label><input id="cm-site_link" dir="ltr" value="${c?esc(c.site_link):''}"></div>
    </div>
    ${fin||can('manage_users')?`<label class="form-lbl">ملاحظات داخلية (إدارة فقط)</label><textarea id="cm-notes_internal">${c?esc(c.notes_internal||''):''}</textarea>`:''}
    <label class="form-lbl">ملاحظات ظاهرة للموظف</label><textarea id="cm-notes_visible">${c?esc(c.notes_visible||''):''}</textarea>
    <button class="btn btn-p btn-full" onclick="saveClient(this,${c?c.id:0})">حفظ العميل</button>`,true);
}
async function saveClient(b,id){
  const g=k=>{const el=$('cm-'+k);return el?el.value:undefined};
  const data={id:id||undefined};
  ['name_ar','name_en','phone','email','country','city','industry','status_id','primary_assignee','leader_id','contract_value','currency','pay_method','pay_cycle','start_date','end_date','renew_date','last_contact','next_action','next_action_date','files_link','wa_link','ad_link','site_link','notes_internal','notes_visible'].forEach(k=>{const v=g(k);if(v!==undefined)data[k]=v});
  data.services=chkVals('cm-srv'); data.depts=chkVals('cm-dep'); data.assignees=chkVals('cm-asg');
  await tryApi('clients.save',data,b); toast('✓ تم حفظ العميل'); closeModal(); pgClients();
}
function clientStatus(id){
  const c=CL_ROWS.find(x=>x.id==id); if(!c) return;
  openModal(mHead('🔄 تغيير حالة: '+esc(c.name_ar))+`
    <label class="form-lbl">الحالة الجديدة</label>
    <select id="cs-st">${listBy('client_status').map(s=>`<option value="${s.id}" ${c.status_id==s.id?'selected':''}>${esc(s.label)}</option>`).join('')}</select>
    <label class="form-lbl">ملاحظة</label><textarea id="cs-note"></textarea>
    <button class="btn btn-p btn-full" onclick="tryApi('clients.status',{id:${id},status_id:$('cs-st').value,note:$('cs-note').value},this).then(()=>{toast('✓ تم التغيير');closeModal();pgClients()})">تأكيد</button>`);
}
async function clientView(id){
  const c=CL_ROWS.find(x=>x.id==id); if(!c) return;
  const h=(await api('clients.history',{id})).history;
  const fin=can('view_financials');
  const link=u=>u?`<a href="${esc(u)}" target="_blank">فتح ↗</a>`:'—';
  openModal(mHead('🏢 '+esc(c.name_ar))+`
    <div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:10px">
      <span class="pill" style="background:${listColor('client_status',c.status_id)}">${esc(listLabel('client_status',c.status_id))}</span>
      <button class="btn btn-gh btn-sm" onclick="closeModal();clientStatus(${id})">تغيير الحالة</button>
      <button class="btn btn-g btn-sm" onclick="clientNote(${id})">+ ملاحظة</button>
      ${c.phone?`<a class="btn btn-g btn-sm" href="https://wa.me/${esc(c.phone.replace(/^0/,'2'))}" target="_blank"><i class="ti ti-brand-whatsapp"></i></a>`:''}
    </div>
    <div class="kv"><b>الهاتف / الإيميل</b><span dir="ltr">${esc(c.phone||'—')} · ${esc(c.email||'—')}</span></div>
    <div class="kv"><b>المكان / النشاط</b><span>${esc([c.country,c.city].filter(Boolean).join(' - ')||'—')} · ${esc(c.industry||'—')}</span></div>
    <div class="kv"><b>الخدمات</b><span>${(c.services||[]).map(s=>`<span class="tag">${esc(s)}</span>`).join('')||'—'}</span></div>
    <div class="kv"><b>الفريق</b><span>${(c.assignees||[]).map(a=>`<span class="tag">${esc(uname(a))}${a==c.primary_assignee?' ⭐':''}</span>`).join('')||'—'}</span></div>
    ${fin?`<div class="kv"><b>العقد</b><span>${fM(c.contract_value)} ${esc(c.currency||'')} · ${esc(c.pay_method||'')}</span></div>
    <div id="cv-payments"><div class="sec"><i class="ti ti-cash"></i> الدفعات <button class="btn btn-g btn-sm" style="margin-right:8px" onclick="paymentModal(${c.id})">+ دفعة</button></div>
    <div id="cv-pay-list" style="font-size:12px;color:var(--muted)">جارِ التحميل...</div></div>`:''}
    <div class="kv"><b>البداية / التجديد</b><span>${fD(c.start_date)} · ${fD(c.renew_date)}</span></div>
    <div class="kv"><b>الإجراء القادم</b><span>${esc(c.next_action||'—')} ${c.next_action_date?'('+fD(c.next_action_date)+')':''}</span></div>
    <div class="kv"><b>روابط</b><span>${link(c.files_link)} · ${link(c.wa_link)} · ${link(c.ad_link)} · ${link(c.site_link)}</span></div>
    ${c.notes_internal?`<div class="sec"><i class="ti ti-lock"></i> ملاحظات الإدارة</div><div style="font-size:12px;background:var(--hover);border-radius:9px;padding:9px">${esc(c.notes_internal)}</div>`:''}
    ${c.notes_visible?`<div class="sec"><i class="ti ti-eye"></i> ملاحظات تشغيلية</div><div style="font-size:12px;background:var(--hover);border-radius:9px;padding:9px">${esc(c.notes_visible)}</div>`:''}
    <div class="sec"><i class="ti ti-history"></i> السجل</div>
    ${h.map(x=>`<div class="hist"><div class="hdot" style="background:${x.kind==='status'?'#7c3aed':x.kind==='transfer'?'#d97706':'#2563eb'}"></div>
      <div><div>${x.kind==='status'?esc(x.from_v)+' ← <b>'+esc(x.to_v)+'</b>':x.kind==='transfer'?'🔁 '+esc(x.from_v)+' ← '+esc(x.to_v):''} ${x.note?esc(x.note):''}</div>
      <div class="hmeta">${esc(x.by_user)} · ${fDT(x.at)}</div></div></div>`).join('')||'<div class="empty">لا يوجد سجل</div>'}`,true);
  if(fin) loadClientPayments(c.id, c.contract_value);
}
function clientNote(id){
  openModal(mHead('📝 ملاحظة جديدة')+`<textarea id="cn-t"></textarea>
    <button class="btn btn-p btn-full" onclick="tryApi('clients.note',{id:${id},text:$('cn-t').value},this).then(()=>{toast('✓ تم');closeModal();pgClients()})">حفظ</button>`);
}


async function loadClientPayments(cid, contract){
  try{
    const r=await api('clients.payments',{id:cid});
    const paid=r.payments.filter(p=>p.status==='paid').reduce((s,p)=>s+ +p.amount,0);
    const due=r.payments.filter(p=>p.status==='due').reduce((s,p)=>s+ +p.amount,0);
    const el=$('cv-pay-list'); if(!el) return;
    el.innerHTML=`
      <div style="display:flex;gap:7px;flex-wrap:wrap;margin-bottom:8px">
        <span class="tag" style="color:#16a34a">محصَّل: ${fM(paid)}</span>
        <span class="tag" style="color:#d97706">مستحق: ${fM(due)}</span>
        ${contract?`<span class="tag">فاضل من العقد: ${fM(Math.max(0,contract-paid))}</span>`:''}
      </div>
      ${r.payments.map(p=>{
        const overdue=p.status==='due'&&p.due_date&&p.due_date<new Date().toISOString().slice(0,10);
        return `<div class="kv"><b>${p.status==='paid'?'✅':(overdue?'⛔':'⏳')} ${fM(p.amount)} ${esc(p.currency)}</b>
        <span>${p.status==='paid'?'اتدفعت '+fD(p.pay_date):'مستحقة '+fD(p.due_date)+(overdue?' <b style="color:#dc2626">متأخرة!</b>':'')} ${esc(p.method||'')} ${p.note?'· '+esc(p.note):''}
        ${p.status==='due'?`<button class="btn btn-g btn-sm" onclick="markPaid(${p.id},${cid},${p.amount},'${esc(p.currency)}')">تحصيل</button>`:''}
        <button class="mclose" style="color:#dc2626" onclick="tryApi('clients.payment.delete',{id:${p.id}},this).then(()=>{toast('تم الحذف');loadClientPayments(${cid},${contract||0})})"><i class="ti ti-trash"></i></button></span></div>`;
      }).join('')||'<div style="color:var(--muted)">لا توجد دفعات مسجلة</div>'}`;
  }catch(e){ const el=$('cv-pay-list'); if(el) el.textContent='⚠️ '+e.message; }
}
function paymentModal(cid){
  openModal(mHead('💵 تسجيل دفعة')+`
    <div class="grid2">
      <div><label class="form-lbl">المبلغ *</label><input id="pm-amount" type="number" step="0.01"></div>
      <div><label class="form-lbl">العملة</label><select id="pm-currency"><option>EGP</option><option>SAR</option><option>USD</option></select></div>
      <div><label class="form-lbl">الحالة</label><select id="pm-status" onchange="$('pm-paydate-w').style.display=this.value==='paid'?'block':'none';$('pm-duedate-w').style.display=this.value==='due'?'block':'none'">
        <option value="paid">✅ اتدفعت فعلًا</option><option value="due">⏳ مستحقة (هتتدفع)</option></select></div>
      <div><label class="form-lbl">طريقة الدفع</label><select id="pm-method">${['تحويل بنكي','إنستاباي','فودافون كاش','كاش','شيك'].map(x=>`<option>${x}</option>`).join('')}</select></div>
      <div id="pm-paydate-w"><label class="form-lbl">تاريخ الدفع</label><input type="date" id="pm-pay_date" value="${new Date().toISOString().slice(0,10)}"></div>
      <div id="pm-duedate-w" style="display:none"><label class="form-lbl">تاريخ الاستحقاق</label><input type="date" id="pm-due_date"></div>
    </div>
    <label class="form-lbl">ملاحظة</label><input id="pm-note" placeholder="مثال: دفعة الربع التاني">
    <p style="font-size:11px;color:var(--muted)">💡 الدفعة المدفوعة بتتسجل تلقائيًا كإيراد في حسابات الشركة.</p>
    <button class="btn btn-p btn-full" onclick="tryApi('clients.payment.save',{client_id:${cid},amount:$('pm-amount').value,currency:$('pm-currency').value,status:$('pm-status').value,method:$('pm-method').value,pay_date:$('pm-pay_date').value,due_date:$('pm-due_date').value,note:$('pm-note').value},this).then(()=>{toast('✓ اتسجلت');closeModal();clientView(${cid})})">حفظ الدفعة</button>`);
}
async function markPaid(pid,cid,amount,currency){
  await tryApi('clients.payment.save',{id:pid,client_id:cid,amount,currency,status:'paid',pay_date:new Date().toISOString().slice(0,10),method:'تحويل بنكي'},null);
  toast('✓ اتحصّلت ونزلت في حسابات الشركة');
  clientView(cid);
}
/* ══ LEADS ══ */
const L_ST={new:['New Lead','#2563eb'],not_contacted:['لم يتم التواصل','#64748b'],contacted:['تم التواصل','#0ea5e9'],interested:['مهتم','#16a34a'],follow_up:['متابعة','#d97706'],meeting:['اجتماع محدد','#7c3aed'],proposal:['عرض مُرسل','#9d174d'],negotiation:['تفاوض','#e11d48'],won:['Won ✅','#16a34a'],lost:['Lost ❌','#dc2626'],no_answer:['لا يرد','#94a3b8'],not_qualified:['غير مؤهل','#64748b'],postponed:['مؤجل','#a16207']};
let LD_ROWS=[];
async function pgLeads(){
  const s=await api('leads.stats');
  $('content').innerHTML=`
    <div class="stats">
      ${[['Leads جديدة',s.new,'#2563eb'],['بدون تواصل',s.not_contacted,'#64748b'],['متابعات اليوم',s.today_followups,'#d97706'],
        ['صفقات مكسوبة',s.won,'#16a34a'],['مفقودة',s.lost,'#dc2626'],['معدل التحويل',s.conv_rate+'%','#7c3aed'],
        ['قيمة متوقعة (Pipeline)',fM(s.pipeline),'#0ea5e9']].map(x=>
        `<div class="stat"><div class="stat-lbl">${x[0]}</div><div class="stat-val" style="color:${x[2]}">${x[1]}</div></div>`).join('')}
    </div>
    <div class="toolbar">
      <input class="grow" id="ld-q" placeholder="🔍 اسم / رقم / شركة..." oninput="renderLeads()">
      <select id="ld-st" onchange="renderLeads()"><option value="">كل الحالات</option>${Object.entries(L_ST).map(([k,v])=>`<option value="${k}">${v[0]}</option>`).join('')}</select>
      <select id="ld-emp" onchange="renderLeads()"><option value="">كل الموظفين</option>${USERS_OPT.map(u=>`<option value="${u.id}">${esc(u.name)}</option>`).join('')}</select>
      ${can('manage_leads')?`<button class="btn btn-p" onclick="leadModal()"><i class="ti ti-plus"></i> Lead جديد</button>`:''}
    </div>
    <div class="tbl-wrap"><table><thead><tr><th>الاسم</th><th>الهاتف</th><th>المصدر</th><th>الخدمة</th><th>المسؤول</th><th>قيمة متوقعة</th><th>متابعة</th><th>الحالة</th><th></th></tr></thead>
    <tbody id="ld-tbody"></tbody></table></div>
    ${s.by_emp.length?`<div class="card" style="margin-top:14px"><h3><i class="ti ti-chart-bar"></i> أداء الموظفين</h3>
      <div class="tbl-wrap"><table><thead><tr><th>الموظف</th><th>Leads</th><th>Won</th><th>معدل</th></tr></thead><tbody>
      ${s.by_emp.map(e=>`<tr><td>${esc(e.name)}</td><td>${e.c}</td><td>${e.w||0}</td><td>${e.c?Math.round((e.w||0)*100/e.c):0}%</td></tr>`).join('')}
      </tbody></table></div></div>`:''}`;
  LD_ROWS=(await api('leads.list')).leads;
  renderLeads();
}
function renderLeads(){
  const q=($('ld-q').value||'').toLowerCase(), fs=$('ld-st').value, fe=$('ld-emp').value;
  const list=LD_ROWS.filter(l=>{
    if(fs&&l.status!==fs) return false;
    if(fe&&String(l.assignee)!==fe) return false;
    if(q&&![l.name,l.phone,l.company].join(' ').toLowerCase().includes(q)) return false;
    return true;
  });
  $('ld-tbody').innerHTML=list.length?list.map(l=>{
    const st=L_ST[l.status]||['?','#64748b'];
    return `<tr><td style="font-weight:700">${esc(l.name)}<div class="hmeta">${esc(l.company||'')}</div></td>
    <td dir="ltr">${esc(l.phone)} <a href="https://wa.me/${esc(l.phone.replace(/^0/,'2'))}" target="_blank" title="فتح واتساب"><i class="ti ti-brand-whatsapp" style="color:#16a34a"></i></a></td>
    <td>${esc(l.source||'—')}${l.campaign?`<div class="hmeta">${esc(l.campaign)}</div>`:''}</td>
    <td>${esc(l.service||'—')}</td><td>${esc(l.assignee_name||'—')}</td>
    <td>${l.expected_value?fM(l.expected_value):'—'}</td>
    <td>${l.next_date?fD(l.next_date):'—'}</td>
    <td><span class="pill" style="background:${st[1]}">${st[0]}</span></td>
    <td>${can('manage_leads')?`<button class="mclose" onclick="leadModal(LD_ROWS.find(x=>x.id==${l.id}))"><i class="ti ti-edit"></i></button>`:''}
      ${can('convert_lead')&&!l.converted_client_id&&l.status!=='lost'?`<button class="btn btn-g btn-sm" onclick="leadConvert(${l.id})">تحويل لعميل</button>`:''}
      ${l.converted_client_id?`<span class="tag">عميل #${l.converted_client_id}</span>`:''}
      ${can('manage_leads')?`<button class="mclose" style="color:#dc2626" onclick="confirmModal('حذف Lead','هيتنقل للسلة.',async b=>{await tryApi('leads.delete',{id:${l.id}},b);toast('تم');closeModal();pgLeads()})"><i class="ti ti-trash"></i></button>`:''}</td></tr>`;
  }).join(''):`<tr><td colspan="9"><div class="empty"><i class="ti ti-target-off"></i>لا توجد Leads</div></td></tr>`;
}
function leadModal(l=null){
  openModal(mHead(l?'✏️ تعديل Lead':'➕ Lead جديد')+`
    <div class="grid2">
      <div><label class="form-lbl">الاسم *</label><input id="lm-name" value="${l?esc(l.name):''}"></div>
      <div><label class="form-lbl">الهاتف * (فريد)</label><input id="lm-phone" dir="ltr" value="${l?esc(l.phone):''}"></div>
      <div><label class="form-lbl">واتساب</label><input id="lm-wa" dir="ltr" value="${l?esc(l.wa):''}"></div>
      <div><label class="form-lbl">الإيميل</label><input id="lm-email" dir="ltr" value="${l?esc(l.email):''}"></div>
      <div><label class="form-lbl">الشركة</label><input id="lm-company" value="${l?esc(l.company):''}"></div>
      <div><label class="form-lbl">النشاط</label><input id="lm-industry" value="${l?esc(l.industry):''}"></div>
      <div><label class="form-lbl">المصدر</label><select id="lm-source"><option value="">—</option>${listBy('lead_source').map(s=>`<option ${l&&l.source===s.label?'selected':''}>${esc(s.label)}</option>`).join('')}</select></div>
      <div><label class="form-lbl">الحملة</label><input id="lm-campaign" value="${l?esc(l.campaign):''}"></div>
      <div><label class="form-lbl">الخدمة المطلوبة</label><select id="lm-service"><option value="">—</option>${listBy('service').map(s=>`<option ${l&&l.service===s.label?'selected':''}>${esc(s.label)}</option>`).join('')}</select></div>
      <div><label class="form-lbl">الموظف المسؤول</label><select id="lm-assignee"><option value="">—</option>${USERS_OPT.map(u=>`<option value="${u.id}" ${l&&l.assignee==u.id?'selected':''}>${esc(u.name)}</option>`).join('')}</select></div>
      <div><label class="form-lbl">قيمة الصفقة المتوقعة</label><input id="lm-expected_value" type="number" value="${l?.expected_value??''}"></div>
      <div><label class="form-lbl">الحالة</label><select id="lm-status" onchange="$('lm-lost-w').style.display=this.value==='lost'?'block':'none'">${Object.entries(L_ST).map(([k,v])=>`<option value="${k}" ${l&&l.status===k?'selected':''}>${v[0]}</option>`).join('')}</select></div>
      <div><label class="form-lbl">آخر تواصل</label><input type="date" id="lm-last_contact" value="${l?.last_contact||''}"></div>
      <div><label class="form-lbl">تاريخ المتابعة</label><input type="date" id="lm-next_date" value="${l?.next_date||''}"></div>
      <div><label class="form-lbl">الإجراء القادم</label><input id="lm-next_action" value="${l?esc(l.next_action):''}"></div>
      <div><label class="form-lbl">رابط المحادثة</label><input id="lm-chat_link" dir="ltr" value="${l?esc(l.chat_link):''}"></div>
    </div>
    <div id="lm-lost-w" style="display:${l&&l.status==='lost'?'block':'none'}"><label class="form-lbl">سبب الخسارة</label><input id="lm-lost_reason" value="${l?esc(l.lost_reason):''}"></div>
    <label class="form-lbl">ملاحظات</label><textarea id="lm-notes">${l?esc(l.notes||''):''}</textarea>
    <button class="btn btn-p btn-full" onclick="saveLead(this,${l?l.id:0})">حفظ</button>`,true);
}
async function saveLead(b,id){
  const d={id:id||undefined};
  ['name','phone','wa','email','company','industry','source','campaign','service','assignee','expected_value','status','last_contact','next_date','next_action','chat_link','lost_reason','notes'].forEach(k=>d[k]=$('lm-'+k)?$('lm-'+k).value:'');
  await tryApi('leads.save',d,b); toast('✓ تم الحفظ'); closeModal(); pgLeads();
}
function leadConvert(id){
  const l=LD_ROWS.find(x=>x.id==id);
  openModal(mHead('🎉 تحويل "'+esc(l.name)+'" إلى عميل')+`
    <p style="font-size:12px;color:var(--sub);margin-bottom:10px">هيتم إنشاء عميل جديد بنفس البيانات، والاحتفاظ بسجل الـ Lead، وتغيير حالته إلى Won.</p>
    ${can('edit_financials')?`<label class="form-lbl">قيمة التعاقد الفعلية</label><input id="lc-val" type="number" value="${l.expected_value||''}">`:''}
    <button class="btn btn-g btn-full" onclick="tryApi('leads.convert',{id:${id},contract_value:$('lc-val')?$('lc-val').value:''},this).then(r=>{toast('✓ تم التحويل — عميل #'+r.client_id);closeModal();pgLeads()})">تأكيد التحويل</button>`);
}
</script>
<script>
/* ══ OWNER FINANCE ══ */
let FIN_CHART=null;
async function pgFin(){
  const s=await api('fin.stats');
  const diff=(a,b)=>{if(!b)return '';const p=Math.round((a-b)*100/b);return `<span style="font-size:10px;color:${p>=0?'#16a34a':'#dc2626'}">${p>=0?'▲':'▼'} ${Math.abs(p)}% عن الشهر السابق</span>`};
  $('content').innerHTML=`
    <div class="stats">
      <div class="stat"><div class="stat-lbl"><i class="ti ti-wallet"></i> الرصيد الحالي</div><div class="stat-val" style="color:${s.balance>=0?'#16a34a':'#dc2626'}">${fM(s.balance)}</div></div>
      <div class="stat"><div class="stat-lbl"><i class="ti ti-trending-up"></i> إجمالي الإيرادات</div><div class="stat-val" style="color:#16a34a">${fM(s.income)}</div></div>
      <div class="stat"><div class="stat-lbl"><i class="ti ti-trending-down"></i> إجمالي المصروفات</div><div class="stat-val" style="color:#dc2626">${fM(s.expense)}</div></div>
      <div class="stat"><div class="stat-lbl"><i class="ti ti-scale"></i> صافي الربح</div><div class="stat-val">${fM(s.net)}</div></div>
      <div class="stat"><div class="stat-lbl">إيرادات الشهر</div><div class="stat-val" style="color:#16a34a">${fM(s.month_income)}</div>${diff(s.month_income,s.prev_income)}</div>
      <div class="stat"><div class="stat-lbl">مصروفات الشهر</div><div class="stat-val" style="color:#dc2626">${fM(s.month_expense)}</div>${diff(s.month_expense,s.prev_expense)}</div>
    </div>
    <div style="display:grid;grid-template-columns:2fr 1fr;gap:12px;margin-bottom:14px">
      <div class="card"><h3><i class="ti ti-chart-line"></i> الإيرادات والمصروفات (6 شهور)</h3><canvas id="fin-chart" height="180"></canvas></div>
      <div class="card"><h3><i class="ti ti-chart-pie"></i> المصروفات حسب التصنيف</h3>
        ${s.by_cat.map(c=>`<div class="kv"><b>${esc(c.category||'بدون تصنيف')}</b><span style="color:#dc2626;font-weight:700">${fM(c.s)}</span></div>`).join('')||'<div class="empty">لا توجد مصروفات</div>'}</div>
    </div>
    <div class="toolbar">
      <select id="fn-kind" onchange="loadFin()"><option value="">الكل</option><option value="income">إيرادات</option><option value="expense">مصروفات</option><option value="opening">رصيد افتتاحي</option></select>
      <input type="date" id="fn-from" onchange="loadFin()"><input type="date" id="fn-to" onchange="loadFin()">
      <button class="btn btn-gh btn-sm" onclick="finCSV()"><i class="ti ti-file-spreadsheet"></i> CSV</button>
      <button class="btn btn-g" onclick="finModal(null,'income')"><i class="ti ti-plus"></i> إيراد</button>
      <button class="btn btn-r" onclick="finModal(null,'expense')"><i class="ti ti-plus"></i> مصروف</button>
      <button class="btn btn-gh" onclick="finModal(null,'opening')">رصيد افتتاحي</button>
    </div>
    <div class="tbl-wrap"><table><thead><tr><th>النوع</th><th>التاريخ</th><th>الوصف</th><th>التصنيف</th><th>الطريقة</th><th>العميل</th><th>المبلغ</th><th></th></tr></thead>
    <tbody id="fn-tbody"></tbody></table></div>`;
  if(FIN_CHART) FIN_CHART.destroy();
  if(window.Chart){ Chart.defaults.font.family='Cairo';
    FIN_CHART=new Chart($('fin-chart'),{type:'bar',data:{labels:s.months.map(m=>m.m),datasets:[
      {label:'إيرادات',data:s.months.map(m=>m.inc),backgroundColor:'#16a34a',borderRadius:6},
      {label:'مصروفات',data:s.months.map(m=>m.exp),backgroundColor:'#dc2626',borderRadius:6}]},
      options:{plugins:{legend:{position:'bottom'}}}});
  }
  loadFin();
}
let FN_ROWS=[];
async function loadFin(){
  const r=await api('fin.list',{kind:$('fn-kind').value,from:$('fn-from').value,to:$('fn-to').value});
  FN_ROWS=r.rows;
  const K={income:['إيراد','#16a34a'],expense:['مصروف','#dc2626'],opening:['رصيد افتتاحي','#2563eb']};
  $('fn-tbody').innerHTML=r.rows.length?r.rows.map(f=>{
    const k=K[f.kind];
    return `<tr><td><span class="pill" style="background:${k[1]}">${k[0]}</span></td><td>${fD(f.tx_date)}</td>
      <td style="font-weight:700">${esc(f.descr||'—')}${f.note?`<div class="hmeta">${esc(f.note)}</div>`:''}</td>
      <td>${esc(f.category||'—')}</td><td>${esc(f.method||'—')}</td><td>${esc(f.client_name||'—')}</td>
      <td style="font-weight:900;color:${k[1]}">${f.kind==='expense'?'-':'+'}${fM(f.amount)} ${esc(f.currency)}</td>
      <td><button class="mclose" onclick="finModal(FN_ROWS.find(x=>x.id==${f.id}))"><i class="ti ti-edit"></i></button>
      <button class="mclose" style="color:#dc2626" onclick="confirmModal('حذف الحركة','هتتنقل لسلة المهملات وتقدر تسترجعها.',async b=>{await tryApi('fin.delete',{id:${f.id}},b);toast('اتنقلت للسلة');closeModal();pgFin()})"><i class="ti ti-trash"></i></button></td></tr>`;
  }).join(''):`<tr><td colspan="8"><div class="empty"><i class="ti ti-cash-off"></i>لا توجد حركات</div></td></tr>`;
}
function finCSV(){
  csv('finance.csv',[['النوع','التاريخ','الوصف','التصنيف','الطريقة','المبلغ','العملة'],
    ...FN_ROWS.map(f=>[f.kind,f.tx_date,f.descr,f.category,f.method,f.amount,f.currency])]);
}
function finModal(f=null, kind='expense'){
  kind=f?f.kind:kind;
  openModal(mHead(f?'✏️ تعديل حركة':({income:'➕ إيراد جديد',expense:'➕ مصروف جديد',opening:'رصيد افتتاحي'}[kind]))+`
    <div class="grid2">
      <div><label class="form-lbl">النوع</label><select id="fm-kind"><option value="income" ${kind==='income'?'selected':''}>إيراد</option><option value="expense" ${kind==='expense'?'selected':''}>مصروف</option><option value="opening" ${kind==='opening'?'selected':''}>رصيد افتتاحي</option></select></div>
      <div><label class="form-lbl">المبلغ *</label><input id="fm-amount" type="number" step="0.01" value="${f?.amount??''}"></div>
      <div><label class="form-lbl">العملة</label><select id="fm-currency">${['EGP','SAR','USD','AED'].map(x=>`<option ${f&&f.currency===x?'selected':''}>${x}</option>`).join('')}</select></div>
      <div><label class="form-lbl">التاريخ</label><input type="date" id="fm-tx_date" value="${f?.tx_date||new Date().toISOString().slice(0,10)}"></div>
      <div><label class="form-lbl">التصنيف</label><select id="fm-category"><option value="">—</option>${listBy('fin_cat').map(c=>`<option ${f&&f.category===c.label?'selected':''}>${esc(c.label)}</option>`).join('')}</select></div>
      <div><label class="form-lbl">طريقة الدفع</label><select id="fm-method">${['تحويل بنكي','إنستاباي','فودافون كاش','كاش','شيك',''].map(x=>`<option ${f&&f.method===x?'selected':''}>${x||'—'}</option>`).join('')}</select></div>
    </div>
    <label class="form-lbl">الوصف</label><input id="fm-descr" value="${f?esc(f.descr):''}">
    <label class="form-lbl">ربط بعميل (اختياري)</label><select id="fm-client_id"><option value="">بدون</option>${(CL_ROWS||[]).map(c=>`<option value="${c.id}" ${f&&f.client_id==c.id?'selected':''}>${esc(c.name_ar)}</option>`).join('')}</select>
    <label class="form-lbl">ملاحظة</label><textarea id="fm-note">${f?esc(f.note||''):''}</textarea>
    <button class="btn btn-p btn-full" onclick="saveFin(this,${f?f.id:0})">حفظ</button>`);
  if(!CL_ROWS.length && (can('view_all_clients'))) api('clients.list').then(r=>{CL_ROWS=r.clients;
    $('fm-client_id').innerHTML='<option value="">بدون</option>'+CL_ROWS.map(c=>`<option value="${c.id}">${esc(c.name_ar)}</option>`).join('');});
}
async function saveFin(b,id){
  const d={id:id||undefined};
  ['kind','amount','currency','tx_date','category','method','descr','client_id','note'].forEach(k=>d[k]=$('fm-'+k).value);
  await tryApi('fin.save',d,b); toast('✓ تم الحفظ'); closeModal(); pgFin();
}

/* ══ USERS & ROLES ══ */
let U_ROWS=[], R_ROWS=[], ALL_PERMS=[];
const PERM_AR={view_all_clients:'رؤية كل العملاء',view_assigned_clients:'رؤية عملائه فقط',create_client:'إضافة عميل',edit_client:'تعديل عميل',delete_client:'حذف عميل',view_financials:'رؤية الماليات',edit_financials:'تعديل الماليات',delete_financials:'حذف حركات مالية',view_all_tasks:'رؤية كل المهام',view_assigned_tasks:'رؤية مهامه فقط',create_task:'إضافة مهمة',edit_task:'تعديل مهمة',delete_task:'حذف مهمة',edit_task_value:'تعديل قيم المهام',view_task_value:'رؤية قيم كل المهام',approve_leave:'الموافقة على الإجازات',reject_leave:'رفض الإجازات',manage_attendance:'لوحة الحضور',edit_attendance:'تعديل الحضور',manage_users:'إدارة المستخدمين',reset_user_password:'إعادة تعيين كلمات المرور',manage_roles:'إدارة الأدوار',manage_permissions:'إدارة الصلاحيات',view_leads:'رؤية الـ Leads',manage_leads:'إدارة الـ Leads',convert_lead:'تحويل Lead لعميل',manage_whatsapp:'إدارة واتساب',manage_devices:'إدارة الأجهزة',manage_settings:'الإعدادات',owner_finance:'فلوس الـ Owner 🔒',view_activity_log:'رؤية سجل النشاط الكامل',manage_trash:'سلة المهملات',company_finance:'حسابات الشركة 🏦',manage_renewals:'عملاء التجديد'};
async function pgUsers(){
  const [u,r]=await Promise.all([api('users.list'),api('roles.list')]);
  U_ROWS=u.users; R_ROWS=r.roles; ALL_PERMS=r.all_perms;
  $('content').innerHTML=`
    <div class="toolbar">
      <button class="btn btn-p" onclick="userModal()"><i class="ti ti-user-plus"></i> مستخدم جديد</button>
      ${can('manage_roles')?`<button class="btn btn-gh" onclick="roleModal()"><i class="ti ti-shield-plus"></i> دور جديد</button>`:''}
    </div>
    <div class="tbl-wrap"><table><thead><tr><th>المستخدم</th><th>الدور</th><th>القسم / المسمى</th><th>آخر دخول</th><th>الحالة</th><th>إجراءات</th></tr></thead>
    <tbody>${U_ROWS.map(x=>`<tr style="${x.active==0?'opacity:.55':''}">
      <td><b>${esc(x.name)}</b><div class="hmeta">@${esc(x.username)} · ${esc(x.email||'')}</div></td>
      <td><span class="tag">${esc(x.role_label)}</span>${x.extra_perms.length?`<span class="tag" title="صلاحيات إضافية">+${x.extra_perms.length}</span>`:''}${x.must_change_pw==1?`<span class="tag" style="color:#d97706">ينتظر تغيير الباسورد</span>`:''}</td>
      <td>${esc(x.dept||'—')} · ${esc(x.title||'—')}</td>
      <td style="color:var(--muted)">${x.last_login?fDT(x.last_login):'لم يدخل بعد'}</td>
      <td><span class="pill" style="background:${x.active==1?'#16a34a':'#64748b'}">${x.active==1?'نشط':'معطّل'}</span></td>
      <td>
        <button class="mclose" title="تعديل" onclick="userModal(U_ROWS.find(z=>z.id==${x.id}))"><i class="ti ti-edit"></i></button>
        ${can('reset_user_password')?`<button class="mclose" title="كلمة مرور" onclick="userPw(${x.id},'${esc(x.name)}')"><i class="ti ti-key"></i></button>`:''}
        <button class="mclose" title="${x.active==1?'تعطيل':'تفعيل'}" onclick="tryApi('users.toggle',{user_id:${x.id}},this).then(()=>{toast('✓ تم');pgUsers()})"><i class="ti ti-${x.active==1?'ban':'circle-check'}"></i></button>
        <button class="mclose" title="خروج من كل الأجهزة" onclick="tryApi('logout_all',{user_id:${x.id}},this).then(()=>toast('✓ تم تسجيل خروجه من كل الأجهزة'))"><i class="ti ti-device-laptop-off"></i></button>
        <button class="mclose" style="color:#dc2626" title="حذف" onclick="confirmModal('حذف المستخدم','هيتنقل للسلة ويتسجل خروجه.',async(b,nm)=>{await tryApi('users.delete',{user_id:${x.id},confirm_name:nm},b);toast('تم');closeModal();pgUsers()},'${esc(x.name)}')"><i class="ti ti-trash"></i></button>
      </td></tr>`).join('')}</tbody></table></div>
    <div class="card" style="margin-top:14px"><h3><i class="ti ti-shield"></i> الأدوار والصلاحيات</h3>
      ${R_ROWS.map(r=>`<div class="kv"><b>${esc(r.label)} <span class="tag">${r.users} مستخدم</span></b>
        <span>${r.perms.slice(0,5).map(p=>`<span class="tag">${PERM_AR[p]||p}</span>`).join('')}${r.perms.length>5?`<span class="tag">+${r.perms.length-5}</span>`:''}
        ${can('manage_roles')?`<button class="mclose" onclick="roleModal(R_ROWS.find(z=>z.id==${r.id}))"><i class="ti ti-edit"></i></button>
        ${r.name!=='super_admin'?`<button class="mclose" style="color:#dc2626" onclick="tryApi('roles.delete',{id:${r.id}},this).then(()=>{toast('تم الحذف');pgUsers()})"><i class="ti ti-trash"></i></button>`:''}`:''}</span></div>`).join('')}
    </div>`;
}
function userModal(x=null){
  openModal(mHead(x?'✏️ تعديل: '+esc(x.name):'➕ مستخدم جديد')+`
    <div class="grid2">
      <div><label class="form-lbl">الاسم *</label><input id="um-name" value="${x?esc(x.name):''}"></div>
      <div><label class="form-lbl">اسم المستخدم *</label><input id="um-username" dir="ltr" value="${x?esc(x.username):''}"></div>
      <div><label class="form-lbl">الإيميل</label><input id="um-email" dir="ltr" value="${x?esc(x.email):''}"></div>
      <div><label class="form-lbl">الهاتف</label><input id="um-phone" dir="ltr" value="${x?esc(x.phone):''}"></div>
      <div><label class="form-lbl">القسم</label><select id="um-dept"><option value="">—</option>${listBy('department').map(d=>`<option ${x&&x.dept===d.label?'selected':''}>${esc(d.label)}</option>`).join('')}</select></div>
      <div><label class="form-lbl">المسمى الوظيفي</label><input id="um-title" value="${x?esc(x.title):''}"></div>
      <div><label class="form-lbl">الدور</label><select id="um-role">${R_ROWS.map(r=>`<option value="${r.id}" ${x&&x.role_id==r.id?'selected':''}>${esc(r.label)}</option>`).join('')}</select></div>
      ${!x?`<div><label class="form-lbl">كلمة مرور مبدئية (8+)</label><input id="um-password" type="text" autocomplete="off" placeholder="هيتطلب منه تغييرها أول دخول"></div>`:''}
      ${can('company_finance')?`<div><label class="form-lbl">💵 الراتب الشهري (سري)</label><input id="um-salary" type="number" value="${x&&x.salary!=null?x.salary:''}" placeholder="يُستخدم في صرف الرواتب"></div>`:''}
    </div>
    ${can('manage_permissions')?`<label class="form-lbl">صلاحيات إضافية فوق صلاحيات الدور</label>${chkGrid(ALL_PERMS.map(p=>({id:p,label:PERM_AR[p]||p})), x?x.extra_perms:[], 'um-xp')}`:''}
    <button class="btn btn-p btn-full" onclick="saveUser(this,${x?x.id:0})">حفظ</button>`,true);
}
async function saveUser(b,id){
  const d={id:id||undefined,extra_perms:can('manage_permissions')?chkVals('um-xp'):undefined};
  ['name','username','email','phone','title','password','salary'].forEach(k=>{if($('um-'+k))d[k]=$('um-'+k).value});
  d.dept=$('um-dept').value; d.role_id=$('um-role').value;
  await tryApi('users.save',d,b); toast('✓ تم الحفظ'); closeModal(); pgUsers();
}
function userPw(id,name){
  openModal(mHead('🔑 كلمة مرور جديدة — '+esc(name))+`
    <label class="form-lbl">الجديدة (8+ أحرف)</label><input id="up-p" type="text" autocomplete="off">
    <label class="form-lbl">تأكيد</label><input id="up-c" type="text" autocomplete="off">
    <label style="display:flex;gap:7px;align-items:center;font-size:12px;margin-bottom:11px;cursor:pointer"><input type="checkbox" id="up-force" checked style="margin:0"> إجباره على تغييرها أول دخول</label>
    <button class="btn btn-p btn-full" onclick="tryApi('users.set_pw',{user_id:${id},password:$('up-p').value,confirm:$('up-c').value,force_change:$('up-force').checked?1:0},this).then(()=>{toast('✓ تم تعيينها وتسجيل خروجه من كل الأجهزة');closeModal()})">تعيين</button>`);
}
function roleModal(r=null){
  openModal(mHead(r?'✏️ تعديل دور: '+esc(r.label):'➕ دور جديد')+`
    <label class="form-lbl">اسم الدور</label><input id="rm-label" value="${r?esc(r.label):''}">
    <label class="form-lbl">الصلاحيات</label>
    ${chkGrid(ALL_PERMS.map(p=>({id:p,label:PERM_AR[p]||p})), r?r.perms:[], 'rm-p')}
    <button class="btn btn-p btn-full" onclick="tryApi('roles.save',{id:${r?r.id:0},label:$('rm-label').value,perms:chkVals('rm-p')},this).then(()=>{toast('✓ تم');closeModal();pgUsers()})">حفظ</button>`,true);
}

/* ══ DEVICES ══ */
async function pgDevices(){
  const r=await api('devices.list');
  const pend=r.devices.filter(d=>d.approved==0);
  $('content').innerHTML=`
    ${pend.length?`<div class="card" style="border-color:#dc2626"><h3 style="color:#dc2626"><i class="ti ti-alert-triangle"></i> أجهزة تنتظر الاعتماد (${pend.length})</h3></div>`:''}
    <div class="tbl-wrap"><table><thead><tr><th>الموظف</th><th>الجهاز</th><th>آخر استخدام</th><th>الحالة</th><th></th></tr></thead>
    <tbody>${r.devices.length?r.devices.map(d=>`<tr>
      <td style="font-weight:700">${esc(d.user_name)}</td>
      <td>${esc(d.name)}<div class="hmeta">${esc(d.os)} · ${esc(d.browser)}</div></td>
      <td>${fDT(d.last_used)}</td>
      <td><span class="pill" style="background:${d.approved==1?'#16a34a':'#d97706'}">${d.approved==1?'معتمد':'ينتظر الاعتماد'}</span></td>
      <td>${d.approved==0?`<button class="btn btn-g btn-sm" onclick="tryApi('devices.approve',{id:${d.id}},this).then(()=>{toast('✓ تم الاعتماد وإخطار الموظف');pgDevices()})">اعتماد</button>`
        :`<button class="btn btn-gh btn-sm" onclick="tryApi('devices.revoke',{id:${d.id}},this).then(()=>{toast('تم التعطيل');pgDevices()})">تعطيل</button>`}
        <button class="mclose" style="color:#dc2626" onclick="tryApi('devices.delete',{id:${d.id}},this).then(()=>{toast('تم الحذف');pgDevices()})"><i class="ti ti-trash"></i></button></td>
    </tr>`).join(''):'<tr><td colspan="5"><div class="empty">لا توجد أجهزة مسجلة</div></td></tr>'}</tbody></table></div>
    <p style="font-size:11px;color:var(--muted);margin-top:10px">💡 نظام اعتماد الأجهزة بيتفعّل من الإعدادات. لما يكون شغال: أي جهاز جديد يحاول الدخول بيتسجل هنا ويستنى موافقتك.</p>`;
}

/* ══ TRASH ══ */
async function pgTrash(){
  const r=await api('trash.list');
  const T={clients:'العملاء',users:'المستخدمون',tasks:'المهام',finance:'الحركات المالية',leads:'Leads'};
  $('content').innerHTML=Object.entries(r.trash).map(([type,items])=>`
    <div class="card"><h3><i class="ti ti-trash"></i> ${T[type]} (${items.length})</h3>
    ${items.length?items.map(x=>`<div class="kv"><b>${esc(x.label)}</b>
      <span><span class="hmeta">${fDT(x.deleted_at)}</span>
      <button class="btn btn-g btn-sm" onclick="tryApi('trash.restore',{type:'${type}',id:${x.id}},this).then(()=>{toast('✓ تم الاسترجاع');pgTrash()})"><i class="ti ti-restore"></i> استرجاع</button>
      ${ME.role==='super_admin'?`<button class="btn btn-r btn-sm" onclick="confirmModal('حذف نهائي','⚠️ الحذف النهائي لا رجعة فيه إطلاقًا!',async b=>{await tryApi('trash.purge',{type:'${type}',id:${x.id}},b);toast('تم الحذف نهائيًا');closeModal();pgTrash()})">حذف نهائي</button>`:''}</span></div>`).join('')
    :'<div style="font-size:12px;color:var(--muted)">فاضي</div>'}</div>`).join('');
}

/* ══ ACTIVITY LOG ══ */
async function pgLog(){
  const r=await api('log.list');
  $('content').innerHTML=`<div class="card"><h3><i class="ti ti-history"></i> سجل النشاط ${can('view_activity_log')?'(كامل)':'(نشاطي فقط)'}</h3>
    <input placeholder="🔍 فلترة..." oninput="document.querySelectorAll('.log-row').forEach(x=>x.style.display=x.textContent.includes(this.value)?'':'none')">
    ${r.log.length?r.log.map(l=>`<div class="hist log-row"><div class="hdot"></div><div style="flex:1">
      <div><b>${esc(l.user_name)}</b> — ${esc(l.action)}${l.entity?': <b>'+esc(l.entity)+'</b>':''}
      ${l.old_v||l.new_v?`<span style="color:var(--muted)"> (${esc(l.old_v)} ← ${esc(l.new_v)})</span>`:''}</div>
      <div class="hmeta">${fDT(l.at)} ${l.ip?'· '+esc(l.ip):''}</div></div></div>`).join('')
    :'<div class="empty">لا يوجد نشاط</div>'}</div>`;
}

/* ══ SETTINGS ══ */
async function pgSettings(){
  const s=(await api('settings.get')).settings;
  const lst=(k,title)=>`<div class="card"><h3>${title}</h3><div id="ls-${k}">
    ${listBy(k).map(x=>`<div style="display:flex;gap:7px;align-items:center;padding:5px 0;border-bottom:1px dashed var(--border)">
      <input type="color" value="${x.color}" style="width:34px;padding:2px;height:28px;margin:0" onchange="tryApi('lists.save',{id:${x.id},kind:'${k}',label:'${esc(x.label)}',color:this.value},null).then(()=>toast('✓'))">
      <input value="${esc(x.label)}" style="margin:0;flex:1;padding:6px 9px" onchange="tryApi('lists.save',{id:${x.id},kind:'${k}',label:this.value,color:'${x.color}'},null).then(()=>toast('✓'))">
      <button class="mclose" style="color:#dc2626" onclick="tryApi('lists.delete',{id:${x.id}},this).then(()=>{toast('تم');loadCommon().then(pgSettings)})"><i class="ti ti-trash"></i></button></div>`).join('')}
    </div><button class="btn btn-gh btn-sm" style="margin-top:8px" onclick="const l=prompt('الاسم:');if(l)tryApi('lists.save',{kind:'${k}',label:l},this).then(()=>loadCommon().then(pgSettings))">+ إضافة</button></div>`;
  $('content').innerHTML=`
    <div class="card"><h3><i class="ti ti-shield-lock"></i> سياسات الدخول والأجهزة</h3>
      ${[['block_mobile','🚫 منع تسجيل الدخول من الهواتف (من السيرفر)'],['block_tablet','منع الدخول من التابلت'],['device_approval','✅ تفعيل نظام الأجهزة المعتمدة (أي جهاز جديد يستنى موافقتك)']].map(([k,lb])=>`
      <label style="display:flex;gap:8px;align-items:center;padding:7px 0;font-size:12.5px;font-weight:700;cursor:pointer">
        <input type="checkbox" ${s[k]==='1'?'checked':''} style="margin:0" onchange="tryApi('settings.set',{${k}:this.checked?'1':'0'},null).then(()=>toast('✓ تم الحفظ'))"> ${lb}</label>`).join('')}
      <label class="form-lbl" style="margin-top:8px">حصر تسجيل الحضور في IPs معينة (افصل بفاصلة — سيبها فاضية للسماح من أي مكان)</label>
      <input value="${esc(s.attendance_ips||'')}" dir="ltr" placeholder="مثال: 156.220." onchange="tryApi('settings.set',{attendance_ips:this.value},null).then(()=>toast('✓'))">
      <div class="grid2">
        <div><label class="form-lbl">بداية الدوام</label><input type="time" value="${s.work_start||'10:00'}" onchange="tryApi('settings.set',{work_start:this.value},null).then(()=>toast('✓'))"></div>
        <div><label class="form-lbl">يعتبر متأخر بعد</label><input type="time" value="${s.late_after||'10:15'}" onchange="tryApi('settings.set',{late_after:this.value},null).then(()=>toast('✓'))"></div>
      </div>
      <p style="font-size:11px;color:var(--muted)">💡 لاستثناء موظف معين من منع الموبايل/اعتماد الأجهزة: افتح المستخدمين ← تعديل ← فعّل "استثناء من قيود الأجهزة" (متاح للـ Owner افتراضيًا).</p>
    </div>
    <div class="card"><h3><i class="ti ti-brand-whatsapp"></i> واتساب</h3>
      <p style="font-size:12px;color:var(--sub);margin-bottom:8px">النظام جاهز لاستقبال Webhook رسمي من WhatsApp Cloud API: أي رسالة جديدة على رقم الشركة بتتحول تلقائيًا لـ Lead. عنوان الـ Webhook:</p>
      <input readonly dir="ltr" value="${location.origin+location.pathname.replace('index.php','')}wa_webhook.php" onclick="this.select()">
      <label class="form-lbl">Verify Token (هتحطه في إعدادات Meta)</label>
      <input readonly dir="ltr" value="${esc(s.wa_verify_token||'')}" onclick="this.select()">
      <p style="font-size:11px;color:var(--muted)">ولحد ما تفعّل الـ API الرسمي: كل رقم في صفحة Leads جنبه زر واتساب بيفتح المحادثة مباشرة.</p>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:12px">
      ${lst('client_status','حالات العملاء')}${lst('service','الخدمات')}${lst('department','الأقسام')}${lst('fin_cat','تصنيفات مالية')}${lst('lead_source','مصادر الـ Leads')}
    </div>`;
}

boot();
</script>
<script>
/* ═══ v2 MODULE: داشبورد غنية + حسابات الشركة + عملاء تجديد + دفعات العملاء ═══ */

/* بادج التجديدات الحمرا في القائمة */
async function updateRenewBadge(){
  if(!can('manage_renewals')) return;
  try{
    const r=await api('renewals.list');
    const n=r.renewals.filter(x=>x.days_left!=null && x.days_left<=30).length;
    const b=$('bdg-renewals');
    if(b){ if(n>0){b.textContent=n;b.classList.remove('hide')} else b.classList.add('hide'); }
  }catch(e){}
}

/* ══ الداشبورد الغنية (تستبدل pgDash القديمة) ══ */
async function pgDash(){
  const r=await api('dash.summary');
  const att=r.attendance;
  let cards=[];
  cards.push({lb:'مهامي المفتوحة',v:r.my_open_tasks,ic:'ti-checklist',go:'tasks'});
  if(r.pending_approvals!=null) cards.push({lb:'طلبات تنتظر قرارك',v:r.pending_approvals,ic:'ti-inbox',go:'leaves',hot:r.pending_approvals>0});
  if(r.clients!=null) cards.push({lb:'العملاء',v:r.clients,ic:'ti-building-store',go:'clients'});
  if(r.leads_new!=null) cards.push({lb:'Leads جديدة',v:r.leads_new,ic:'ti-target-arrow',go:'leads'});
  if(r.today_followups!=null&&r.today_followups>0) cards.push({lb:'متابعات مبيعات اليوم',v:r.today_followups,ic:'ti-phone-call',go:'leads',hot:1});
  if(r.present_today!=null) cards.push({lb:'الحاضرون اليوم',v:r.present_today,ic:'ti-user-check',go:'attendance'});
  if(r.co_month_income!=null) cards.push({lb:'إيراد الشركة (الشهر)',v:fM(r.co_month_income),ic:'ti-trending-up',go:'cofinance'});
  if(r.co_month_expense!=null) cards.push({lb:'مصاريف الشركة (الشهر)',v:fM(r.co_month_expense),ic:'ti-trending-down',go:'cofinance'});
  if(r.pending_devices) cards.push({lb:'أجهزة تنتظر الاعتماد',v:r.pending_devices,ic:'ti-devices',go:'devices',hot:1});

  const T_ST_MINI={new:['جديدة','#2563eb'],in_progress:['جارية','#d97706'],waiting_review:['مراجعة','#7c3aed'],revision:['تعديلات','#e11d48'],completed:['مكتملة','#16a34a'],cancelled:['ملغاة','#64748b']};
  const tbs=r.tasks_by_status||{};

  let widgets='';
  // عملاء التجديد (أحمر)
  if(r.renewals_soon&&r.renewals_soon.length){
    widgets+=`<div class="card" style="border-color:#dc2626"><h3 style="color:#dc2626"><i class="ti ti-refresh-alert"></i> 🔴 تجديدات ويب/استضافة قربت (${r.renewals_soon.length})</h3>
      ${r.renewals_soon.map(x=>`<div class="kv"><b>${esc(x.name)} <span class="hmeta" dir="ltr">${esc(x.phone||'')}</span></b>
        <span><span class="pill" style="background:${x.days_left<0?'#7f1d1d':x.days_left<=7?'#dc2626':'#d97706'}">${x.days_left<0?'فات بـ'+Math.abs(x.days_left)+' يوم!':x.days_left==0?'النهارده!':'باقي '+x.days_left+' يوم'}</span> ${fD(x.renew_date)}</span></div>`).join('')}
      <button class="btn btn-gh btn-sm" style="margin-top:8px" onclick="go('renewals')">فتح عملاء التجديد ←</button></div>`;
  }
  // تجديدات عقود CRM
  if(r.client_renewals&&r.client_renewals.length){
    widgets+=`<div class="card"><h3><i class="ti ti-calendar-repeat"></i> عقود عملاء هتتجدد خلال 30 يوم</h3>
      ${r.client_renewals.map(x=>`<div class="kv"><b>${esc(x.name_ar)}</b><span><span class="tag" style="color:#7c3aed">باقي ${x.days_left} يوم</span> ${fD(x.renew_date)}</span></div>`).join('')}</div>`;
  }
  // دفعات متأخرة
  if(r.overdue_payments&&r.overdue_payments.length){
    widgets+=`<div class="card" style="border-color:#d97706"><h3 style="color:#d97706"><i class="ti ti-alert-circle"></i> دفعات متأخرة عن ميعادها</h3>
      ${r.overdue_payments.map(x=>`<div class="kv"><b>${esc(x.name_ar)}</b><span style="color:#dc2626;font-weight:700">${fM(x.amount)} — كانت مستحقة ${fD(x.due_date)}</span></div>`).join('')}</div>`;
  }
  // مهامي القادمة
  if(r.my_upcoming&&r.my_upcoming.length){
    const today=new Date().toISOString().slice(0,10);
    widgets+=`<div class="card"><h3><i class="ti ti-clock"></i> أقرب استحقاقات مهامي</h3>
      ${r.my_upcoming.map(t=>`<div class="kv"><b style="cursor:pointer" onclick="go('tasks')">${esc(t.title)}</b>
      <span style="${t.deadline<today?'color:#dc2626;font-weight:900':''}">${fD(t.deadline)}${t.deadline<today?' ⚠️ متأخرة':''}</span></div>`).join('')}</div>`;
  }

  $('content').innerHTML=`
    <div class="att-hero">
      <div style="font-size:12px;opacity:.7">أهلًا ${esc(ME.name)} 👋</div>
      <div class="att-time" id="clock">--:--</div>
      <div style="font-size:11px;opacity:.6" id="today-date"></div>
      ${!att? `<button class="btn btn-g att-btn" onclick="checkIn(this)"><i class="ti ti-fingerprint"></i> تسجيل الحضور</button>`
       : !att.out_time? `<div style="margin-top:8px;font-size:12px">✅ حضرت الساعة ${att.in_time.slice(11,16)} ${att.status==='late'?'(متأخر)':''}</div>
          <button class="btn btn-r att-btn" onclick="checkOut(this)"><i class="ti ti-logout"></i> تسجيل الانصراف</button>`
       : `<div style="margin-top:8px;font-size:12px">✅ يوم مكتمل: ${att.in_time.slice(11,16)} → ${att.out_time.slice(11,16)}</div>`}
    </div>
    <div class="stats">${cards.map(c=>`<div class="stat" style="cursor:pointer${c.hot?';border-color:#dc2626':''}" onclick="go('${c.go}')">
      <div class="stat-lbl"><i class="ti ${c.ic}"></i> ${c.lb}</div><div class="stat-val" style="font-size:${String(c.v).length>8?'15':'20'}px">${c.v}</div></div>`).join('')}</div>
    ${Object.keys(tbs).length?`<div class="card"><h3><i class="ti ti-layout-kanban"></i> المهام على البورد</h3>
      <div style="display:flex;gap:8px;flex-wrap:wrap">${Object.entries(T_ST_MINI).map(([k,[lb,c]])=>
        `<div style="flex:1;min-width:90px;text-align:center;background:var(--hover);border-radius:10px;padding:10px;cursor:pointer" onclick="go('tasks')">
        <div style="font-size:19px;font-weight:900;color:${c}">${tbs[k]||0}</div><div style="font-size:10.5px;color:var(--sub);font-weight:700">${lb}</div></div>`).join('')}</div></div>`:''}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:12px">${widgets}</div>`;
  clockTick();
}

/* ══ عملاء تجديد ══ */
let RN_ROWS=[];
async function pgRenewals(){
  $('content').innerHTML=`
    <div class="toolbar">
      <input class="grow" id="rn-q" placeholder="🔍 بحث بالاسم أو الرقم..." oninput="renderRenewals()">
      <select id="rn-f" onchange="renderRenewals()">
        <option value="">الكل</option><option value="soon">🔴 خلال 30 يوم</option><option value="late">⛔ فات ميعادها</option><option value="ok">✅ لسه بدري</option>
      </select>
      <button class="btn btn-gh btn-sm" onclick="renewalsCSV()"><i class="ti ti-file-spreadsheet"></i> CSV</button>
      <button class="btn btn-p" onclick="renewalModal()"><i class="ti ti-plus"></i> عميل تجديد جديد</button>
    </div>
    <div class="tbl-wrap"><table><thead><tr><th>العميل</th><th>الهاتف</th><th>الموقع</th><th>تاريخ التجديد</th><th>الباقي</th><th>قيمة التجديد</th><th>ملاحظات</th><th></th></tr></thead>
    <tbody id="rn-tbody"></tbody></table></div>
    <p style="font-size:11px;color:var(--muted);margin-top:10px">💡 أي عميل باقي على تجديده 30 يوم أو أقل بيظهر بتحذير أحمر هنا وفي الداشبورد وعلى القائمة الجانبية.</p>`;
  RN_ROWS=(await api('renewals.list')).renewals;
  renderRenewals();
  updateRenewBadge();
}
function renderRenewals(){
  const q=($('rn-q').value||'').toLowerCase(), f=$('rn-f').value;
  const list=RN_ROWS.filter(x=>{
    const d=+x.days_left;
    if(f==='soon'&&!(d>=0&&d<=30)) return false;
    if(f==='late'&&!(d<0)) return false;
    if(f==='ok'&&!(d>30)) return false;
    if(q&&![x.name,x.phone].join(' ').toLowerCase().includes(q)) return false;
    return true;
  });
  $('rn-tbody').innerHTML=list.length?list.map(x=>{
    const d=+x.days_left;
    const alert=d<=30;
    const pill=d<0?['فات بـ'+Math.abs(d)+' يوم!','#7f1d1d']:d===0?['النهارده!','#dc2626']:d<=7?['باقي '+d+' يوم','#dc2626']:d<=30?['باقي '+d+' يوم','#d97706']:['باقي '+d+' يوم','#16a34a'];
    return `<tr style="${alert?'background:rgba(220,38,38,.05)':''}">
      <td style="font-weight:700">${alert?'🔴 ':''}${esc(x.name)}</td>
      <td dir="ltr">${esc(x.phone||'—')} ${x.phone?`<a href="https://wa.me/${esc(x.phone.replace(/^0/,'2'))}" target="_blank"><i class="ti ti-brand-whatsapp" style="color:#16a34a"></i></a>`:''}</td>
      <td>${x.site_link?`<a href="${esc(x.site_link)}" target="_blank">فتح الموقع ↗</a>`:'—'}</td>
      <td style="font-weight:700">${fD(x.renew_date)}</td>
      <td><span class="pill" style="background:${pill[1]}">${pill[0]}</span></td>
      <td>${x.amount?fM(x.amount):'—'}</td>
      <td style="color:var(--sub)">${esc(x.notes||'—')}</td>
      <td><button class="mclose" onclick="renewalModal(RN_ROWS.find(z=>z.id==${x.id}))"><i class="ti ti-edit"></i></button>
      <button class="mclose" style="color:#dc2626" onclick="confirmModal('حذف عميل التجديد','متأكد من حذف ${esc(x.name)}؟',async b=>{await tryApi('renewals.delete',{id:${x.id}},b);toast('تم الحذف');closeModal();pgRenewals()})"><i class="ti ti-trash"></i></button></td></tr>`;
  }).join(''):`<tr><td colspan="8"><div class="empty"><i class="ti ti-refresh"></i>لا يوجد عملاء تجديد</div></td></tr>`;
}
function renewalsCSV(){
  csv('renewals.csv',[['العميل','الهاتف','الموقع','تاريخ التجديد','الباقي (يوم)','القيمة','ملاحظات'],
    ...RN_ROWS.map(x=>[x.name,x.phone,x.site_link,x.renew_date,x.days_left,x.amount||'',x.notes])]);
}
function renewalModal(x=null){
  openModal(mHead(x?'✏️ تعديل: '+esc(x.name):'➕ عميل تجديد جديد')+`
    <label class="form-lbl">اسم العميل *</label><input id="rw-name" value="${x?esc(x.name):''}">
    <div class="grid2">
      <div><label class="form-lbl">رقم الهاتف</label><input id="rw-phone" dir="ltr" value="${x?esc(x.phone):''}"></div>
      <div><label class="form-lbl">تاريخ التجديد *</label><input type="date" id="rw-date" value="${x?.renew_date||''}"></div>
    </div>
    <label class="form-lbl">لينك الموقع</label><input id="rw-link" dir="ltr" placeholder="https://" value="${x?esc(x.site_link):''}">
    <div class="grid2">
      <div><label class="form-lbl">قيمة التجديد (اختياري)</label><input id="rw-amount" type="number" value="${x?.amount??''}"></div>
      <div><label class="form-lbl">ملاحظات</label><input id="rw-notes" value="${x?esc(x.notes):''}"></div>
    </div>
    <button class="btn btn-p btn-full" onclick="tryApi('renewals.save',{id:${x?x.id:0},name:$('rw-name').value,phone:$('rw-phone').value,site_link:$('rw-link').value,renew_date:$('rw-date').value,amount:$('rw-amount').value,notes:$('rw-notes').value},this).then(()=>{toast('✓ تم الحفظ');closeModal();pgRenewals()})">حفظ</button>`);
}

/* ══ حسابات الشركة ══ */
let COF_CHART=null, COF_ROWS=[];
async function pgCoFin(){
  const s=await api('cofin.stats');
  $('content').innerHTML=`
    <div class="stats">
      <div class="stat"><div class="stat-lbl"><i class="ti ti-trending-up"></i> إجمالي إيرادات الشركة</div><div class="stat-val" style="color:#16a34a">${fM(s.income)}</div></div>
      <div class="stat"><div class="stat-lbl"><i class="ti ti-trending-down"></i> إجمالي المصاريف</div><div class="stat-val" style="color:#dc2626">${fM(s.expense)}</div></div>
      <div class="stat"><div class="stat-lbl"><i class="ti ti-scale"></i> صافي ربح الشركة</div><div class="stat-val" style="color:${s.net>=0?'#16a34a':'#dc2626'}">${fM(s.net)}</div></div>
      <div class="stat"><div class="stat-lbl">إيراد الشهر</div><div class="stat-val" style="color:#16a34a">${fM(s.month_income)}</div></div>
      <div class="stat"><div class="stat-lbl">مصاريف الشهر</div><div class="stat-val" style="color:#dc2626">${fM(s.month_expense)}</div></div>
      <div class="stat"><div class="stat-lbl"><i class="ti ti-percentage"></i> نسبة التحصيل</div><div class="stat-val" style="color:${s.collection_rate>=80?'#16a34a':'#d97706'}">${s.collection_rate}%</div></div>
    </div>
    <div style="display:grid;grid-template-columns:2fr 1fr;gap:12px;margin-bottom:14px">
      <div class="card"><h3><i class="ti ti-chart-line"></i> إيرادات × مصاريف الشركة (6 شهور)</h3><canvas id="cof-chart" height="170"></canvas></div>
      <div class="card"><h3><i class="ti ti-chart-pie"></i> المصاريف حسب التصنيف</h3>
        ${s.by_cat.map(c=>`<div class="kv"><b>${esc(c.category||'بدون تصنيف')}</b><span style="color:#dc2626;font-weight:700">${fM(c.s)}</span></div>`).join('')||'<div class="empty">لا توجد مصاريف</div>'}</div>
    </div>
    <div class="toolbar">
      <select id="cof-kind" onchange="loadCoFin()"><option value="">الكل</option><option value="income">إيرادات</option><option value="expense">مصاريف</option></select>
      <input type="date" id="cof-from" onchange="loadCoFin()"><input type="date" id="cof-to" onchange="loadCoFin()">
      <button class="btn btn-gh btn-sm" onclick="cofCSV()"><i class="ti ti-file-spreadsheet"></i> CSV</button>
      <button class="btn btn-g" onclick="cofModal(null,'income')"><i class="ti ti-plus"></i> إيراد يدوي</button>
      <button class="btn btn-r" onclick="cofModal(null,'expense')"><i class="ti ti-plus"></i> مصروف</button>
      <button class="btn btn-p" onclick="payrollModal()"><i class="ti ti-users-group"></i> 💵 صرف رواتب الشهر</button>
    </div>
    <div class="tbl-wrap"><table><thead><tr><th>النوع</th><th>التاريخ</th><th>الوصف</th><th>التصنيف</th><th>الطريقة</th><th>المصدر</th><th>المبلغ</th><th></th></tr></thead>
    <tbody id="cof-tbody"></tbody></table></div>
    <p style="font-size:11px;color:var(--muted);margin-top:10px">💡 دفعات العملاء اللي بتسجلها من ملف العميل بتنزل هنا تلقائيًا كإيراد (مصدرها "دفعة عميل"). الرواتب بتتحدد لكل موظف من صفحة المستخدمين.</p>`;
  if(COF_CHART) COF_CHART.destroy();
  if(window.Chart){ Chart.defaults.font.family='Cairo';
    COF_CHART=new Chart($('cof-chart'),{type:'bar',data:{labels:s.months.map(m=>m.m),datasets:[
      {label:'إيرادات',data:s.months.map(m=>m.inc),backgroundColor:'#16a34a',borderRadius:6},
      {label:'مصاريف',data:s.months.map(m=>m.exp),backgroundColor:'#dc2626',borderRadius:6}]},
      options:{plugins:{legend:{position:'bottom'}}}});
  }
  loadCoFin();
}
async function loadCoFin(){
  const r=await api('cofin.list',{kind:$('cof-kind').value,from:$('cof-from').value,to:$('cof-to').value});
  COF_ROWS=r.rows;
  const SRC={manual:'يدوي',payment:'دفعة عميل',payroll:'رواتب'};
  $('cof-tbody').innerHTML=r.rows.length?r.rows.map(f=>{
    const inc=f.kind==='income';
    return `<tr><td><span class="pill" style="background:${inc?'#16a34a':'#dc2626'}">${inc?'إيراد':'مصروف'}</span></td>
      <td>${fD(f.tx_date)}</td>
      <td style="font-weight:700">${esc(f.descr||'—')}${f.note?`<div class="hmeta">${esc(f.note)}</div>`:''}</td>
      <td>${esc(f.category||'—')}</td><td>${esc(f.method||'—')}</td>
      <td><span class="tag">${SRC[f.source]||f.source}</span></td>
      <td style="font-weight:900;color:${inc?'#16a34a':'#dc2626'}">${inc?'+':'-'}${fM(f.amount)} ${esc(f.currency)}</td>
      <td><button class="mclose" onclick="cofModal(COF_ROWS.find(x=>x.id==${f.id}))"><i class="ti ti-edit"></i></button>
      <button class="mclose" style="color:#dc2626" onclick="confirmModal('حذف الحركة','هتتشال من حسابات الشركة.',async b=>{await tryApi('cofin.delete',{id:${f.id}},b);toast('تم');closeModal();pgCoFin()})"><i class="ti ti-trash"></i></button></td></tr>`;
  }).join(''):`<tr><td colspan="8"><div class="empty"><i class="ti ti-cash-off"></i>لا توجد حركات</div></td></tr>`;
}
function cofCSV(){
  csv('company-finance.csv',[['النوع','التاريخ','الوصف','التصنيف','الطريقة','المصدر','المبلغ','العملة'],
    ...COF_ROWS.map(f=>[f.kind==='income'?'إيراد':'مصروف',f.tx_date,f.descr,f.category,f.method,f.source,f.amount,f.currency])]);
}
function cofModal(f=null, kind='expense'){
  kind=f?f.kind:kind;
  openModal(mHead(f?'✏️ تعديل حركة':({income:'➕ إيراد يدوي',expense:'➕ مصروف شركة'}[kind]))+`
    <div class="grid2">
      <div><label class="form-lbl">النوع</label><select id="cf2-kind"><option value="income" ${kind==='income'?'selected':''}>إيراد</option><option value="expense" ${kind==='expense'?'selected':''}>مصروف</option></select></div>
      <div><label class="form-lbl">المبلغ *</label><input id="cf2-amount" type="number" step="0.01" value="${f?.amount??''}"></div>
      <div><label class="form-lbl">التاريخ</label><input type="date" id="cf2-tx_date" value="${f?.tx_date||new Date().toISOString().slice(0,10)}"></div>
      <div><label class="form-lbl">التصنيف</label><select id="cf2-category"><option value="">—</option>${listBy('fin_cat').map(c=>`<option ${f&&f.category===c.label?'selected':''}>${esc(c.label)}</option>`).join('')}</select></div>
      <div><label class="form-lbl">طريقة الدفع</label><select id="cf2-method">${['تحويل بنكي','إنستاباي','فودافون كاش','كاش','فيزا',''].map(x=>`<option ${f&&f.method===x?'selected':''}>${x||'—'}</option>`).join('')}</select></div>
      <div><label class="form-lbl">العملة</label><select id="cf2-currency">${['EGP','SAR','USD'].map(x=>`<option ${f&&f.currency===x?'selected':''}>${x}</option>`).join('')}</select></div>
    </div>
    <label class="form-lbl">الوصف</label><input id="cf2-descr" value="${f?esc(f.descr):''}">
    <label class="form-lbl">ملاحظة</label><textarea id="cf2-note">${f?esc(f.note||''):''}</textarea>
    <button class="btn btn-p btn-full" onclick="saveCoFin(this,${f?f.id:0})">حفظ</button>`);
}
async function saveCoFin(b,id){
  const d={id:id||undefined};
  ['kind','amount','tx_date','category','method','currency','descr','note'].forEach(k=>d[k]=$('cf2-'+k).value);
  await tryApi('cofin.save',d,b); toast('✓ تم الحفظ'); closeModal(); pgCoFin();
}
async function payrollModal(){
  const m=new Date().toISOString().slice(0,7);
  const r=await api('payroll.preview',{month:m});
  if(!r.employees.length){
    openModal(mHead('💵 صرف رواتب '+m)+`<p style="font-size:12.5px;color:var(--sub)">مفيش موظفين محدد لهم راتب لسه. افتح <b>المستخدمون</b> ← تعديل الموظف ← اكتب راتبه الشهري، وارجع هنا.</p>
    <button class="btn btn-p btn-full" onclick="closeModal();go('users')">فتح المستخدمين</button>`);
    return;
  }
  openModal(mHead('💵 صرف رواتب شهر '+m)+`
    ${r.already_run?`<div style="background:rgba(217,119,6,.1);color:#d97706;font-size:12px;font-weight:700;border-radius:9px;padding:9px;margin-bottom:10px">⚠️ رواتب الشهر ده اتصرفت قبل كده — الصرف التاني هيترفض. تقدر تعدل البنود من جدول الحركات.</div>`:''}
    <p style="font-size:11.5px;color:var(--sub);margin-bottom:10px">راجع المبالغ وعدّل اللي محتاج (خصم/حافز/سلفة) واكتب السبب في الملاحظة، وبعدين اعتمد:</p>
    <div id="pr-rows">${r.employees.map(e=>`
      <div style="display:grid;grid-template-columns:1.2fr 1fr 1.3fr;gap:7px;align-items:center;padding:6px 0;border-bottom:1px dashed var(--border)" data-uid="${e.id}">
        <div style="font-size:12px;font-weight:700">${esc(e.name)}<div class="hmeta">${esc(e.title||e.dept||'')}</div></div>
        <input type="number" class="pr-amt" value="${e.salary}" style="margin:0;padding:7px 9px">
        <input class="pr-note" placeholder="ملاحظة (خصم/حافز...)" style="margin:0;padding:7px 9px">
      </div>`).join('')}</div>
    <div style="display:flex;justify-content:space-between;font-size:12.5px;font-weight:900;padding:10px 0"><span>الإجمالي:</span><span id="pr-total" style="color:#dc2626"></span></div>
    <button class="btn btn-p btn-full" ${r.already_run?'disabled':''} onclick="commitPayroll(this,'${m}')">✅ اعتماد وصرف الرواتب</button>`,true);
  const tot=()=>{$('pr-total').textContent=fM([...document.querySelectorAll('.pr-amt')].reduce((s,x)=>s+(+x.value||0),0))+' ج'};
  document.querySelectorAll('.pr-amt').forEach(x=>x.oninput=tot); tot();
}
async function commitPayroll(b,m){
  const rows=[...document.querySelectorAll('#pr-rows > div')].map(d=>({user_id:+d.dataset.uid,amount:+d.querySelector('.pr-amt').value||0,note:d.querySelector('.pr-note').value}));
  const r=await tryApi('payroll.commit',{month:m,rows},b);
  toast('✓ تم صرف رواتب '+r.count+' موظف وتسجيلها في المصاريف');
  closeModal(); pgCoFin();
}
</script>

</body>
</html>
