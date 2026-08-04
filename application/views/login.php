<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="<?= $this->security->get_csrf_hash() ?>">
  <title>Jadi Motor — Login</title>
  <link rel="icon" href="<?=base_url()?>assets/dist/img/LOGO JDM BW.jpg" type="image/x-icon">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?=base_url()?>assets/bower_components/font-awesome/css/font-awesome.min.css">
  <style>
    *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

    :root {
      --green:      #00a65a;
      --green-dark: #008d4c;
      --green-glow: rgba(0,166,90,.25);
      --blue:       #3c8dbc;
      --bg-body:    #0d1117;
      --bg-left:    #0a0f1a;
      --bg-right:   #111520;
      --card-bg:    #161b2e;
      --border:     rgba(255,255,255,.07);
      --text-main:  #f3f4f6;
      --text-muted: #6b7280;
      --input-bg:   #1c2136;
      --input-border: #252d4a;
    }
    body.login-light {
      --bg-body:    #eef0f5;
      --bg-left:    #1a2744;
      --bg-right:   #f4f6fb;
      --card-bg:    #ffffff;
      --border:     rgba(0,0,0,.08);
      --text-main:  #111827;
      --text-muted: #6b7280;
      --input-bg:   #f9fafb;
      --input-border: #d1d5db;
    }

    html, body {
      height: 100%;
      font-family: 'Inter', sans-serif;
      background: var(--bg-body);
      overflow: hidden;
    }

    /* ── Split wrapper ───────────────────────────────── */
    .split {
      display: flex;
      height: 100vh;
      width: 100vw;
    }

    /* ══════════════════════════════════════════════════
       LEFT PANEL
    ══════════════════════════════════════════════════ */
    .panel-left {
      flex: 0 0 46%;
      position: relative;
      background: var(--bg-left);
      overflow: hidden;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      padding: 48px 52px;
    }

    /* mesh gradient overlay */
    .panel-left::before {
      content: '';
      position: absolute; inset: 0; z-index: 0;
      background:
        radial-gradient(ellipse 90% 70% at 20% 110%, rgba(0,166,90,.28) 0%, transparent 55%),
        radial-gradient(ellipse 70% 60% at 90% -10%, rgba(60,141,188,.22) 0%, transparent 55%),
        radial-gradient(ellipse 50% 40% at 50% 50%,  rgba(0,166,90,.05) 0%, transparent 60%);
    }

    /* grid lines decoration */
    .panel-left::after {
      content: '';
      position: absolute; inset: 0; z-index: 0;
      background-image:
        linear-gradient(rgba(255,255,255,.025) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255,255,255,.025) 1px, transparent 1px);
      background-size: 52px 52px;
    }

    .left-content {
      position: relative; z-index: 1;
      display: flex; flex-direction: column; height: 100%;
    }

    /* Logo + brand */
    .left-brand {
      display: flex; align-items: center; gap: 14px;
      margin-bottom: auto;
    }
    .left-logo {
      width: 52px; height: 52px;
      border-radius: 14px;
      object-fit: cover;
      border: 2px solid rgba(0,166,90,.45);
      box-shadow: 0 0 24px rgba(0,166,90,.3);
    }
    .left-brand-name {
      font-size: 20px; font-weight: 700; color: #fff;
      line-height: 1.2;
    }
    .left-brand-sub {
      font-size: 12px; color: rgba(255,255,255,.45); margin-top: 2px;
    }

    /* Hero text */
    .left-hero {
      flex: 1;
      display: flex; flex-direction: column; justify-content: center;
      padding: 40px 0;
    }
    .left-eyebrow {
      font-size: 11px; font-weight: 600; letter-spacing: 3px;
      text-transform: uppercase;
      color: var(--green);
      margin-bottom: 18px;
      display: flex; align-items: center; gap: 10px;
    }
    .left-eyebrow::before {
      content: '';
      display: inline-block; width: 28px; height: 2px;
      background: var(--green);
      border-radius: 2px;
    }
    .left-headline {
      font-size: clamp(30px, 3vw, 42px);
      font-weight: 800; line-height: 1.18;
      color: #fff; margin-bottom: 20px;
      letter-spacing: -.5px;
    }
    .left-headline span {
      background: linear-gradient(135deg, #00a65a 0%, #34d399 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }
    .left-desc {
      font-size: 14px; color: rgba(255,255,255,.5);
      line-height: 1.7; max-width: 340px;
      margin-bottom: 44px;
    }

    /* Feature pills */
    .left-features {
      display: flex; flex-direction: column; gap: 14px;
    }
    .feat-row {
      display: flex; align-items: center; gap: 14px;
    }
    .feat-icon {
      width: 36px; height: 36px; border-radius: 10px;
      background: rgba(0,166,90,.12);
      border: 1px solid rgba(0,166,90,.25);
      display: flex; align-items: center; justify-content: center;
      flex-shrink: 0;
    }
    .feat-icon i { font-size: 14px; color: var(--green); }
    .feat-label {
      font-size: 13px; font-weight: 500; color: rgba(255,255,255,.75);
    }

    /* Bottom left footer */
    .left-foot {
      font-size: 11px; color: rgba(255,255,255,.25);
      letter-spacing: .3px;
    }

    /* Floating decoration circles */
    .deco-circle {
      position: absolute; border-radius: 50%;
      border: 1px solid rgba(0,166,90,.15);
      pointer-events: none; z-index: 0;
    }
    .deco-c1 { width: 320px; height: 320px; bottom: -80px; right: -100px; }
    .deco-c2 { width: 180px; height: 180px; bottom: 20px;  right: -40px;
               border-color: rgba(60,141,188,.2); }
    .deco-c3 { width:  80px; height:  80px; top: 160px; right: 60px;
               border-color: rgba(0,166,90,.2);
               background: rgba(0,166,90,.04); }

    /* vertical divider line */
    .divider {
      flex: 0 0 1px;
      background: linear-gradient(to bottom,
        transparent 0%,
        var(--border) 20%,
        var(--border) 80%,
        transparent 100%);
    }

    /* ══════════════════════════════════════════════════
       RIGHT PANEL
    ══════════════════════════════════════════════════ */
    .panel-right {
      flex: 1;
      background: var(--bg-right);
      display: flex; align-items: center; justify-content: center;
      position: relative; overflow: hidden;
    }
    .panel-right::before {
      content: '';
      position: absolute; inset: 0; z-index: 0;
      background:
        radial-gradient(ellipse 60% 50% at 80% 90%, rgba(0,166,90,.05) 0%, transparent 60%),
        radial-gradient(ellipse 50% 40% at 20% 10%, rgba(60,141,188,.04) 0%, transparent 60%);
    }

    /* card — wraps all right-side content */
    .form-wrap {
      position: relative; z-index: 1;
      width: 100%; max-width: 430px;
      margin: 0 32px;
      background: var(--card-bg);
      border: 1px solid var(--border);
      border-radius: 22px;
      padding: 40px 40px 32px;
      box-shadow: 0 24px 64px rgba(0,0,0,.28);
    }
    body.login-light .form-wrap {
      box-shadow: 0 8px 40px rgba(0,0,0,.09);
    }

    /* brand inside card */
    .r-brand {
      display: flex; align-items: center; gap: 14px;
      margin-bottom: 32px;
    }
    .r-brand img {
      width: 52px; height: 52px; border-radius: 14px;
      object-fit: cover;
      border: 2px solid rgba(0,166,90,.35);
      box-shadow: 0 4px 12px rgba(0,0,0,.2);
    }
    .r-brand-name {
      font-size: 18px; font-weight: 700; color: var(--text-main);
      line-height: 1.2;
    }
    .r-brand-sub {
      font-size: 12px; color: var(--text-muted); margin-top: 2px;
    }

    /* heading */
    .r-heading { margin-bottom: 28px; }
    .r-heading h2 {
      font-size: 24px; font-weight: 700;
      color: var(--text-main); letter-spacing: -.4px;
    }
    .r-heading p {
      font-size: 13px; color: var(--text-muted); margin-top: 5px;
    }

    /* ── Alert ──────────────────────────────────────── */
    .alert {
      border-radius: 10px; padding: 12px 14px;
      font-size: 13px; font-weight: 500;
      display: none; align-items: flex-start; gap: 10px;
      margin-bottom: 20px;
    }
    .alert-error  { background:rgba(239,68,68,.1);  border:1px solid rgba(239,68,68,.25);  color:#f87171; }
    .alert-success{ background:rgba(0,166,90,.1);   border:1px solid rgba(0,166,90,.25);   color:#34d399; }
    .alert i { font-size:15px; margin-top:1px; flex-shrink:0; }
    .alert.show { display: flex; }
    @keyframes shake {
      0%,100%{ transform:translateX(0); }
      20%    { transform:translateX(-6px); }
      40%    { transform:translateX(6px); }
      60%    { transform:translateX(-4px); }
      80%    { transform:translateX(4px); }
    }
    .alert-error.shake { animation: shake .4s ease; }

    /* ── Form inputs ─────────────────────────────────── */
    .form-group { margin-bottom: 16px; }
    .form-group label {
      display: block;
      font-size: 11.5px; font-weight: 600;
      color: var(--text-muted);
      text-transform: uppercase; letter-spacing: .6px;
      margin-bottom: 7px;
    }
    .input-wrap { position: relative; }
    .input-wrap i.icon-left {
      position: absolute; left: 14px; top: 50%;
      transform: translateY(-50%);
      color: var(--text-muted); font-size: 14px;
      pointer-events: none; transition: color .2s;
    }
    .input-wrap input {
      width: 100%;
      background: var(--input-bg);
      border: 1.5px solid var(--input-border);
      border-radius: 12px;
      padding: 13px 14px 13px 42px;
      color: var(--text-main);
      font-size: 14px; font-family: 'Inter', sans-serif;
      outline: none;
      transition: border-color .2s, box-shadow .2s, background .2s;
    }
    .input-wrap input::placeholder { color: var(--text-muted); opacity: .55; }
    .input-wrap input:focus {
      border-color: var(--green);
      box-shadow: 0 0 0 3px var(--green-glow);
    }
    .input-wrap:focus-within i.icon-left { color: var(--green); }
    .input-wrap .icon-right {
      position: absolute; right: 14px; top: 50%;
      transform: translateY(-50%);
      color: var(--text-muted); font-size: 14px;
      cursor: pointer; pointer-events: all; transition: color .2s;
    }
    .input-wrap .icon-right:hover { color: var(--text-main); }
    .input-wrap input.has-right { padding-right: 42px; }

    /* ── Rate limit bar ──────────────────────────────── */
    .rate-bar {
      height: 3px; border-radius: 2px;
      background: rgba(239,68,68,.15);
      margin-top: 6px; overflow: hidden; display: none;
    }
    .rate-bar-fill { height:100%; border-radius:2px; background:#ef4444; transition:width .3s; }
    .rate-bar.show { display: block; }

    /* ── Submit button ───────────────────────────────── */
    .btn-login {
      width: 100%; padding: 14px;
      background: linear-gradient(135deg, #00a65a 0%, #00c96e 100%);
      border: none; border-radius: 12px;
      color: #fff; font-size: 15px; font-weight: 600;
      font-family: 'Inter', sans-serif;
      cursor: pointer; margin-top: 8px;
      display: flex; align-items: center; justify-content: center; gap: 9px;
      transition: opacity .2s, transform .15s, box-shadow .2s;
      box-shadow: 0 4px 20px rgba(0,166,90,.35);
      letter-spacing: .2px;
    }
    .btn-login:hover:not(:disabled) {
      opacity: .92; transform: translateY(-1px);
      box-shadow: 0 8px 28px rgba(0,166,90,.45);
    }
    .btn-login:active:not(:disabled) { transform: translateY(0); }
    .btn-login:disabled { opacity: .6; cursor: not-allowed; box-shadow: none; }
    .btn-login.loading .icon-arrow { display: none; }
    .btn-login.success { background: linear-gradient(135deg, #059669, #10b981); }

    .spinner {
      width: 16px; height: 16px;
      border: 2px solid rgba(255,255,255,.3);
      border-top-color: #fff; border-radius: 50%;
      animation: spin .7s linear infinite; display: none;
    }
    .btn-login.loading .spinner { display: block; }
    @keyframes spin { to { transform: rotate(360deg); } }

    /* ── Footer inside card ──────────────────────────── */
    .r-footer {
      margin-top: 24px; padding-top: 20px;
      border-top: 1px solid var(--border);
      text-align: center;
      font-size: 11.5px; color: var(--text-muted);
    }

    /* ── Theme toggle ────────────────────────────────── */
    #login-theme-toggle {
      position: fixed; top: 16px; right: 18px;
      background: rgba(255,255,255,.06);
      border: 1px solid var(--border);
      color: var(--text-muted);
      font-size: 14px; cursor: pointer;
      padding: 8px 14px; border-radius: 8px;
      transition: background .2s, color .2s;
      z-index: 10;
    }
    #login-theme-toggle:hover {
      background: rgba(255,255,255,.12); color: #fff;
    }
    body.login-light #login-theme-toggle {
      background: rgba(0,0,0,.04); color: #6b7280;
    }
    body.login-light #login-theme-toggle:hover {
      background: rgba(0,0,0,.08); color: #111;
    }

    /* ── Responsive ──────────────────────────────────── */
    @media (max-width: 860px) {
      .panel-left  { display: none; }
      .divider     { display: none; }
      .panel-right { background: var(--bg-body); }
      .form-wrap   { margin: 20px 16px; }
    }

    /* ══════════════════════════════════════════════════
       GREETING OVERLAY
    ══════════════════════════════════════════════════ */
    #greeting-overlay {
      position: fixed; inset: 0; z-index: 999;
      display: flex; align-items: center; justify-content: center;
      background: #0d1117;
      opacity: 0; pointer-events: none;
      transition: opacity .35s ease;
    }
    body.login-light #greeting-overlay { background: #f3f4f6; }
    #greeting-overlay.show { opacity: 1; pointer-events: all; }

    .g-blob {
      position: absolute; border-radius: 50%;
      filter: blur(80px); opacity: .35;
      animation: blobFloat 5s ease-in-out infinite alternate;
    }
    .g-blob-1 { width:400px; height:400px; background:#00a65a; top:-80px; left:-80px; }
    .g-blob-2 { width:300px; height:300px; background:#3c8dbc; bottom:-60px; right:-60px; animation-delay:-2s; }
    body.login-light .g-blob-1 { opacity:.1; }
    body.login-light .g-blob-2 { opacity:.08; }
    @keyframes blobFloat {
      from { transform: scale(1) translate(0,0); }
      to   { transform: scale(1.15) translate(20px,-20px); }
    }

    .g-content {
      position: relative; z-index: 1; text-align: center;
      animation: gFadeUp .5s ease both; animation-delay: .15s; opacity: 0;
    }
    #greeting-overlay.show .g-content { opacity: 1; }
    @keyframes gFadeUp {
      from { opacity:0; transform:translateY(24px); }
      to   { opacity:1; transform:translateY(0); }
    }
    .g-check {
      width:72px; height:72px;
      background: rgba(0,166,90,.15); border:2px solid rgba(0,166,90,.5);
      border-radius:50%; display:flex; align-items:center; justify-content:center;
      margin:0 auto 24px;
      animation: popIn .4s cubic-bezier(.175,.885,.32,1.275) both; animation-delay:.3s;
    }
    .g-check i { font-size:30px; color:#00a65a; }
    @keyframes popIn {
      from { opacity:0; transform:scale(.5); }
      to   { opacity:1; transform:scale(1); }
    }
    .g-salam {
      font-size:13px; letter-spacing:2px; text-transform:uppercase;
      color:#6b7280; margin-bottom:8px;
    }
    body.login-light .g-salam { color:#9ca3af; }
    .g-nama {
      font-size:32px; font-weight:700; color:#f9fafb;
      margin-bottom:12px; line-height:1.2;
    }
    body.login-light .g-nama { color:#111827; }
    .g-badge {
      display:inline-block; padding:4px 16px; border-radius:20px;
      font-size:12px; font-weight:600; letter-spacing:.5px; text-transform:uppercase;
      margin-bottom:28px;
    }
    .g-badge.lvl-1 { background:rgba(220,53,69,.15);  color:#f87171; border:1px solid rgba(220,53,69,.3); }
    .g-badge.lvl-2 { background:rgba(60,141,188,.15); color:#60a5fa; border:1px solid rgba(60,141,188,.3); }
    .g-badge.lvl-3 { background:rgba(0,166,90,.15);   color:#34d399; border:1px solid rgba(0,166,90,.3); }
    .g-badge.lvl-4 { background:rgba(243,156,18,.15); color:#fbbf24; border:1px solid rgba(243,156,18,.3); }
    .g-sub {
      font-size:13px; color:#6b7280; margin-bottom:36px;
    }
    body.login-light .g-sub { color:#9ca3af; }
    .g-bar-wrap {
      width:240px; height:3px; background:rgba(255,255,255,.08);
      border-radius:2px; overflow:hidden; margin:0 auto;
    }
    body.login-light .g-bar-wrap { background:rgba(0,0,0,.08); }
    .g-bar-fill {
      height:100%; width:0%;
      background:linear-gradient(90deg,#00a65a,#34d399); border-radius:2px;
      transition:width 1.8s cubic-bezier(.4,0,.2,1);
    }
  </style>
</head>
<body>

<!-- Greeting Overlay -->
<div id="greeting-overlay">
  <div class="g-blob g-blob-1"></div>
  <div class="g-blob g-blob-2"></div>
  <div class="g-content">
    <div class="g-check"><i class="fa fa-check"></i></div>
    <div class="g-salam" id="g-salam">Selamat Datang</div>
    <div class="g-nama"  id="g-nama">—</div>
    <div class="g-badge" id="g-badge">—</div>
    <div class="g-sub"   id="g-sub">Mengarahkan ke dashboard...</div>
    <div class="g-bar-wrap"><div class="g-bar-fill" id="g-bar"></div></div>
  </div>
</div>

<!-- Theme Toggle -->
<button id="login-theme-toggle" title="Ganti tema gelap/terang">
  <i class="fa fa-moon-o" id="login-theme-icon"></i>
</button>

<div class="split">

  <!-- ═══ LEFT PANEL ═══ -->
  <div class="panel-left">
    <div class="deco-circle deco-c1"></div>
    <div class="deco-circle deco-c2"></div>
    <div class="deco-circle deco-c3"></div>

    <div class="left-content">
      <!-- Brand -->
      <div class="left-brand">
        <img src="<?=base_url()?>assets/dist/img/LOGO JDM BW.jpg" alt="Logo" class="left-logo">
        <div>
          <div class="left-brand-name">Jadi Motor Bandung</div>
          <div class="left-brand-sub">Jalan Banceuy Gg.Cikapundung No.18</div>
          <div class="left-brand-sub">Bandung, Jawa Barat</div>
        </div>
      </div>

      <!-- Hero -->
      <div class="left-hero">
        <div class="left-eyebrow">Sistem Management Toko Jadi Motor</div>
        <h1 class="left-headline">
          Halo, selamat<br>
          datang <span>kembali</span>
        </h1>
        <p class="left-desc">
          Portal operasional Jadi Motor 
        </p>

        <div class="left-features">
          <div class="feat-row">
            <div class="feat-icon"><i class="fa fa-lock"></i></div>
            <div class="feat-label">Akses terbatas untuk staf terdaftar</div>
          </div>
          <div class="feat-row">
            <div class="feat-icon"><i class="fa fa-users"></i></div>
            <div class="feat-label">Hak akses sesuai jabatan & divisi</div>
          </div>
          <div class="feat-row">
            <div class="feat-icon"><i class="fa fa-history"></i></div>
            <div class="feat-label">Aktivitas tercatat & terlacak</div>
          </div>
          <div class="feat-row">
            <div class="feat-icon"><i class="fa fa-life-ring"></i></div>
            <div class="feat-label">Hubungi admin jika lupa password</div>
          </div>
        </div>
      </div>

      <!-- Bottom -->
      <div class="left-foot">
        &copy; <?= date('Y') ?> Jadi Motor &mdash; All rights reserved
      </div>
    </div>
  </div>

  <!-- divider -->
  <div class="divider"></div>

  <!-- ═══ RIGHT PANEL ═══ -->
  <div class="panel-right">
    <div class="form-wrap">

      <!-- Brand -->
      <div class="r-brand">
        <img src="<?=base_url()?>assets/dist/img/LOGO JDM BW.jpg" alt="Logo">
        <div>
          <div class="r-brand-name">Jadi Motor </div>
          <div class="r-brand-sub">Sistem Manajemen Toko</div>
        </div>
      </div>

      <!-- Heading -->
      <div class="r-heading">
        <h2>Selamat Datang</h2>
        <p>Masuk untuk melanjutkan ke sesi</p>
      </div>

      <!-- Alert Error -->
      <div class="alert alert-error" id="alert-error" role="alert">
        <i class="fa fa-exclamation-circle"></i>
        <span id="alert-error-msg"></span>
      </div>

      <!-- Alert Success -->
      <div class="alert alert-success" id="alert-success" role="alert">
        <i class="fa fa-check-circle"></i>
        <span>Login berhasil! Mengarahkan...</span>
      </div>

      <!-- Rate limit bar -->
      <div class="rate-bar" id="rate-bar">
        <div class="rate-bar-fill" id="rate-bar-fill" style="width:0%"></div>
      </div>

      <!-- Form -->
      <form id="login-form" novalidate>
        <input type="hidden" name="csrf_token" id="csrf_token" value="<?= $this->security->get_csrf_hash() ?>">

        <div class="form-group">
          <label for="username">Username</label>
          <div class="input-wrap">
            <i class="fa fa-user icon-left"></i>
            <input type="text" name="username" id="username"
                   placeholder="Masukkan username" required autofocus autocomplete="username">
          </div>
        </div>

        <div class="form-group">
          <label for="password">Password</label>
          <div class="input-wrap">
            <i class="fa fa-lock icon-left"></i>
            <input type="password" name="password" id="password" class="has-right"
                   placeholder="Masukkan password" required autocomplete="current-password">
            <i class="fa fa-eye icon-right" id="toggle-pw" title="Tampilkan password"></i>
          </div>
        </div>

        <button type="submit" class="btn-login" id="btn-login">
          <div class="spinner"></div>
          <i class="fa fa-sign-in icon-arrow"></i>
          <span id="btn-text">Masuk</span>
        </button>
      </form>

      <!-- Footer -->
      <div class="r-footer">
        &copy; <?= date('Y') ?> Jadi Motor Bandung
      </div>

    </div>
  </div>

</div><!-- .split -->

<script src="<?=base_url()?>assets/bower_components/jquery/dist/jquery.min.js"></script>
<script>
// ── Theme ─────────────────────────────────────────────
(function() {
  var isDark = localStorage.getItem('jdm_theme') !== 'light';
  function applyLoginTheme(dark) {
    if (dark) {
      document.body.classList.remove('login-light');
      document.getElementById('login-theme-icon').className = 'fa fa-sun-o';
    } else {
      document.body.classList.add('login-light');
      document.getElementById('login-theme-icon').className = 'fa fa-moon-o';
    }
  }
  applyLoginTheme(isDark);
  document.getElementById('login-theme-toggle').addEventListener('click', function() {
    isDark = !isDark;
    localStorage.setItem('jdm_theme', isDark ? 'dark' : 'light');
    applyLoginTheme(isDark);
  });
})();

var loginAttempts = 0;
var MAX_ATTEMPTS  = 5;

// Toggle password
$('#toggle-pw').on('click', function() {
  var $pw   = $('#password');
  var show  = $pw.attr('type') === 'password';
  $pw.attr('type', show ? 'text' : 'password');
  $(this).toggleClass('fa-eye fa-eye-slash');
});

function showError(msg) {
  var $alert = $('#alert-error');
  $('#alert-success').removeClass('show');
  $alert.find('#alert-error-msg').text(msg);
  $alert.addClass('show').removeClass('shake');
  void $alert[0].offsetWidth;
  $alert.addClass('shake');

  loginAttempts++;
  if (loginAttempts >= 2) {
    var pct = Math.min((loginAttempts / MAX_ATTEMPTS) * 100, 100);
    $('#rate-bar').addClass('show');
    $('#rate-bar-fill').css('width', pct + '%');
  }
}

function getGreeting() {
  var h = new Date().getHours();
  if (h >= 5  && h < 12) return 'Selamat Pagi';
  if (h >= 12 && h < 15) return 'Selamat Siang';
  if (h >= 15 && h < 18) return 'Selamat Sore';
  return 'Selamat Malam';
}

var motif = {
  1: 'Semua sistem siap. Selamat memimpin hari ini.',
  2: 'Sistem siap dikelola. Selamat bekerja!',
  3: 'Semangat melayani pelanggan hari ini!',
  4: 'Gudang menanti. Selamat bertugas!',
};

function showGreeting(nama, levelName, level, redirectUrl) {
  $('#g-salam').text(getGreeting() + ',');
  $('#g-nama').text(nama);
  $('#g-badge').text(levelName).attr('class', 'g-badge lvl-' + level);
  $('#g-sub').text(motif[level] || 'Selamat bekerja!');

  $('#greeting-overlay').addClass('show');
  setTimeout(function() { $('#g-bar').css('width', '100%'); }, 100);
  setTimeout(function() { window.location.href = redirectUrl; }, 2100);
}

function resetBtn() {
  $('#btn-login').prop('disabled', false).removeClass('loading success');
  $('#btn-text').text('Masuk');
}

// Submit
$('#login-form').on('submit', function(e) {
  e.preventDefault();
  var $btn = $('#btn-login');
  $btn.prop('disabled', true).addClass('loading');
  $('#btn-text').text('Memproses...');
  $('#alert-error').removeClass('show shake');
  $('#alert-success').removeClass('show');

  $.ajax({
    url     : '<?= site_url('auth/process') ?>',
    method  : 'POST',
    data    : {
      username   : $('#username').val(),
      password   : $('#password').val(),
      csrf_token : $('#csrf_token').val()
    },
    dataType: 'json',
    success : function(res) {
      if (res.success) {
        loginAttempts = 0;
        showGreeting(res.nama, res.level_name, res.level, res.redirect);
      } else {
        if (res.csrf_token) $('#csrf_token').val(res.csrf_token);
        showError(res.message || 'Username atau password salah.');
        resetBtn();
        $('#password').val('').focus();
      }
    },
    error: function() {
      showError('Terjadi kesalahan server. Coba beberapa saat lagi.');
      resetBtn();
    }
  });
});

$('#username, #password').on('input', function() {
  if ($('#alert-error').hasClass('show')) {
    $('#alert-error').removeClass('show shake');
  }
});
</script>
</body>
</html>
