{{-- resources/views/Landing/pbj.blade.php --}}
@extends('layouts.app')
@section('title', 'PBJ - Arsip Pengadaan | SIAPABAJA')

@section('content')
<section class="pbj-page">
  <div class="container">

    <a class="detail-back" href="{{ route('landing') }}">
      <i class="bi bi-chevron-left"></i> Kembali
    </a>

    @php
    use App\Models\Pengadaan;
    use App\Models\Unit;
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\Schema;

    $q = request('q');
    $unitId = request('unit_id');
    $statusPekerjaan = request('status_pekerjaan');
    $tahun = request('tahun');

    $statusPekerjaanOptions = \App\Models\MasterMenu::where('category', 'status_pekerjaan')
    ->where('is_active', true)
    ->orderBy('order_index')
    ->pluck('nama')
    ->toArray();

    if (empty($statusPekerjaanOptions)) {
    $statusPekerjaanOptions = ["Perencanaan", "Pemilihan", "Pelaksanaan", "Selesai"];
    }

    $unitOptions = Unit::orderBy('nama')->get();

    $tahunOptions = \App\Models\MasterMenu::where('category', 'tahun')
    ->where('is_active', true)
    ->orderBy('order_index')
    ->pluck('nama')
    ->map(fn($t) => (int)$t)
    ->filter()
    ->sortDesc()
    ->values();

    if ($tahunOptions->isEmpty()) {
    $tahunOptions = Pengadaan::where('status_arsip', 'Publik')
    ->whereNotNull('tahun')->select('tahun')->distinct()
    ->orderBy('tahun', 'desc')->pluck('tahun')
    ->map(fn($t) => (int)$t)->values();
    }

    $arsipQuery = Pengadaan::with('unit')->where('status_arsip', 'Publik');

    if($q){
    $terms = preg_split('/\s+/', trim((string)$q), -1, PREG_SPLIT_NO_EMPTY);
    foreach($terms as $term){
    $arsipQuery->where(function($sub) use ($term){
    $like = "%{$term}%";
    $sub->where('nama_pekerjaan', 'ILIKE', $like)
    ->orWhere('status_pekerjaan', 'ILIKE', $like)
    ->orWhereRaw('CAST(tahun AS TEXT) ILIKE ?', [$like])
    ->orWhereHas('unit', fn($u) => $u->where('nama', 'ILIKE', $like));
    $digits = preg_replace('/\D+/', '', (string)$term);
    if($digits !== '') $sub->orWhereRaw("regexp_replace(CAST(nilai_kontrak AS TEXT), '\\D', '', 'g') LIKE ?", ["%{$digits}%"]);
    });
    }
    }
    if($unitId && is_numeric($unitId)) $arsipQuery->where('unit_id', (int)$unitId);
    if($statusPekerjaan && in_array($statusPekerjaan, $statusPekerjaanOptions, true)) $arsipQuery->where('status_pekerjaan', $statusPekerjaan);
    if($tahun && is_numeric($tahun)) $arsipQuery->where('tahun', (int)$tahun);

    $arsips = $arsipQuery->orderByDesc('updated_at')->orderByDesc('id')->paginate(10);
    $qs = request()->except('page');

    $rupiah = function($v){
    if($v === null || $v === '') return '-';
    if(is_string($v) && Str::contains($v, 'Rp')) return $v;
    $n = is_numeric($v) ? (float)$v : (float)preg_replace('/[^\d]/', '', (string)$v);
    return 'Rp ' . number_format($n, 0, ',', '.');
    };

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
    $decoded = json_decode(trim($rawValue), true);
    $files = is_array($decoded) ? $decoded : [trim($rawValue)];
    }
    $files = array_values(array_filter(array_map(function($x){
    if($x === null) return null;
    $s = trim((string)$x); if($s === '') return null;
    $s = str_replace('\\','/',$s); $s = explode('?',$s)[0];
    if(Str::startsWith($s,['http://','https://'])){ $u=parse_url($s); if(!empty($u['path'])) $s=$u['path']; }
    $s = ltrim($s,'/');
    if(Str::startsWith($s,'public/')) $s=Str::after($s,'public/');
    if(Str::startsWith($s,'storage/')) $s=Str::after($s,'storage/');
    $s = preg_replace('#^storage/#','',$s);
    return $s !== '' ? $s : null;
    }, $files)));
    if(count($files) === 0) continue;
    foreach($files as $path){ $out[$field][] = ['field'=>$field,'name'=>basename($path),'url'=>'/storage/'.ltrim($path,'/')]; }
    }
    return $out;
    }

    function buildDocNoteForLanding($pengadaan){
    if(!$pengadaan) return '';
    $rawE = is_array($pengadaan->dokumen_tidak_dipersyaratkan ?? null)
    ? $pengadaan->dokumen_tidak_dipersyaratkan
    : (json_decode((string)($pengadaan->dokumen_tidak_dipersyaratkan ?? ''), true) ?: []);
    if(is_array($rawE) && count($rawE) > 0) return implode(', ', array_map(fn($x) => is_string($x) ? $x : json_encode($x), $rawE));
    $eVal = is_string($pengadaan->dokumen_tidak_dipersyaratkan ?? null) ? trim((string)$pengadaan->dokumen_tidak_dipersyaratkan) : ($pengadaan->dokumen_tidak_dipersyaratkan ?? null);
    if($eVal === true || $eVal === 1 || $eVal === "1" || (is_string($eVal) && in_array(strtolower($eVal),["ya","iya","true","yes"],true))) return "Dokumen pada Kolom E bersifat opsional (tidak dipersyaratkan).";
    return is_string($eVal) ? $eVal : '';
    }
    @endphp

    {{-- Filter Bar --}}
    <form class="pbj-filters" id="pbjFilterForm" method="GET" action="{{ url()->current() }}">
      <div class="pbj-search">
        <i class="bi bi-search"></i>
        <input type="text" id="pbjSearch" name="q" value="{{ $q ?? '' }}" placeholder="Cari..." />
      </div>
      <select class="pbj-select" id="pbjUnit" name="unit_id">
        <option value="" {{ !$unitId ? 'selected' : '' }}>Semua Unit</option>
        @foreach($unitOptions as $u)
        <option value="{{ $u->id }}" {{ (string)$unitId === (string)$u->id ? 'selected' : '' }}>{{ $u->nama }}</option>
        @endforeach
      </select>
      <select class="pbj-select" id="pbjStatusPekerjaan" name="status_pekerjaan">
        <option value="" {{ !$statusPekerjaan ? 'selected' : '' }}>Semua Status</option>
        @foreach($statusPekerjaanOptions as $sp)
        <option value="{{ $sp }}" {{ (string)$statusPekerjaan === (string)$sp ? 'selected' : '' }}>{{ $sp }}</option>
        @endforeach
      </select>
      <select class="pbj-select" id="pbjYear" name="tahun">
        <option value="" {{ !$tahun ? 'selected' : '' }}>Semua Tahun</option>
        @foreach($tahunOptions as $t)
        <option value="{{ $t }}" {{ (string)$tahun === (string)$t ? 'selected' : '' }}>{{ $t }}</option>
        @endforeach
      </select>
      <div class="pbj-actions">
        <a class="pbj-icon-btn" id="pbjRefreshBtn" href="{{ url()->current() }}" title="Refresh" style="display:inline-flex;align-items:center;justify-content:center;">
          <i class="bi bi-arrow-clockwise"></i>
        </a>
      </div>
    </form>

    {{-- Table --}}
    <div class="ap-table-section">
      <div class="ap-tbl-head">
        <div class="ap-col ap-col-tahun">Tahun</div>
        <div class="ap-col ap-col-unit">Unit Kerja</div>
        <div class="ap-col ap-col-job">Nama Pekerjaan</div>
        <div class="ap-col ap-col-metode">Metode PBJ</div>
        <div class="ap-col ap-col-nilai">
          <span>Nilai Kontrak</span>
          <button type="button" class="ap-sort-btn" id="sortNilaiBtn" title="Urutkan">
            <i class="bi bi-arrow-down-up" id="sortNilaiIcon"></i>
          </button>
        </div>
        <div class="ap-col ap-col-status">Status Pekerjaan</div>
        <div class="ap-col ap-col-aksi">Aksi</div>
      </div>

      @forelse($arsips as $a)
      @php
      $nilaiText = $rupiah($a->nilai_kontrak ?? null);
      $unitName = $a->unit?->nama ?? '-';
      $sp = strtolower(trim((string)($a->status_pekerjaan ?? '')));
      $spClass = match($sp){
      'perencanaan' => 'sp-badge sp-plan',
      'pemilihan' => 'sp-badge sp-select',
      'pelaksanaan' => 'sp-badge sp-do',
      'selesai' => 'sp-badge sp-done',
      default => 'sp-badge',
      };
      $nilaiRaw = preg_replace('/[^\d]/', '', (string)($a->nilai_kontrak ?? ''));
      $payload = [
      'title' => $a->nama_pekerjaan ?? '-',
      'unit' => $unitName,
      'tahun' => $a->tahun ?? '-',
      'idrup' => $a->id_rup ?? '-',
      'status' => $a->status_pekerjaan ?? '-',
      'rekanan' => $a->nama_rekanan ?? '-',
      'jenis' => $a->jenis_pengadaan ?? '-',
      'metode' => $a->metode_pbj ?? $a->metode_pengadaan ?? $a->metode ?? '-',
      'pagu' => $rupiah($a->pagu_anggaran),
      'hps' => $rupiah($a->hps),
      'kontrak' => $rupiah($a->nilai_kontrak),
      'docnote' => buildDocNoteForLanding($a),
      'docs' => buildDokumenListForLanding($a),
      ];
      @endphp
      <div class="ap-tbl-row" data-moneyraw="{{ $nilaiRaw }}">
        <div class="ap-col ap-col-tahun">{{ $a->tahun ?? '-' }}</div>
        <div class="ap-col ap-col-unit">{{ $unitName }}</div>
        <div class="ap-col ap-col-job">{{ $a->nama_pekerjaan ?? '-' }}</div>
        <div class="ap-col ap-col-metode">
          <span class="metode-badge">{{ $a->metode_pbj ?? $a->metode_pengadaan ?? $a->metode ?? '-' }}</span>
        </div>
        <div class="ap-col ap-col-nilai">{{ $nilaiText }}</div>
        <div class="ap-col ap-col-status">
          <span class="{{ $spClass }}">{{ $a->status_pekerjaan ?? '-' }}</span>
        </div>
        <div class="ap-col ap-col-aksi">
          <button type="button" class="aksi-btn aksi-info js-open-detail"
            title="Detail"
            data-title="{{ $payload['title'] }}"
            data-unit="{{ $payload['unit'] }}"
            data-tahun="{{ $payload['tahun'] }}"
            data-idrup="{{ $payload['idrup'] }}"
            data-status="{{ $payload['status'] }}"
            data-rekanan="{{ $payload['rekanan'] }}"
            data-jenis="{{ $payload['jenis'] }}"
            data-metode="{{ $payload['metode'] }}"
            data-pagu="{{ $payload['pagu'] }}"
            data-hps="{{ $payload['hps'] }}"
            data-kontrak="{{ $payload['kontrak'] }}"
            data-docnote="{{ $payload['docnote'] }}"
            data-docs='@json($payload["docs"])'>
            <i class="bi bi-info-circle-fill"></i>
          </button>
        </div>
      </div>
      @empty
      <div style="text-align:center;padding:32px;color:#94a3b8;">Tidak ada data arsip publik yang sesuai filter.</div>
      @endforelse

      <div class="ap-pagination-wrap">
        <div class="ap-page-info">
          Halaman {{ $arsips->currentPage() }} dari {{ $arsips->lastPage() }}
          &bull; Menampilkan {{ $arsips->count() }} dari {{ $arsips->total() }} data
        </div>
        <div class="ap-pagination">
          @php
          $current = $arsips->currentPage(); $last = $arsips->lastPage();
          $start = max(1,$current-2); $end = min($last,$current+2);
          $prevHref = $arsips->onFirstPage() ? '#' : $arsips->appends($qs)->url($current-1);
          $nextHref = $arsips->hasMorePages() ? $arsips->appends($qs)->url($current+1) : '#';
          @endphp
          <a class="ap-page-btn {{ $arsips->onFirstPage() ? 'is-disabled' : '' }}" href="{{ $prevHref }}"><i class="bi bi-chevron-left"></i></a>
          @if($start > 1)
          <a class="ap-page-btn" href="{{ $arsips->appends($qs)->url(1) }}">1</a>
          @if($start > 2)
          <span class="ap-page-btn is-ellipsis">…</span>
          @endif
          @endif
          @for($i=$start;$i<=$end;$i++)
            <a class="ap-page-btn {{ $i===$current ? 'is-active' : '' }}"
            href="{{ $arsips->appends($qs)->url($i) }}">
            {{ $i }}
            </a>
            @endfor
            @if($end < $last)

              @if($end < $last-1)<span class="ap-page-btn is-ellipsis">…</span>@endif<a class="ap-page-btn" href="{{ $arsips->appends($qs)->url($last) }}">{{ $last }}</a>
              @endif
              <a class="ap-page-btn {{ $arsips->hasMorePages() ? '' : 'is-disabled' }}" href="{{ $nextHref }}"><i class="bi bi-chevron-right"></i>
              </a>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- DETAIL MODAL --}}
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

<style>
  :root {
    --navy: #184f61;
    --navy2: #184f61;
    --border: #e8eef3;
    --tbl-head-bg: #184f61;
    --tbl-row-border: #eef3f6;
    --yellow: #f6c100;
  }

  /* Filter */
  .pbj-filters {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #fff;
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 12px 14px;
    margin-bottom: 16px;
    flex-wrap: wrap;
  }

  .pbj-search {
    position: relative;
    flex: 1 1 120px;
    min-width: 100px;
    display: flex;
    align-items: center;
  }

  .pbj-search i {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 15px;
    color: #94a3b8;
    pointer-events: none;
  }

  .pbj-search input {
    width: 100%;
    height: 40px;
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 0 12px 0 38px;
    font-size: 14px;
    background: #f8fafc;
    box-sizing: border-box;
    outline: none;
  }

  .pbj-select {
    height: 40px;
    padding: 0 32px 0 12px;
    border: 1px solid var(--border);
    border-radius: 10px;
    font-size: 14px;
    background: #f8fafc;
    appearance: none;
    cursor: pointer;
    outline: none;
    min-width: 90px;
    max-width: 160px;
  }

  .pbj-actions {
    display: flex;
    gap: 6px;
    align-items: center;
    margin-left: auto;
  }

  .pbj-icon-btn {
    width: 40px;
    height: 40px;
    border: 1px solid var(--border);
    border-radius: 10px;
    background: #f8fafc;
    color: var(--navy);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 17px;
    text-decoration: none;
    transition: .15s;
  }

  .pbj-icon-btn:hover {
    background: var(--navy);
    color: #fff;
  }

  /* Table */
  .ap-table-section {
    background: #fff;
    border: 1px solid var(--border);
    border-radius: 16px;
    overflow-x: auto;
    overflow-y: auto;
    max-height: 600px;
  }

  .ap-tbl-head,
  .ap-tbl-row {
    display: grid;
    grid-template-columns: 80px 1.5fr 1.5fr 1.4fr 1.4fr 1.2fr 80px;
    align-items: center;
    column-gap: 12px;
    padding: 0 14px;
    min-width: 820px;
  }

  .ap-tbl-head {
    background: var(--tbl-head-bg);
    min-height: 52px;
    position: sticky;
    top: 0;
    z-index: 2;
  }

  .ap-tbl-head .ap-col {
    color: #fff;
    font-size: 13px;
    font-weight: 600;
    letter-spacing: .3px;
    white-space: nowrap;
  }

  .ap-tbl-head .ap-col-nilai {
    display: flex;
    align-items: center;
    gap: 4px;
  }

  .ap-sort-btn {
    width: 28px;
    height: 28px;
    border: none;
    background: transparent;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    border-radius: 8px;
    color: #fff;
    transition: .15s;
    padding: 0;
  }

  .ap-sort-btn:hover {
    background: rgba(255, 255, 255, .15);
  }

  .ap-sort-btn i {
    font-size: 16px;
    display: block;
    line-height: 1;
  }

  .ap-tbl-row {
    min-height: 64px;
    border-top: 1px solid var(--tbl-row-border);
    transition: background .15s;
    background: #fff;
  }

  .ap-tbl-row:hover {
    background: #f8fafc;
  }

  .ap-col {
    font-size: 14px;
    color: #1e293b;
    min-width: 0;
    overflow-wrap: anywhere;
    text-align: left;
  }

  .ap-col-tahun {
    font-weight: 400;
    color: #374151;
  }

  .ap-col-unit {
    color: #374151;
    font-weight: 400;
    font-size: 13px;
    line-height: 1.35;
  }

  .ap-col-job {
    line-height: 1.4;
  }

  .ap-col-nilai {
    font-weight: 400;
    color: var(--navy2);
  }

  .ap-col-aksi {
    display: flex;
    align-items: center;
    gap: 6px;
  }

  .ap-col-status,
  .ap-col-metode {
    display: flex;
    align-items: center;
  }

  .metode-badge {
    display: inline-flex;
    align-items: center;
    justify-content: flex-start;
    width: 100%;
    padding: 6px 12px;
    border-radius: 8px;
    background: #dbeafe;
    color: #1e40af;
    font-size: 12px;
    font-weight: 400;
    line-height: 1.4;
    white-space: normal;
    word-break: break-word;
    text-align: left;
    box-sizing: border-box;
  }

  .sp-badge {
    display: inline-flex;
    align-items: center;
    justify-content: left;
    min-width: 100px;
    padding: 5px 12px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 400;
    white-space: nowrap;
  }

  .sp-plan {
    background: #fef9c3;
    color: #854d0e;
  }

  .sp-select {
    background: #ede9fe;
    color: #5b21b6;
  }

  .sp-do {
    background: #fee2e2;
    color: #b91c1c;
  }

  .sp-done {
    background: #dcfce7;
    color: #15803d;
  }

  .aksi-btn {
    width: 34px;
    height: 34px;
    border: 1px solid var(--border);
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

  .aksi-btn:hover {
    transform: translateY(-1px);
  }

  .aksi-info:hover {
    background: #eff6ff;
    border-color: #bfdbfe;
    color: #1d4ed8;
  }

  /* Pagination */
  .ap-pagination-wrap {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 14px 16px;
    border-top: 1px solid var(--tbl-row-border);
    min-width: 700px;
    position: sticky;
    bottom: 0;
    background: #fff;
    z-index: 2;
  }

  .ap-page-info {
    font-size: 13px;
    color: #64748b;
  }

  .ap-pagination {
    display: flex;
    align-items: center;
    gap: 5px;
    flex-wrap: wrap;
    justify-content: flex-end;
  }

  .ap-page-btn {
    min-width: 34px;
    height: 32px;
    padding: 0 9px;
    border-radius: 8px;
    border: 1px solid var(--border);
    background: #fff;
    color: #0f172a;
    font-size: 13px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: .15s;
    user-select: none;
  }

  .ap-page-btn:hover:not(.is-disabled):not(.is-ellipsis):not(.is-active) {
    background: #f1f5f9;
  }

  .ap-page-btn.is-active {
    background: var(--navy);
    color: #fff;
    border-color: var(--navy);
  }

  .ap-page-btn.is-disabled {
    opacity: .45;
    pointer-events: none;
  }

  .ap-page-btn.is-ellipsis {
    pointer-events: none;
    background: transparent;
    border-color: transparent;
  }

  /* Detail Modal */
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

  /* =========================================================
   RESPONSIVE PATCH — Landing PBJ
========================================================= */

/* Filter bar wrap di mobile */
@media (max-width: 768px) {

  .pbj-filters {
    flex-wrap: wrap !important;
    gap: 8px !important;
    padding: 10px !important;
  }

  .pbj-search {
    flex: 1 1 100% !important;
    min-width: 100% !important;
  }

  .pbj-select {
    flex: 1 1 calc(50% - 4px) !important;
    max-width: 100% !important;
    width: 100% !important;
  }

  .pbj-actions {
    flex: 1 1 100% !important;
    margin-left: 0 !important;
    justify-content: flex-start !important;
  }
}

/* Tabel responsif di mobile */
@media (max-width: 900px) {

  .ap-tbl-head { display: none !important; }

  .ap-table-section {
    overflow-x: hidden !important;
    max-height: none !important;
    padding: 12px !important;
  }

  .ap-tbl-row {
    display: grid !important;
    grid-template-columns: 1fr 1fr !important;
    grid-template-areas:
      "tahun  status"
      "job    job"
      "metode nilai"
      "aksi   aksi" !important;
    background: #fff !important;
    border: 0.5px solid #e2e8f0 !important;
    border-radius: 14px !important;
    padding: 14px !important;
    margin: 0 0 10px !important;
    min-width: 0 !important;
    width: 100% !important;
    box-sizing: border-box !important;
    column-gap: 8px !important;
    row-gap: 0 !important;
  }

  .ap-col-tahun {
    grid-area: tahun !important;
    display: block !important;
    font-size: 12px !important;
    color: #64748b !important;
    font-weight: 500 !important;
    align-self: center !important;
    padding: 0 0 6px 0 !important;
  }

  .ap-col-unit { display: none !important; }

  .ap-col-status {
    grid-area: status !important;
    display: flex !important;
    justify-content: flex-end !important;
    align-items: flex-start !important;
    padding: 0 0 6px 0 !important;
    width: auto !important;
  }

  .sp-badge {
    font-size: 11px !important;
    padding: 3px 10px !important;
    min-width: unset !important;
  }

  .ap-col-job {
    grid-area: job !important;
    font-size: 14px !important;
    font-weight: 500 !important;
    color: #0f172a !important;
    line-height: 1.4 !important;
    padding: 0 0 12px 0 !important;
    border-bottom: 0.5px solid #e2e8f0 !important;
  }

  .ap-col-metode {
    grid-area: metode !important;
    display: flex !important;
    flex-direction: column !important;
    padding: 10px 0 !important;
    width: auto !important;
    border-bottom: 0.5px solid #e2e8f0 !important;
  }

  .ap-col-metode::before {
    content: 'Metode';
    font-size: 11px;
    color: #94a3b8;
    margin-bottom: 3px;
    display: block;
  }

  .metode-badge {
    background: transparent !important;
    color: #0f172a !important;
    padding: 0 !important;
    font-size: 13px !important;
    border-radius: 0 !important;
    width: auto !important;
    display: block !important;
  }

  .ap-col-nilai {
    grid-area: nilai !important;
    display: flex !important;
    flex-direction: column !important;
    padding: 10px 0 !important;
    width: auto !important;
    font-size: 14px !important;
    font-weight: 500 !important;
    color: #184f61 !important;
    border-bottom: 0.5px solid #e2e8f0 !important;
  }

  .ap-col-nilai::before {
    content: 'Nilai Kontrak';
    font-size: 11px;
    color: #94a3b8;
    font-weight: 400;
    margin-bottom: 3px;
    display: block;
  }

  .ap-col-aksi {
    grid-area: aksi !important;
    display: flex !important;
    gap: 8px !important;
    padding-top: 10px !important;
    justify-content: flex-start !important;
    width: auto !important;
  }

  .aksi-btn {
    flex: 0 0 auto !important;
    width: 36px !important;
    height: 36px !important;
    border-radius: 10px !important;
  }

  .ap-pagination-wrap {
    flex-direction: column !important;
    align-items: flex-start !important;
    gap: 10px !important;
    min-width: unset !important;
    position: static !important;
  }

  .ap-pagination { justify-content: flex-start !important; }
}

/* Detail modal fullscreen di mobile */
@media (max-width: 768px) {

  .dt-modal.is-open {
    align-items: flex-start !important;
    padding: 0 !important;
  }

  .dt-panel {
    width: 100vw !important;
    height: 100vh !important;
    max-height: 100vh !important;
    border-radius: 0 !important;
  }

  .dt-card {
    border-radius: 0 !important;
    height: 100% !important;
  }

  .dt-info-grid,
  .dt-budget-grid { grid-template-columns: 1fr !important; }

  .dt-doc-grid { grid-template-columns: 1fr !important; }
}
</style>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', () => {

    /* Sort nilai */
    const sortBtn = document.getElementById('sortNilaiBtn');
    const sortIcon = document.getElementById('sortNilaiIcon');
    let sortDir = '';
    sortBtn?.addEventListener('click', () => {
      const section = document.querySelector('.ap-table-section');
      const pagination = section.querySelector('.ap-pagination-wrap');
      const rows = Array.from(section.querySelectorAll('.ap-tbl-row'));
      sortDir = (sortDir === '' || sortDir === 'desc') ? 'asc' : 'desc';
      sortIcon.className = sortDir === 'asc' ? 'bi bi-sort-up' : 'bi bi-sort-down-alt';
      rows.sort((a, b) => {
        const av = parseInt(a.dataset.moneyraw || '0'),
          bv = parseInt(b.dataset.moneyraw || '0');
        return sortDir === 'asc' ? av - bv : bv - av;
      });
      rows.forEach(r => section.insertBefore(r, pagination));
    });

    /* Filter */
    const baseUrl = "{{ url()->current() }}";
    const searchEl = document.getElementById('pbjSearch'),
      unitEl = document.getElementById('pbjUnit');
    const statusEl = document.getElementById('pbjStatusPekerjaan'),
      yearEl = document.getElementById('pbjYear');
    const refreshBtn = document.getElementById('pbjRefreshBtn'),
      form = document.getElementById('pbjFilterForm');
    let navTimer = null;

    function buildUrl() {
      const url = new URL(baseUrl, window.location.origin);
      const q = (searchEl ? searchEl.value : '').trim();
      if (q) url.searchParams.set('q', q);
      if (unitEl?.value) url.searchParams.set('unit_id', unitEl.value);
      if (statusEl?.value) url.searchParams.set('status_pekerjaan', statusEl.value);
      if (yearEl?.value) url.searchParams.set('tahun', yearEl.value);
      url.searchParams.delete('page');
      return url.toString();
    }

    function scheduleNav() {
      clearTimeout(navTimer);
      navTimer = setTimeout(() => {
        const n = buildUrl();
        if (n !== window.location.href) window.location.href = n;
      }, 800);
    }

    unitEl?.addEventListener('change', scheduleNav);
    statusEl?.addEventListener('change', scheduleNav);
    yearEl?.addEventListener('change', scheduleNav);
    searchEl?.addEventListener('keydown', e => {
      if (e.key === 'Enter') {
        e.preventDefault();
        window.location.href = buildUrl();
      }
    });
    searchEl?.addEventListener('input', scheduleNav);
    form?.addEventListener('submit', e => {
      e.preventDefault();
      window.location.href = buildUrl();
    });
    refreshBtn?.addEventListener('click', e => {
      e.preventDefault();
      window.location.href = baseUrl;
    });

    /* Detail Modal */
    const dtModal = document.getElementById('dtModal');

    function normalizeStorageUrl(path) {
      if (!path) return '#';
      let s = String(path).trim().replace(/\\/g, '/');
      if (s.startsWith('http')) return s;
      if (s.startsWith('/storage/')) return s;
      return '/storage/' + s.replace(/^\/+/, '');
    }

    function openDetail(data) {
      document.getElementById('dtTitle').textContent = data.title || '-';
      document.getElementById('dtUnit').textContent = data.unit || '-';
      document.getElementById('dtTahun').textContent = data.tahun || '-';
      document.getElementById('dtIdRup').textContent = data.idrup || '-';
      document.getElementById('dtMetode').textContent = data.metode || '-';
      document.getElementById('dtRekanan').textContent = data.rekanan || '-';
      document.getElementById('dtJenis').textContent = data.jenis || '-';
      document.getElementById('dtPagu').textContent = data.pagu || '-';
      document.getElementById('dtHps').textContent = data.hps || '-';
      document.getElementById('dtKontrak').textContent = data.kontrak || '-';

      const badge = document.getElementById('dtStatusBadge');
      if (badge) {
        const sp = (data.status || '').toLowerCase().trim();
        badge.textContent = data.status || '';
        badge.className = 'dt-status-badge';
        if (sp === 'perencanaan') badge.classList.add('sp-plan');
        else if (sp === 'pemilihan') badge.classList.add('sp-select');
        else if (sp === 'pelaksanaan') badge.classList.add('sp-do');
        else if (sp === 'selesai') badge.classList.add('sp-done');
        badge.hidden = !data.status;
      }

      const dtDocList = document.getElementById('dtDocList');
      const dtDocEmpty = document.getElementById('dtDocEmpty');
      dtDocList.innerHTML = '';
      const docs = data.docs || {};
      let total = 0;

      const FILE_VIEWER_URL = @json(route('file.viewer'));
      const toViewerUrl = (storageUrl) => {
        const u = new URL(FILE_VIEWER_URL, window.location.origin);
        u.searchParams.set('file', storageUrl);
        u.searchParams.set('mode', 'public');
        return u.toString();
      };

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
            <div class="dt-doc-title">${grp||'Dokumen'}</div>
            <div class="dt-doc-sub">${doc.name||'Dokumen'}</div>
          </div>
          <a class="dt-doc-act" href="${toViewerUrl(doc.url)}" target="_blank" rel="noopener"><i class="bi bi-eye"></i></a>
        `;
          dtDocList.appendChild(card);
        });
      });
      dtDocEmpty.hidden = total > 0;

      const note = (data.docnote || '').trim();
      const noteWrap = document.getElementById('dtDocNoteWrap');
      const noteEl = document.getElementById('dtDocNote');
      noteWrap.hidden = !note;
      if (note) noteEl.textContent = note;

      dtModal.classList.add('is-open');
      dtModal.setAttribute('aria-hidden', 'false');
      document.body.style.overflow = 'hidden';
    }

    function closeDetail() {
      dtModal.classList.remove('is-open');
      dtModal.setAttribute('aria-hidden', 'true');
      document.body.style.overflow = '';
    }

    document.querySelectorAll('.js-open-detail').forEach(btn => {
      btn.addEventListener('click', function() {
        let docs = {};
        try {
          docs = JSON.parse(btn.getAttribute('data-docs') || '{}');
        } catch (_) {}
        openDetail({
          title: btn.dataset.title,
          unit: btn.dataset.unit,
          tahun: btn.dataset.tahun,
          idrup: btn.dataset.idrup,
          status: btn.dataset.status,
          rekanan: btn.dataset.rekanan,
          jenis: btn.dataset.jenis,
          metode: btn.dataset.metode,
          pagu: btn.dataset.pagu,
          hps: btn.dataset.hps,
          kontrak: btn.dataset.kontrak,
          docnote: btn.dataset.docnote,
          docs,
        });
      });
    });

    document.getElementById('dtCloseBtn')?.addEventListener('click', closeDetail);
    dtModal?.addEventListener('click', e => {
      if (e.target?.dataset?.close === 'true') closeDetail();
    });
    document.addEventListener('keydown', e => {
      if (e.key === 'Escape') closeDetail();
    });
  });
</script>
@endpush