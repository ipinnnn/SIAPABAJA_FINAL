{{-- resources/views/PPK/KelolaAkunPPK.blade.php --}}
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

<body class="dash-body page-akun">

  {{-- MOBILE TOPBAR --}}
  <div class="mob-topbar" id="mobTopbar">
    <div class="mob-logo">
      <img src="{{ asset('image/Logo_Unsoed.png') }}" alt="Logo">
      <span class="mob-logo-txt">SIAPABAJA</span>
    </div>

    <button class="mob-ham" id="mobHamBtn" aria-label="Buka menu">
      <i class="bi bi-list"></i>
    </button>
  </div>

  {{-- BACKDROP --}}
  <div class="sidebar-drawer-backdrop" id="sidebarBackdrop"></div>

  @php
  $user = auth()->user();

  // fallback dummy kalau belum login/backend belum beres
  $ppkName = $user->name ?? 'Admin PPK';
  $ppkEmail = $user->email ?? 'ppk@contoh.ac.id';
  $roleText = 'ADMIN (PPK)';

  $initials = strtoupper(mb_substr(trim($ppkName), 0, 1));
  @endphp

  <div class="dash-wrap">
    {{-- SIDEBAR (SAMA PERSIS DENGAN DASHBOARD PPK) --}}
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

      <nav class="dash-nav">
        <a class="dash-link" href="{{ route('ppk.dashboard') }}">
          <span class="ic"><i class="bi bi-grid-fill"></i></span>
          Dashboard
        </a>

        <a class="dash-link" href="{{ route('ppk.arsip') }}">
          <span class="ic"><i class="bi bi-archive"></i></span>
          Arsip PBJ
        </a>

        <a class="dash-link" href="{{ route('ppk.pengadaan.create') }}">
          <span class="ic"><i class="bi bi-plus-square"></i></span>
          Tambah Pengadaan
        </a>

        {{-- ✅ MENU BARU --}}
        <a class="dash-link active" href="{{ route('ppk.kelola.akun') }}">
          <span class="ic"><i class="bi bi-person-gear"></i></span>
          Kelola Akun
        </a>
      </nav>

      {{-- Footer buttons (DISAMAKAN DENGAN ARSIP PBJ) --}}
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
        <p>Perbarui informasi akun PPK kamu (nama, email, dan password) dengan aman.</p>
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
                <div class="a-name">{{ $ppkName }}</div>
                <div class="a-pills">
                  <span class="a-pill"><i class="bi bi-envelope"></i> {{ $ppkEmail }}</span>
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
            <form class="a-form" action="{{ route('ppk.akun.update') ?? '#' }}" method="POST" autocomplete="off">
              @csrf
              @method('PUT')

              <div class="a-row">
                <div class="a-field">
                  <label class="a-label"><i class="bi bi-person"></i> Nama</label>
                  <input type="text" name="name" value="{{ old('name', $ppkName) }}" placeholder="Masukkan nama" required>
                  <div class="a-hint">Nama yang tampil di sistem.</div>
                </div>

                <div class="a-field">
                  <label class="a-label"><i class="bi bi-envelope"></i> Email / Akun</label>
                  <input type="email" name="email" value="{{ old('email', $ppkEmail) }}" placeholder="Masukkan email" required>
                  <div class="a-hint">Email ini dipakai untuk login.</div>
                </div>
              </div>

              <div class="a-sep"></div>

              <div class="a-field">
                <label class="a-label"><i class="bi bi-key"></i> Password Saat Ini</label>

                {{-- ✅ eye benar2 "di dalam kolom" --}}
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

                  {{-- ✅ eye benar2 "di dalam kolom" --}}
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

                  {{-- ✅ eye benar2 "di dalam kolom" --}}
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

    .page-akun {
      font-size: 20px;
      line-height: 1.65;
      font-weight: 400;
    }

    html,
    body {
      height: 100%;
      overflow-x: hidden;
    }

    .dash-wrap {
      min-height: 100vh;
    }

    .dash-main {
      min-width: 0;
      overflow-x: hidden;
    }

    .dash-header {
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      gap: 6px;
    }

    .dash-header h1 {
      margin: 0;
      font-weight: 700;
      color: #184f61;
    }

    .dash-header p {
      margin: 10;
      color: #64748b;
    }

    .a-alert {
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
      display: grid;
      grid-template-columns: 1fr 1.2fr;
      gap: 14px;
    }

    @media(max-width:1100px) {
      .a-grid {
        grid-template-columns: 1fr;
      }
    }

    .a-card {
      background: #fff;
      border: 1px solid #e6eef2;
      border-radius: 18px;
      box-shadow: 0 10px 20px rgba(2, 8, 23, .04);
      overflow: hidden;
    }

    .a-card-head {
      padding: 14px 16px;
      border-bottom: 1px solid rgba(255, 255, 255, .18);
      background: var(--unsoed-blue);
    }

    .a-head-left {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .a-ico {
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

    .a-head-text .t1 {
      font-size: 18px;
      color: #fff;
      font-weight: 600 !important;
      line-height: 1.2;
    }

    .a-head-text .t2 {
      margin-top: 3px;
      font-size: 14px;
      color: rgba(255, 255, 255, .85);
      line-height: 1.2;
    }

    .a-card-body {
      padding: 16px;
    }

    .a-profile {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .a-avatar {
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

    .a-meta {
      min-width: 0;
    }

    .a-name {
      font-size: 18px;
      color: #0f172a;
      font-weight: 600;
      line-height: 1.25;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .a-pills {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      margin-top: 8px;
    }

    .a-pill {
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

    .a-pill i {
      opacity: .75;
    }

    .a-tips {
      margin-top: 16px;
      border-radius: 14px;
      border: 1px dashed #d7e9ee;
      background: #f7fbfd;
      padding: 12px 12px;
    }

    .a-tip-title {
      display: flex;
      align-items: center;
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
      color: #0f172a;
      opacity: .88;
      line-height: 1.55;
    }

    .a-tips li {
      margin: 5px 0;
    }

    .a-form {
      display: flex;
      flex-direction: column;
      gap: 14px;
    }

    .a-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px;
    }

    @media(max-width:720px) {
      .a-row {
        grid-template-columns: 1fr;
      }
    }

    .a-field {
      display: flex;
      flex-direction: column;
      gap: 8px;
    }

    .a-label {
      font-size: 15px;
      color: #0f172a;
      opacity: .9;
      display: flex;
      align-items: center;
      gap: 8px;
      font-weight: 600 !important;
    }

    .a-label i {
      opacity: .75;
    }

    .a-field input[type="text"],
    .a-field input[type="email"] {
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

    /* ==========================
     ✅ INPUT PASSWORD GROUP
     (TAMBAH: hide icon mata bawaan browser + space atas bawah)
     ========================== */
    .a-pass {
      height: 48px;
      border-radius: 12px;
      border: 1px solid #e2e8f0;
      background: #fff;
      display: flex;
      align-items: center;
      overflow: hidden;
      transition: .15s ease;

      /* ✅ Tambahan space atas-bawah (sedikit aja) */
      margin: 4px 0;
    }

    .a-pass:focus-within {
      border-color: var(--unsoed-blue);
      box-shadow: 0 0 0 4px rgba(24, 79, 97, .12);
    }

    .a-pass input[type="password"],
    .a-pass input[type="text"] {
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
    input[type="password"]::-ms-reveal,
    input[type="password"]::-ms-clear {
      display: none !important;
    }

    /* ✅ Kadang muncul tombol kredensial di Chromium, sembunyikan */
    input::-webkit-credentials-auto-fill-button {
      visibility: hidden !important;
      display: none !important;
      pointer-events: none !important;
      opacity: 0 !important;
    }

    .a-eye {
      height: 100%;
      width: 48px;
      /* sama tinggi biar presisi */
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

    .a-eye i {
      font-size: 18px;
      opacity: .75;
      line-height: 1;
    }

    .a-eye:hover {
      background: #eef2f7;
    }

    .a-hint {
      font-size: 14px;
      color: #64748b;
    }

    .a-sep {
      height: 1px;
      background: #e6eef2;
      margin: 4px 0;
    }

    .a-actions {
      display: flex;
      justify-content: flex-end;
      gap: 10px;
      margin-top: 6px;
      flex-wrap: wrap;
    }

    .a-btn {
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

    .a-btn:hover {
      transform: translateY(-1px);
      box-shadow: 0 10px 20px rgba(2, 8, 23, .06);
    }

    .a-btn--primary {
      background: var(--unsoed-yellow);
      border-color: rgba(0, 0, 0, .12);
      color: #0f172a;
    }

    .a-btn--primary:hover {
      background: var(--unsoed-yellow-dark);
    }

    /* =========================================================
   RESPONSIVE PATCH — Kelola Akun
========================================================= */

    .mob-topbar {
      display: none;
      align-items: center;
      justify-content: space-between;
      padding: 14px 16px;
      background: #184f61;
      position: sticky;
      top: 0;
      z-index: 200;
    }

    .mob-logo {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .mob-logo img {
      width: 32px;
      height: 32px;
    }

    .mob-logo-txt {
      color: #f4c542;
      font-size: 17px;
      font-weight: 700;
    }

    .mob-ham {
      width: 40px;
      height: 40px;
      border: none;
      border-radius: 10px;
      background: #fff;
      color: #184f61;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
    }

    .sidebar-drawer-backdrop {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, .4);
      z-index: 300;
    }

    .sidebar-drawer-backdrop.is-open {
      display: block;
    }

    @media (max-width:1024px) {

      .mob-topbar {
        display: flex;
      }

      .dash-sidebar {
        position: fixed !important;
        top: 0 !important;
        left: -280px !important;
        width: 260px !important;
        height: 100vh !important;
        z-index: 400 !important;
        transition: left .25s ease !important;
        overflow-y: auto !important;
      }

      .dash-sidebar.drawer-open {
        left: 0 !important;
      }

      html,
      body {
        height: auto !important;
        min-height: 100% !important;
        overflow-y: auto !important;
        overflow-x: hidden !important;
      }

      .dash-wrap {
        display: block !important;
        height: auto !important;
        min-height: 100vh !important;
        overflow: visible !important;
      }

      .dash-main {
        height: auto !important;
        min-height: calc(100vh - 70px) !important;
        overflow: visible !important;
        padding: 20px !important;
      }

      .a-card,
      .a-card-body,
      .a-form {
        height: auto !important;
        max-height: none !important;
        overflow: visible !important;
      }
    }

    @media (max-width:768px) {

      .dash-main {
        padding: 12px !important;
      }

      .a-grid {
        grid-template-columns: 1fr !important;
      }

      .a-row {
        grid-template-columns: 1fr !important;
      }

      .a-actions {
        justify-content: stretch !important;
      }

      .a-btn {
        width: 100%;
        justify-content: center;
      }
    }
  </style>

  <script>
    document.addEventListener('DOMContentLoaded', function() {

      // Toggle password
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

      // Drawer sidebar mobile
      const sidebar = document.querySelector('.dash-sidebar');
      const hamBtn = document.getElementById('mobHamBtn');
      const backdrop = document.getElementById('sidebarBackdrop');

      function openDrawer() {
        sidebar?.classList.add('drawer-open');
        backdrop?.classList.add('is-open');
      }

      function closeDrawer() {
        sidebar?.classList.remove('drawer-open');
        backdrop?.classList.remove('is-open');
      }

      hamBtn?.addEventListener('click', openDrawer);
      backdrop?.addEventListener('click', closeDrawer);

    });
  </script>
  @include('Partials.chatbot')
</body>

</html>