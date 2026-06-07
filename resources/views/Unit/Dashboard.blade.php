{{-- resources/views/unit/dashboard.blade.php --}}
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard Admin Unit - SIAPABAJA</title>

  {{-- Font Nunito (HANYA 400 & 600 biar tidak ada bold) --}}
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600&display=swap" rel="stylesheet">

  {{-- Bootstrap Icons --}}
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

  <link rel="stylesheet" href="{{ asset('css/Unit.css?v=2') }}">

  {{-- Chart.js (untuk donut & bar) --}}
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</head>

<body class="dash-body">
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
  <div class="sidebar-drawer-backdrop" id="sidebarBackdrop"></div>

  <div class="dash-wrap">
    {{-- SIDEBAR (konsisten dengan tambah pengadaan) --}}
    <aside class="dash-sidebar">
      <div class="dash-brand">
        <div class="dash-logo">
          <img src="{{ asset('image/Logo_Unsoed.png') }}" alt="Logo Unsoed">
        </div>

        <div class="dash-text">
          <div class="dash-app">SIAPABAJA</div>
          <div class="dash-role">PIC (Unit)</div>
        </div>
      </div>

      <div class="dash-unitbox">
        <div class="dash-unit-label">Unit Kerja :</div>
        <div class="dash-unit-name">{{ $unitName }}</div>
      </div>

      <nav class="dash-nav">
        <a class="dash-link active" href="{{ url('/unit/dashboard') }}">
          <span class="ic"><i class="bi bi-grid-fill"></i></span>
          Dashboard
        </a>

        <a class="dash-link" href="{{ url('/unit/arsip') }}">
          <span class="ic"><i class="bi bi-archive"></i></span>
          Arsip PBJ
        </a>

        <a class="dash-link" href="{{ url('/unit/pengadaan/tambah') }}">
          <span class="ic"><i class="bi bi-plus-square"></i></span>
          Tambah Pengadaan
        </a>

        <a class="dash-link" href="{{ route('unit.kelola.akun') }}">
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
        <h1>Dashboard PIC Unit</h1>
        <p>Kelola arsip pengadaan barang dan jasa {{ $unitName }}</p>
      </header>

      {{-- SUMMARY CARDS --}}
      <section class="u-sum">
        <div class="u-sum-row u-sum-row--3">
          {{-- 1 --}}
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

          {{-- 2 --}}
          <div class="u-card">
            <div class="u-bar u-bar--yellow"></div>
            <div class="u-top">
              <div>
                <div class="u-label">{{ $summary[1]['label'] }}</div>
                <div class="u-value u-value--yellow js-count" data-count="{{ (int) $summary[1]['value'] }}">0</div>
              </div>
              <div class="u-ic u-ic--yellow"><i class="bi {{ $summary[1]['icon'] }}"></i></div>
            </div>
          </div>

          {{-- 3 --}}
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
        </div>

        <div class="u-sum-row u-sum-row--2">
          {{-- 4 --}}
          <div class="u-card">
            <div class="u-bar u-bar--navy"></div>
            <div class="u-top">
              <div>
                <div class="u-label">{{ $summary[3]['label'] }}</div>
                <div class="u-value u-value--navy js-count" id="valPaket" data-count="{{ (int) $summary[3]['value'] }}">0</div>
                <div class="u-sub">{{ $summary[3]['sub'] }}</div>
              </div>
              <div class="u-ic"><i class="bi {{ $summary[3]['icon'] }}"></i></div>
            </div>

            <div class="u-card-filter">
              <div class="u-mini-select">
                <select id="fTahunPaket">
                  {{-- ✅ DEFAULT: Semua Tahun --}}
                  <option value="" selected>Tahun</option>
                  @foreach($tahunOptions as $t)
                  <option value="{{ $t }}">{{ $t }}</option>
                  @endforeach
                </select>
                <i class="bi bi-chevron-down"></i>
              </div>
            </div>
          </div>

          {{-- 5 --}}
          <div class="u-card">
            <div class="u-bar u-bar--yellow"></div>
            <div class="u-top">
              <div>
                <div class="u-label">{{ $summary[4]['label'] }}</div>
                <div class="u-money js-count" id="valNilai" data-count="{{ (int) preg_replace('/[^0-9]/','', $summary[4]['value']) }}">Rp 0</div>
                <div class="u-sub">{{ $summary[4]['sub'] }}</div>
              </div>
              <div class="u-ic u-ic--yellow"><i class="bi {{ $summary[4]['icon'] }}"></i></div>
            </div>

            <div class="u-card-filter">
              <div class="u-mini-select">
                <select id="fTahunNilai">
                  {{-- ✅ DEFAULT: Semua Tahun --}}
                  <option value="" selected> Tahun</option>
                  @foreach($tahunOptions as $t)
                  <option value="{{ $t }}">{{ $t }}</option>
                  @endforeach
                </select>
                <i class="bi bi-chevron-down"></i>
              </div>
            </div>
          </div>
        </div>
      </section>

      {{-- STATISTIKA --}}
      <section class="u-charts">
        {{-- Donut --}}
        <div class="u-chart-card">
          <div class="u-chart-head">
            <div class="u-chart-title">Status Arsip</div>

            <button class="u-info-btn" type="button" data-pop="popDonut" aria-label="Lihat detail Status Arsip">
              <i class="bi bi-info"></i>
            </button>

            <div id="popDonut" class="u-popover" role="dialog" aria-hidden="true">
              <div class="u-popover-title">Detail Status Arsip</div>
              <div class="u-popover-meta">
                <span id="metaDonut">—</span>
              </div>
              <div class="u-popover-list" id="listDonut"></div>
              <div class="u-popover-foot">Klik di luar untuk menutup</div>
            </div>
          </div>

          <div class="u-chart-divider"></div>

          <div class="u-chart-filters u-chart-filters--one">
            <div class="u-select u-select--full">
              <select id="fTahun1">
                {{-- ✅ UBAH: Tahun -> Semua Tahun --}}
                <option value="">Semua Tahun</option>
                @foreach($tahunOptions as $t)
                <option value="{{ $t }}">{{ $t }}</option>
                @endforeach
              </select>
              <i class="bi bi-chevron-down"></i>
            </div>
          </div>

          <div class="u-canvas-wrap">
            <canvas id="donutStatus"></canvas>
          </div>
        </div>

        {{-- Bar --}}
        <div class="u-chart-card">
          <div class="u-chart-head">
            <div class="u-chart-title">Metode Pengadaan</div>

            <button class="u-info-btn" type="button" data-pop="popBar" aria-label="Lihat detail Metode Pengadaan">
              <i class="bi bi-info"></i>
            </button>

            <div id="popBar" class="u-popover" role="dialog" aria-hidden="true">
              <div class="u-popover-title">Detail Metode Pengadaan</div>
              <div class="u-popover-meta">
                <span id="metaBar">—</span>
              </div>
              <div class="u-popover-list" id="listBar"></div>
              <div class="u-popover-foot">Klik di luar untuk menutup</div>
            </div>
          </div>

          <div class="u-chart-divider"></div>

          <div class="u-chart-filters u-chart-filters--one">
            <div class="u-select u-select--full">
              <select id="fTahun2">
                {{-- ✅ UBAH: Tahun -> Semua Tahun --}}
                <option value="">Semua Tahun</option>
                @foreach($tahunOptions as $t)
                <option value="{{ $t }}">{{ $t }}</option>
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

  <style>
    /* =========================
     ✅ FIX FINAL SCROLL (Unit/Dashboard)
     - Samakan dengan global Unit.css:
       scroll hanya di .dash-main
       sidebar selalu nempel
  ========================= */
    html,
    body {
      height: 100% !important;
      overflow: hidden !important;
      overflow-x: hidden !important;
    }

    .dash-wrap {
      height: 100vh !important;
      min-height: 100vh !important;
      overflow: hidden !important;
    }

    .dash-main {
      height: 100vh !important;
      overflow-y: auto !important;
      overflow-x: hidden !important;
    }

    .dash-sidebar {
      position: sticky !important;
      top: 0 !important;
      height: 100vh !important;
      overflow: hidden !important;
      display: flex;
      flex-direction: column;
    }

    /* ====== (CSS kamu: TIDAK DIUBAH selain 3 baris pengunci di atas) ====== */
    .dash-body {
      font-size: 18px;
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
      font-size: 34px;
      font-weight: 600;
      color: #184f61;
    }

    .dash-header p {
      margin: 10;
      font-size: 15px;
      color: #184f61;
      opacity: 0.85;
    }

    .u-label,
    .u-value,
    .u-money,
    .u-sub,
    .u-chart-title,
    .u-select select {
      font-weight: 400 !important;
    }

    .u-card {
      position: relative;
    }

    .u-card-filter {
      position: absolute;
      right: 12px;
      bottom: 10px;
    }

    .u-mini-select {
      position: relative;
    }

    .u-mini-select select {
      border: 1px solid #e2e8f0;
      border-radius: 10px;
      padding: 8px 32px 8px 12px;
      font-family: inherit;
      font-size: 14px;
      font-weight: 400 !important;
      background: #fff;
      outline: none;
      appearance: none;
      cursor: pointer;
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

    .u-sum {
      display: grid;
      gap: 16px;
    }

    .u-sum-row {
      display: grid;
      gap: 16px;
    }

    .u-sum-row--3 {
      grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .u-sum-row--2 {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .u-card {
      background: #fff;
      border: 1px solid #e6eef2;
      border-radius: 14px;
      box-shadow: 0 10px 20px rgba(2, 8, 23, .04);
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

    .u-bar--navy {
      background: #184f61;
    }

    .u-bar--yellow {
      background: #f6c100;
    }

    .u-bar--gray {
      background: #0f172a;
      opacity: .75;
    }

    .u-top {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: 14px;
    }

    .u-label {
      font-size: 16px;
      color: #64748b;
      margin-bottom: 6px;
    }

    .u-value {
      font-size: 34px;
      line-height: 1;
    }

    .u-value--navy {
      color: #184f61;
    }

    .u-value--yellow {
      color: #f6c100;
    }

    .u-value--gray {
      color: #0f172a;
      opacity: .85;
    }

    .u-money {
      font-size: 34px;
      line-height: 1.05;
      color: #c98800;
    }

    .u-sub {
      margin-top: 8px;
      font-size: 14px;
      color: #94a3b8;
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

    .u-ic--yellow {
      color: #c98800;
      background: #fff6cc;
    }

    .u-ic--gray {
      color: #0f172a;
      background: #eef2f7;
    }

    .u-charts {
      margin-top: 18px;
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 18px;
    }

    .u-chart-card {
      background: #fff;
      border: 1px solid #e6eef2;
      border-radius: 18px;
      padding: 14px 16px 16px;
      box-shadow: 0 10px 20px rgba(2, 8, 23, .04);
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
      text-align: center;
      font-size: 20px;
      color: #0f172a;
      margin-top: 2px;
    }

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
      line-height: 1;
      padding: 0;
      color: #184f61;
      box-shadow: 0 10px 20px rgba(2, 8, 23, .06);
    }

    .u-info-btn i {
      font-size: 14px;
      opacity: .9;
      pointer-events: none;
      -webkit-text-stroke: .4px #184f61;
    }

    .u-info-btn:hover {
      border-color: #143f4d;
      transform: translateY(-.5px);
    }

    .u-popover {
      position: absolute;
      right: 0;
      top: 30px;
      width: 380px;
      background: #fff;
      border: 1px solid #e6eef2;
      border-radius: 12px;
      box-shadow: 0 18px 30px rgba(2, 8, 23, .12);
      padding: 10px 10px 8px;
      z-index: 50;
      display: none;
    }

    .u-popover.is-open {
      display: block;
    }

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
      font-size: 14px;
      color: #0f172a;
      font-weight: 400 !important;
      margin-bottom: 4px;
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
      padding-right: 2px;
    }

    .u-popover-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 10px;
      border: 1px solid #eef2f7;
      border-radius: 10px;
      padding: 8px 8px;
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

    .u-chart-filters--one {
      grid-template-columns: 1fr !important;
    }

    .u-select--full {
      width: 100%;
    }

    .u-select--full select {
      width: 100%;
    }

    .u-select {
      position: relative;
    }

    .u-select select {
      width: 100%;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      padding: 10px 38px 10px 12px;
      font-size: 16px;
      outline: none;
      background: #fff;
      appearance: none;
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

    .u-sum {
      gap: 14px;
    }

    .u-sum-row {
      gap: 14px;
    }

    .u-card {
      padding: 14px 14px 12px;
    }

    .u-label {
      margin-bottom: 4px;
    }

    .u-value,
    .u-money {
      font-size: 32px;
    }

    .u-sub {
      margin-top: 6px;
    }

    .u-charts {
      margin-top: 14px;
      gap: 14px;
    }

    .u-chart-card {
      padding: 12px 14px 14px;
    }

    .u-chart-divider {
      margin: 8px 0 10px;
    }

    .u-chart-filters {
      margin-bottom: 10px;
    }

    .u-canvas-wrap {
      height: 230px;
    }

    .u-canvas-wrap canvas {
      max-height: 230px !important;
    }

    @media(max-width:1100px) {
      .u-sum-row--3 {
        grid-template-columns: 1fr;
      }

      .u-sum-row--2 {
        grid-template-columns: 1fr;
      }

      .u-charts {
        grid-template-columns: 1fr;
      }

      .u-money,
      .u-value {
        font-size: 28px;
      }

      .u-card-filter {
        right: 10px;
        bottom: 10px;
      }

      .u-canvas-wrap {
        height: 220px;
      }

      .u-canvas-wrap canvas {
        max-height: 220px !important;
      }
    }

    /* =========================================================
   MOBILE TOPBAR & DRAWER
========================================================= */
    .mob-topbar {
      display: none;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      padding: 14px 16px;
      background: #184f61;
      border-bottom: 1px solid #e6eef2;
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
      object-fit: contain;
    }

    .mob-logo-txt {
      font-size: 17px;
      font-weight: 700;
      color: #f4c542;
    }

    .mob-ham {
      width: 40px;
      height: 40px;
      border: 1px solid #e6eef2;
      border-radius: 10px;
      background: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      font-size: 18px;
      color: #184f61;
    }

    .sidebar-drawer-backdrop {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(15, 23, 42, .45);
      backdrop-filter: blur(4px);
      z-index: 300;
    }

    .sidebar-drawer-backdrop.is-open {
      display: block;
    }

    /* =========================================================
   SPLIT SCREEN <= 1024px
========================================================= */
    @media (max-width: 1024px) {

      html,
      body {
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

      .dash-sidebar {
        position: fixed !important;
        top: 0 !important;
        left: -280px !important;
        height: 100vh !important;
        width: 260px !important;
        z-index: 400 !important;
        transition: left .25s ease !important;
        overflow-y: auto !important;
        overflow-x: hidden !important;
      }

      .dash-sidebar.drawer-open {
        left: 0 !important;
        box-shadow: 4px 0 24px rgba(2, 8, 23, .18) !important;
      }

      .mob-topbar {
        display: flex;
      }

      .u-sum-row--3 {
        grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
      }

      .u-sum-row--2 {
        grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
      }

      .u-charts {
        grid-template-columns: 1fr !important;
      }
    }

    @media (min-width: 1025px) {
      .mob-topbar {
        display: none !important;
      }

      .sidebar-drawer-backdrop {
        display: none !important;
      }
    }

    /* =========================================================
   MOBILE <= 768px
========================================================= */
    @media (max-width: 768px) {

      .dash-main {
        padding: 12px !important;
        gap: 12px !important;
      }

      .dash-header h1 {
        font-size: 20px !important;
      }

      .dash-header p {
        font-size: 13px !important;
      }

      .u-sum-row--3 {
        grid-template-columns: 1fr 1fr !important;
      }

      .u-sum-row--2 {
        grid-template-columns: 1fr !important;
      }

      .u-value {
        font-size: 26px !important;
      }

      .u-money {
        font-size: 22px !important;
      }

      .u-label {
        font-size: 13px !important;
      }

      .u-chart-filters {
        grid-template-columns: 1fr !important;
        gap: 8px !important;
      }

      .u-select select {
        font-size: 13px !important;
        padding: 8px 28px 8px 10px !important;
      }

      .u-canvas-wrap {
        height: 200px !important;
      }

      .u-canvas-wrap canvas {
        max-height: 200px !important;
      }

      .u-chart-title {
        font-size: 16px !important;
      }

      .u-popover {
        width: calc(100vw - 48px) !important;
        right: -8px !important;
      }

      .u-card-filter {
        position: static !important;
        margin-top: 8px !important;
        display: flex !important;
        gap: 8px !important;
        align-items: center !important;
        width: 100% !important;
      }

      .u-mini-select {
        position: relative !important;
        display: inline-flex !important;
        align-items: center !important;
      }

      .u-mini-select select {
        width: 120px !important;
        max-width: 120px !important;
        padding-right: 28px !important;
      }

      .u-mini-select i {
        position: absolute !important;
        right: 10px !important;
        top: 50% !important;
        transform: translateY(-50%) !important;
        pointer-events: none !important;
      }
    }

    /* =========================================================
   VERY SMALL <= 400px
========================================================= */
    @media (max-width: 400px) {
      .u-sum-row--3 {
        grid-template-columns: 1fr !important;
      }

      .u-value {
        font-size: 24px !important;
      }

      .u-money {
        font-size: 20px !important;
      }
    }
  </style>


  <script>
    document.addEventListener('DOMContentLoaded', function() {

      // ✅ FIX: route yang benar sesuai web.php kamu
      const STATS_URL = @json(route('unit.dashboard.data'));

      // ✅ LOCK ke unit login (agar fetch data dashboard selalu per-unit)
      const lockedUnitName = @json($unitName);

      // =========================
      // ✅ COUNT-UP ANIMATION (tetap)
      // =========================
      const CountFX = (() => {
        const DEFAULT_DURATION = 1200;
        const easeOutCubic = (t) => 1 - Math.pow(1 - t, 3);

        const parseNumber = (s) => {
          const raw = String(s ?? '').replace(/[^\d.-]/g, '');
          const n = Number(raw);
          return Number.isFinite(n) ? n : 0;
        };

        const formatID = (n) => Math.round(n).toLocaleString('id-ID');

        const formatRupiah = (n) => {
          const x = Math.max(0, Math.round(Number(n || 0)));
          const parts = x.toString().split('');
          let out = '';
          for (let i = 0; i < parts.length; i++) {
            const idx = parts.length - i;
            out += parts[i];
            if (idx > 1 && idx % 3 === 1) out += '.';
          }
          return 'Rp ' + out;
        };

        const getTarget = (el) => {
          const dc = el.getAttribute('data-count');
          if (dc !== null && dc !== '') return parseNumber(dc);
          return parseNumber(el.textContent);
        };

        const isMoney = (el) => {
          return el.classList.contains('u-money') || /rp/i.test(String(el.textContent)) || /rp/i.test(String(el.getAttribute('data-format') || ''));
        };

        const setText = (el, val) => {
          el.textContent = isMoney(el) ? formatRupiah(val) : formatID(val);
        };

        const animate = (el, to, duration = DEFAULT_DURATION) => {
          if (el.dataset.counted === '1') return;
          el.dataset.counted = '1';

          const from = 0;
          const start = performance.now();

          setText(el, 0);

          const tick = (now) => {
            const t = Math.min(1, (now - start) / duration);
            const p = easeOutCubic(t);
            const cur = from + (to - from) * p;
            setText(el, cur);
            if (t < 1) requestAnimationFrame(tick);
          };
          requestAnimationFrame(tick);
        };

        const playOnceWhenVisible = (els) => {
          const list = Array.from(els || []);
          if (!list.length) return;

          if (!('IntersectionObserver' in window)) {
            list.forEach(el => animate(el, getTarget(el)));
            return;
          }

          const io = new IntersectionObserver((entries) => {
            entries.forEach(ent => {
              if (ent.isIntersecting) {
                const el = ent.target;
                animate(el, getTarget(el));
                io.unobserve(el);
              }
            });
          }, {
            threshold: 0.25
          });

          list.forEach(el => io.observe(el));
        };

        const rerunTo = (el, nextValue, duration = DEFAULT_DURATION) => {
          const to = Number(nextValue || 0);
          const from = parseNumber(el.textContent);

          const start = performance.now();
          const tick = (now) => {
            const t = Math.min(1, (now - start) / duration);
            const p = easeOutCubic(t);
            const cur = from + (to - from) * p;
            el.textContent = formatID(cur);
            if (t < 1) requestAnimationFrame(tick);
          };
          requestAnimationFrame(tick);
        };

        const rerunMoneyTo = (el, nextValue, duration = DEFAULT_DURATION) => {
          const to = Number(nextValue || 0);
          const from = parseNumber(el.textContent);

          const start = performance.now();
          const tick = (now) => {
            const t = Math.min(1, (now - start) / duration);
            const p = easeOutCubic(t);
            const cur = from + (to - from) * p;
            el.textContent = formatRupiah(cur);
            if (t < 1) requestAnimationFrame(tick);
          };
          requestAnimationFrame(tick);
        };

        return {
          playOnceWhenVisible,
          rerunTo,
          rerunMoneyTo
        };
      })();

      CountFX.playOnceWhenVisible(document.querySelectorAll('.js-count'));
      /* -------- Sidebar Drawer -------- */
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

      // ✅ warna donut sesuai gambar
      const donutColors = ['#0B4A5E', '#111827', '#F6C100', '#D6A357'];

      const splitLabel = (value) => {
        if (Array.isArray(value)) return value;
        const s = String(value ?? '');
        if (s.includes('\n')) return s.split('\n');
        const parts = s.trim().split(/\s+/);
        if (parts.length === 2) return [parts[0], parts[1]];
        return s;
      };

      // =========================
      // ✅ POPOVER LOGIC (tetap)
      // =========================
      const closeAllPopovers = () => {
        document.querySelectorAll('.u-popover.is-open').forEach(p => {
          p.classList.remove('is-open');
          p.setAttribute('aria-hidden', 'true');
        });
      };

      document.addEventListener('click', function(e) {
        const isInside = e.target.closest('.u-chart-head');
        if (!isInside) closeAllPopovers();
      });

      document.querySelectorAll('.u-info-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
          e.preventDefault();
          e.stopPropagation();
          const id = btn.getAttribute('data-pop');
          const pop = document.getElementById(id);
          if (!pop) return;

          const isOpen = pop.classList.contains('is-open');
          closeAllPopovers();
          if (!isOpen) {
            pop.classList.add('is-open');
            pop.setAttribute('aria-hidden', 'false');
          }
        });
      });

      const fmtInt = (n) => {
        const x = Number(n || 0);
        return (isFinite(x) ? Math.round(x) : 0).toString();
      };

      const buildDetail = (chart, opts = {}) => {
        if (!chart) return {
          meta: '—',
          rows: []
        };
        const labels = chart.data.labels || [];
        const data = (chart.data.datasets && chart.data.datasets[0] && chart.data.datasets[0].data) ? chart.data.datasets[0].data : [];
        const colors = (chart.data.datasets && chart.data.datasets[0] && chart.data.datasets[0].backgroundColor) ? chart.data.datasets[0].backgroundColor : [];
        const total = data.reduce((a, b) => a + (Number(b) || 0), 0) || 0;

        const metaParts = [];
        if (opts.tahun !== undefined && opts.tahun !== null) metaParts.push(`Tahun: ${opts.tahun || 'Semua Tahun'}`);
        metaParts.push(`Total: ${fmtInt(total)}`);

        const rows = labels.map((name, i) => {
          const val = Number(data[i] || 0);
          const pct = total > 0 ? Math.round((val / total) * 100) : 0;
          return {
            name: String(name),
            val: `${fmtInt(val)} (${pct}%)`,
            color: Array.isArray(colors) ? (colors[i] || '#184f61') : (colors || '#184f61')
          };
        });

        rows.sort((a, b) => {
          const av = parseInt((a.val || '0').replace(/[^0-9]/g, ''), 10) || 0;
          const bv = parseInt((b.val || '0').replace(/[^0-9]/g, ''), 10) || 0;
          return bv - av;
        });

        return {
          meta: metaParts.join(' • '),
          rows
        };
      };

      const renderDetailTo = (detail, metaEl, listEl) => {
        if (metaEl) metaEl.textContent = detail.meta || '—';
        if (!listEl) return;

        listEl.innerHTML = '';
        (detail.rows || []).forEach(r => {
          const row = document.createElement('div');
          row.className = 'u-popover-row';

          const left = document.createElement('div');
          left.className = 'u-popover-left';

          const dot = document.createElement('span');
          dot.className = 'u-dot';
          dot.style.background = r.color || '#184f61';

          const name = document.createElement('div');
          name.className = 'u-popover-name';
          name.textContent = r.name;

          const val = document.createElement('div');
          val.className = 'u-popover-val';
          val.textContent = r.val;

          left.appendChild(dot);
          left.appendChild(name);
          row.appendChild(left);
          row.appendChild(val);
          listEl.appendChild(row);
        });
      };

      // =========================
      // ✅ FETCH STATS (real DB)
      // =========================
      const cache = {};
      const fetchStats = async (tahun) => {
        const key = `${String(lockedUnitName || '')}__${(tahun === null || tahun === undefined) ? '' : String(tahun)}`;
        if (cache[key]) return cache[key];

        const url = new URL(STATS_URL, window.location.origin);

        // ✅ selalu kirim unit login -> data per-unit
        url.searchParams.set('unit', String(lockedUnitName || ''));

        const tahunKey = (tahun === null || tahun === undefined) ? '' : String(tahun);
        if (tahunKey !== '') url.searchParams.set('tahun', tahunKey);

        const res = await fetch(url.toString(), {
          headers: {
            'Accept': 'application/json'
          }
        });
        if (!res.ok) throw new Error('Gagal memuat data dashboard');
        const json = await res.json();

        cache[key] = json;
        return json;
      };

      // =========================
      // CHART INSTANCES (init dari controller)
      // =========================
      let donutChart = null;
      let barChart = null;

      const donutCtx = document.getElementById('donutStatus');
      if (donutCtx) {
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
      if (barCtx) {
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
                  stepSize: 1,
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
                  callback: function(value) {
                    const raw = this.getLabelForValue(value);
                    return splitLabel(raw);
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

      // =========================
      // ✅ DETAIL POPUP CONTENT
      // =========================
      const metaDonut = document.getElementById('metaDonut');
      const listDonut = document.getElementById('listDonut');
      const metaBar = document.getElementById('metaBar');
      const listBar = document.getElementById('listBar');

      const refreshDonutDetail = () => {
        const tahun = (document.getElementById('fTahun1')?.value || '');
        const detail = buildDetail(donutChart, {
          tahun
        });
        renderDetailTo(detail, metaDonut, listDonut);
      };

      const refreshBarDetail = () => {
        const tahun = (document.getElementById('fTahun2')?.value || '');
        const detail = buildDetail(barChart, {
          tahun
        });
        renderDetailTo(detail, metaBar, listBar);
      };

      refreshDonutDetail();
      refreshBarDetail();

      // =========================
      // ✅ FILTER HANDLERS (REAL)
      // =========================
      const fTahun1 = document.getElementById('fTahun1');
      const fTahun2 = document.getElementById('fTahun2');

      const fPaket = document.getElementById('fTahunPaket');
      const fNilai = document.getElementById('fTahunNilai');
      const elPaket = document.getElementById('valPaket');
      const elNilai = document.getElementById('valNilai');

      const applyPaketNilai = async (tahun) => {
        const stats = await fetchStats(tahun);
        if (elPaket) {
          elPaket.setAttribute('data-count', String(stats.paket.count || 0));
          CountFX.rerunTo(elPaket, stats.paket.count || 0);
        }
        if (elNilai) {
          const n = Number(stats.nilai.sum || 0);
          elNilai.setAttribute('data-count', String(n));
          CountFX.rerunMoneyTo(elNilai, n);
        }
      };

      const applyDonut = async () => {
        if (!donutChart) return;
        const tahun = (fTahun1?.value || '');
        const stats = await fetchStats(tahun);

        donutChart.data.labels = stats.status.labels;
        donutChart.data.datasets[0].data = stats.status.values;
        donutChart.update();

        refreshDonutDetail();
      };

      const applyBar = async () => {
        if (!barChart) return;
        const tahun = (fTahun2?.value || '');
        const stats = await fetchStats(tahun);

        barChart.data.labels = stats.metode.labels;
        barChart.data.datasets[0].data = stats.metode.values;
        barChart.data.datasets[0].label = tahun ? String(tahun) : 'Semua';
        barChart.update();

        refreshBarDetail();
      };

      if (fTahun1) fTahun1.addEventListener('change', () => applyDonut().catch(console.error));
      if (fTahun2) fTahun2.addEventListener('change', () => applyBar().catch(console.error));

      if (fPaket) fPaket.addEventListener('change', () => applyPaketNilai(fPaket.value).catch(console.error));
      if (fNilai) fNilai.addEventListener('change', () => applyPaketNilai(fNilai.value).catch(console.error));

      // ✅ render awal: default "Semua Tahun"
      if (fPaket) applyPaketNilai(fPaket.value).catch(console.error);

      // =========================
      // ✅ observer untuk refresh detail saat popover dibuka
      // =========================
      const popDonut = document.getElementById('popDonut');
      const popBar = document.getElementById('popBar');

      const observer = new MutationObserver(() => {
        if (popDonut && popDonut.classList.contains('is-open')) refreshDonutDetail();
        if (popBar && popBar.classList.contains('is-open')) refreshBarDetail();
      });
      if (popDonut) observer.observe(popDonut, {
        attributes: true,
        attributeFilter: ['class']
      });
      if (popBar) observer.observe(popBar, {
        attributes: true,
        attributeFilter: ['class']
      });

    });
  </script>
  <style>
    body.dash-body .dash-main {
      margin-left: 0 !important;
    }
  </style>
  @include('Partials.chatbot')
</body>

</html>