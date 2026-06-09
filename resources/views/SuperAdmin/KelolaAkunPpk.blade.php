<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kelola Akun PPK - SIAPABAJA</title>
  <link rel="stylesheet" href="{{ asset('css/Unit.css') }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>

<body class="dash-body page-sa-akun-ppk">
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
  $superAdminName = $superAdminName ?? 'Super Admin';
  $kelolaAkunActive = request()->routeIs('superadmin.kelola.akun')
  || request()->routeIs('superadmin.kelola.akun.ppk')
  || request()->routeIs('superadmin.kelola.akun.unit');
  $ppkAccounts = $ppkAccounts ?? [];
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

        <button class="dash-link dash-link-accordion {{ $kelolaAkunActive ? 'active' : '' }}"
          id="kelolaAkunParent" type="button">
          <span class="ic"><i class="bi bi-person-gear"></i></span>
          Kelola Akun
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
      <div class="dash-header-row">

  <div class="dash-header">
    <h1>Manajemen Akun PPK</h1>
    <p>Kelola akun admin PPK (Pejabat Pembuat Komitmen)</p>
  </div>

  <button type="button" class="btn-add" id="btnOpenAddModal">
    <i class="bi bi-plus-lg"></i>
    Tambah Admin (PPK)
  </button>

</div>

      <div class="table-card">
        <table>
          <colgroup>
            <col class="col-username">
            <col class="col-unit">
            <col class="col-email">
            <col class="col-password">
            <col class="col-status">
            <col class="col-aksi">
          </colgroup>
          <thead>
            <tr>
              <th>Username</th>
              <th>Unit Kerja</th>
              <th>Email</th>
              <th>Password</th>
              <th>Status</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($ppkAccounts as $item)
            <tr>
              <td>{{ $item['username'] }}</td>
              <td>{{ $item['unit_nama'] }}</td>
              <td>{{ $item['email'] }}</td>
              <td>••••••••</td>
              <td>
                <span class="status {{ $item['status'] === 'active' ? 'aktif' : 'nonaktif' }}">
                  {{ $item['status'] === 'active' ? 'Aktif' : 'Tidak Aktif' }}
                </span>
              </td>
              <td class="aksi">
  <button
    type="button"
    class="aksi-btn aksi-edit btn-edit"
    data-id="{{ $item['id'] ?? '' }}"
    data-username="{{ $item['username'] ?? '' }}"
    data-unit-nama="{{ $item['unit_nama'] ?? '' }}"
    data-email="{{ $item['email'] ?? '' }}"
    data-status="{{ $item['status'] ?? 'active' }}"
    title="Edit">
    <i class="bi bi-pencil-fill"></i>
  </button>

  <form
    action="{{ route('superadmin.kelola.akun.ppk.destroy', $item['id']) }}"
    method="POST"
    class="form-delete js-delete-form">
    @csrf
    @method('DELETE')
    <button type="button" class="aksi-btn aksi-delete js-open-confirm" title="Hapus">
      <i class="bi bi-trash3-fill"></i>
    </button>
  </form>
</td>
            </tr>
            @empty
            <tr>
              <td colspan="6" class="empty-cell">Belum ada data akun PPK.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </main>
  </div>

  {{-- ======= MODAL TAMBAH ======= --}}
  <div class="modal-backdrop" id="addModal">
    <div class="modal-card">
      <div class="modal-head">
        <h2>Tambah Admin (PPK)</h2>

        <button type="button" class="modal-close" data-close="addModal">
          <i class="bi bi-x-lg"></i>
        </button>
      </div>

      <div class="modal-divider"></div>

      <form action="{{ route('superadmin.kelola.akun.ppk.store') }}"
        method="POST"
        class="modal-form">

        @csrf

        {{-- Username --}}
        <div class="form-group">
          <label>Username</label>

          <input
            type="text"
            name="username"
            placeholder="Masukkan username"
            required>
        </div>

        {{-- Unit Kerja --}}
        <div class="form-group">
          <label>Unit Kerja</label>

          <input
            type="text"
            name="unit_kerja"
            placeholder="Masukkan nama unit"
            required>
        </div>

        {{-- Email --}}
        <div class="form-group">
          <label>Email</label>

          <input
            type="email"
            name="email"
            placeholder="Masukkan email"
            required>
        </div>

        {{-- Password --}}
        <div class="form-group">
          <label>Password</label>

          <input
            type="text"
            name="password"
            placeholder="Masukkan password"
            required>
        </div>

        {{-- Status --}}
        <div class="form-group">
          <label>Status</label>

          <div class="select-wrap">
            <select name="status" required>
              <option value="active">Aktif</option>
              <option value="inactive">Tidak Aktif</option>
            </select>

            <i class="bi bi-chevron-down"></i>
          </div>
        </div>

        {{-- Button --}}
        <div class="modal-actions">
          <button type="button"
            class="btn-cancel"
            data-close="addModal">
            Batal
          </button>

          <button type="submit"
            class="btn-save">
            Simpan
          </button>
        </div>

      </form>
    </div>
  </div>


  {{-- ======= MODAL EDIT ======= --}}
  <div class="modal-backdrop" id="editModal">
    <div class="modal-card">

      <div class="modal-head">
        <h2>Edit Admin (PPK)</h2>

        <button type="button"
          class="modal-close"
          data-close="editModal">
          <i class="bi bi-x-lg"></i>
        </button>
      </div>

      <div class="modal-divider"></div>

      <form action="{{ route('superadmin.kelola.akun.ppk.update', 0) }}"
        method="POST"
        class="modal-form"
        id="editForm">

        @csrf
        @method('PUT')

        <input type="hidden" name="id" id="edit_id">

        {{-- Username --}}
        <div class="form-group">
          <label>Username</label>

          <input
            type="text"
            name="username"
            id="edit_username"
            placeholder="Masukkan username"
            required>
        </div>

        {{-- Unit --}}
        <div class="form-group">
          <label>Unit Kerja</label>

          <input
            type="text"
            name="unit_kerja"
            id="edit_unit_kerja"
            placeholder="Masukkan nama unit"
            required>
        </div>

        {{-- Email --}}
        <div class="form-group">
          <label>Email</label>

          <input
            type="email"
            name="email"
            id="edit_email"
            placeholder="Masukkan email"
            required>
        </div>

        {{-- Password --}}
        <div class="form-group">
          <label>Password Baru</label>

          <input
            type="text"
            name="password"
            id="edit_password"
            placeholder="Kosongkan jika tidak diubah">
        </div>

        {{-- Status --}}
        <div class="form-group">
          <label>Status</label>

          <div class="select-wrap">
            <select name="status"
              id="edit_status"
              required>

              <option value="active">Aktif</option>
              <option value="inactive">Tidak Aktif</option>

            </select>

            <i class="bi bi-chevron-down"></i>
          </div>
        </div>

        {{-- Button --}}
        <div class="modal-actions">

          <button type="button"
            class="btn-cancel"
            data-close="editModal">
            Batal
          </button>

          <button type="submit"
            class="btn-save">
            Simpan
          </button>

        </div>
      </form>
    </div>
  </div>
  {{-- ======= TOAST ======= --}}
  @if(session('success'))
  <div class="nt-wrap" id="ntWrap">
    <div class="nt-toast nt-success" id="ntToast">
      <div class="nt-ic"><i class="bi bi-check2-circle"></i></div>
      <div class="nt-content">
        <div class="nt-title">Berhasil</div>
        <div class="nt-desc">{{ session('success') }}</div>
      </div>
      <button type="button" class="nt-close" id="ntCloseBtn">
        <i class="bi bi-x-lg"></i>
      </button>
      <div class="nt-bar"></div>
    </div>
  </div>
  @endif

  @if(session('error') || $errors->any())
  <div class="nt-wrap" id="ntWrapErr">
    <div class="nt-toast nt-error" id="ntToastErr">
      <div class="nt-ic nt-ic-err"><i class="bi bi-x-circle"></i></div>
      <div class="nt-content">
        <div class="nt-title">Gagal</div>
        <div class="nt-desc">{{ session('error') ?? $errors->first() }}</div>
      </div>
      <button type="button" class="nt-close" id="ntCloseBtnErr">
        <i class="bi bi-x-lg"></i>
      </button>
      <div class="nt-bar nt-bar-err"></div>
    </div>
  </div>
  @endif

  {{-- ======= CONFIRM DELETE ======= --}}
  <div class="cf-modal" id="cfModal" aria-hidden="true">
    <div class="cf-backdrop" data-close="true"></div>
    <div class="cf-panel" role="dialog" aria-modal="true">
      <div class="cf-card">
        <div class="cf-top">
          <div class="cf-badge"><i class="bi bi-shield-exclamation"></i></div>
          <button type="button" class="cf-close" id="cfCloseBtn">
            <i class="bi bi-x-lg"></i>
          </button>
        </div>
        <div class="cf-body">
          <div class="cf-title">Konfirmasi Hapus</div>
          <div class="cf-desc">Akun PPK yang dihapus tidak dapat dikembalikan.</div>
          <div class="cf-actions">
            <button type="button" class="cf-btn cf-btn-ghost" id="cfCancelBtn">Batal</button>
            <button type="button" class="cf-btn cf-btn-danger" id="cfConfirmBtn">
              <i class="bi bi-trash3"></i> Ya, Hapus
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <style>
    :root {
      --sidebar: #1f5872;
      --yellow: #f6c100;
      --unsoed-blue: #184f61;
    }



    .dash-link-accordion {
      width: 100%;
      text-align: left;
      background: transparent;
      border: none;
      cursor: pointer;
      color: rgba(255, 255, 255, .92);
      font-family: 'Nunito', sans-serif;
      font-size: 16px;
      font-weight: 400;
      display: flex;
      gap: 10px;
      align-items: center;
      padding: 12px 12px;
      border-radius: 10px;
      transition: background .15s;
    }

    .dash-link-accordion:hover {
      background: rgba(255, 255, 255, .08);
    }

    .dash-link-accordion.active {
      background: var(--yellow);
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
      gap: 4px;
      padding-left: 14px;
      margin-top: 2px;
    }

    .dash-sub.is-open {
      display: flex;
    }

    .dash-sub-link {
      display: flex;
      align-items: center;
      gap: 10px;
      color: rgba(255, 255, 255, .80);
      text-decoration: none;
      padding: 12px 12px;
      border-radius: 10px;
      font-size: 16px;
      font-weight: 400;
      transition: background .15s ease, color .15s ease;
    }

    .dash-sub-link .ic {
      width: 18px;
      text-align: center;
      opacity: .95;
    }

    .dash-sub-link:hover {
      background: rgba(255, 255, 255, .08);
      color: #fff;
    }

    .dash-sub-link.active {
      background: var(--yellow);
      color: #0f172a;
    }

    .dash-header {
      width: 100%;
      display: flex;
      flex-direction: column;
      gap: 4px;
    }

    .dash-header h1 {
      margin: 0;
      font-size: 26px;
      font-weight: 600;
      color: #184f61;
    }

    .dash-header p {
      margin: 0;
      font-size: 15px;
      color: #64748b;
      font-weight: 400;
    }

    .dash-header-row {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      gap: 16px;
      margin-bottom: 18px;
    }

    .btn-add {
      border: 0;
      background: var(--sidebar);
      color: #fff;
      border-radius: 8px;
      padding: 12px 18px;
      font-family: inherit;
      font-size: 16px;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      white-space: nowrap;
      flex-shrink: 0;
      min-width: max-content;
    }

    html,
    body {
      overflow-y: auto !important;
      overflow-x: hidden !important;
    }

    .dash-main {
      height: auto !important;
      overflow: visible !important;
    }

    .dash-sidebar {
      height: 100vh;
      display: flex;
      flex-direction: column;
    }

    .dash-nav {
      flex: 1;
      overflow-y: auto;
    }

    .dash-side-actions {
      margin-top: auto;
    }

    .dash-nav::-webkit-scrollbar {
      width: 6px;
    }

    .dash-nav::-webkit-scrollbar-thumb {
      background: rgba(255, 255, 255, 0.3);
      border-radius: 10px;
    }

    .table-card {
      width: 100%;
      background: #fff;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 6px 20px rgba(0, 0, 0, .08);
    }

    table {
      width: 100%;
      table-layout: fixed;
      border-collapse: collapse;
    }

    .col-username {
      width: 18%;
    }

    .col-unit {
      width: 16%;
    }

    .col-email {
      width: 22%;
    }

    .col-password {
      width: 18%;
    }

    .col-status {
      width: 13%;
    }

    .col-aksi {
      width: 13%;
    }

    thead {
      background: var(--sidebar);
      color: #fff;
    }

    th,
    td {
      padding: 18px 16px;
      text-align: left;
      font-size: 15px;
      vertical-align: middle;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    th:nth-child(5),
    td:nth-child(5),
    th:nth-child(6),
    td:nth-child(6) {
      text-align: center;
    }

    tbody tr {
      border-bottom: 1px solid #d1d5db;
    }

    tbody td {
      color: #24526c;
    }

    .status {
      display: inline-block;
      min-width: 90px;
      text-align: center;
      padding: 8px 14px;
      border-radius: 6px;
      font-size: 14px;
    }

    .status.aktif {
      background: #a8dca1;
      color: #166534;
    }

    .status.nonaktif {
      background: #f2b4b4;
      color: #991b1b;
    }

    .aksi {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 14px;
    }

    .icon-btn {
      border: 0;
      background: transparent;
      color: var(--unsoed-blue);
      font-size: 18px;
      cursor: pointer;
      padding: 0;
    }

    .icon-btn i,
    .icon-btn .bi {
      color: var(--unsoed-blue) !important;
      -webkit-text-fill-color: var(--unsoed-blue) !important;
      opacity: 1 !important;
    }

    .icon-btn:hover i,
    .icon-btn:hover .bi {
      color: #143f4d !important;
      -webkit-text-fill-color: #143f4d !important;
    }

    .form-delete {
      display: inline;
      margin: 0;
      padding: 0;
    }

    .empty-cell {
      text-align: center;
      color: #6b7280;
      padding: 28px 16px;
    }

    .modal-backdrop {
      position: fixed;
      inset: 0;
      background: rgba(15, 23, 42, 0.28);
      backdrop-filter: blur(6px);
      -webkit-backdrop-filter: blur(6px);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 9999;
      padding: 24px;
    }

.aksi-btn {
  width: 34px;
  height: 34px;
  border: 1px solid #e8eef3;
  border-radius: 10px;
  background: #f8fafc;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  font-size: 15px;
  text-decoration: none;
  color: #374151;
  transition: .15s;
  padding: 0;
}
.aksi-btn:hover { transform: translateY(-1px); }
.aksi-edit:hover {
  background: #fefce8;
  border-color: #fde68a;
  color: #a16207;
}
.aksi-delete:hover {
  background: #fef2f2;
  border-color: #fecaca;
  color: #dc2626;
}

    .modal-backdrop.show {
      display: flex;
    }

    .modal-card {
      width: 100%;
      max-width: 520px;
      background: #fff;
      border-radius: 24px;
      box-shadow: 0 18px 40px rgba(0, 0, 0, .18);
      padding: 26px 28px;
      max-height: 90vh;
      overflow-y: auto;
    }

    .modal-head {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
    }

    .modal-head h2 {
      margin: 0;
      font-size: 24px;
      color: #1f5872;
      font-weight: 700;
    }

    .modal-close {
      border: 0;
      background: transparent;
      color: #1f5872;
      font-size: 26px;
      cursor: pointer;
      padding: 0;
    }

    .modal-divider {
      height: 1px;
      background: #e5e7eb;
      margin: 18px 0 20px;
    }

    .modal-form {
      display: flex;
      flex-direction: column;
      gap: 14px;
    }

    .form-group {
      display: flex;
      flex-direction: column;
      gap: 8px;
    }

    .form-group label {
      font-size: 15px;
      color: #1f5872;
      font-weight: 600;
    }

    .form-group input,
    .form-group select {
      width: 100%;
      height: 46px;
      border: 1px solid #cfd5db;
      border-radius: 14px;
      padding: 0 14px;
      font-size: 15px;
      font-family: inherit;
      color: #24526c;
      outline: none;
      background: #fff;
    }

    .form-group input:focus,
    .form-group select:focus {
      border-color: #1f5872;
    }

    .select-wrap {
      position: relative;
    }

    .select-wrap select {
      appearance: none;
      -webkit-appearance: none;
      padding-right: 42px;
    }

    .select-wrap i {
      position: absolute;
      right: 16px;
      top: 50%;
      transform: translateY(-50%);
      color: #1f5872;
      pointer-events: none;
      font-size: 20px;
    }

    .modal-actions {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 18px;
      margin-top: 6px;
    }

    .btn-cancel,
    .btn-save {
      min-width: 116px;
      height: 46px;
      border-radius: 14px;
      font-family: inherit;
      font-size: 16px;
      cursor: pointer;
    }

    .btn-cancel {
      border: 1px solid #cfd5db;
      background: #fff;
      color: #1f5872;
    }

    .btn-save {
      border: 0;
      background: #1f5872;
      color: #fff;
    }

    .nt-wrap {
      position: fixed;
      top: 18px;
      right: 18px;
      z-index: 11000;
      pointer-events: none;
    }

    .nt-toast {
      width: min(380px, calc(100vw - 36px));
      background: #fff;
      border: 1px solid #e6eef2;
      border-radius: 16px;
      box-shadow: 0 16px 32px rgba(2, 8, 23, .12);
      padding: 14px;
      display: flex;
      gap: 12px;
      align-items: flex-start;
      position: relative;
      overflow: hidden;
      pointer-events: auto;
      animation: ntIn .35s cubic-bezier(.21, 1.02, .73, 1);
    }

    .nt-success {
      border-left: 4px solid #22c55e;
    }

    .nt-error {
      border-left: 4px solid #ef4444;
    }

    .nt-ic {
      width: 38px;
      height: 38px;
      border-radius: 12px;
      display: grid;
      place-items: center;
      background: #ecfdf3;
      border: 1px solid #d8f5e3;
      color: #16a34a;
      flex: 0 0 auto;
    }

    .nt-ic-err {
      background: #fef2f2;
      border-color: #fecaca;
      color: #dc2626;
    }

    .nt-content {
      flex: 1;
    }

    .nt-title {
      font-size: 14px;
      font-weight: 800;
      color: #0f172a;
    }

    .nt-desc {
      font-size: 13px;
      color: #475569;
      margin-top: 2px;
      line-height: 1.5;
    }

    .nt-close {
      margin-left: auto;
      width: 32px;
      height: 32px;
      border-radius: 10px;
      border: 1px solid #eef2f7;
      background: #fff;
      display: grid;
      place-items: center;
      padding: 0;
      cursor: pointer;
    }

    .nt-bar {
      position: absolute;
      left: 0;
      bottom: 0;
      height: 3px;
      width: 100%;
      background: linear-gradient(90deg, #22c55e, #16a34a);
      animation: ntbar 4s linear forwards;
    }

    .nt-bar-err {
      background: linear-gradient(90deg, #ef4444, #dc2626);
    }

    @keyframes ntIn {
      from {
        opacity: 0;
        transform: translateX(40px);
      }

      to {
        opacity: 1;
        transform: translateX(0);
      }
    }

    @keyframes ntOut {
      from {
        opacity: 1;
        transform: translateX(0);
      }

      to {
        opacity: 0;
        transform: translateX(40px);
      }
    }

    @keyframes ntbar {
      from {
        width: 100%;
      }

      to {
        width: 0%;
      }
    }

    .cf-modal {
      position: fixed;
      inset: 0;
      z-index: 10000;
      display: none;
    }

    .cf-modal.is-open {
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 12px;
    }

    .cf-backdrop {
      position: fixed;
      inset: 0;
      background: rgba(15, 23, 42, .40);
      backdrop-filter: blur(8px);
    }

    .cf-panel {
      width: min(480px, 94vw);
      position: relative;
      z-index: 1;
      border-radius: 20px;
      overflow: hidden;
      box-shadow: 0 20px 50px rgba(2, 6, 23, .25);
    }

    .cf-card {
      background: #fff;
      border-radius: 20px;
      overflow: hidden;
    }

    .cf-top {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: 12px;
      padding: 16px 16px 0;
    }

    .cf-badge {
      width: 50px;
      height: 50px;
      border-radius: 16px;
      display: grid;
      place-items: center;
      background: #fef3c7;
      border: 1px solid #fde68a;
      font-size: 22px;
      color: #d97706;
    }

    .cf-close {
      width: 40px;
      height: 40px;
      border-radius: 12px;
      border: 1px solid #e8eef3;
      background: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 0;
      cursor: pointer;
    }

    .cf-body {
      padding: 10px 16px 20px;
    }

    .cf-title {
      font-size: 18px;
      font-weight: 800;
      color: #0f172a;
      margin: 6px 0 4px;
    }

    .cf-desc {
      font-size: 13.5px;
      color: #475569;
      line-height: 1.55;
    }

    .cf-actions {
      display: flex;
      gap: 8px;
      justify-content: flex-end;
      margin-top: 16px;
    }

    .cf-btn {
      height: 40px;
      padding: 0 16px;
      border-radius: 12px;
      border: 1px solid transparent;
      font-size: 14px;
      font-weight: 700;
      font-family: inherit;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 7px;
      transition: .15s;
    }

    .cf-btn-ghost {
      background: #fff;
      border-color: #e8eef3;
      color: #0f172a;
    }

    .cf-btn-ghost:hover {
      background: #f1f5f9;
    }

    .cf-btn-danger {
      background: #ef4444;
      color: #fff;
    }

    .cf-btn-danger:hover {
      background: #dc2626;
    }

    @media (max-width: 900px) {
      .dash-header-row {
        flex-direction: column;
        align-items: stretch;
      }

      .btn-add {
        justify-content: center;
        width: 100%;
      }

      .modal-card {
        padding: 24px 20px;
        border-radius: 22px;
      }

      table {
        display: block;
        overflow-x: auto;
      }
    }

    /* =========================================================
   RESPONSIVE SUPERADMIN KELOLA AKUN PPK
========================================================= */

:where(.page-sa-akun-ppk) .mob-topbar{
  display:none;
  align-items:center;
  justify-content:space-between;
  padding:14px 16px;
  background:#184f61;
  position:sticky;
  top:0;
  z-index:200;
}

:where(.page-sa-akun-ppk) .mob-logo{
  display:flex;
  align-items:center;
  gap:10px;
}

:where(.page-sa-akun-ppk) .mob-logo img{
  width:32px;
  height:32px;
}

:where(.page-sa-akun-ppk) .mob-logo-txt{
  color:#f4c542;
  font-size:17px;
  font-weight:700;
}

:where(.page-sa-akun-ppk) .mob-ham{
  width:40px;
  height:40px;
  border:1px solid #e6eef2;
  border-radius:10px;
  background:#fff;
  color:#184f61;
  display:flex;
  align-items:center;
  justify-content:center;
  cursor:pointer;
  font-size:18px;
}

:where(.page-sa-akun-ppk) .sidebar-drawer-backdrop{
  display:none;
  position:fixed;
  inset:0;
  background:rgba(0,0,0,.4);
  z-index:300;
}

:where(.page-sa-akun-ppk) .sidebar-drawer-backdrop.is-open{
  display:block;
}

@media (max-width:1024px){

  :where(.page-sa-akun-ppk) .mob-topbar{
    display:flex;
  }

  :where(.page-sa-akun-ppk) .dash-sidebar{
    position:fixed !important;
    top:0 !important;
    left:-280px !important;
    width:260px !important;
    height:100vh !important;
    z-index:400 !important;
    transition:left .25s ease !important;
    overflow-y:auto !important;
  }

  :where(.page-sa-akun-ppk) .dash-sidebar.drawer-open{
    left:0 !important;
  }

  :where(.page-sa-akun-ppk) .dash-wrap{
    display:block !important;
    min-height:100vh !important;
  }

  :where(.page-sa-akun-ppk) .dash-main{
    height:auto !important;
    overflow:visible !important;
    padding:20px !important;
  }

  :where(.page-sa-akun-ppk) .dash-header-row{
    flex-direction:column !important;
    align-items:stretch !important;
  }

  :where(.page-sa-akun-ppk) .btn-add{
    width:100% !important;
    justify-content:center !important;
  }

  :where(.page-sa-akun-ppk) .table-card{
    overflow-x:auto !important;
  }

  :where(.page-sa-akun-ppk) table{
    min-width:900px;
  }
}

@media (max-width:768px){

  :where(.page-sa-akun-ppk) .dash-main{
    padding:12px !important;
  }

  :where(.page-sa-akun-ppk) .modal-card{
    padding:20px 16px !important;
  }

  :where(.page-sa-akun-ppk) .modal-actions{
    flex-direction:column;
  }

  :where(.page-sa-akun-ppk) .btn-save,
  :where(.page-sa-akun-ppk) .btn-cancel{
    width:100%;
  }
}
  </style>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
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

      const addModal = document.getElementById('addModal');
      const editModal = document.getElementById('editModal');
      const editForm = document.getElementById('editForm');

      document.getElementById('btnOpenAddModal')?.addEventListener('click', () => addModal.classList.add('show'));

      document.querySelectorAll('[data-close]').forEach(btn => {
        btn.addEventListener('click', () => {
          const modal = document.getElementById(btn.getAttribute('data-close'));
          if (modal) modal.classList.remove('show');
        });
      });

      [addModal, editModal].forEach(modal => {
        modal?.addEventListener('click', e => {
          if (e.target === modal) modal.classList.remove('show');
        });
      });

      document.querySelectorAll('.btn-edit').forEach(btn => {
        btn.addEventListener('click', () => {
          const id = btn.dataset.id || '';
          document.getElementById('edit_id').value = id;
          document.getElementById('edit_username').value = btn.dataset.username || '';
          document.getElementById('edit_unit_kerja').value =
            btn.dataset.unitNama || '';
          document.getElementById('edit_email').value = btn.dataset.email || '';
          document.getElementById('edit_password').value = '';
          document.getElementById('edit_status').value = btn.dataset.status || 'Aktif';
          if (editForm && id) editForm.action = "{{ url('super-admin/kelola-akun/ppk') }}/" + id;
          editModal.classList.add('show');
        });
      });

      const parent = document.getElementById('kelolaAkunParent');
      const sub = document.getElementById('kelolaAkunSub');
      if (parent && sub) {
        parent.addEventListener('click', () => {
          sub.classList.toggle('is-open');
          parent.classList.toggle('is-open');
        });
      }

      function closeToast(id) {
        const el = document.getElementById(id);
        if (!el) return;
        el.style.animation = 'ntOut .35s cubic-bezier(.21,1.02,.73,1) forwards';
        setTimeout(() => el.parentElement?.remove(), 350);
      }
      setTimeout(() => closeToast('ntToast'), 4000);
      setTimeout(() => closeToast('ntToastErr'), 4000);
      document.getElementById('ntCloseBtn')?.addEventListener('click', () => closeToast('ntToast'));
      document.getElementById('ntCloseBtnErr')?.addEventListener('click', () => closeToast('ntToastErr'));

      const cfModal = document.getElementById('cfModal');
      const cfCancelBtn = document.getElementById('cfCancelBtn');
      const cfCloseBtn = document.getElementById('cfCloseBtn');
      const cfConfirmBtn = document.getElementById('cfConfirmBtn');
      let pendingForm = null;

      document.querySelectorAll('.js-open-confirm').forEach(btn => {
        btn.addEventListener('click', () => {
          pendingForm = btn.closest('.js-delete-form');
          cfModal.classList.add('is-open');
        });
      });

      function closeConfirm() {
        cfModal.classList.remove('is-open');
        pendingForm = null;
      }

      cfCancelBtn?.addEventListener('click', closeConfirm);
      cfCloseBtn?.addEventListener('click', closeConfirm);
      cfModal?.addEventListener('click', e => {
        if (e.target?.dataset?.close === 'true') closeConfirm();
      });
      cfConfirmBtn?.addEventListener('click', () => {
        cfModal.classList.remove('is-open');
        if (pendingForm) pendingForm.submit();
      });

    });
  </script>
  @include('Partials.chatbot')
</body>

</html>