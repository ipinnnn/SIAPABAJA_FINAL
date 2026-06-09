{{-- resources/views/Landing/IndexContent.blade.php --}}

{{-- HERO --}}
<section id="Dashboard" class="hero">
  <div class="container">
    <div class="hero-grid">
      <div>
        <h1>
          Sistem Informasi Arsip<br />
          Pengadaan Barang dan Jasa
          <span class="u">Universitas Jenderal Soedirman</span>
        </h1>

        <p>
          SIAPABAJA merupakan sistem informasi berbasis web yang digunakan untuk mengelola dan mengarsipkan dokumen
          pengadaan barang dan jasa di lingkungan Universitas Jenderal Soedirman.
        </p>

        <a class="btn btn-primary" href="#arsip">Lihat Arsip Terbaru</a>
      </div>

      <div class="hero-illustration">
        <img
          src="{{ asset('image/amico.png') }}"
          alt="Ilustrasi Arsip"
          class="hero-img">
      </div>
    </div>
  </div>
</section>

@php
use Illuminate\Support\Str;

/**
* ✅ Ambil 5 arsip PUBLIK paling update
*/
$arsipPublik = \App\Models\Pengadaan::with('unit')
->where('status_arsip', 'Publik')
->orderByDesc('updated_at')
->limit(5)
->get();

function idDate($dt){
if(!$dt) return '-';
try{
$t = \Carbon\Carbon::parse($dt);
$bulan = [1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
return (int)$t->format('d').' '.$bulan[(int)$t->format('m')].' '.$t->format('Y');
}catch(\Throwable $e){
return '-';
}
}

function rupiah($v){
if($v === null || $v === '') return '-';
if(is_string($v) && Str::contains($v, 'Rp')) return $v;
$n = is_numeric($v) ? (float)$v : (float)preg_replace('/[^\d]/', '', (string)$v);
return 'Rp '.number_format($n, 0, ',', '.');
}

/**
* ✅ Builder dokumen untuk modal
*/
function buildDokumenListForLanding($pengadaan){
if(!$pengadaan) return [];
$attrs = method_exists($pengadaan, 'getAttributes') ? $pengadaan->getAttributes() : (array)$pengadaan;

$out = [];
foreach($attrs as $field => $rawValue){
$lk = strtolower((string)$field);

if(!(str_contains($lk,'dokumen') || str_contains($lk,'file') || str_contains($lk,'lampiran'))) continue;
if(in_array($field, ['dokumen_tidak_dipersyaratkan','dokumen_tidak_dipersyaratkan_json'], true)) continue;

$files = [];
if(is_array($rawValue)) $files = $rawValue;
elseif(is_string($rawValue) && trim($rawValue) !== ''){
$s = trim($rawValue);
$decoded = json_decode($s, true);
if(is_array($decoded)) $files = $decoded;
else $files = [$s];
}

$files = array_values(array_filter(array_map(function($x){
if($x === null) return null;
$s = trim((string)$x);
if($s === '') return null;

$s = str_replace('\\','/',$s);
$s = explode('?', $s)[0];

if(Str::startsWith($s, ['http://','https://'])){
$u = parse_url($s);
if(!empty($u['path'])) $s = $u['path'];
}

$s = ltrim($s,'/');
if(Str::startsWith($s, 'public/')) $s = Str::after($s, 'public/');
if(Str::startsWith($s, 'storage/')) $s = Str::after($s, 'storage/');
$s = preg_replace('#^storage/#','',$s);

return $s !== '' ? $s : null;
}, $files)));

if(count($files) === 0) continue;

foreach($files as $path){
$out[$field][] = [
'field' => $field,
'name' => basename($path),
'url' => '/storage/'.ltrim($path,'/'),
];
}
}

return $out;
}

function buildDocNoteForLanding($pengadaan){
if(!$pengadaan) return '';

$rawE = is_array($pengadaan->dokumen_tidak_dipersyaratkan ?? null)
? $pengadaan->dokumen_tidak_dipersyaratkan
: (json_decode((string)($pengadaan->dokumen_tidak_dipersyaratkan ?? ''), true) ?: []);

if(is_array($rawE) && count($rawE) > 0){
return implode(', ', array_map(fn($x) => is_string($x) ? $x : json_encode($x), $rawE));
}

$eVal = is_string($pengadaan->dokumen_tidak_dipersyaratkan ?? null)
? trim((string)$pengadaan->dokumen_tidak_dipersyaratkan)
: ($pengadaan->dokumen_tidak_dipersyaratkan ?? null);

if($eVal === true || $eVal === 1 || $eVal === "1" || (is_string($eVal) && in_array(strtolower($eVal), ["ya","iya","true","yes"], true))){
return "Dokumen pada Kolom E bersifat opsional (tidak dipersyaratkan).";
}

return is_string($eVal) ? $eVal : '';
}
@endphp

{{-- ARSIP LIST --}}
<section id="arsip">
  <div class="container">
    <div class="section-title">
      <h2>Arsip Pengadaan Barang dan Jasa</h2>
      <p>Daftar dokumen pengadaan barang dan jasa yang dapat diakses oleh masyarakat.</p>
    </div>

    <div class="cards">
      @if($arsipPublik->count() === 0)
      <div style="opacity:.85;padding:18px;border-radius:14px;background:#fff;border:1px solid rgba(0,0,0,.08);">
        Belum ada arsip publik yang bisa ditampilkan.
      </div>
      @endif

      @foreach($arsipPublik as $item)
      @php
      $unitName = $item->unit?->nama ?? '-';

      $payload = [
      'title' => $item->nama_pekerjaan ?? '-',
      'unit' => $unitName,
      'tahun' => $item->tahun ?? '-',
      'idrup' => $item->id_rup ?? '-',
      'status' => $item->status_pekerjaan ?? '-',
      'rekanan' => $item->nama_rekanan ?? '-',
      'jenis' => $item->jenis_pengadaan ?? '-',
      'pagu' => rupiah($item->pagu_anggaran),
      'metode' => $item->metode_pbj ?? $item->metode_pengadaan ?? $item->metode ?? $item->jenis_pengadaan ?? '-',
      'hps' => rupiah($item->hps),
      'kontrak' => rupiah($item->nilai_kontrak),
      'docnote' => buildDocNoteForLanding($item),
      'docs' => buildDokumenListForLanding($item),
      ];

      $dateLabel = idDate($item->updated_at ?? $item->created_at);
      @endphp

      <article class="card">
        <div class="card-top">
          <div>
            <div class="card-date">{{ $dateLabel }}</div>
            <div class="card-title">{{ $item->nama_pekerjaan ?? '-' }}</div>
          </div>

          <button
            type="button"
            class="btn-detail js-open-detail"
            data-payload='@json($payload)'>
            <i class="bi bi-info-circle"></i> Lihat Detail
          </button>
        </div>

        <div class="card-meta">
          <div class="meta-line"><span class="meta-k">Unit Kerja</span> : <span class="meta-v">{{ $unitName }}</span></div>
          <div class="meta-line"><span class="meta-k">ID RUP</span> : <span class="meta-v">{{ $item->id_rup ?? '-' }}</span></div>
          <div class="meta-line"><span class="meta-k">Status Pekerjaan</span> : <span class="meta-v">{{ $item->status_pekerjaan ?? '-' }}</span></div>
          <div class="meta-line"><span class="meta-k">Nilai Kontrak</span> : <span class="meta-v">{{ rupiah($item->nilai_kontrak) }}</span></div>
          <div class="meta-line"><span class="meta-k">Rekanan</span> : <span class="meta-v">{{ $item->nama_rekanan ?? '-' }}</span></div>
        </div>
      </article>
      @endforeach
    </div>

    <div class="more">
      <a href="{{ route('landing.pbj') }}">
        Lihat Selengkapnya <span style="font-size:18px">›</span>
      </a>
    </div>

  </div>
</section>

{{-- ✅ MODAL DETAIL — sama persis dengan pbj.blade.php --}}
<div class="dt-modal" id="dtModal" aria-hidden="true">
  <div class="dt-backdrop" data-close="true"></div>
  <div class="dt-panel" role="dialog" aria-modal="true" aria-labelledby="dtTitle">
    <div class="dt-card">

      <div class="dt-topbar">
        <button type="button" class="dt-back-btn" id="dtCloseBtn" aria-label="Kembali">
          <i class="bi bi-chevron-left"></i> Kembali
        </button>
        <span class="dt-status-badge" id="dtStatusBadge" hidden></span>
      </div>

      <div class="dt-body">
        <div class="dt-title" id="dtTitle">-</div>
        <div class="dt-title-divider"></div>

        <div class="dt-info-grid">
          <div class="dt-info">
            <div class="dt-ic"><i class="bi bi-building"></i></div>
            <div class="dt-info-txt">
              <div class="dt-label">Unit Kerja</div>
              <div class="dt-val" id="dtUnit">-</div>
            </div>
          </div>
          <div class="dt-info">
            <div class="dt-ic"><i class="bi bi-calendar-event"></i></div>
            <div class="dt-info-txt">
              <div class="dt-label">Tahun Anggaran</div>
              <div class="dt-val" id="dtTahun">-</div>
            </div>
          </div>
          <div class="dt-info">
            <div class="dt-ic"><i class="bi bi-person-badge"></i></div>
            <div class="dt-info-txt">
              <div class="dt-label">ID RUP</div>
              <div class="dt-val" id="dtIdRup">-</div>
            </div>
          </div>
          <div class="dt-info">
            <div class="dt-ic"><i class="bi bi-diagram-3"></i></div>
            <div class="dt-info-txt">
              <div class="dt-label">Metode Pengadaan</div>
              <div class="dt-val" id="dtMetode">-</div>
            </div>
          </div>
          <div class="dt-info">
            <div class="dt-ic"><i class="bi bi-person"></i></div>
            <div class="dt-info-txt">
              <div class="dt-label">Nama Rekanan</div>
              <div class="dt-val" id="dtRekanan">-</div>
            </div>
          </div>
          <div class="dt-info">
            <div class="dt-ic"><i class="bi bi-box"></i></div>
            <div class="dt-info-txt">
              <div class="dt-label">Jenis Pengadaan</div>
              <div class="dt-val" id="dtJenis">-</div>
            </div>
          </div>
        </div>

        <div class="dt-divider"></div>
        <div class="dt-section-title">Informasi Anggaran</div>
        <div class="dt-budget-grid">
          <div class="dt-budget">
            <div class="dt-label">Pagu Anggaran</div>
            <div class="dt-money" id="dtPagu">-</div>
          </div>
          <div class="dt-budget">
            <div class="dt-label">HPS</div>
            <div class="dt-money" id="dtHps">-</div>
          </div>
          <div class="dt-budget">
            <div class="dt-label">Nilai Kontrak</div>
            <div class="dt-money" id="dtKontrak">-</div>
          </div>
        </div>

        <div class="dt-divider"></div>
        <div class="dt-section-title">Dokumen Pengadaan</div>
        <div class="dt-doc-grid" id="dtDocList"></div>
        <div class="dt-doc-empty" id="dtDocEmpty" hidden>Tidak ada dokumen yang diupload.</div>

        <div class="dt-doc-note" id="dtDocNoteWrap" hidden>
          <div class="dt-doc-note-ic"><i class="bi bi-info-circle"></i></div>
          <div class="dt-doc-note-txt">
            <div class="dt-doc-note-title">Dokumen tidak dipersyaratkan</div>
            <div class="dt-doc-note-desc" id="dtDocNote">-</div>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

{{-- STATISTIKA --}}
<section class="stats-wrap" id="statistika">
  <div class="container">
    <div class="section-title">
      <h2>Statistik</h2>
    </div>

    @php
    $statTahunOptions = \App\Models\MasterMenu::where('category', 'tahun')
        ->where('is_active', true)
        ->orderBy('order_index')
        ->pluck('nama')
        ->map(fn($t) => (int)$t)
        ->filter()
        ->sortDesc()
        ->values();

    if ($statTahunOptions->isEmpty()) {
        $statTahunOptions = \App\Models\Pengadaan::where('status_arsip', 'Publik')
            ->whereNotNull('tahun')->select('tahun')->distinct()
            ->orderBy('tahun', 'desc')->pluck('tahun')
            ->map(fn($t) => (int)$t)->values();
    }

    $statUnitOptions = \App\Models\User::with('unit')
    ->whereIn('role', ['unit', 'ppk'])
    ->where('status', 'active')
    ->whereNotNull('unit_id')
    ->get()
    ->pluck('unit')
    ->filter()
    ->unique('id')
    ->sortBy('nama')
    ->values();
    @endphp

    <div class="stats-2col">
      @include('Partials.statistika-donut', [
          'title'            => 'Status Pekerjaan',
          'donutId'          => 'landingDonut',
          'statTahunOptions' => $statTahunOptions,
          'statUnitOptions'  => $statUnitOptions,
      ])
      @include('Partials.statistika-bar', [
          'title'            => 'Metode Pengadaan',
          'barId'            => 'landingBar',
          'statTahunOptions' => $statTahunOptions,
          'statUnitOptions'  => $statUnitOptions,
      ])
    </div>
  </div>
</section>

{{-- REGULASI --}}
<section class="reg-wrap" id="regulasi">
  @php
  $regulasi = [
  [
  'judul' => '01 Perpres-No-12-Tahun-2021 Perubahan Atas Peraturan Presiden Nomor 16 Tahun 2018 tentang PBJ Pemerintah',
  'file' => '01 Perpres-No-12-Tahun-2021 Perubahan Atas Peraturan Presiden Nomor 16 Tahun 2018 tentang PBJ Pemerintah.pdf'
  ],
  [
  'judul' => '02 Peraturan LKPP No. 12 Tahun 2021 Tentang Pedoman Pelaksanaan PBJ Pemerintah Melalui Penyedia',
  'file' => '02 Peraturan LKPP No. 12 Tahun 2021 Tentang Pedoman Pelaksanaan PBJ Pemerintah Melalui Penyedia.pdf'
  ],
  [
  'judul' => '03 Peraturan Rektor Unsoed No. 2 Tahun 2023 Tentang Pedoman Pengadaan BarangJasa Unsoed',
  'file' => '03 Peraturan Rektor Unsoed No. 2 Tahun 2023 Tentang Pedoman Pengadaan BarangJasa Unsoed.pdf'
  ],
  ];
  @endphp

  <div class="container">
    <div class="section-title">
      <h2>Regulasi</h2>
    </div>
  </div>

  <div class="reg-card">
    @foreach($regulasi as $item)
    <a href="{{ asset('regulasi/'.$item['file']) }}" target="_blank" class="reg-item">
      <div class="reg-icon"><i class="bi bi-file-earmark-text"></i></div>
      <div class="reg-text">{{ $item['judul'] }}</div>
    </a>
    @endforeach
  </div>
</section>

<style>
  :root {
    --navy: #184f61;
    --navy2: #184f61;
    --border: #e8eef3;
  }

  /* ================================
   DETAIL MODAL — sama dengan pbj.blade.php
================================ */
  .dt-modal {
    position: fixed;
    inset: 0;
    z-index: 9999;
    display: none;
  }

  .dt-modal.is-open {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 10px;
  }

  .dt-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, .35);
    backdrop-filter: blur(8px);
  }

  .dt-panel {
    width: min(1100px, 96vw);
    max-height: calc(100vh - 20px);
    display: flex;
    flex-direction: column;
    position: relative;
    z-index: 1;
    border-radius: 20px;
    overflow: hidden;
  }

  .dt-card {
    width: 100%;
    display: flex;
    flex-direction: column;
    min-height: 0;
    border-radius: 20px;
    background: #fff;
    overflow: hidden;
  }

  .dt-topbar {
    position: sticky;
    top: 0;
    z-index: 3;
    background: #fff;
    padding: 16px 18px 14px;
    border-bottom: 1px solid #eef3f6;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    font-weight: 400;
  }

  .dt-back-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: none;
    border: none;
    font-size: 14.5px;
    font-weight: 400;
    color: var(--navy);
    cursor: pointer;
    padding: 0;
    transition: opacity .15s;
  }

  .dt-back-btn:hover {
    opacity: .65;
  }

  .dt-status-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 5px 14px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 400;
    white-space: nowrap;
  }

  .dt-status-badge.sp-plan {
    background: #fef9c3;
    color: #854d0e;
  }

  .dt-status-badge.sp-select {
    background: #ede9fe;
    color: #5b21b6;
  }

  .dt-status-badge.sp-do {
    background: #fee2e2;
    color: #b91c1c;
  }

  .dt-status-badge.sp-done {
    background: #dcfce7;
    color: #15803d;
  }

  .dt-body {
    flex: 1;
    overflow-y: auto;
    min-height: 0;
    padding: 20px 22px 24px;
    overscroll-behavior: contain;
  }

  .dt-title {
    font-size: 22px;
    font-weight: 400;
    color: #0f172a;
    overflow-wrap: anywhere;
    margin-bottom: 4px;
  }

  .dt-title-divider {
    height: 1px;
    background: #eef3f6;
    margin: 14px 0;
  }

  .dt-info-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
  }

  .dt-info {
    display: flex;
    gap: 10px;
    align-items: flex-start;
  }

  .dt-ic {
    width: 38px;
    height: 38px;
    border-radius: 12px;
    border: 1px solid #eef3f6;
    background: #f8fbfd;
    display: grid;
    place-items: center;
    flex: 0 0 auto;
    font-size: 16px;
    color: var(--navy);
  }

  .dt-info-txt {
    min-width: 0;
  }

  .dt-label {
    font-size: 12px;
    color: #64748b;
    font-weight: 400;
  }

  .dt-val {
    font-size: 14px;
    color: #0f172a;
    font-weight: 400;
    margin-top: 2px;
    overflow-wrap: anywhere;
  }

  .dt-divider {
    height: 1px;
    background: #eef3f6;
    margin: 14px 0;
  }

  .dt-section-title {
    font-size: 13px;
    font-weight: 400;
    color: #64748b;
    letter-spacing: .5px;
    text-transform: uppercase;
    margin-bottom: 10px;
  }

  .dt-budget-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
  }

  .dt-budget {
    padding: 12px 14px;
    background: #f8fbfd;
    border: 1px solid #eef3f6;
    border-radius: 12px;
    font-weight: 400;
  }

  .dt-money {
    font-size: 16px;
    font-weight: 400;
    color: var(--navy2);
    margin-top: 4px;
  }

  .dt-doc-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
    margin-top: 10px;
  }

  .dt-doc-card {
    border: 1px solid #e8eef3;
    background: #fff;
    border-radius: 14px;
    padding: 12px 14px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 400;
  }

  .dt-doc-ic {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    display: grid;
    place-items: center;
    background: #f8fbfd;
    border: 1px solid #eef3f6;
    flex: 0 0 auto;
    font-size: 18px;
  }

  .dt-doc-info {
    min-width: 0;
    flex: 1;
  }

  .dt-doc-title {
    font-size: 14px;
    font-weight: 600;
    line-height: 1.3;
    overflow-wrap: anywhere;
  }

  .dt-doc-sub {
    font-size: 12px;
    color: #64748b;
    margin-top: 2px;
    overflow-wrap: anywhere;
    font-weight: 400;
  }

  .dt-doc-act {
    width: 34px;
    height: 34px;
    border-radius: 12px;
    display: grid;
    place-items: center;
    background: #f8fbfd;
    border: 1px solid #eef3f6;
    text-decoration: none;
    color: inherit;
    flex: 0 0 auto;
    font-size: 15px;
    transition: .15s;
  }

  .dt-doc-act:hover {
    background: #eef6f8;
  }

  .dt-doc-empty {
    margin-top: 10px;
    opacity: .75;
    font-size: 14px;
    color: #64748b;
    font-weight: 400;
  }

  .dt-doc-note {
    display: flex;
    gap: 10px;
    margin-top: 12px;
    padding: 12px 14px;
    background: #fffbeb;
    border: 1px solid #fde68a;
    border-radius: 12px;
    font-weight: 400;
  }

  .dt-doc-note-ic {
    font-size: 18px;
    color: #d97706;
    flex: 0 0 auto;
  }

  .dt-doc-note-title {
    font-size: 13px;
    font-weight: 400;
    color: #92400e;
  }

  .dt-doc-note-desc {
    font-size: 13px;
    color: #78350f;
    margin-top: 2px;
    font-weight: 400;
  }

  @media (max-width:1100px) {

    .dt-info-grid,
    .dt-budget-grid {
      grid-template-columns: repeat(2, 1fr);
    }

    .dt-doc-grid {
      grid-template-columns: 1fr;
    }
  }

  @media (max-width:600px) {

    .dt-info-grid,
    .dt-budget-grid {
      grid-template-columns: 1fr;
    }
  }
</style>

@push('scripts')
<script>
  /* ================================
   MODAL
================================ */
  const dtModal = document.getElementById('dtModal');

  // Hamburger homepage
document.getElementById('homeNavToggle')?.addEventListener('click', function () {
  const links = document.getElementById('homeNavLinks');
  const icon  = this.querySelector('i');
  links?.classList.toggle('is-open');
  if (icon) {
    icon.className = links?.classList.contains('is-open')
      ? 'bi bi-x-lg'
      : 'bi bi-list';
  }
});

// Tutup saat klik link
document.querySelectorAll('#homeNavLinks a').forEach(a => {
  a.addEventListener('click', () => {
    document.getElementById('homeNavLinks')?.classList.remove('is-open');
    const icon = document.querySelector('#homeNavToggle i');
    if (icon) icon.className = 'bi bi-list';
  });
});

// Dropdown user
document.getElementById('homeUserBtn')?.addEventListener('click', function (e) {
  e.stopPropagation();
  document.getElementById('homeUserDropdown')?.classList.toggle('show');
});
document.addEventListener('click', () => {
  document.getElementById('homeUserDropdown')?.classList.remove('show');
});


document.querySelectorAll('#homeNavLinks a').forEach(a => {
  a.addEventListener('click', () => {
    document.getElementById('homeNavLinks')?.classList.remove('is-open');
    const icon = document.getElementById('homeNavToggle')?.querySelector('i');
    if (icon) icon.className = 'bi bi-list';
  });
});

// Tampilkan/sembunyikan logout mobile sesuai ukuran layar
function syncLogoutMobile() {
  const logoutMobile = document.getElementById('logoutFormMobile');
  if (!logoutMobile) return;
  logoutMobile.style.display = window.innerWidth <= 768 ? 'block' : 'none';
}

syncLogoutMobile();
window.addEventListener('resize', syncLogoutMobile);

  function openDetailModal(payload) {
    document.getElementById('dtTitle').textContent = payload?.title || '-';
    document.getElementById('dtUnit').textContent = payload?.unit || '-';
    document.getElementById('dtTahun').textContent = payload?.tahun || '-';
    document.getElementById('dtIdRup').textContent = payload?.idrup || '-';
    document.getElementById('dtMetode').textContent = payload?.metode || '-';
    document.getElementById('dtRekanan').textContent = payload?.rekanan || '-';
    document.getElementById('dtJenis').textContent = payload?.jenis || '-';
    document.getElementById('dtPagu').textContent = payload?.pagu || '-';
    document.getElementById('dtHps').textContent = payload?.hps || '-';
    document.getElementById('dtKontrak').textContent = payload?.kontrak || '-';

    const badge = document.getElementById('dtStatusBadge');
    if (badge) {
      const sp = (payload?.status || '').toLowerCase().trim();
      badge.textContent = payload?.status || '';
      badge.className = 'dt-status-badge';
      if (sp === 'perencanaan') badge.classList.add('sp-plan');
      else if (sp === 'pemilihan') badge.classList.add('sp-select');
      else if (sp === 'pelaksanaan') badge.classList.add('sp-do');
      else if (sp === 'selesai') badge.classList.add('sp-done');
      badge.hidden = !payload?.status;
    }

    const dtDocList = document.getElementById('dtDocList');
    const dtDocEmpty = document.getElementById('dtDocEmpty');
    dtDocList.innerHTML = '';

    const toViewerUrl = (storageUrl) =>
      `/file-viewer?file=${encodeURIComponent(storageUrl)}&mode=public`;

    const docs = payload?.docs || {};
    let total = 0;

    Object.keys(docs).forEach(grp => {
      const arr = Array.isArray(docs[grp]) ? docs[grp] : [];
      arr.forEach(doc => {
        if (!doc?.url) return;
        total++;
        const card = document.createElement('div');
        card.className = 'dt-doc-card';
        card.innerHTML = `
        <div class="dt-doc-ic"><i class="bi bi-file-earmark-text"></i></div>
        <div class="dt-doc-info">
          <div class="dt-doc-title">${grp || 'Dokumen'}</div>
          <div class="dt-doc-sub">${doc.name || 'Dokumen'}</div>
        </div>
        <a class="dt-doc-act"
           href="${toViewerUrl(doc.url)}"
           target="_blank"
           rel="noopener"
           title="Lihat Dokumen"
           onclick="event.stopPropagation();">
          <i class="bi bi-eye"></i>
        </a>
      `;
        dtDocList.appendChild(card);
      });
    });

    dtDocEmpty.hidden = total > 0;

    const note = (payload?.docnote || '').trim();
    const noteWrap = document.getElementById('dtDocNoteWrap');
    const noteEl = document.getElementById('dtDocNote');
    noteWrap.hidden = !note;
    if (note) noteEl.textContent = note;

    dtModal.classList.add('is-open');
    dtModal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
  }

  function closeDetailModal() {
    dtModal.classList.remove('is-open');
    dtModal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
  }

  window.openDetailModal = openDetailModal;
  window.closeDetailModal = closeDetailModal;

  document.getElementById('dtCloseBtn')?.addEventListener('click', closeDetailModal);
  dtModal?.addEventListener('click', e => {
    if (e.target?.dataset?.close === 'true') closeDetailModal();
  });
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeDetailModal();
  });

  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.js-open-detail').forEach(btn => {
      btn.addEventListener('click', function() {
        let payload = {};
        try {
          payload = JSON.parse(this.dataset.payload || '{}') || {};
        } catch (e) {}
        openDetailModal(payload);
      });
    });
  });

  /* =========================
     ✅ STATISTIKA REALTIME — fetch via AJAX dari endpoint yang sama
        dengan sumber data pbj.blade.php (database langsung)
  ========================= */
  const CHART_STATS_URL = @json(route('landing.chartstats'));
  const PBJ_BASE_URL = @json(route('landing.pbj'));

  const STATUS_LABELS = ['Perencanaan', 'Pemilihan', 'Pelaksanaan', 'Selesai'];

  const METHOD_LABELS = [
    'Pengadaan Langsung',
    'Penunjukan Langsung',
    'E-Purchasing/E-Catalog',
    'Tender Terbatas',
    'Tender Terbuka',
    'Swakelola'
  ];

  const BAR_LABELS = [
    ["Pengadaan", "Langsung"],
    ["Penunjukan", "Langsung"],
    ["E-Purchasing/", "E-Catalog"],
    ["Tender", "Terbatas"],
    ["Tender", "Terbuka"],
    ["Swakelola"]
  ];

  const goToPBJ = (params) => {
    const url = new URL(PBJ_BASE_URL, window.location.origin);
    Object.keys(params || {}).forEach(k => {
      const v = params[k];
      if (v !== undefined && v !== null && String(v).trim() !== '') {
        url.searchParams.set(k, String(v));
      }
    });
    url.searchParams.delete('page');
    window.location.href = url.toString();
  };

  /**
   * Fetch data chart + opsi filter dari server.
   * Response JSON harus mengandung:
   *   - donut: [n,n,n,n]
   *   - metode: { labels: [...], values: [...] }
   *   - year_options: [2023, 2024, ...]        ← untuk populate dropdown
   *   - unit_options: [{id, nama}, ...]         ← untuk populate dropdown
   */
  async function fetchChartStats(tahun, unitId) {
    const url = new URL(CHART_STATS_URL, window.location.origin);
    if (tahun) url.searchParams.set('tahun', tahun);
    if (unitId) url.searchParams.set('unit_id', unitId);

    const res = await fetch(url.toString(), {
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      }
    });
    if (!res.ok) throw new Error('HTTP ' + res.status);
    return await res.json();
  }

  /**
   * Populate <select> dropdown dari data master yang dikembalikan API.
   * Dipanggil sekali saat load awal — tidak diulang saat filter berubah.
   */
  function populateSelectOptions(selectEl, items, type) {
    if (!selectEl) return;
    // Jangan overwrite jika sudah ada opsi selain placeholder
    if (selectEl.options.length > 1) return;

    if (type === 'year') {
      (items || []).forEach(y => {
        const opt = document.createElement('option');
        opt.value = String(y);
        opt.textContent = String(y);
        selectEl.appendChild(opt);
      });
    } else if (type === 'unit') {
      (items || []).forEach(u => {
        const opt = document.createElement('option');
        opt.value = String(u.id);
        opt.textContent = String(u.nama);
        selectEl.appendChild(opt);
      });
    }
  }

  document.addEventListener('DOMContentLoaded', async () => {
    const donutYearEl = document.getElementById('donutYear');
    const donutUnitEl = document.getElementById('donutUnit');
    const barYearEl = document.getElementById('barYear');
    const barUnitEl = document.getElementById('barUnit');

    const splitLabel = (v) => Array.isArray(v) ? v : String(v ?? '');

    /* ---- Init Donut Chart ---- */
    let donutChart = null;
    const donutCtx = document.getElementById('landingDonut');
    if (donutCtx && window.Chart) {
      donutChart = new Chart(donutCtx, {
        type: 'doughnut',
        data: {
          labels: STATUS_LABELS,
          datasets: [{
            data: [0, 0, 0, 0],
            backgroundColor: ['#0B4A5E', '#111827', '#F6C100', '#D6A357'],
            borderWidth: 0
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          cutout: '55%',
          layout: {
            padding: {
              right: 70
            }
          },
          plugins: {
            legend: {
              display: true,
              position: 'right'
            },
            tooltip: {
              enabled: true
            }
          },
          onClick(evt, elements) {
            if (!elements?.length) return;
            const status = STATUS_LABELS[elements[0].index] || '';
            if (!status) return;
            goToPBJ({
              tahun: donutYearEl?.value || '',
              unit_id: donutUnitEl?.value || '',
              status_pekerjaan: status
            });
          }
        }
      });
    }

    /* ---- Init Bar Chart ---- */
    let barChart = null;
    const barCtx = document.getElementById('landingBar');
    if (barCtx && window.Chart) {
      barChart = new Chart(barCtx, {
        type: 'bar',
        data: {
          labels: BAR_LABELS,
          datasets: [{
            label: 'Semua Tahun',
            data: [0, 0, 0, 0, 0, 0],
            backgroundColor: '#F6C100',
            borderWidth: 0,
            borderRadius: 6
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: 'bottom'
            },
            tooltip: {
              enabled: true
            }
          },
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                precision: 0
              }
            },
            x: {
              ticks: {
                maxRotation: 0,
                minRotation: 0,
                autoSkip: false,
                padding: 6,
                callback(value) {
                  return splitLabel(this.getLabelForValue(value));
                }
              },
              grid: {
                display: false
              }
            }
          },
          onClick(evt, elements) {
            if (!elements?.length) return;
            const method = METHOD_LABELS[elements[0].index] || '';
            if (!method) return;
            goToPBJ({
              tahun: barYearEl?.value || '',
              unit_id: barUnitEl?.value || '',
              metode_pengadaan: method
            });
          }
        }
      });
    }

    /* ---- Update chart dari API ---- */
    async function updateDonut() {
      if (!donutChart) return;
      try {
        const data = await fetchChartStats(
          donutYearEl?.value || '',
          donutUnitEl?.value || ''
        );
        donutChart.data.datasets[0].data = data.donut;
        donutChart.update();
      } catch (err) {
        console.error('[chart-donut] fetch error', err);
      }
    }

    async function updateBar() {
      if (!barChart) return;
      try {
        const data = await fetchChartStats(
          barYearEl?.value || '',
          barUnitEl?.value || ''
        );
        barChart.data.labels = (data.metode?.labels || METHOD_LABELS).map(label => {
          // Pecah label panjang jadi multi-baris di chart
          if (label.includes('/')) {
            const parts = label.split('/');
            return [parts[0] + '/', parts[1]];
          }
          const words = label.split(' ');
          return words.length > 1 ? [words.slice(0, -1).join(' '), words[words.length - 1]] : [label];
        });
        barChart.data.datasets[0].data = data.metode?.values || [];
        barChart.data.datasets[0].label =
          (barYearEl?.value || '') === '' ? 'Semua Tahun' : barYearEl.value;
        barChart.update();
      } catch (err) {
        console.error('[chart-bar] fetch error', err);
      }
    }

    /* ---- Load awal: isi dropdown + render chart sekaligus ---- */
    try {
      const initData = await fetchChartStats('', '');

      // Populate dropdown Tahun & Unit dari master yang dikembalikan API
      populateSelectOptions(donutYearEl, initData.year_options, 'year');
      populateSelectOptions(donutUnitEl, initData.unit_options, 'unit');
      populateSelectOptions(barYearEl, initData.year_options, 'year');
      populateSelectOptions(barUnitEl, initData.unit_options, 'unit');

      // Render chart dengan data awal (semua tahun, semua unit)
      if (donutChart) {
        donutChart.data.datasets[0].data = initData.donut;
        donutChart.update();
      }
      if (barChart) {
        barChart.data.labels = (initData.metode?.labels || METHOD_LABELS).map(label => {
          if (label.includes('/')) {
            const parts = label.split('/');
            return [parts[0] + '/', parts[1]];
          }
          const words = label.split(' ');
          return words.length > 1 ? [words.slice(0, -1).join(' '), words[words.length - 1]] : [label];
        });
        barChart.data.datasets[0].data = initData.metode?.values || [];
        barChart.update();
      }
    } catch (err) {
      console.error('[chart-init] fetch error', err);
    }

    /* ---- Event listener filter ---- */
    donutYearEl?.addEventListener('change', updateDonut);
    donutUnitEl?.addEventListener('change', updateDonut);
    barYearEl?.addEventListener('change', updateBar);
    barUnitEl?.addEventListener('change', updateBar);
  });
</script>