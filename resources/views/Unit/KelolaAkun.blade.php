{{-- resources/views/Unit/KelolaAkun.blade.php --}}
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Kelola Akun - SIAPABAJA</title>

  {{-- Font Nunito (HANYA 400 & 600 biar tidak ada bold) --}}
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600&display=swap" rel="stylesheet">

  {{-- Bootstrap Icons --}}
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

  {{-- CSS base --}}
  <link rel="stylesheet" href="{{ asset('css/Unit.css?v=2') }}">
</head>

{{-- ✅ SAMA KONSEP DENGAN TAMBAH PENGADAAN: pakai 1 class khusus page --}}

<body class="dash-body page-unit-akun">

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

  // fallback dummy kalau belum login/backend belum beres
  $unitName = $user->name ?? 'Admin Unit';
  $unitEmail = $user->email ?? 'unit@contoh.ac.id';
  $roleText = 'PIC (Unit)';

  $initials = strtoupper(mb_substr(trim($unitName), 0, 1));
  @endphp

  <div class="dash-wrap">
    {{-- SIDEBAR (✅ BIARKAN 100% NGIKUT Unit.css biar SAMA PERSIS) --}}
    <aside class="dash-sidebar">
      <div class="dash-brand">
        <div class="dash-logo">
          <img src="{{ asset('image/Logo_Unsoed.png') }}" alt="Logo Unsoed">
        </div>

        <div class="dash-text">
          <div class="dash-app">SIAPABAJA</div>
          <div class="dash-role">{{ $roleText }}</div>
        </div>
      </div>

      {{-- ✅ SAMA PERSIS: pakai dash-unitbox --}}
      <div class="dash-unitbox">
        <div class="dash-unit-label">Unit Kerja :</div>
        <div class="dash-unit-name">{{ $unitName }}</div>
      </div>

      <nav class="dash-nav">
        <a class="dash-link" href="{{ route('unit.dashboard') }}">
          <span class="ic"><i class="bi bi-grid-fill"></i></span>
          Dashboard
        </a>

        <a class="dash-link" href="{{ route('unit.arsip') }}">
          <span class="ic"><i class="bi bi-archive"></i></span>
          Arsip PBJ
        </a>

        <a class="dash-link" href="{{ route('unit.pengadaan.create') }}">
          <span class="ic"><i class="bi bi-plus-square"></i></span>
          Tambah Pengadaan
        </a>

        <a class="dash-link active" href="{{ route('unit.kelola.akun') }}">
          <span class="ic"><i class="bi bi-person-gear"></i></span>
          Kelola Akun
        </a>
      </nav>

      {{-- Footer buttons (DISAMAKAN DENGAN ARSIP PBJ / TAMBAH PENGADAAN) --}}
      <div class="dash-side-actions">
        <a class="dash-side-btn" href="{{ route('home') }}">
          <i class="bi bi-house-door"></i> Kembali
        </a>
        <a class="dash-side-btn" href="{{ url('/logout') }}">
          <i class="bi bi-box-arrow-right"></i> Keluar
        </a>
      </div>
    </aside>

    {{-- MAIN --}}
    <main class="dash-main">
      <header class="dash-header">
        <h1>Kelola Akun</h1>
        <p>Perbarui informasi akun Unit kamu (nama, email, dan password) dengan aman.</p>
      </header>

      {{-- ALERTS --}}
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

      {{-- CONTENT --}}
      <section class="a-grid">
        {{-- KARTU PROFIL --}}
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
                <div class="a-name">{{ $unitName }}</div>
                <div class="a-pills">
                  <span class="a-pill"><i class="bi bi-envelope"></i> {{ $unitEmail }}</span>
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

        {{-- FORM UPDATE --}}
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
            <form class="a-form"
              action="{{ \Illuminate\Support\Facades\Route::has('unit.akun.update') ? route('unit.akun.update') : '#' }}"
              method="POST"
              autocomplete="off">
              @csrf
              @method('PUT')

              <div class="a-row">
                <div class="a-field">
                  <label class="a-label"><i class="bi bi-person"></i> Nama</label>
                  <input type="text" name="name" value="{{ old('name', $unitName) }}" placeholder="Masukkan nama" required>
                  <div class="a-hint">Nama yang tampil di sistem.</div>
                </div>

                <div class="a-field">
                  <label class="a-label"><i class="bi bi-envelope"></i> Email / Akun</label>
                  <input type="email" name="email" value="{{ old('email', $unitEmail) }}" placeholder="Masukkan email" required>
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
    :root {
      --unsoed-blue: #184f61;
      --unsoed-blue-dark: #143f4d;
      --unsoed-yellow: #f6c100;
      --unsoed-yellow-dark: #d9a800;
    }

    /* =========================================================
     ✅ SCOPE CSS KE PAGE SAJA (MODEL SAMA KAYAK TAMBAH PENGADAAN)
     PENTING: JANGAN override sidebar (.dash-unitbox dkk)
     ========================================================= */
    :where(.page-unit-akun) {
      line-height: 1.6;
      font-weight: 400;
    }

    .dash-header {
      margin-bottom: 18px;
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      /* 🔥 ini kuncinya */
      text-align: left;
      /* 🔥 paksa ke kiri */
      gap: 6px;
    }

    .dash-header h1 {
      margin: 0;
      font-size: 26px;
      font-weight: 700;
      color: #184f61;
    }

    .dash-header p {
      margin: 10;
      font-size: 15px;
      color: #184f61;
      opacity: 0.85;
    }

    /* Sidebar footer actions sama seperti tambah pengadaan */
    :where(.page-unit-akun) .dash-sidebar {
      display: flex;
      flex-direction: column;
    }

    :where(.page-unit-akun) .dash-side-actions {
      margin-top: auto;
      padding-top: 14px;
      border-top: 1px solid rgba(255, 255, 255, .12);
      display: grid;
      gap: 10px;
    }

    /* ====== Alerts ====== */
    :where(.page-unit-akun) .a-alert {
      margin-top: 12px;
      margin-bottom: 16px;
      border-radius: 14px;
      border: 1px solid #e6eef2;
      background: #fff;
      box-shadow: 0 10px 20px rgba(2, 8, 23, .04);
      padding: 12px 14px;
      display: flex;
      gap: 10px;
      align-items: flex-start;
      font-size: 15px;
      color: #0f172a;
    }

    :where(.page-unit-akun) .a-alert i {
      font-size: 18px;
      margin-top: 1px;
    }

    :where(.page-unit-akun) .a-alert--ok {
      border-left: 4px solid var(--unsoed-blue);
    }

    :where(.page-unit-akun) .a-alert--err {
      border-left: 4px solid var(--unsoed-yellow);
    }

    :where(.page-unit-akun) .a-errlist {
      margin: 6px 0 0 0;
      padding-left: 18px;
    }

    :where(.page-unit-akun) .a-errlist li {
      margin: 2px 0;
    }

    /* ====== Layout cards ====== */
    :where(.page-unit-akun) .a-grid {
      display: grid;
      grid-template-columns: 1fr 1.2fr;
      gap: 14px;
      overflow: auto;
      padding-right: 2px;
    }

    @media(max-width:1100px) {
      :where(.page-unit-akun) .a-grid {
        grid-template-columns: 1fr;
      }
    }

    :where(.page-unit-akun) .a-card {
      background: #fff;
      border: 1px solid #e6eef2;
      border-radius: 18px;
      box-shadow: 0 10px 20px rgba(2, 8, 23, .04);
      overflow: hidden;
    }

    :where(.page-unit-akun) .a-card-head {
      padding: 14px 16px;
      border-bottom: 1px solid rgba(255, 255, 255, .18);
      background: var(--unsoed-blue);
    }

    :where(.page-unit-akun) .a-head-left {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    :where(.page-unit-akun) .a-ico {
      width: 40px;
      height: 40px;
      border-radius: 12px;
      display: grid;
      place-items: center;
      background: rgba(255, 255, 255, .18);
      color: #fff;
      border: 1px solid rgba(255, 255, 255, .22);
      font-size: 18px;
      flex: 0 0 auto;
    }

    :where(.page-unit-akun) .a-head-text .t1 {
      font-size: 18px;
      color: #fff;
      font-weight: 600 !important;
      line-height: 1.2;
    }

    :where(.page-unit-akun) .a-head-text .t2 {
      margin-top: 3px;
      font-size: 14px;
      color: rgba(255, 255, 255, .85);
      line-height: 1.2;
    }

    :where(.page-unit-akun) .a-card-body {
      padding: 16px;
    }

    /* ====== Profile ====== */
    :where(.page-unit-akun) .a-profile {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    :where(.page-unit-akun) .a-avatar {
      width: 58px;
      height: 58px;
      border-radius: 18px;
      display: grid;
      place-items: center;
      background: #e9f3f6;
      border: 1px solid #d7e9ee;
      color: var(--unsoed-blue);
      font-size: 22px;
      font-weight: 600;
      flex: 0 0 auto;
    }

    :where(.page-unit-akun) .a-meta {
      min-width: 0;
    }

    :where(.page-unit-akun) .a-name {
      font-size: 18px;
      color: #0f172a;
      font-weight: 600;
      line-height: 1.25;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    :where(.page-unit-akun) .a-pills {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      margin-top: 8px;
    }

    :where(.page-unit-akun) .a-pill {
      display: inline-flex;
      align-items: center;
      gap: 7px;
      padding: 8px 12px;
      border-radius: 999px;
      border: 1px solid #eef2f7;
      background: #f8fafc;
      font-size: 14px;
      color: #0f172a;
      opacity: .92;
    }

    :where(.page-unit-akun) .a-pill i {
      opacity: .75;
    }

    :where(.page-unit-akun) .a-tips {
      margin-top: 16px;
      border-radius: 14px;
      border: 1px dashed #d7e9ee;
      background: #f7fbfd;
      padding: 12px 12px;
    }

    :where(.page-unit-akun) .a-tip-title {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 15px;
      color: var(--unsoed-blue);
      margin-bottom: 10px;
      font-weight: 600;
    }

    :where(.page-unit-akun) .a-tips ul {
      margin: 0;
      padding-left: 18px;
      font-size: 15px;
      color: #0f172a;
      opacity: .88;
      line-height: 1.55;
    }

    :where(.page-unit-akun) .a-tips li {
      margin: 5px 0;
    }

    /* ====== Form ====== */
    :where(.page-unit-akun) .a-form {
      display: flex;
      flex-direction: column;
      gap: 14px;
    }

    :where(.page-unit-akun) .a-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px;
    }

    @media(max-width:720px) {
      :where(.page-unit-akun) .a-row {
        grid-template-columns: 1fr;
      }
    }

    :where(.page-unit-akun) .a-field {
      display: flex;
      flex-direction: column;
      gap: 8px;
    }

    :where(.page-unit-akun) .a-label {
      font-size: 15px;
      color: #0f172a;
      opacity: .9;
      display: flex;
      align-items: center;
      gap: 8px;
      font-weight: 600 !important;
    }

    :where(.page-unit-akun) .a-label i {
      opacity: .75;
    }

    :where(.page-unit-akun) .a-field input[type="text"],
    :where(.page-unit-akun) .a-field input[type="email"] {
      height: 48px;
      border-radius: 12px;
      border: 1px solid #e2e8f0;
      padding: 0 14px;
      outline: none;
      background: #fff;
      font-family: inherit;
      font-size: 16px;
      font-weight: 400 !important;
      transition: .15s ease;
    }

    /* ✅ Password group: TANPA margin biar ga berjarak */
    :where(.page-unit-akun) .a-pass {
      height: 48px;
      border-radius: 12px;
      border: 1px solid #e2e8f0;
      background: #fff;
      display: flex;
      align-items: center;
      overflow: hidden;
      transition: .15s ease;
      margin: 0;
      /* ✅ rapat */
    }

    :where(.page-unit-akun) .a-pass:focus-within {
      border-color: var(--unsoed-blue);
      box-shadow: 0 0 0 4px rgba(24, 79, 97, .12);
    }

    :where(.page-unit-akun) .a-pass input[type="password"],
    :where(.page-unit-akun) .a-pass input[type="text"] {
      border: 0 !important;
      outline: none !important;
      height: 100%;
      flex: 1 1 auto;
      padding: 0 14px;
      font-family: inherit;
      font-size: 16px;
      font-weight: 400 !important;
      background: transparent;
    }

    /* ✅ HILANGKAN icon mata bawaan Edge/IE (penyebab icon dobel) */
    :where(.page-unit-akun) input[type="password"]::-ms-reveal,
    :where(.page-unit-akun) input[type="password"]::-ms-clear {
      display: none !important;
    }

    /* ✅ Kadang muncul tombol kredensial di Chromium, sembunyikan */
    :where(.page-unit-akun) input::-webkit-credentials-auto-fill-button {
      visibility: hidden !important;
      display: none !important;
      pointer-events: none !important;
      opacity: 0 !important;
    }

    :where(.page-unit-akun) .a-eye {
      height: 100%;
      width: 48px;
      border: 0;
      border-left: 1px solid #e6eef2;
      background: #f8fafc;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      padding: 0;
      flex: 0 0 auto;
    }

    :where(.page-unit-akun) .a-eye i {
      font-size: 18px;
      opacity: .75;
      line-height: 1;
    }

    :where(.page-unit-akun) .a-eye:hover {
      background: #eef2f7;
    }

    :where(.page-unit-akun) .a-hint {
      font-size: 14px;
      color: #64748b;
    }

    :where(.page-unit-akun) .a-sep {
      height: 1px;
      background: #e6eef2;
      margin: 4px 0;
    }

    :where(.page-unit-akun) .a-actions {
      display: flex;
      justify-content: flex-end;
      gap: 10px;
      margin-top: 6px;
      flex-wrap: wrap;
    }

    :where(.page-unit-akun) .a-btn {
      height: 44px;
      padding: 0 16px;
      border-radius: 12px;
      border: 1px solid #e2e8f0;
      background: #fff;
      font-family: inherit;
      font-size: 15px;
      font-weight: 600;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      cursor: pointer;
      transition: .15s ease;
    }

    :where(.page-unit-akun) .a-btn:hover {
      transform: translateY(-1px);
      box-shadow: 0 10px 20px rgba(2, 8, 23, .06);
    }

    :where(.page-unit-akun) .a-btn--primary {
      background: var(--unsoed-yellow);
      border-color: rgba(0, 0, 0, .12);
      color: #0f172a;
    }

    :where(.page-unit-akun) .a-btn--primary:hover {
      background: var(--unsoed-yellow-dark);
    }

    :where(.page-unit-akun) .mob-topbar {
      display: none;
      align-items: center;
      justify-content: space-between;
      padding: 14px 16px;
      background: #184f61;
      position: sticky;
      top: 0;
      z-index: 200;
    }

    :where(.page-unit-akun) .mob-logo {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    :where(.page-unit-akun) .mob-logo img {
      width: 32px;
      height: 32px;
    }

    :where(.page-unit-akun) .mob-logo-txt {
      color: #f4c542;
      font-size: 17px;
      font-weight: 700;
    }

    :where(.page-unit-akun) .mob-ham {
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

    :where(.page-unit-akun) .sidebar-drawer-backdrop {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, .4);
      z-index: 300;
    }

    :where(.page-unit-akun) .sidebar-drawer-backdrop.is-open {
      display: block;
    }

    @media (max-width: 1024px) {

      :where(.page-unit-akun) .mob-topbar {
        display: flex;
      }

      :where(.page-unit-akun) .dash-sidebar {
        position: fixed !important;
        top: 0 !important;
        left: -280px !important;
        width: 260px !important;
        height: 100vh !important;
        z-index: 400 !important;
        transition: left .25s ease !important;
        overflow-y: auto !important;
      }

      :where(.page-unit-akun) .dash-sidebar.drawer-open {
        left: 0 !important;
      }

      html,
      body {
        height: auto !important;
        min-height: 100% !important;
        overflow-y: auto !important;
        overflow-x: hidden !important;
      }

      :where(.page-unit-akun) .dash-wrap {
        display: block !important;
        height: auto !important;
        min-height: 100vh !important;
        overflow: visible !important;
      }

      :where(.page-unit-akun) .dash-main {
        height: auto !important;
        min-height: calc(100vh - 70px) !important;
        overflow: visible !important;
        padding: 20px !important;
      }

      :where(.page-unit-akun) .a-card,
      :where(.page-unit-akun) .a-card-body,
      :where(.page-unit-akun) .a-form {
        height: auto !important;
        max-height: none !important;
        overflow: visible !important;
      }
    }

    @media (max-width: 768px) {

      :where(.page-unit-akun) .dash-main {
        padding: 12px !important;
      }

      :where(.page-unit-akun) .a-grid {
        grid-template-columns: 1fr !important;
      }

      :where(.page-unit-akun) .a-row {
        grid-template-columns: 1fr !important;
      }

      :where(.page-unit-akun) .a-actions {
        justify-content: stretch !important;
      }

      :where(.page-unit-akun) .a-btn {
        width: 100%;
        justify-content: center;
      }
    }
  </style>

  <script>
    // Toggle show/hide password (konsisten, ringan)
    document.addEventListener('DOMContentLoaded', function() {

      document.querySelectorAll('[data-eye]').forEach(btn => {
        btn.addEventListener('click', function() {
          const id = btn.getAttribute('data-eye');
          const input = document.getElementById(id);
          if (!input) return;
          const isPw = input.type === 'password';
          input.type = isPw ? 'text' : 'password';
          const ico = btn.querySelector('i');
          if (ico) {
            ico.classList.toggle('bi-eye', !isPw);
            ico.classList.toggle('bi-eye-slash', isPw);
          }
        });
      });

      const sidebar = document.querySelector('.dash-sidebar');
      const hamBtn = document.getElementById('mobHamBtn');
      const backdrop = document.getElementById('sidebarBackdrop');

      function openDrawer() {
        sidebar?.classList.add('drawer-open');
        backdrop?.classList.add('is-open');
        document.body.style.overflow = 'hidden';
      }

      function closeDrawer() {
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
    });
  </script>
  @include('Partials.chatbot')
</body>

</html>