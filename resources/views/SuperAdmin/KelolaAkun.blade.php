<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Kelola Akun - SIAPABAJA</title>
  <link rel="stylesheet" href="{{ asset('css/Unit.css') }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>

<body class="dash-body page-sa-akun">
<div class="mob-topbar" id="mobTopbar">
  <div class="mob-logo">
    <img src="{{ asset('image/Logo_Unsoed.png') }}" alt="Logo">
    <span class="mob-logo-txt">SIAPABAJA</span>
  </div>

  <button class="mob-ham" id="mobHamBtn" aria-label="Buka menu">
    <i class="bi bi-list"></i>
  </button>
</div>

<div class="sidebar-drawer-backdrop" id="sidebarBackdrop"></div>
@php
  $user = auth()->user();
  $name     = $superAdminName ?? ($user->name ?? 'Super Admin');
  $email    = $superAdminEmail ?? ($user->email ?? 'superadmin@gmail.com');
  $roleText = $roleText ?? 'SUPER ADMIN';
  $initials = strtoupper(mb_substr(trim($name), 0, 1));

  $kelolaAkunActive = request()->routeIs('superadmin.kelola.akun')
    || request()->routeIs('superadmin.kelola.akun.ppk')
    || request()->routeIs('superadmin.kelola.akun.unit');
@endphp

<div class="dash-wrap">

  {{-- ======= SIDEBAR ======= --}}
  <aside class="dash-sidebar">
    <div class="dash-brand">
      <div class="dash-logo">
        <img src="{{ asset('image/Logo_Unsoed.png') }}" alt="Logo Unsoed">
      </div>
      <div class="dash-text">
        <div class="dash-app">SIAPABAJA</div>
        <div class="dash-role">Super Admin</div>
      </div>
    </div>

    <nav class="dash-nav">
      <a class="dash-link {{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}"
         href="{{ route('superadmin.dashboard') }}">
        <span class="ic"><i class="bi bi-grid-fill"></i></span>
        Dashboard
      </a>

      <a class="dash-link {{ request()->routeIs('superadmin.arsip*') ? 'active' : '' }}"
         href="{{ route('superadmin.arsip') }}">
        <span class="ic"><i class="bi bi-archive"></i></span>
        Arsip PBJ
      </a>

      <a class="dash-link {{ request()->routeIs('superadmin.pengadaan.create') ? 'active' : '' }}"
         href="{{ route('superadmin.pengadaan.create') }}">
        <span class="ic"><i class="bi bi-plus-square"></i></span>
        Tambah Pengadaan
      </a>

      <a class="dash-link {{ request()->routeIs('superadmin.kelola.menu') ? 'active' : '' }}"
         href="{{ route('superadmin.kelola.menu') }}">
        <span class="ic"><i class="bi bi-gear-fill"></i></span>
        Kelola Menu
      </a>

      {{-- Kelola Akun: accordion --}}
      <div class="dash-accordion-item">
        <button class="dash-link dash-link-accordion {{ $kelolaAkunActive ? 'active' : '' }} {{ $kelolaAkunActive ? 'is-open' : '' }}"
                id="kelolaAkunParent" type="button">
          <span class="dash-link-left">
            <span class="ic"><i class="bi bi-person-gear"></i></span>
            <span>Kelola Akun</span>
          </span>
          <i class="bi bi-chevron-down dash-chevron"></i>
        </button>

        <div class="dash-sub {{ $kelolaAkunActive ? 'is-open' : '' }}" id="kelolaAkunSub">
          <a class="dash-sub-link {{ request()->routeIs('superadmin.kelola.akun') && !request()->routeIs('superadmin.kelola.akun.ppk') && !request()->routeIs('superadmin.kelola.akun.unit') ? 'active' : '' }}"
             href="{{ route('superadmin.kelola.akun') }}">
            <span class="ic"><i class="bi bi-person-circle"></i></span>
            Kelola Akun Saya
          </a>
          <a class="dash-sub-link {{ request()->routeIs('superadmin.kelola.akun.ppk') ? 'active' : '' }}"
             href="{{ route('superadmin.kelola.akun.ppk') }}">
            <span class="ic"><i class="bi bi-person-badge"></i></span>
            Kelola Akun PPK
          </a>
          <a class="dash-sub-link {{ request()->routeIs('superadmin.kelola.akun.unit') ? 'active' : '' }}"
             href="{{ route('superadmin.kelola.akun.unit') }}">
            <span class="ic"><i class="bi bi-people"></i></span>
            Kelola Akun Unit
          </a>
        </div>
      </div>
    </nav>

    <div class="dash-side-actions">
      <a class="dash-side-btn" href="{{ route('home') }}">
        <i class="bi bi-house-door"></i> Kembali
      </a>
      <a class="dash-side-btn" href="{{ url('/logout') }}">
        <i class="bi bi-box-arrow-right"></i> Keluar
      </a>
    </div>
  </aside>

  {{-- ======= MAIN ======= --}}
  <main class="dash-main">
    <header class="dash-header">
      <h1>Kelola Akun</h1>
      <p>Perbarui informasi akun Super Admin kamu (nama, email, dan password) dengan aman.</p>
    </header>

    @if (session('success'))
      <div class="a-alert a-alert--ok">
        <i class="bi bi-check-circle"></i>
        <div>{{ session('success') }}</div>
      </div>
    @endif

    @if ($errors->any())
      <div class="a-alert a-alert--err">
        <i class="bi bi-exclamation-triangle"></i>
        <div>
          Ada input yang perlu diperbaiki:
          <ul class="a-errlist">
            @foreach ($errors->all() as $e)
              <li>{{ $e }}</li>
            @endforeach
          </ul>
        </div>
      </div>
    @endif

    <section class="a-grid">
      <div class="a-card">
        <div class="a-card-head">
          <div class="a-head-left">
            <div class="a-ico"><i class="bi bi-person-badge"></i></div>
            <div class="a-head-text">
              <div class="t1">Informasi Akun</div>
              <div class="t2">Ringkasan identitas & status login</div>
            </div>
          </div>
        </div>
        <div class="a-card-body">
          <div class="a-profile">
            <div class="a-avatar">{{ $initials }}</div>
            <div class="a-meta">
              <div class="a-name">{{ $name }}</div>
              <div class="a-pills">
                <span class="a-pill"><i class="bi bi-envelope"></i> {{ $email }}</span>
                <span class="a-pill"><i class="bi bi-shield-lock"></i> {{ $roleText }}</span>
              </div>
            </div>
          </div>
          <div class="a-tips">
            <div class="a-tip-title"><i class="bi bi-info-circle"></i> Tips keamanan</div>
            <ul>
              <li>Gunakan password minimal 8 karakter (lebih aman 12+).</li>
              <li>Hindari password yang sama dengan akun lain.</li>
              <li>Jika pernah login di perangkat umum, disarankan ganti password.</li>
            </ul>
          </div>
        </div>
      </div>

      <div class="a-card">
        <div class="a-card-head">
          <div class="a-head-left">
            <div class="a-ico"><i class="bi bi-sliders"></i></div>
            <div class="a-head-text">
              <div class="t1">Pengaturan</div>
              <div class="t2">Ubah nama, email, dan password</div>
            </div>
          </div>
        </div>
        <div class="a-card-body">
          <form class="a-form" action="{{ route('superadmin.akun.update') }}" method="POST" autocomplete="off">
            @csrf
            @method('PUT')

            <div class="a-row">
              <div class="a-field">
                <label class="a-label"><i class="bi bi-person"></i> Nama</label>
                <input type="text" name="name" value="{{ old('name', $name) }}" placeholder="Masukkan nama" required>
                <div class="a-hint">Nama yang tampil di sistem.</div>
              </div>
              <div class="a-field">
                <label class="a-label"><i class="bi bi-envelope"></i> Email / Akun</label>
                <input type="email" name="email" value="{{ old('email', $email) }}" placeholder="Masukkan email" required>
                <div class="a-hint">Email ini dipakai untuk login.</div>
              </div>
            </div>

            <div class="a-sep"></div>

            <div class="a-field">
              <label class="a-label"><i class="bi bi-key"></i> Password Saat Ini</label>
              <div class="a-pass">
                <input id="curPw" type="password" name="current_password" placeholder="Wajib jika ingin mengganti password">
                <button class="a-eye" type="button" data-eye="curPw" aria-label="Tampilkan password">
                  <i class="bi bi-eye"></i>
                </button>
              </div>
              <div class="a-hint">Kosongkan jika tidak mengganti password.</div>
            </div>

            <div class="a-row">
              <div class="a-field">
                <label class="a-label"><i class="bi bi-lock"></i> Password Baru</label>
                <div class="a-pass">
                  <input id="newPw" type="password" name="password" placeholder="Password baru">
                  <button class="a-eye" type="button" data-eye="newPw" aria-label="Tampilkan password">
                    <i class="bi bi-eye"></i>
                  </button>
                </div>
                <div class="a-hint">Minimal 8 karakter.</div>
              </div>
              <div class="a-field">
                <label class="a-label"><i class="bi bi-lock-fill"></i> Konfirmasi Password</label>
                <div class="a-pass">
                  <input id="cnfPw" type="password" name="password_confirmation" placeholder="Ulangi password baru">
                  <button class="a-eye" type="button" data-eye="cnfPw" aria-label="Tampilkan password">
                    <i class="bi bi-eye"></i>
                  </button>
                </div>
                <div class="a-hint">Harus sama dengan password baru.</div>
              </div>
            </div>

            <div class="a-actions">
              <button type="submit" class="a-btn a-btn--primary">Simpan Perubahan</button>
            </div>
          </form>
        </div>
      </div>
    </section>
  </main>
</div>

<style>
:root{
  --unsoed-blue: #184f61;
  --unsoed-blue-dark: #143f4d;
  --unsoed-yellow: #f6c100;
  --unsoed-yellow-dark: #d9a800;
}

.page-akun{
  font-size: 20px;
  line-height: 1.65;
  font-weight: 400;
}

html, body{
  height: 100%;
  overflow-x: hidden;
}

.dash-wrap {
  min-height: 100vh;
  height: auto !important;
  overflow: visible !important;
}

html, body {
  height: auto !important;
  min-height: 100vh;
  overflow-y: auto !important;
  overflow-x: hidden !important;
}

.dash-sidebar {
  height: 100vh;
  display: flex;
  flex-direction: column;
}

.dash-nav {
  flex: 1;
  overflow-y: auto;
  overflow-x: hidden;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.dash-link,
.dash-link-accordion,
.dash-sub-link {
  flex-shrink: 0;
  box-sizing: border-box;
}

.dash-link-accordion {
  width: 100%;
  min-height: 52px;
  text-align: left;
  background: transparent;
  border: none;
  cursor: pointer;
  color: rgba(255,255,255,.92);
  font-family: 'Nunito', sans-serif;
  font-size: 16px;
  font-weight: 400;
  display: flex;
  justify-content: space-between;
  gap: 10px;
  align-items: center;
  padding: 12px 12px;
  border-radius: 10px;
  transition: background .15s;
}

.dash-link-left {
  display: flex;
  align-items: center;
  gap: 10px;
}

.dash-link-accordion:hover {
  background: rgba(255,255,255,.08);
}

.dash-link-accordion.active {
  background: #f6c100;
  color: #0f172a;
}

.dash-chevron {
  margin-left: auto;
  font-size: 12px;
  transition: transform .2s ease;
}

.dash-link-accordion.is-open .dash-chevron {
  transform: rotate(180deg);
}

.dash-sub {
  display: none;
  flex-direction: column;
  gap: 8px;
  padding-left: 14px;
  margin-top: 8px;
  margin-bottom: 2px;
}

.dash-sub.is-open {
  display: flex;
}

.dash-sub-link {
  min-height: 48px;
  display: flex;
  align-items: center;
  gap: 10px;
  color: rgba(255,255,255,.80);
  text-decoration: none;
  padding: 10px 12px;
  border-radius: 8px;
  font-size: 15px;
  font-weight: 400;
  transition: background .15s, color .15s;
}

.dash-sub-link .ic {
  width: 16px;
  text-align: center;
}

.dash-sub-link:hover {
  background: rgba(255,255,255,.08);
  color: #fff;
}

.dash-sub-link.active {
  background: #f6c100;
  color: #0f172a;
}

.dash-side-actions {
  margin-top: auto;
  padding-top: 14px;
  flex-shrink: 0;
}

.dash-nav::-webkit-scrollbar {
  width: 6px;
}

.dash-nav::-webkit-scrollbar-thumb {
  background: rgba(255,255,255,0.3);
  border-radius: 10px;
}

.dash-main {
  min-width: 0;
  height: auto;
  overflow-y: visible;
  overflow-x: hidden;
  padding-right: 6px;
}

html, body {
  overflow-y: auto !important;
  overflow-x: hidden !important;
}

.dash-header {
  display:flex;
  flex-direction:column;
  align-items:flex-start;
  gap:6px;
  margin-bottom: 20px;
}

.dash-header h1 {
  margin:0;
  font-size: 26px;
  font-weight:700;
  color:#184f61;
}

.dash-header p {
  margin:0;
  font-size: 15px;
  color:#64748b;
  font-weight: 400;
}

.a-alert {
  margin-top: 12px;
  margin-bottom: 16px;
  border-radius: 14px;
  border: 1px solid #e6eef2;
  background: #fff;
  box-shadow: 0 10px 20px rgba(2,8,23,.04);
  padding: 12px 14px;
  display:flex;
  gap: 10px;
  align-items:flex-start;
  font-size: 15px;
  color:#0f172a;
}

.a-alert i {
  font-size: 18px;
  margin-top: 1px;
}

.a-alert--ok {
  border-left: 4px solid var(--unsoed-blue);
}

.a-alert--err {
  border-left: 4px solid var(--unsoed-yellow);
}

.a-errlist {
  margin: 6px 0 0 0;
  padding-left: 18px;
}

.a-errlist li {
  margin: 2px 0;
}

.a-grid {
  display:grid;
  grid-template-columns: 1fr 1.2fr;
  gap: 14px;
}

.a-card {
  background:#fff;
  border: 1px solid #e6eef2;
  border-radius: 18px;
  box-shadow: 0 10px 20px rgba(2,8,23,.04);
  overflow:hidden;
  width: 100%;
  max-width: 100%;
}

.a-card-head {
  padding: 14px 16px;
  border-bottom: 1px solid rgba(255,255,255,.18);
  background: var(--unsoed-blue);
}

.a-head-left {
  display:flex;
  align-items:center;
  gap: 12px;
}

.a-ico {
  width: 40px;
  height: 40px;
  border-radius: 12px;
  display:grid;
  place-items:center;
  background: rgba(255,255,255,.18);
  color: #fff;
  border: 1px solid rgba(255,255,255,.22);
  font-size: 18px;
  flex: 0 0 auto;
}

.a-head-text .t1 {
  font-size: 18px;
  color:#fff;
  font-weight: 600 !important;
  line-height: 1.2;
}

.a-head-text .t2 {
  margin-top: 3px;
  font-size: 14px;
  color: rgba(255,255,255,.85);
  line-height: 1.2;
}

.a-card-body {
  padding: 16px;
}

.a-profile {
  display:flex;
  align-items:center;
  gap: 12px;
}

.a-avatar {
  width: 58px;
  height: 58px;
  border-radius: 18px;
  display:grid;
  place-items:center;
  background:#e9f3f6;
  border: 1px solid #d7e9ee;
  color: var(--unsoed-blue);
  font-size: 22px;
  font-weight: 600;
  flex: 0 0 auto;
}

.a-meta {
  min-width:0;
}

.a-name {
  font-size: 18px;
  color:#0f172a;
  font-weight: 600;
  line-height: 1.25;
  white-space: nowrap;
  overflow:hidden;
  text-overflow: ellipsis;
}

.a-pills {
  display:flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-top: 8px;
}

.a-pill {
  display:inline-flex;
  align-items:center;
  gap: 7px;
  padding: 8px 12px;
  border-radius: 999px;
  border: 1px solid #eef2f7;
  background:#f8fafc;
  font-size: 14px;
  color:#0f172a;
  opacity: .92;
}

.a-pill i {
  opacity:.75;
}

.a-tips {
  margin-top: 16px;
  border-radius: 14px;
  border: 1px dashed #d7e9ee;
  background: #f7fbfd;
  padding: 12px 12px;
}

.a-tip-title {
  display:flex;
  align-items:center;
  gap: 8px;
  font-size: 15px;
  color: var(--unsoed-blue);
  margin-bottom: 10px;
  font-weight: 600;
}

.a-tips ul {
  margin: 0;
  padding-left: 18px;
  font-size: 15px;
  color:#0f172a;
  opacity: .88;
  line-height: 1.55;
}

.a-tips li {
  margin: 5px 0;
}

.a-form {
  display:flex;
  flex-direction:column;
  gap: 14px;
}

.a-row {
  display:grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}

.a-field {
  display:flex;
  flex-direction:column;
  gap: 8px;
  min-width: 0;
}

.a-label {
  font-size: 15px;
  color:#0f172a;
  opacity: .9;
  display:flex;
  align-items:center;
  gap: 8px;
  font-weight: 600 !important;
}

.a-label i {
  opacity:.75;
}

.a-field input[type="text"],
.a-field input[type="email"] {
  height: 48px;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
  padding: 0 14px;
  outline:none;
  background:#fff;
  font-family: inherit;
  font-size: 16px;
  font-weight: 400 !important;
  transition: .15s ease;
}

.a-pass {
  height: 48px;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
  background:#fff;
  display:flex;
  align-items:center;
  overflow:hidden;
  transition: .15s ease;
  margin: 4px 0;
}

.a-pass:focus-within {
  border-color: var(--unsoed-blue);
  box-shadow: 0 0 0 4px rgba(24,79,97,.12);
}

.a-pass input[type="password"],
.a-pass input[type="text"] {
  border: 0 !important;
  outline: none !important;
  height: 100%;
  flex: 1 1 auto;
  min-width: 0;
  padding: 0 14px;
  font-family: inherit;
  font-size: 16px;
  font-weight: 400 !important;
  background: transparent;
}

input[type="password"]::-ms-reveal,
input[type="password"]::-ms-clear {
  display:none !important;
}

input::-webkit-credentials-auto-fill-button {
  visibility: hidden !important;
  display: none !important;
  pointer-events: none !important;
  opacity: 0 !important;
}

.a-eye {
  height: 100%;
  width: 48px;
  border: 0;
  border-left: 1px solid #e6eef2;
  background: #f8fafc;
  display:flex;
  align-items:center;
  justify-content:center;
  cursor:pointer;
  padding: 0;
  flex: 0 0 auto;
}

.a-eye i {
  font-size: 18px;
  opacity: .75;
  line-height: 1;
}

.a-eye:hover {
  background:#eef2f7;
}

.a-hint {
  font-size: 14px;
  color:#64748b;
}

.a-sep {
  height: 1px;
  background:#e6eef2;
  margin: 4px 0;
}

.a-actions {
  display:flex;
  justify-content:flex-end;
  gap: 10px;
  margin-top: 6px;
  flex-wrap: wrap;
  padding-bottom: 0;
}

.a-btn {
  height: 44px;
  padding: 0 16px;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
  background:#fff;
  font-family: inherit;
  font-size: 15px;
  font-weight: 600;
  display:inline-flex;
  align-items:center;
  gap: 8px;
  cursor:pointer;
  transition: .15s ease;
}

.a-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 10px 20px rgba(2,8,23,.06);
}

.a-btn--primary {
  background: var(--unsoed-yellow);
  border-color: rgba(0,0,0,.12);
  color: #0f172a;
}

.a-btn--primary:hover {
  background: var(--unsoed-yellow-dark);
}

@media(max-width:1100px){
  .a-grid{
    grid-template-columns: 1fr;
  }
}

@media(max-width:720px){
  .a-row{
    grid-template-columns: 1fr;
  }
}

/* =========================================================
   RESPONSIVE SUPERADMIN KELOLA AKUN
========================================================= */

:where(.page-sa-akun) .mob-topbar {
  display: none;
  align-items: center;
  justify-content: space-between;
  padding: 14px 16px;
  background: #184f61;
  position: sticky;
  top: 0;
  z-index: 200;
}

:where(.page-sa-akun) .mob-logo {
  display: flex;
  align-items: center;
  gap: 10px;
}

:where(.page-sa-akun) .mob-logo img {
  width: 32px;
  height: 32px;
}

:where(.page-sa-akun) .mob-logo-txt {
  color: #f4c542;
  font-size: 17px;
  font-weight: 700;
}

:where(.page-sa-akun) .mob-ham {
  width: 40px;
  height: 40px;
  border: 1px solid #e6eef2;
  border-radius: 10px;
  background: #fff;
  color: #184f61;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  font-size: 18px;
}

:where(.page-sa-akun) .sidebar-drawer-backdrop {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,.4);
  z-index: 300;
}

:where(.page-sa-akun) .sidebar-drawer-backdrop.is-open {
  display: block;
}

@media (max-width:1024px){

  :where(.page-sa-akun) .mob-topbar{
    display:flex;
  }

  :where(.page-sa-akun) .dash-sidebar{
    position:fixed !important;
    top:0 !important;
    left:-280px !important;
    width:260px !important;
    height:100vh !important;
    z-index:400 !important;
    transition:left .25s ease !important;
    overflow-y:auto !important;
  }

  :where(.page-sa-akun) .dash-sidebar.drawer-open{
    left:0 !important;
  }

  :where(.page-sa-akun) .dash-wrap{
    display:block !important;
    min-height:100vh !important;
  }

  :where(.page-sa-akun) .dash-main{
    height:auto !important;
    min-height:calc(100vh - 70px) !important;
    overflow:visible !important;
    padding:20px !important;
  }

  :where(.page-sa-akun) .a-grid{
    grid-template-columns:1fr !important;
  }
}

@media (max-width:768px){

  :where(.page-sa-akun) .dash-main{
    padding:12px !important;
  }

  :where(.page-sa-akun) .a-row{
    grid-template-columns:1fr !important;
  }

  :where(.page-sa-akun) .a-actions{
    justify-content:stretch !important;
  }

  :where(.page-sa-akun) .a-btn{
    width:100%;
    justify-content:center;
  }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function(){
  const sidebar  = document.querySelector('.dash-sidebar');
const hamBtn   = document.getElementById('mobHamBtn');
const backdrop = document.getElementById('sidebarBackdrop');

function openDrawer(){
  sidebar?.classList.add('drawer-open');
  backdrop?.classList.add('is-open');
  document.body.style.overflow = 'hidden';
}

function closeDrawer(){
  sidebar?.classList.remove('drawer-open');
  backdrop?.classList.remove('is-open');
  document.body.style.overflow = '';
}

hamBtn?.addEventListener('click', openDrawer);
backdrop?.addEventListener('click', closeDrawer);

sidebar?.querySelectorAll('.dash-link, .dash-side-btn').forEach(el => {
  el.addEventListener('click', closeDrawer);
});

document.addEventListener('keydown', e => {
  if (e.key === 'Escape') closeDrawer();
});
  document.querySelectorAll('[data-eye]').forEach(btn => {
    btn.addEventListener('click', function(){
      const input = document.getElementById(btn.dataset.eye);
      if(!input) return;
      const isPw = input.type === 'password';
      input.type = isPw ? 'text' : 'password';
      const ico = btn.querySelector('i');
      if(ico) ico.className = isPw ? 'bi bi-eye-slash' : 'bi bi-eye';
    });
  });

  const parent = document.getElementById('kelolaAkunParent');
  const sub    = document.getElementById('kelolaAkunSub');

  if(parent && sub){
    parent.addEventListener('click', function(){
      sub.classList.toggle('is-open');
      parent.classList.toggle('is-open');
    });
  }
});
</script>
@include('Partials.chatbot')
</body>
</html>