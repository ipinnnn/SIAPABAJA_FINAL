{{-- resources/views/SuperAdmin/Dashboard.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard Super Admin - SIAPABAJA</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="stylesheet" href="{{ asset('css/Unit.css') }}">
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</head>

<body class="dash-body">
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
<div class="dash-wrap">

  {{-- ===================== SIDEBAR ===================== --}}
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

      <a class="dash-link {{ request()->routeIs('superadmin.kelola.akun') ? 'active' : '' }}"
         href="{{ route('superadmin.kelola.akun') }}">
        <span class="ic"><i class="bi bi-person-gear"></i></span>
        Kelola Akun
      </a>
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

  {{-- ===================== MAIN ===================== --}}
  <main class="dash-main">
    <header class="dash-header">
      <h1>Dashboard Super Admin</h1>
      <p>Kelola seluruh arsip pengadaan dari semua unit kerja</p>
    </header>

    {{-- SUMMARY CARDS --}}
    <section class="u-sum">

      {{-- Row 1: 5 card --}}
      <div class="u-sum-row u-sum-row--5">

        <div class="u-card">
          <div class="u-bar u-bar--navy"></div>
          <div class="u-top">
            <div>
              <div class="u-label">{{ $summary[0]['label'] }}</div>
              <div class="u-value u-value--navy js-count" data-count="{{ (int) $summary[0]['value'] }}">0</div>
            </div>
            <div class="u-ic"><i class="bi {{ $summary[0]['icon'] }}"></i></div>
          </div>
        </div>

        <div class="u-card">
          <div class="u-bar u-bar--green"></div>
          <div class="u-top">
            <div>
              <div class="u-label">{{ $summary[1]['label'] }}</div>
              <div class="u-value u-value--green js-count" data-count="{{ (int) $summary[1]['value'] }}">0</div>
            </div>
            <div class="u-ic u-ic--green"><i class="bi {{ $summary[1]['icon'] }}"></i></div>
          </div>
        </div>

        <div class="u-card">
          <div class="u-bar u-bar--gray"></div>
          <div class="u-top">
            <div>
              <div class="u-label">{{ $summary[2]['label'] }}</div>
              <div class="u-value u-value--gray js-count" data-count="{{ (int) $summary[2]['value'] }}">0</div>
            </div>
            <div class="u-ic u-ic--gray"><i class="bi {{ $summary[2]['icon'] }}"></i></div>
          </div>
        </div>

        <div class="u-card">
          <div class="u-bar u-bar--green"></div>
          <div class="u-top">
            <div>
              <div class="u-label">{{ $summary[3]['label'] }}</div>
              <div class="u-value u-value--green js-count" data-count="{{ (int) $summary[3]['value'] }}">0</div>
            </div>
            <div class="u-ic u-ic--green"><i class="bi {{ $summary[3]['icon'] }}"></i></div>
          </div>
        </div>

        {{-- Card Unit Kerja + tombol info --}}
        <div class="u-card u-card--unit">
          <div class="u-bar u-bar--yellow"></div>
          <div class="u-top">
            <div>
              <div class="u-label">{{ $summary[4]['label'] }}</div>
              <div class="u-value u-value--yellow js-count" data-count="{{ (int) $summary[4]['value'] }}">0</div>
            </div>
            <div class="u-ic u-ic--yellow"><i class="bi {{ $summary[4]['icon'] }}"></i></div>
          </div>
          <button class="u-info-btn u-info-btn--card" type="button" data-modal="modalUnit"
                  aria-label="Lihat daftar unit kerja terdaftar">
            <i class="bi bi-info"></i>
          </button>
        </div>

      </div>{{-- /row-5 --}}

      {{-- Row 2: 2 card (paket + nilai) --}}
      <div class="u-sum-row u-sum-row--2">

        {{-- Total Arsip Pengadaan --}}
        <div class="u-card">
          <div class="u-bar u-bar--navy"></div>

          <div class="u-top">
            <div>
              <div class="u-label">{{ $summary[5]['label'] }}</div>
              <div class="u-value u-value--navy js-count" id="valPaket"
                   data-count="{{ (int) $summary[0]['value'] }}">0</div>
            </div>

            <div class="u-ic">
              <i class="bi {{ $summary[5]['icon'] }}"></i>
            </div>
          </div>

          <div class="u-sub-filter-row">
            <div class="u-sub">{{ $summary[5]['sub'] }}</div>

            <div class="u-card-filter">
              <div class="u-mini-select">
                <select id="fTahunPaket">
                  <option value="">Tahun</option>
                  @foreach($tahunOptions as $t)
                    <option value="{{ $t }}">{{ $t }}</option>
                  @endforeach
                </select>
                <i class="bi bi-chevron-down"></i>
              </div>

              <div class="u-mini-select">
                <select id="fUnitPaket">
                  <option value="">Unit</option>
                  @foreach($unitOptions as $u)
                    <option value="{{ $u['id'] }}">{{ $u['name'] }}</option>
                  @endforeach
                </select>
                <i class="bi bi-chevron-down"></i>
              </div>
            </div>
          </div>
        </div>

        {{-- Total Nilai Pengadaan --}}
        <div class="u-card">
          <div class="u-bar u-bar--yellow"></div>

          <div class="u-top">
            <div>
              <div class="u-label">{{ $summary[6]['label'] }}</div>
              <div class="u-money js-count" id="valNilai"
                   data-count="{{ (int) preg_replace('/[^0-9]/','', (string) $summary[6]['value']) }}">Rp 0</div>
            </div>

            <div class="u-ic u-ic--yellow">
              <i class="bi {{ $summary[6]['icon'] }}"></i>
            </div>
          </div>

          <div class="u-sub-filter-row">
            <div class="u-sub">{{ $summary[6]['sub'] }}</div>

            <div class="u-card-filter">
              <div class="u-mini-select">
                <select id="fTahunNilai">
                  <option value="">Tahun</option>
                  @foreach($tahunOptions as $t)
                    <option value="{{ $t }}">{{ $t }}</option>
                  @endforeach
                </select>
                <i class="bi bi-chevron-down"></i>
              </div>

              <div class="u-mini-select">
                <select id="fUnitNilai">
                  <option value="">Unit</option>
                  @foreach($unitOptions as $u)
                    <option value="{{ $u['id'] }}">{{ $u['name'] }}</option>
                  @endforeach
                </select>
                <i class="bi bi-chevron-down"></i>
              </div>
            </div>
          </div>
        </div>

      </div>{{-- /row-2 --}}
    </section>

    {{-- CHARTS --}}
    <section class="u-charts">

      {{-- Donut: Status Arsip --}}
      <div class="u-chart-card">
        <div class="u-chart-head">
          <div class="u-chart-title">Status Arsip</div>
          <button class="u-info-btn" type="button" data-pop="popDonut"
                  aria-label="Lihat detail Status Arsip">
            <i class="bi bi-info"></i>
          </button>
          <div id="popDonut" class="u-popover" role="dialog" aria-hidden="true">
            <div class="u-popover-title">Detail Status Arsip</div>
            <div class="u-popover-meta"><span id="metaDonut">—</span></div>
            <div class="u-popover-list" id="listDonut"></div>
            <div class="u-popover-foot">Klik di luar untuk menutup</div>
          </div>
        </div>
        <div class="u-chart-divider"></div>
        <div class="u-chart-filters">
          <div class="u-select">
            <select id="fTahun1">
              <option value="">Semua Tahun</option>
              @foreach($tahunOptions as $t)
                <option value="{{ $t }}">{{ $t }}</option>
              @endforeach
            </select>
            <i class="bi bi-chevron-down"></i>
          </div>
          <div class="u-select">
            <select id="fUnit1">
              <option value="">Semua Unit</option>
              @foreach($unitOptions as $u)
                <option value="{{ $u['id'] }}">{{ $u['name'] }}</option>
              @endforeach
            </select>
            <i class="bi bi-chevron-down"></i>
          </div>
        </div>
        <div class="u-canvas-wrap">
          <canvas id="donutStatus"></canvas>
        </div>
      </div>

      {{-- Bar: Metode Pengadaan --}}
      <div class="u-chart-card">
        <div class="u-chart-head">
          <div class="u-chart-title">Metode Pengadaan</div>
          <button class="u-info-btn" type="button" data-pop="popBar"
                  aria-label="Lihat detail Metode Pengadaan">
            <i class="bi bi-info"></i>
          </button>
          <div id="popBar" class="u-popover" role="dialog" aria-hidden="true">
            <div class="u-popover-title">Detail Metode Pengadaan</div>
            <div class="u-popover-meta"><span id="metaBar">—</span></div>
            <div class="u-popover-list" id="listBar"></div>
            <div class="u-popover-foot">Klik di luar untuk menutup</div>
          </div>
        </div>
        <div class="u-chart-divider"></div>
        <div class="u-chart-filters">
          <div class="u-select">
            <select id="fTahun2">
              <option value="">Semua Tahun</option>
              @foreach($tahunOptions as $t)
                <option value="{{ $t }}">{{ $t }}</option>
              @endforeach
            </select>
            <i class="bi bi-chevron-down"></i>
          </div>
          <div class="u-select">
            <select id="fUnit2">
              <option value="">Semua Unit</option>
              @foreach($unitOptions as $u)
                <option value="{{ $u['id'] }}">{{ $u['name'] }}</option>
              @endforeach
            </select>
            <i class="bi bi-chevron-down"></i>
          </div>
        </div>
        <div class="u-canvas-wrap">
          <canvas id="barStatus"></canvas>
        </div>
      </div>

    </section>
  </main>
</div>

{{-- MODAL UNIT KERJA --}}
<div class="u-modal" id="modalUnit" aria-hidden="true" role="dialog"
     aria-label="Daftar Unit Kerja Terdaftar">
  <div class="u-modal-backdrop" data-close="modalUnit"></div>
  <div class="u-modal-dialog" role="document">
    <div class="u-modal-head">
      <div class="u-modal-title">
        <div class="t1">Daftar Unit Kerja Terdaftar</div>
        <div class="t2">Total: {{ count($registeredUnits) }} unit</div>
      </div>
      <button class="u-modal-close" type="button" data-close="modalUnit"
              aria-label="Tutup popup">
        <i class="bi bi-x-lg"></i>
      </button>
    </div>
    <div class="u-modal-body">
      <div class="u-unit-grid">
        @foreach($registeredUnits as $i => $name)
          <div class="u-unit-item">
            <div class="u-unit-badge">{{ $i + 1 }}</div>
            <div class="u-unit-name">{{ $name }}</div>
          </div>
        @endforeach
      </div>
    </div>
    <div class="u-modal-foot">
      <div class="u-modal-hint">Klik area gelap atau tombol X untuk menutup</div>
    </div>
  </div>
</div>

<style>
  body.is-modal-open { overflow: hidden !important; }

  .dash-header {
    display: flex;
    flex-direction: column;
    gap: 4px;
    margin-bottom: 20px;
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

  .u-bar--green { background: #22c55e; }
  .u-value--green { color: #16a34a; }
  .u-ic--green { color: #16a34a; background: #dcfce7; }
  .u-value--yellow { color: #d39a00; }

  .u-sum { display: grid; gap: 16px; }
  .u-sum-row { display: grid; gap: 16px; }
  .u-sum-row--5 { grid-template-columns: repeat(5, minmax(0,1fr)); }
  .u-sum-row--2 { grid-template-columns: repeat(2, minmax(0,1fr)); }

  .u-card {
    background: #fff;
    border: 1px solid #e6eef2;
    border-radius: 14px;
    box-shadow: 0 10px 20px rgba(2,8,23,.04);
    overflow: hidden;
    position: relative;
    padding: 16px 16px 14px;
    min-height: 86px;
  }

  .u-bar {
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 4px;
    border-radius: 14px 0 0 14px;
  }

  .u-bar--navy { background: #184f61; }
  .u-bar--yellow { background: #f6c100; }
  .u-bar--gray { background: #0f172a; opacity: .75; }

  .u-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 14px;
  }

  .u-label {
    font-size: 14px;
    color: #64748b;
    margin-bottom: 6px;
    font-weight: 400;
  }

  .u-value {
    font-size: 32px;
    line-height: 1;
    font-weight: 400;
  }

  .u-value--navy { color: #184f61; }
  .u-value--gray { color: #0f172a; opacity: .85; }

  .u-money {
    font-size: 32px;
    line-height: 1.05;
    color: #c98800;
    font-weight: 400;
  }

  .u-sub {
    margin-top: 8px;
    font-size: 13px;
    color: #94a3b8;
    font-weight: 400;
  }

  .u-sub-filter-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-top: 6px;
  }

  .u-sub-filter-row .u-sub {
    margin-top: 0;
    flex: 1;
    min-width: 0;
  }

  .u-card-filter {
    display: flex;
    gap: 8px;
    align-items: center;
    justify-content: flex-end;
    margin-top: 0;
    flex-wrap: nowrap;
    flex: 0 0 auto;
  }

  .u-mini-select {
    position: relative;
  }

  .u-mini-select select {
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 6px 28px 6px 10px;
    font-family: inherit;
    font-size: 13px;
    font-weight: 400;
    background: #fff;
    outline: none;
    appearance: none;
    cursor: pointer;
    width: 80px;
    max-width: 80px;
  }

  .u-mini-select i {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    opacity: .6;
    pointer-events: none;
    font-size: 12px;
  }

  .u-ic {
    width: 40px;
    height: 40px;
    display: grid;
    place-items: center;
    border-radius: 10px;
    background: #f1f5f9;
    color: #184f61;
    font-size: 20px;
    flex: 0 0 auto;
  }

  .u-ic--yellow { color: #c98800; background: #fff6cc; }
  .u-ic--gray { color: #0f172a; background: #eef2f7; }

  .u-info-btn {
    position: absolute;
    right: 0;
    top: 0;
    width: 26px;
    height: 26px;
    border-radius: 999px;
    border: 2px solid #184f61;
    background: #fff;
    display: grid;
    place-items: center;
    cursor: pointer;
    padding: 0;
    color: #184f61;
    box-shadow: 0 10px 20px rgba(2,8,23,.06);
  }

  .u-card--unit .u-info-btn--card {
    top: auto;
    bottom: 12px;
    right: 12px;
  }

  .u-info-btn i {
    font-size: 14px;
    opacity: .9;
    pointer-events: none;
  }

  .u-info-btn:hover {
    border-color: #143f4d;
    transform: translateY(-.5px);
  }

  .u-info-btn--card {
    position: absolute;
    right: 12px;
    top: 12px;
  }

  .u-popover {
    position: absolute;
    right: 0;
    top: 30px;
    width: 340px;
    background: #fff;
    border: 1px solid #e6eef2;
    border-radius: 12px;
    box-shadow: 0 18px 30px rgba(2,8,23,.12);
    padding: 10px 10px 8px;
    z-index: 50;
    display: none;
  }

  .u-popover.is-open { display: block; }

  .u-popover::before {
    content: "";
    position: absolute;
    right: 12px;
    top: -6px;
    width: 10px;
    height: 10px;
    background: #fff;
    border-left: 1px solid #e6eef2;
    border-top: 1px solid #e6eef2;
    transform: rotate(45deg);
  }

  .u-popover-title {
    font-size: 13px;
    color: #0f172a;
    margin-bottom: 4px;
    font-weight: 400;
  }

  .u-popover-meta {
    font-size: 12px;
    color: #64748b;
    margin-bottom: 8px;
  }

  .u-popover-list {
    display: grid;
    gap: 6px;
    max-height: 160px;
    overflow: auto;
  }

  .u-popover-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    border: 1px solid #eef2f7;
    border-radius: 10px;
    padding: 8px;
  }

  .u-popover-left {
    display: flex;
    align-items: center;
    gap: 8px;
    min-width: 0;
  }

  .u-dot {
    width: 8px;
    height: 8px;
    border-radius: 999px;
    background: #184f61;
    flex: 0 0 auto;
  }

  .u-popover-name {
    font-size: 13px;
    color: #0f172a;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .u-popover-val {
    font-size: 13px;
    color: #0f172a;
    flex: 0 0 auto;
  }

  .u-popover-foot {
    margin-top: 8px;
    font-size: 11px;
    color: #94a3b8;
    text-align: right;
  }

  .u-charts {
    margin-top: 18px;
    display: grid;
    grid-template-columns: repeat(2, minmax(0,1fr));
    gap: 18px;
  }

  .u-chart-card {
    background: #fff;
    border: 1px solid #e6eef2;
    border-radius: 18px;
    padding: 14px 16px 16px;
    box-shadow: 0 10px 20px rgba(2,8,23,.04);
    position: relative;
  }

  .u-chart-head {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 28px;
  }

  .u-chart-title {
    font-size: 18px;
    color: #0f172a;
    font-weight: 600;
    text-align: center;
    margin-top: 2px;
  }

  .u-chart-divider {
    height: 1px;
    background: #e6eef2;
    margin: 10px 0 12px;
  }

  .u-chart-filters {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    margin-bottom: 12px;
  }

  .u-select {
    position: relative;
  }

  .u-select select {
    width: 100%;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 10px 38px 10px 12px;
    font-size: 14px;
    outline: none;
    background: #fff;
    appearance: none;
    font-weight: 400;
  }

  .u-select i {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    opacity: .6;
    pointer-events: none;
  }

  .u-canvas-wrap {
    height: 260px;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .u-canvas-wrap canvas {
    max-height: 260px !important;
  }

  .u-modal {
    position: fixed;
    inset: 0;
    z-index: 999;
    display: none;
  }

  .u-modal.is-open {
    display: block;
  }

  .u-modal-backdrop {
    position: absolute;
    inset: 0;
    background: rgba(2,8,23,.35);
    backdrop-filter: blur(8px);
  }

  .u-modal-dialog {
    position: absolute;
    left: 50%;
    top: 50%;
    transform: translate(-50%,-50%);
    width: min(980px, calc(100vw - 44px));
    height: min(86vh, 720px);
    background: #fff;
    border: 1px solid #e6eef2;
    border-radius: 18px;
    box-shadow: 0 22px 44px rgba(2,8,23,.18);
    overflow: hidden;
    display: grid;
    grid-template-rows: auto 1fr auto;
  }

  .u-modal-head {
    padding: 14px 16px;
    border-bottom: 1px solid #e6eef2;
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
  }

  .u-modal-title .t1 {
    font-size: 18px;
    color: #0f172a;
    font-weight: 400;
  }

  .u-modal-title .t2 {
    font-size: 13px;
    color: #64748b;
    margin-top: 4px;
  }

  .u-modal-close {
    width: 38px;
    height: 38px;
    border-radius: 12px;
    border: 1px solid #e6eef2;
    background: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: #0f172a;
    padding: 0;
  }

  .u-modal-body {
    padding: 14px 16px 16px;
    overflow: auto;
  }

  .u-unit-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0,1fr));
    gap: 10px;
  }

  .u-unit-item {
    border: 1px solid #eef2f7;
    border-radius: 14px;
    padding: 12px;
    display: flex;
    align-items: center;
    gap: 10px;
    background: #fff;
  }

  .u-unit-badge {
    min-width: 34px;
    height: 30px;
    border-radius: 999px;
    display: grid;
    place-items: center;
    font-size: 13px;
    color: #184f61;
    background: #e9f3f6;
    border: 1px solid #d7e9ee;
    flex: 0 0 auto;
  }

  .u-unit-name {
    font-size: 15px;
    color: #0f172a;
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  .u-modal-foot {
    padding: 12px 16px;
    border-top: 1px solid #e6eef2;
    display: flex;
    align-items: center;
    justify-content: flex-end;
  }

  .u-modal-hint {
    font-size: 12px;
    color: #94a3b8;
  }

  @media (max-width: 1200px) {
    .u-sum-row--5 {
      grid-template-columns: repeat(3, minmax(0,1fr));
    }
  }

  @media (max-width: 900px) {
    .u-sum-row--5 {
      grid-template-columns: repeat(2, minmax(0,1fr));
    }

    .u-sum-row--2 {
      grid-template-columns: 1fr;
    }

    .u-charts {
      grid-template-columns: 1fr;
    }

    .u-sub-filter-row {
      align-items: flex-start;
      flex-direction: column;
      gap: 8px;
    }

    .u-card-filter {
      width: 100%;
      justify-content: flex-start;
    }
  }

  @media (max-width: 600px) {
    .u-sum-row--5 {
      grid-template-columns: 1fr;
    }
  }

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

.mob-logo img { width: 32px; height: 32px; }

.mob-logo-txt {
  color: #f4c542;
  font-size: 17px;
  font-weight: 700;
}

.mob-ham {
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

.sidebar-drawer-backdrop {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, .45);
  backdrop-filter: blur(4px);
  z-index: 300;
}

.sidebar-drawer-backdrop.is-open { display: block; }

@media (max-width: 1024px) {

  .mob-topbar { display: flex; }

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
    box-shadow: 4px 0 24px rgba(2, 8, 23, .18) !important;
  }

  html, body {
    height: auto !important;
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
    overflow-y: visible !important;
    padding: 20px !important;
  }

  .u-sum-row--5 {
    grid-template-columns: repeat(2, minmax(0,1fr)) !important;
  }

  .u-sum-row--2 {
    grid-template-columns: repeat(2, minmax(0,1fr)) !important;
  }

  .u-charts {
    grid-template-columns: 1fr !important;
  }

  .u-sub-filter-row {
    flex-direction: column !important;
    align-items: flex-start !important;
    gap: 8px !important;
  }

  .u-card-filter {
    width: 100% !important;
    justify-content: flex-start !important;
  }

  .u-mini-select select {
    width: 110px !important;
    max-width: 110px !important;
  }
}

@media (max-width: 768px) {

  .dash-main { padding: 12px !important; }

  .dash-header h1 { font-size: 20px !important; }
  .dash-header p  { font-size: 13px !important; }

  .u-sum-row--5 {
    grid-template-columns: 1fr 1fr !important;
  }

  .u-sum-row--2 {
    grid-template-columns: 1fr !important;
  }

  .u-value  { font-size: 26px !important; }
  .u-money  { font-size: 22px !important; }

  .u-chart-filters {
    grid-template-columns: 1fr 1fr !important;
    gap: 8px !important;
  }

  .u-select select {
    font-size: 13px !important;
    padding: 8px 28px 8px 10px !important;
  }

  .u-canvas-wrap { height: 200px !important; }
  .u-canvas-wrap canvas { max-height: 200px !important; }

  .u-popover {
    width: calc(100vw - 48px) !important;
    right: -8px !important;
  }

  .u-modal-dialog {
    width: calc(100vw - 20px) !important;
    height: 90vh !important;
    border-radius: 14px !important;
  }

  .u-unit-grid { grid-template-columns: 1fr !important; }

  .u-mini-select select {
    width: calc(50vw - 48px) !important;
    max-width: 140px !important;
  }
}

@media (max-width: 400px) {
  .u-sum-row--5 { grid-template-columns: 1fr !important; }
  .u-value { font-size: 24px !important; }
  .u-money { font-size: 20px !important; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function(){
/* -------- Sidebar Drawer -------- */
const sidebar  = document.querySelector('.dash-sidebar');
const hamBtn   = document.getElementById('mobHamBtn');
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
  const CountFX = (() => {
    const DURATION = 1200;
    const ease = (t) => 1 - Math.pow(1 - t, 3);
    const parse = (s) => {
      const n = Number(String(s ?? '').replace(/[^\d.-]/g,''));
      return isFinite(n) ? n : 0;
    };
    const fmtID = (n) => Math.round(n).toLocaleString('id-ID');
    const fmtRp = (n) => {
      const x = Math.max(0, Math.round(Number(n || 0)));
      const p = x.toString().split('');
      let out = '';
      for(let i = 0; i < p.length; i++){
        const idx = p.length - i;
        out += p[i];
        if(idx > 1 && idx % 3 === 1) out += '.';
      }
      return 'Rp ' + out;
    };

    const isMoney = (el) => el.classList.contains('u-money') || /rp/i.test(String(el.textContent));
    const set = (el, v) => { el.textContent = isMoney(el) ? fmtRp(v) : fmtID(v); };
    const target = (el) => {
      const d = el.getAttribute('data-count');
      return d != null && d !== '' ? parse(d) : parse(el.textContent);
    };

    const run = (el, to, dur = DURATION) => {
      const from = 0;
      const s = performance.now();
      set(el, 0);
      const tick = (now) => {
        const t = Math.min(1, (now - s) / dur);
        const c = from + (to - from) * ease(t);
        set(el, c);
        if(t < 1) requestAnimationFrame(tick);
      };
      requestAnimationFrame(tick);
    };

    const playOnceWhenVisible = (els) => {
      const list = Array.from(els || []);
      if(!list.length) return;

      if(!('IntersectionObserver' in window)){
        list.forEach(el => run(el, target(el)));
        return;
      }

      const io = new IntersectionObserver((entries) => {
        entries.forEach(ent => {
          if(ent.isIntersecting){
            run(ent.target, target(ent.target));
            io.unobserve(ent.target);
          }
        });
      }, { threshold: 0.25 });

      list.forEach(el => io.observe(el));
    };

    const rerunTo = (el, next, dur = DURATION) => {
      const to = Number(next || 0);
      const from = parse(el.textContent);
      const s = performance.now();

      const tick = (now) => {
        const t = Math.min(1, (now - s) / dur);
        const c = from + (to - from) * ease(t);
        set(el, c);
        if(t < 1) requestAnimationFrame(tick);
      };

      requestAnimationFrame(tick);
    };

    return { playOnceWhenVisible, rerunTo };
  })();

  CountFX.playOnceWhenVisible(document.querySelectorAll('.js-count'));

  const STATS_URL = @json(route('superadmin.dashboard.data'));

  const fetchStats = async ({ tahun = '', unit_id = '' } = {}) => {
    const url = new URL(STATS_URL, window.location.origin);

    if(tahun) url.searchParams.set('tahun', tahun);
    if(unit_id) url.searchParams.set('unit_id', unit_id);

    const res = await fetch(url.toString(), {
      headers: {
        'Accept': 'application/json'
      }
    });

    if(!res.ok) throw new Error('Gagal memuat data');

    return await res.json();
  };

  const closePopovers = () => {
    document.querySelectorAll('.u-popover.is-open').forEach(p => {
      p.classList.remove('is-open');
      p.setAttribute('aria-hidden','true');
    });
  };

  document.addEventListener('click', e => {
    if(!e.target.closest('.u-chart-head')) closePopovers();
  });

  document.querySelectorAll('.u-info-btn[data-pop]').forEach(btn => {
    btn.addEventListener('click', e => {
      e.preventDefault();
      e.stopPropagation();

      const pop = document.getElementById(btn.dataset.pop);
      if(!pop) return;

      const open = pop.classList.contains('is-open');

      closePopovers();

      if(!open){
        pop.classList.add('is-open');
        pop.setAttribute('aria-hidden','false');
      }
    });
  });

  const openModal = id => {
    const m = document.getElementById(id);
    if(!m) return;

    m.classList.add('is-open');
    m.setAttribute('aria-hidden','false');
    document.body.classList.add('is-modal-open');
  };

  const closeModal = id => {
    const m = document.getElementById(id);
    if(!m) return;

    m.classList.remove('is-open');
    m.setAttribute('aria-hidden','true');
    document.body.classList.remove('is-modal-open');
  };

  document.querySelectorAll('[data-modal]').forEach(btn => {
    btn.addEventListener('click', e => {
      e.preventDefault();
      e.stopPropagation();

      closePopovers();
      openModal(btn.dataset.modal);
    });
  });

  document.querySelectorAll('[data-close]').forEach(el => {
    el.addEventListener('click', e => {
      e.preventDefault();
      closeModal(el.dataset.close);
    });
  });

  document.addEventListener('keydown', e => {
    if(e.key === 'Escape'){
      const open = document.querySelector('.u-modal.is-open');
      open ? closeModal(open.id) : closePopovers();
    }
  });

  const splitLabel = v => {
    if(Array.isArray(v)) return v;

    const s = String(v ?? '');

    if(s.includes('\n')) return s.split('\n');

    const p = s.trim().split(/\s+/);

    return p.length === 2 ? p : s;
  };

  const fmtInt = n => Number(n || 0).toLocaleString('id-ID');

  const buildDetail = (chart, opts = {}) => {
    if(!chart) return { meta:'—', rows:[] };

    const labels = chart.data.labels || [];
    const data = chart.data.datasets?.[0]?.data || [];
    const colors = chart.data.datasets?.[0]?.backgroundColor || [];
    const total = data.reduce((a,b) => a + (Number(b) || 0), 0) || 0;

    const rows = labels.map((name, i) => {
      const val = Number(data[i] || 0);
      const pct = total > 0 ? Math.round(val / total * 100) : 0;

      return {
        name: String(name),
        val: `${fmtInt(val)} (${pct}%)`,
        color: Array.isArray(colors) ? colors[i] || '#184f61' : colors || '#184f61'
      };
    });

    rows.sort((a,b) => {
      const av = parseInt((a.val || '0').replace(/[^0-9]/g,''), 10) || 0;
      const bv = parseInt((b.val || '0').replace(/[^0-9]/g,''), 10) || 0;
      return bv - av;
    });

    return {
      meta: [
        `Tahun: ${opts.tahun || 'Semua'}`,
        `Unit: ${opts.unitName || 'Semua'}`,
        `Total: ${fmtInt(total)}`
      ].join(' • '),
      rows
    };
  };

  const renderDetail = (detail, metaEl, listEl) => {
    if(metaEl) metaEl.textContent = detail.meta || '—';
    if(!listEl) return;

    listEl.innerHTML = '';

    detail.rows.forEach(r => {
      const row = document.createElement('div');
      row.className = 'u-popover-row';

      const left = document.createElement('div');
      left.className = 'u-popover-left';

      const dot = document.createElement('span');
      dot.className = 'u-dot';
      dot.style.background = r.color;

      const nm = document.createElement('div');
      nm.className = 'u-popover-name';
      nm.textContent = r.name;

      const vl = document.createElement('div');
      vl.className = 'u-popover-val';
      vl.textContent = r.val;

      left.append(dot, nm);
      row.append(left, vl);
      listEl.append(row);
    });
  };

  const donutColors = ['#0B4A5E','#111827','#F6C100','#D6A357'];
  let donutChart = null;
  let barChart = null;

  const donutCtx = document.getElementById('donutStatus');

  if(donutCtx){
    donutChart = new Chart(donutCtx, {
      type: 'doughnut',
      data: {
        labels: @json($statusLabels),
        datasets: [{
          data: @json($statusValues),
          backgroundColor: donutColors,
          borderWidth: 0
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        animation: {
          duration: 1800,
          easing: 'easeOutQuart'
        },
        layout: {
          padding: {
            right: 70
          }
        },
        plugins: {
          legend: {
            position: 'right',
            labels: {
              boxWidth: 10,
              boxHeight: 10,
              padding: 12,
              font: {
                family: 'Nunito',
                weight: '400',
                size: 14
              }
            }
          },
          tooltip: {
            enabled: true
          }
        },
        cutout: '55%'
      }
    });
  }

  const barCtx = document.getElementById('barStatus');

  if(barCtx){
    barChart = new Chart(barCtx, {
      type: 'bar',
      data: {
        labels: @json($barLabels),
        datasets: [{
          label: 'Semua',
          data: @json($barValues),
          backgroundColor: '#F6C100',
          borderWidth: 0,
          borderRadius: 6
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        animation: {
          duration: 1800,
          easing: 'easeOutQuart'
        },
        plugins: {
          legend: {
            position: 'bottom',
            labels: {
              font: {
                family: 'Nunito',
                weight: '400',
                size: 14
              }
            }
          },
          tooltip: {
            enabled: true
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              stepSize: 20,
              precision: 0,
              font: {
                family: 'Nunito',
                weight: '400',
                size: 14
              }
            }
          },
          x: {
            ticks: {
              maxRotation: 0,
              minRotation: 0,
              autoSkip: false,
              padding: 6,
              font: {
                family: 'Nunito',
                weight: '400',
                size: 11
              },
              callback: function(v){
                return splitLabel(this.getLabelForValue(v));
              }
            },
            grid: {
              display: false
            }
          }
        }
      }
    });
  }

  const getSelText = el => el?.options[el.selectedIndex]?.textContent || '';

  const metaDonut = document.getElementById('metaDonut');
  const listDonut = document.getElementById('listDonut');
  const metaBar = document.getElementById('metaBar');
  const listBar = document.getElementById('listBar');

  const refreshDonutDetail = () => renderDetail(
    buildDetail(donutChart, {
      tahun: document.getElementById('fTahun1')?.value || '',
      unitName: getSelText(document.getElementById('fUnit1'))
    }),
    metaDonut,
    listDonut
  );

  const refreshBarDetail = () => renderDetail(
    buildDetail(barChart, {
      tahun: document.getElementById('fTahun2')?.value || '',
      unitName: getSelText(document.getElementById('fUnit2'))
    }),
    metaBar,
    listBar
  );

  refreshDonutDetail();
  refreshBarDetail();

  const applyDonutFilter = async () => {
    if(!donutChart) return;

    try{
      const s = await fetchStats({
        tahun: document.getElementById('fTahun1')?.value || '',
        unit_id: document.getElementById('fUnit1')?.value || ''
      });

      donutChart.data.labels = s.status?.labels || donutChart.data.labels;
      donutChart.data.datasets[0].data = s.status?.values || donutChart.data.datasets[0].data;
      donutChart.update();

      refreshDonutDetail();
    }catch(e){}
  };

  const applyBarFilter = async () => {
    if(!barChart) return;

    const tahun = document.getElementById('fTahun2')?.value || '';

    try{
      const s = await fetchStats({
        tahun,
        unit_id: document.getElementById('fUnit2')?.value || ''
      });

      barChart.data.labels = s.metode?.labels || barChart.data.labels;
      barChart.data.datasets[0].data = s.metode?.values || barChart.data.datasets[0].data;
      barChart.data.datasets[0].label = tahun || 'Semua';
      barChart.update();

      refreshBarDetail();
    }catch(e){}
  };

  ['fTahun1','fUnit1'].forEach(id => {
    document.getElementById(id)?.addEventListener('change', applyDonutFilter);
  });

  ['fTahun2','fUnit2'].forEach(id => {
    document.getElementById(id)?.addEventListener('change', applyBarFilter);
  });

  const elPaket = document.getElementById('valPaket');
  const elNilai = document.getElementById('valNilai');

  const basePaket = Number(elPaket?.dataset.count || 0);
  const baseNilai = Number(elNilai?.dataset.count || 0);

  const setIfDiff = (el, n) => {
    if(!el) return false;

    const cur = Number(String(el.dataset.count || '').replace(/[^\d.-]/g,'') || 0);

    if(cur === Number(n || 0)) return false;

    el.dataset.count = String(n || 0);

    return true;
  };

  const applyPaketFilter = async () => {
    const tahun = document.getElementById('fTahunPaket')?.value || '';
    const unit_id = document.getElementById('fUnitPaket')?.value || '';

    if(!elPaket) return;

    if(!tahun && !unit_id){
      if(setIfDiff(elPaket, basePaket)) CountFX.rerunTo(elPaket, basePaket);
      return;
    }

    try{
      const s = await fetchStats({ tahun, unit_id });
      const n = Number(s?.paket?.count || 0);

      if(setIfDiff(elPaket, n)) CountFX.rerunTo(elPaket, n);
    }catch(e){}
  };

  const applyNilaiFilter = async () => {
    const tahun = document.getElementById('fTahunNilai')?.value || '';
    const unit_id = document.getElementById('fUnitNilai')?.value || '';

    if(!elNilai) return;

    if(!tahun && !unit_id){
      if(setIfDiff(elNilai, baseNilai)) CountFX.rerunTo(elNilai, baseNilai);
      return;
    }

    try{
      const s = await fetchStats({ tahun, unit_id });
      const n = Number(s?.nilai?.sum || 0);

      if(setIfDiff(elNilai, n)) CountFX.rerunTo(elNilai, n);
    }
    catch(e){
      if(setIfDiff(elNilai, baseNilai)) CountFX.rerunTo(elNilai, baseNilai);
    }
  };

  ['fTahunPaket','fUnitPaket'].forEach(id => {
    document.getElementById(id)?.addEventListener('change', applyPaketFilter);
  });

  ['fTahunNilai','fUnitNilai'].forEach(id => {
    document.getElementById(id)?.addEventListener('change', applyNilaiFilter);
  });

  const popObs = new MutationObserver(() => {
    if(document.getElementById('popDonut')?.classList.contains('is-open')) refreshDonutDetail();
    if(document.getElementById('popBar')?.classList.contains('is-open')) refreshBarDetail();
  });

  ['popDonut','popBar'].forEach(id => {
    const el = document.getElementById(id);
    if(el) popObs.observe(el, {
      attributes: true,
      attributeFilter: ['class']
    });
  });
});
</script>

@include('Partials.chatbot')

</body>
</html>