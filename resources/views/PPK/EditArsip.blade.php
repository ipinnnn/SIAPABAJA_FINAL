{{-- resources/views/ppk/edit_arsip.blade.php --}}
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Edit Arsip - SIAPABAJA</title>

  {{-- Font Nunito (HANYA 400 & 600 biar tidak ada bold) --}}
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600&display=swap" rel="stylesheet">

  {{-- Bootstrap Icons --}}
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

  {{-- Pakai base dashboard yang sama --}}
  <link rel="stylesheet" href="{{ asset('css/Unit.css?v=2') }}">
</head>

<body class="dash-body">

  <div class="mob-topbar">
    <div class="mob-logo">
      <img src="{{ asset('image/Logo_Unsoed.png') }}" alt="Logo">
      <span class="mob-logo-txt">SIAPABAJA</span>
    </div>

    <button class="mob-ham" id="mobHamBtn">
      <i class="bi bi-list"></i>
    </button>
  </div>

  <div class="sidebar-drawer-backdrop" id="sidebarBackdrop"></div>

  @php
  // =========================================================
  // ✅ FIX UTAMA: samakan dengan controller (PPK)
  // Controller bisa ngirim: $pengadaan atau $arsip
  // =========================================================
  $arsip = $arsip ?? null;
  $pengadaan = $pengadaan ?? $arsip;
  $arsip = $pengadaan;

  // Nama di sidebar (PPK)
  $ppkName = $ppkName ?? (auth()->user()->name ?? "PPK Utama");

  // =========================================================
  // ✅ OPTIONS dari controller (fallback aman)
  // =========================================================
  $tahunOptions = $tahunOptions ?? [date('Y'), date('Y')-1, date('Y')-2, date('Y')-3, date('Y')-4];

  /**
  * ✅ UNIT OPTIONS (SAMA KAYAK FILTER DI PPK/ArsipPBJ)
  * Controller kamu (PPKController) ngirim $unitOptions = pluck('nama') dari tabel units
  * Jadi value yang disubmit adalah STRING NAMA UNIT (unit_kerja),
  * dan controller akan mapping ke unit_id saat update.
  */
  $unitOptions = $unitOptions ?? [];

  // opsi konsisten (boleh dari controller, fallback sama seperti unit)
  $jenisPengadaanOptions = $jenisPengadaanOptions ?? [
  "Pengadaan Barang",
  "Pengadaan Pekerjaan Konstruksi",
  "Pengadaan Jasa Konsultasi",
  "Pengadaan Jasa Lainnya",
  ];
  $metodePengadaanOptions = $metodePengadaanOptions ?? [
  "Pengadaan Langsung",
  "Penunjukan Langsung",
  "E-Purchasing / E-Catalog",
  "Tender Terbatas",
  "Tender Terbuka",
  "Swakelola",
  ];
  $statusPekerjaanOptions = $statusPekerjaanOptions ?? ["Perencanaan", "Pemilihan", "Pelaksanaan", "Selesai"];

  // getter aman
  $get = function($source, string $key){
  if(!$source) return null;
  if(is_object($source)) return $source->$key ?? null;
  if(is_array($source)) return $source[$key] ?? null;
  return null;
  };

  // old() prioritas → lalu data $arsip
  $val = function(string $oldKey, array $attrs = []) use ($arsip, $get){
  $old = old($oldKey);
  if($old !== null) return $old;
  if(!$arsip) return null;
  foreach($attrs as $a){
  $v = $get($arsip, $a);
  if($v !== null && $v !== '') return $v;
  }
  return null;
  };

  // id arsip
  $arsipId = $arsip ? (int)$get($arsip,'id') : 0;

  // ✅ status arsip sesuai DB
  $statusArsipVal = $val('status_arsip', ['status_arsip']) ?? 'Publik';

  // =========================================================
  // ✅ PRESELECT UNIT (nama unit yg upload)
  // Priority:
  // 1) old('unit_kerja')
  // 2) relasi $arsip->unit->nama
  // 3) kolom $arsip->unit_kerja (kalau ada)
  // =========================================================
  $selectedUnitName = old('unit_kerja');
  if($selectedUnitName === null || $selectedUnitName === ''){
  $selectedUnitName =
  ($arsip && isset($arsip->unit) && isset($arsip->unit->nama) ? $arsip->unit->nama : null)
  ?? ($arsip ? ($get($arsip,'unit_kerja') ?? null) : null);
  }
  $selectedUnitName = is_string($selectedUnitName) ? trim($selectedUnitName) : $selectedUnitName;

  $baseName = function($path){
  if(!$path) return '';
  return basename($path);
  };

  // helper parse file existing (support: string, json array, array)
  $parseFiles = function($raw){
  if(!$raw) return [];
  if(is_array($raw)) return array_values(array_filter($raw));
  if(is_string($raw)){
  $trim = trim($raw);
  if(strlen($trim) && ($trim[0] === '[' || $trim[0] === '{')){
  $decoded = json_decode($trim, true);
  if(is_array($decoded)){
  if(array_keys($decoded) !== range(0, count($decoded)-1)){
  $decoded = array_values($decoded);
  }
  return array_values(array_filter($decoded));
  }
  }
  return [$raw];
  }
  return [];
  };

  // ✅ helper parse list (Kolom E)
  $parseList = function($raw){
  if(!$raw) return [];
  if(is_array($raw)) return array_values(array_filter($raw));
  if(is_string($raw)){
  $trim = trim($raw);
  if(strlen($trim) && ($trim[0] === '[' || $trim[0] === '{')){
  $decoded = json_decode($trim, true);
  if(is_array($decoded)){
  if(array_keys($decoded) !== range(0, count($decoded)-1)){
  $decoded = array_values($decoded);
  }
  return array_values(array_filter($decoded));
  }
  }
  if(strpos($trim, ',') !== false){
  $arr = array_map('trim', explode(',', $trim));
  return array_values(array_filter($arr));
  }
  return [$trim];
  }
  return [];
  };

  // ✅ nilai awal Kolom E
  $eSelectedRaw = old('dokumen_tidak_dipersyaratkan');
  if($eSelectedRaw === null){
  $eSelectedRaw = $val('dokumen_tidak_dipersyaratkan', ['dokumen_tidak_dipersyaratkan']) ?? ($arsip ? $get($arsip,'dokumen_tidak_dipersyaratkan') : null);
  }
  $eSelected = $parseList($eSelectedRaw);

  /**
  * DOKUMEN (SAMA PERSIS DENGAN UNIT)
  */
  $docSessions = [
  ['key'=>'dokumen_kak','label'=>'Kerangka Acuan Kerja atau KAK'],
  ['key'=>'dokumen_hps','label'=>'Harga Perkiraan Sendiri atau HPS'],
  ['key'=>'dokumen_spesifikasi_teknis','label'=>'Spesifikasi Teknis'],
  ['key'=>'dokumen_rancangan_kontrak','label'=>'Rancangan Kontrak'],
  ['key'=>'dokumen_lembar_data_kualifikasi','label'=>'Lembar Data Kualifikasi'],
  ['key'=>'dokumen_lembar_data_pemilihan','label'=>'Lembar Data Pemilihan'],
  ['key'=>'dokumen_daftar_kuantitas_harga','label'=>'Daftar Kuantitas dan Harga'],
  ['key'=>'dokumen_jadwal_lokasi_pekerjaan','label'=>'Jadwal dan Lokasi Pekerjaan'],
  ['key'=>'dokumen_gambar_rancangan_pekerjaan','label'=>'Gambar Rancangan Pekerjaan'],
  ['key'=>'dokumen_amdal','label'=>'Dokumen Analisis Mengenai Dampak Lingkungan atau AMDAL'],
  ['key'=>'dokumen_penawaran','label'=>'Dokumen Penawaran'],
  ['key'=>'surat_penawaran','label'=>'Surat Penawaran'],
  ['key'=>'dokumen_kemenkumham','label'=>'Sertifikat atau Lisensi Kemenkumham'],
  ['key'=>'ba_pemberian_penjelasan','label'=>'Berita Acara Pemberian Penjelasan'],
  ['key'=>'ba_pengumuman_negosiasi','label'=>'Berita Acara Pengumuman Negosiasi'],
  ['key'=>'ba_sanggah_banding','label'=>'Berita Acara Sanggah dan Sanggah Banding'],
  ['key'=>'ba_penetapan','label'=>'Berita Acara Penetapan'],
  ['key'=>'laporan_hasil_pemilihan','label'=>'Laporan Hasil Pemilihan Penyedia'],
  ['key'=>'dokumen_sppbj','label'=>'Surat Penunjukan Penyedia Barang Jasa atau SPPBJ'],
  ['key'=>'surat_perjanjian_kemitraan','label'=>'Surat Perjanjian Kemitraan'],
  ['key'=>'surat_perjanjian_swakelola','label'=>'Surat Perjanjian Swakelola'],
  ['key'=>'surat_penugasan_tim_swakelola','label'=>'Surat Penugasan Tim Swakelola'],
  ['key'=>'dokumen_mou','label'=>'Nota Kesepahaman atau MoU'],
  ['key'=>'dokumen_kontrak','label'=>'Dokumen Kontrak'],
  ['key'=>'ringkasan_kontrak','label'=>'Ringkasan Kontrak'],
  ['key'=>'jaminan_pelaksanaan','label'=>'Surat Jaminan Pelaksanaan'],
  ['key'=>'jaminan_uang_muka','label'=>'Surat Jaminan Uang Muka'],
  ['key'=>'jaminan_pemeliharaan','label'=>'Surat Jaminan Pemeliharaan'],
  ['key'=>'surat_tagihan','label'=>'Surat Tagihan'],
  ['key'=>'surat_pesanan_epurchasing','label'=>'Surat Pesanan Elektronik atau E-Purchasing'],
  ['key'=>'dokumen_spmk','label'=>'Surat Perintah Mulai Kerja atau SPMK'],
  ['key'=>'dokumen_sppd','label'=>'Surat Perintah Perjalanan Dinas atau SPPD'],
  ['key'=>'laporan_pelaksanaan_pekerjaan','label'=>'Laporan Pelaksanaan Pekerjaan'],
  ['key'=>'laporan_penyelesaian_pekerjaan','label'=>'Laporan Penyelesaian Pekerjaan'],
  ['key'=>'bap','label'=>'Berita Acara Pembayaran atau BAP'],
  ['key'=>'bast_sementara','label'=>'Berita Acara Serah Terima Sementara atau BAST Sementara'],
  ['key'=>'bast_akhir','label'=>'Berita Acara Serah Terima Final atau BAST Final'],
  ['key'=>'dokumen_pendukung_lainya','label'=>'Dokumen Pendukung Lainya'],
  ];
  @endphp

  <div class="dash-wrap">
    {{-- SIDEBAR --}}
    <aside class="dash-sidebar">
      <div class="dash-brand">
        <div class="dash-logo">
          <img src="{{ asset('image/Logo_Unsoed.png') }}" alt="Logo Unsoed">
        </div>

        <div class="dash-text">
          <div class="dash-app">SIAPABAJA</div>
          <div class="dash-role">Admin (PPK)</div>
        </div>
      </div>

      <nav class="dash-nav">
        <a class="dash-link" href="{{ url('/ppk/dashboard') }}">
          <span class="ic"><i class="bi bi-grid-fill"></i></span>
          Dashboard
        </a>

        <a class="dash-link active" href="{{ url('/ppk/arsip') }}">
          <span class="ic"><i class="bi bi-archive"></i></span>
          Arsip PBJ
        </a>

        <a class="dash-link" href="{{ url('/ppk/pengadaan/tambah') }}">
          <span class="ic"><i class="bi bi-plus-square"></i></span>
          Tambah Pengadaan
        </a>

        <a class="dash-link {{ request()->routeIs('ppk.kelola.akun') ? 'active' : '' }}" href="{{ route('ppk.kelola.akun') }}">
          <span class="ic"><i class="bi bi-person-gear"></i></span>
          Kelola Akun
        </a>
      </nav>

      <div class="dash-side-actions">
        <a class="dash-side-btn" href="{{ route('home') }}">
          <i class="bi bi-house-door"></i>
          Kembali
        </a>

        <a class="dash-side-btn" href="{{ url('/logout') }}">
          <i class="bi bi-box-arrow-right"></i>
          Keluar
        </a>
      </div>
    </aside>

    {{-- MAIN --}}
    <main class="dash-main">
      <header class="dash-header">
        <div class="tp-header-actions">
          <a href="{{ url('/ppk/arsip') }}" class="tp-btn tp-btn-ghost tp-btn-fit">
            <i class="bi bi-arrow-left"></i>
            Kembali
          </a>
        </div>

        <h1>Edit Arsip Pengadaan Barang dan Jasa</h1>
        <p>Perbarui arsip PBJ</p>
      </header>

      <form id="editForm"
        action="{{ $arsip ? route('ppk.arsip.update', $arsipId) : url('/ppk/pengadaan/store') }}"
        method="POST" class="tp-form" enctype="multipart/form-data">
        @csrf
        @if($arsip) @method('PUT') @endif

        {{-- A. Informasi Umum --}}
        <section class="dash-table tp-cardbox" style="border-radius:14px; overflow:visible; margin-bottom:14px;">
          <div style="padding:18px 18px 16px;">
            <div class="tp-section">
              <div class="tp-section-title">
                <span>A. Informasi Umum</span>
              </div>
              <div class="tp-divider"></div>

              <div class="tp-grid">
                <div class="tp-field">
                  <label class="tp-label">Tahun</label>
                  <div class="tp-control">
                    <select name="tahun" class="tp-select" required>
                      <option value="" {{ $val('tahun',['tahun']) ? '' : 'selected' }} disabled hidden>Tahun</option>
                      @foreach($tahunOptions as $t)
                      <option value="{{ $t }}" @selected((string)$val('tahun',['tahun'])===(string)$t)>{{ $t }}</option>
                      @endforeach
                    </select>
                    <i class="bi bi-chevron-down tp-icon"></i>
                  </div>
                </div>

                {{-- ✅ FIX FINAL: gunakan unit_kerja (STRING) sesuai controller PPK --}}
                <div class="tp-field">
                  <label class="tp-label">Unit Kerja</label>
                  <div class="tp-control">
                    <select name="unit_kerja" class="tp-select" required>
                      <option value="" {{ $selectedUnitName ? '' : 'selected' }} disabled hidden>Pilih Unit</option>
                      @foreach($unitOptions as $uname)
                      @php $uname = is_string($uname) ? trim($uname) : (string)$uname; @endphp
                      <option value="{{ $uname }}" @selected((string)$selectedUnitName===(string)$uname)>{{ $uname }}</option>
                      @endforeach
                    </select>
                    <i class="bi bi-chevron-down tp-icon"></i>
                  </div>
                </div>

                <div class="tp-field tp-full">
                  <label class="tp-label">Nama Pekerjaan</label>
                  <input type="text" name="nama_pekerjaan" class="tp-input"
                    value="{{ $val('nama_pekerjaan',['nama_pekerjaan']) }}"
                    placeholder="Nama Pekerjaan" />
                </div>

                <div class="tp-field">
                  <label class="tp-label">ID RUP</label>
                  <input type="text" name="id_rup" class="tp-input"
                    value="{{ $val('id_rup',['id_rup']) }}"
                    placeholder="RUP-xxxx-xxxx-xxx-xx" />
                </div>

                <div class="tp-field">
                  <label class="tp-label">Jenis Pengadaan</label>
                  <div class="tp-control">
                    <select name="jenis_pengadaan" class="tp-select" required>
                      <option value="" {{ $val('jenis_pengadaan',['jenis_pengadaan']) ? '' : 'selected' }} disabled hidden>Pilih Jenis Pengadaan</option>
                      @foreach($jenisPengadaanOptions as $jp)
                      <option value="{{ $jp }}" @selected((string)$val('jenis_pengadaan',['jenis_pengadaan'])===(string)$jp)>{{ $jp }}</option>
                      @endforeach
                    </select>
                    <i class="bi bi-chevron-down tp-icon"></i>
                  </div>
                </div>

                <div class="tp-field">
                  <label class="tp-label">Metode Pengadaan</label>
                  <div class="tp-control">
                    <select name="metode_pengadaan" class="tp-select" required>
                      <option value="" disabled hidden>Pilih Metode Pengadaan</option>

                      @foreach($metodePengadaanOptions as $mp)
                      <option value="{{ $mp }}" {{ $val('metode_pengadaan',['metode_pengadaan']) == $mp ? 'selected' : '' }}>
                        {{ $mp }}
                      </option>
                      @endforeach

                    </select>
                    <i class="bi bi-chevron-down tp-icon"></i>
                  </div>
                </div>

                <div class="tp-field">
                  <label class="tp-label">Status Pekerjaan</label>
                  <div class="tp-control">
                    <select name="status_pekerjaan" class="tp-select" required>
                      <option value="" {{ $val('status_pekerjaan',['status_pekerjaan']) ? '' : 'selected' }} disabled hidden>Pilih Status Pekerjaan</option>
                      @foreach($statusPekerjaanOptions as $sp)
                      <option value="{{ $sp }}" @selected((string)$val('status_pekerjaan',['status_pekerjaan'])===(string)$sp)>{{ $sp }}</option>
                      @endforeach
                    </select>
                    <i class="bi bi-chevron-down tp-icon"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>

        {{-- B. Status Akses Arsip --}}
        <section class="dash-table tp-cardbox" style="border-radius:14px; overflow:visible; margin-bottom:14px;">
          <div style="padding:18px 18px 16px;">
            <div class="tp-section">
              <div class="tp-section-title">
                <span>B. Status Akses Arsip</span>
              </div>
              <div class="tp-divider"></div>

              <div class="tp-grid" style="grid-template-columns: 1fr;">
                <div class="tp-field">
                  <label class="tp-label">Status Arsip</label>

                  <div class="tp-radio-wrap">
                    <label class="tp-radio-card {{ $statusArsipVal==='Publik' ? 'active' : '' }}">
                      <input type="radio" name="status_arsip" value="Publik" {{ $statusArsipVal==='Publik' ? 'checked' : '' }}>
                      <span class="tp-radio-dot"></span>
                      <span class="tp-radio-text">Publik</span>
                    </label>

                    <label class="tp-radio-card {{ $statusArsipVal==='Privat' ? 'active' : '' }}">
                      <input type="radio" name="status_arsip" value="Privat" {{ $statusArsipVal==='Privat' ? 'checked' : '' }}>
                      <span class="tp-radio-dot"></span>
                      <span class="tp-radio-text">Privat</span>
                    </label>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>

        {{-- C. Informasi Anggaran --}}
        <section class="dash-table tp-cardbox" style="border-radius:14px; overflow:visible; margin-bottom:14px;">
          <div style="padding:18px 18px 16px;">
            <div class="tp-section">
              <div class="tp-section-title">
                <span>C. Informasi Anggaran</span>
              </div>
              <div class="tp-divider"></div>

              <div class="tp-grid">
                <div class="tp-field">
                  <label class="tp-label">Pagu Anggaran (Rp)</label>
                  <input type="text" name="pagu_anggaran" class="tp-input"
                    value="{{ $val('pagu_anggaran',['pagu_anggaran']) }}"
                    placeholder="Rp" />
                </div>

                <div class="tp-field">
                  <label class="tp-label">HPS (Rp)</label>
                  <input type="text" name="hps" class="tp-input"
                    value="{{ $val('hps',['hps']) }}"
                    placeholder="Rp" />
                </div>

                <div class="tp-field">
                  <label class="tp-label">Nilai Kontrak (Rp)</label>
                  <input type="text" name="nilai_kontrak" class="tp-input"
                    value="{{ $val('nilai_kontrak',['nilai_kontrak']) }}"
                    placeholder="Rp" />
                </div>

                <div class="tp-field">
                  <label class="tp-label">Nama Rekanan</label>
                  <input type="text" name="nama_rekanan" class="tp-input"
                    value="{{ $val('nama_rekanan',['nama_rekanan']) }}"
                    placeholder="Nama Rekanan" />
                </div>
              </div>
            </div>
          </div>
        </section>

        {{-- D. Dokumen Pengadaan --}}
        <section class="dash-table tp-cardbox" style="border-radius:14px; overflow:visible; margin-bottom:14px;">
          <div style="padding:18px 18px 16px;">
            <div class="tp-section">
              <div class="tp-section-title">
                <span>D. Dokumen Pengadaan</span>
              </div>
              <div class="tp-divider"></div>

              <div class="tp-help" style="margin:0 6px 14px;">
                Upload dokumen pengadaan sesuai dengan tahapan proses.
              </div>

              <div class="tp-acc">
                @foreach($docSessions as $s)
                @php
                $key = $s['key'];

                // ✅ kalau controller kirim dokumenExisting (recommended), pakai itu
                $existingFiles = [];
                if(isset($dokumenExisting) && is_array($dokumenExisting) && isset($dokumenExisting[$key])){
                $existingFiles = collect($dokumenExisting[$key])->pluck('path')->all();
                } else {
                $existingFiles = $parseFiles($arsip ? $get($arsip, $key) : null);
                }

                $existingCount = count($existingFiles);
                @endphp

                <div class="tp-acc-item" data-existing-count="{{ $existingCount }}">
                  <button type="button" class="tp-acc-head" aria-expanded="true">
                    <span class="tp-acc-left">
                      <i class="bi bi-file-earmark-text"></i>
                      {{ $s['label'] }}
                    </span>
                    <span class="tp-acc-right">
                      <i class="bi bi-chevron-down tp-acc-ic"></i>
                    </span>
                  </button>

                  <div class="tp-acc-body">
                    <div class="tp-upload-row" style="margin-bottom:0;">

                      <label class="tp-dropzone">
                        <input
                          type="file"
                          name="{{ $key }}[]"
                          class="tp-file-hidden"
                          multiple
                          data-key="{{ $key }}"
                          data-arsip-id="{{ $arsipId }}" />

                        <div class="tp-drop-ic"><i class="bi bi-upload"></i></div>
                        <div class="tp-drop-title">Upload Dokumen Anda</div>
                        <div class="tp-drop-sub">Klik untuk upload atau drag & drop</div>
                        <div class="tp-drop-meta">Format : PDF, DOC, DOCX, XLS, XLSX, JPG, PNG (Max 10MB)</div>
                        <div class="tp-drop-btn">Pilih File</div>

                        <div class="tp-preview-wrap" {{ $existingCount ? '' : 'hidden' }}>
                          <div class="tp-preview-title">File terpilih</div>

                          <div class="tp-preview-list">
                            @foreach($existingFiles as $path)
                            <div class="tp-preview-item tp-existing" data-existing="1" data-path="{{ $path }}" data-field="{{ $key }}">
                              <div class="tp-preview-left">
                                <div class="tp-preview-thumb">
                                  <i class="bi bi-file-earmark"></i>
                                </div>
                                <div class="tp-preview-info">
                                  <div class="tp-preview-name">{{ $baseName($path) }}</div>
                                  <div class="tp-preview-meta">File tersimpan</div>
                                </div>
                              </div>
                              <button type="button" class="tp-preview-remove js-remove-existing" aria-label="Hapus file">
                                <i class="bi bi-x-lg"></i>
                              </button>
                            </div>
                            @endforeach
                          </div>
                        </div>

                      </label>
                    </div>
                  </div>
                </div>
                @endforeach
              </div>

            </div>
          </div>
        </section>

        {{-- E. Dokumen Tidak Dipersyaratkan --}}
        <section class="dash-table tp-cardbox" style="border-radius:14px; overflow:visible; margin-bottom:14px;">
          <div style="padding:18px 18px 16px;">
            <div class="tp-section">
              <div class="tp-section-title">
                <span>E. Dokumen Tidak Dipersyaratkan</span>
              </div>
              <div class="tp-divider"></div>

              <div class="tp-help" style="margin:0 6px 14px;">
                Centang dokumen yang <b>tidak dipersyaratkan</b>. List ini otomatis mengambil nama dokumen dari kolom D.
              </div>

              <input type="hidden" name="dokumen_tidak_dipersyaratkan_json" id="tp-nondoc-json" value="[]">
              <input type="hidden" id="tp-nondoc-preselect" value='@json($eSelected)'>

              <div class="tp-nondoc-wrap">
                <div class="tp-nondoc-head">
                  <div class="tp-nondoc-title">
                    <i class="bi bi-check2-square"></i>
                    Pilih Dokumen
                  </div>
                  <div class="tp-nondoc-actions">
                    <button type="button" class="tp-nondoc-btn" id="tp-nondoc-clear">
                      <i class="bi bi-x-circle"></i>
                      Reset
                    </button>
                  </div>
                </div>

                <div class="tp-nondoc-box" id="tp-nondoc-list"></div>

                <div class="tp-nondoc-selected" id="tp-nondoc-selected" hidden>
                  <div class="tp-nondoc-selected-title">Terpilih</div>
                  <div class="tp-nondoc-chips" id="tp-nondoc-chips"></div>
                </div>
              </div>

            </div>
          </div>
        </section>

        <div class="tp-actions tp-actions-split">
          <button type="button" class="tp-btn tp-btn-danger tp-btn-same" id="btnResetChanges">
            <i class="bi bi-x-circle"></i>
            Hapus Perubahan
          </button>

          <button type="submit" class="tp-btn tp-btn-primary tp-btn-same">
            <i class="bi bi-check2-circle"></i>
            Simpan Perubahan
          </button>
        </div>
      </form>
    </main>
  </div>

  <style>
    .dash-body {
      font-size: 18px;
      line-height: 1.6;
      font-weight: 400;
    }

    .dash-app {
      font-weight: 600 !important;
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
      margin: 0;
      color: #64748b;
    }

    .dash-role,
    .dash-unit-label,
    .dash-unit-name,
    .dash-link,
    .dash-side-btn,
    .dash-header p,
    .tp-section-title,
    .tp-badge,
    .tp-label,
    .tp-input,
    .tp-select,
    .tp-actions .tp-btn,
    .tp-help,
    .tp-radio-card,
    .tp-radio-text,
    .tp-acc-head,
    .tp-upload-label,
    .tp-drop-title,
    .tp-drop-sub,
    .tp-drop-meta,
    .tp-drop-btn,
    .tp-preview-title,
    .tp-acc-count {
      font-weight: 400 !important;
    }

    .dash-sidebar {
      display: flex;
      flex-direction: column;
    }

    .dash-side-actions {
      margin-top: auto;
      padding-top: 14px;
      border-top: 1px solid rgba(255, 255, 255, .12);
      display: grid;
      gap: 10px;
    }

    .tp-header-actions {
      display: flex;
      align-items: center;
      justify-content: flex-start;
      margin-bottom: 5px;
    }

    .tp-btn-fit {
      min-width: 0 !important;
      width: auto !important;
      height: 46px;
      padding: 12px 16px;
    }

    .tp-section-title {
      display: flex;
      align-items: center;
      gap: 10px;
      background: transparent;
      color: var(--navy2);
      padding: 0;
      border-radius: 0;
      font-size: 18px;
      width: 100%;
      box-sizing: border-box;
    }

    .tp-divider {
      height: 1px;
      background: #eef3f6;
      margin: 12px 0 14px;
    }

    .tp-label {
      display: block;
      font-size: 15px;
      color: var(--muted);
      margin-bottom: 8px;
    }

    .tp-input,
    .tp-select,
    .tp-textarea,
    .tp-file {
      width: 100%;
      border: 1px solid #e2e8f0;
      border-radius: 12px;
      padding: 12px 12px;
      font-family: inherit;
      font-size: 16px;
      outline: none;
      background: #fff;
      transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
    }

    .tp-control {
      position: relative;
    }

    .tp-control .tp-select {
      appearance: none;
      padding-right: 42px;
    }

    .tp-select {
      color: var(--navy2) !important;
      background-color: #fff !important;
    }

    .tp-select:focus {
      color: var(--navy2) !important;
    }

    .tp-select:invalid {
      color: #94a3b8 !important;
    }

    .tp-icon {
      position: absolute;
      right: 14px;
      top: 50%;
      transform: translateY(-50%);
      opacity: .55;
      pointer-events: none;
      font-size: 18px;
      transition: opacity .18s ease, transform .18s ease, color .18s ease;
      color: var(--navy2);
    }

    .tp-input:hover,
    .tp-select:hover {
      border-color: rgba(24, 79, 97, .62);
      box-shadow: 0 8px 14px rgba(2, 8, 23, .05);
      transform: translateY(-1px);
    }

    .tp-input:focus,
    .tp-select:focus,
    .tp-textarea:focus {
      border-color: var(--navy2);
      box-shadow: 0 0 0 4px rgba(24, 79, 97, .14), 0 10px 18px rgba(2, 8, 23, .06);
      transform: translateY(-1px);
    }

    .tp-control:focus-within .tp-icon {
      opacity: .95;
      color: var(--navy2);
      transform: translateY(-50%) rotate(-180deg);
    }

    .tp-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      border-radius: 12px;
      padding: 12px 16px;
      font-size: 16px;
      text-decoration: none;
      border: 1px solid #e2e8f0;
      cursor: pointer;
      background: #fff;
      transition: transform .14s ease, box-shadow .14s ease, border-color .14s ease;
      white-space: nowrap;
    }

    .tp-btn:hover {
      transform: translateY(-1px);
      box-shadow: 0 12px 20px rgba(2, 8, 23, .08);
    }

    .tp-btn i {
      font-size: 18px;
    }

    .tp-btn-same {
      min-width: 210px;
      height: 46px;
      padding: 12px 16px;
    }

    .tp-btn-ghost {
      background: #fff;
      color: var(--navy2);
    }

    .tp-btn-primary {
      background: var(--yellow);
      border-color: transparent;
      color: #0f172a;
    }

    .tp-btn-danger {
      background: #fff;
      border-color: rgba(239, 68, 68, .35);
      color: #ef4444;
    }

    .tp-btn-danger:hover {
      border-color: rgba(239, 68, 68, .65);
      box-shadow: 0 12px 20px rgba(239, 68, 68, .10);
    }

    .tp-actions {
      display: flex;
      gap: 12px;
      padding: 10px 6px 2px;
      margin-top: 6px;
    }

    .tp-actions-split {
      justify-content: space-between;
      align-items: center;
    }

    .tp-radio-wrap {
      display: grid;
      gap: 12px;
    }

    .tp-radio-card {
      display: flex;
      align-items: center;
      gap: 12px;
      border: 1px solid #e2e8f0;
      border-radius: 12px;
      padding: 14px 14px;
      background: #fff;
      cursor: pointer;
      user-select: none;
      color: var(--navy2);
      font-size: 16px;
      transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
    }

    .tp-radio-card:hover {
      border-color: rgba(24, 79, 97, .55);
      box-shadow: 0 10px 18px rgba(2, 8, 23, .07);
      transform: translateY(-1px);
    }

    .tp-radio-card input {
      display: none;
    }

    .tp-radio-dot {
      width: 18px;
      height: 18px;
      border-radius: 999px;
      border: 2px solid var(--navy2);
      display: inline-block;
      position: relative;
      flex: 0 0 auto;
    }

    .tp-radio-card.active {
      background: #dff1ff;
      border-color: #9fd0ff;
      box-shadow: 0 0 0 4px rgba(24, 79, 97, .10);
    }

    .tp-radio-card.active .tp-radio-dot::after {
      content: "";
      position: absolute;
      left: 50%;
      top: 50%;
      width: 8px;
      height: 8px;
      transform: translate(-50%, -50%);
      border-radius: 999px;
      background: var(--navy2);
    }

    .tp-acc-item {
      border: 1px solid #e6eef2;
      border-radius: 14px;
      background: #fff;
      box-shadow: 0 10px 18px rgba(2, 8, 23, .05);
      overflow: hidden;
      transition: transform .16s ease, box-shadow .16s ease, border-color .16s ease;
    }

    .tp-acc-item:hover {
      transform: translateY(-1px);
      box-shadow: 0 12px 20px rgba(2, 8, 23, .07);
    }

    .tp-acc-count {
      font-size: 13px;
      opacity: .78;
      white-space: nowrap;
      margin-right: 10px;
      color: currentColor;
    }

    .tp-acc-item.has-file {
      border-color: rgba(34, 197, 94, .65);
      box-shadow: 0 14px 26px rgba(2, 8, 23, .08);
    }

    .tp-acc-item.has-file .tp-acc-head {
      background: #22c55e;
      color: #fff;
    }

    .tp-acc-item.has-file .tp-acc-left i {
      color: #fff;
      opacity: .95;
    }

    .tp-acc-item.has-file .tp-acc-ic {
      color: #fff;
      opacity: .95;
    }

    .tp-acc-head {
      width: 100%;
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 12px;
      padding: 12px 14px;
      border: 0;
      background: #dff1ff;
      cursor: pointer;
      font-family: inherit;
      color: var(--navy2);
      font-size: 16px;
      transition: background .18s ease, color .18s ease;
    }

    .tp-acc-left {
      display: flex;
      align-items: center;
      gap: 10px;
      min-width: 0;
    }

    .tp-acc-left i {
      font-size: 18px;
    }

    .tp-acc-right {
      display: flex;
      align-items: center;
      gap: 10px;
      flex: 0 0 auto;
    }

    .tp-acc-ic {
      opacity: .9;
      transition: transform .16s ease;
      font-size: 18px;
    }

    .tp-acc-body {
      border-top: 1px solid #eef3f6;
      background: #fff;
      padding: 14px;
    }

    .tp-dropzone {
      display: grid;
      place-items: center;
      text-align: center;
      gap: 8px;
      border: 2px dashed #cbd5e1;
      border-radius: 14px;
      padding: 22px 16px;
      cursor: pointer;
      user-select: none;
      background: #fff;
      transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease, background .18s ease;
    }

    .tp-dropzone:hover {
      border-color: rgba(24, 79, 97, .70);
      box-shadow: 0 0 0 4px rgba(24, 79, 97, .12), 0 12px 20px rgba(2, 8, 23, .06);
      transform: translateY(-1px);
    }

    .tp-acc-item.has-file .tp-dropzone {
      border-style: solid;
      border-color: rgba(34, 197, 94, .90);
      background: rgba(34, 197, 94, .05);
      box-shadow: 0 0 0 4px rgba(34, 197, 94, .09), 0 12px 20px rgba(2, 8, 23, .05);
      transform: translateY(-1px);
    }

    .tp-file-hidden {
      display: none;
    }

    .tp-drop-ic {
      width: 48px;
      height: 48px;
      border-radius: 999px;
      border: 1px solid #e2e8f0;
      display: grid;
      place-items: center;
      color: var(--navy2);
      font-size: 24px;
      background: #fff;
    }

    .tp-drop-title {
      color: var(--navy2);
      font-size: 16px;
    }

    .tp-drop-sub {
      color: var(--muted);
      font-size: 14px;
    }

    .tp-drop-meta {
      color: #94a3b8;
      font-size: 13px;
    }

    .tp-drop-btn {
      margin-top: 8px;
      background: var(--navy2);
      color: #fff;
      font-size: 16px;
      padding: 10px 18px;
      border-radius: 10px;
    }

    .tp-preview-wrap {
      width: 100%;
      margin-top: 12px;
      border-top: 1px solid rgba(2, 8, 23, .06);
      padding-top: 12px;
      text-align: left;
    }

    .tp-preview-title {
      color: var(--navy2);
      font-size: 14px;
      margin-bottom: 10px;
    }

    .tp-preview-list {
      display: grid;
      gap: 10px;
    }

    .tp-preview-item {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 10px;
      padding: 10px 10px;
      border: 1px solid rgba(2, 8, 23, .08);
      border-radius: 12px;
      background: #fff;
    }

    .tp-preview-left {
      display: flex;
      align-items: center;
      gap: 10px;
      min-width: 0;
      flex: 1 1 auto;
    }

    .tp-preview-thumb {
      width: 42px;
      height: 42px;
      border-radius: 10px;
      border: 1px solid rgba(2, 8, 23, .08);
      background: #f8fafc;
      display: grid;
      place-items: center;
      overflow: hidden;
      flex: 0 0 auto;
    }

    .tp-preview-thumb img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
    }

    .tp-preview-info {
      min-width: 0;
    }

    .tp-preview-name {
      font-size: 14px;
      color: #0f172a;
      word-break: break-word;
      line-height: 1.35;
    }

    .tp-preview-meta {
      font-size: 12px;
      color: #64748b;
      margin-top: 2px;
    }

    .tp-preview-remove {
      width: 34px;
      height: 34px;
      border-radius: 10px;
      border: 1px solid rgba(2, 8, 23, .10);
      background: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      flex: 0 0 auto;
      padding: 0;
      line-height: 1;
      color: #0f172a;
    }

    .tp-preview-remove i {
      font-size: 18px;
      line-height: 1;
      display: block;
      transform: translateY(0.5px);
    }

    .tp-actions.tp-actions-split {
      display: flex !important;
      flex-direction: row !important;
      justify-content: space-between !important;
      align-items: center !important;
    }

    .tp-actions.tp-actions-split .tp-btn-same {
      width: auto !important;
      flex: 0 0 auto !important;
      min-width: 140px !important;
    }

    .tp-cardbox {
      background: #fff !important;
      border-radius: 14px !important;
      box-shadow: 0 10px 20px rgba(2, 8, 23, .06) !important;
      border: 1px solid #eef3f6 !important;
      margin-bottom: 14px !important;
      overflow: hidden !important;
    }

    .tp-cardbox>div {
      padding: 18px 18px 18px !important;
    }

    .tp-grid {
      padding: 0 !important;
      gap: 14px 18px !important;
    }

    .tp-divider {
      margin-left: 0 !important;
      margin-right: 0 !important;
    }

    .tp-acc {
      padding: 0 !important;
      display: grid !important;
      gap: 14px !important;
    }

    .tp-help {
      margin: 0 0 12px !important;
      font-size: 15px;
      color: #64748b;
    }

    /* KOLOM E */
    .tp-nondoc-wrap {
      border: 1px solid #eef3f6;
      border-radius: 14px;
      background: #fff;
      box-shadow: 0 10px 18px rgba(2, 8, 23, .05);
      overflow: hidden;
    }

    .tp-nondoc-head {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      padding: 12px 14px;
      background: #dff1ff;
      color: var(--navy2);
      border-bottom: 1px solid #eef3f6;
    }

    .tp-nondoc-title {
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 16px;
      color: var(--navy2);
    }

    .tp-nondoc-title i {
      font-size: 18px;
    }

    .tp-nondoc-actions {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .tp-nondoc-btn {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      border: 1px solid rgba(2, 8, 23, .10);
      background: #fff;
      color: var(--navy2);
      padding: 10px 12px;
      border-radius: 12px;
      cursor: pointer;
      font-family: inherit;
      font-size: 14px;
      transition: transform .14s ease, box-shadow .14s ease, border-color .14s ease;
    }

    .tp-nondoc-btn:hover {
      transform: translateY(-1px);
      box-shadow: 0 12px 18px rgba(2, 8, 23, .08);
      border-color: rgba(24, 79, 97, .35);
    }

    .tp-nondoc-box {
      padding: 14px;
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px;
      max-height: 380px;
      overflow: auto;
    }

    @media(max-width:900px) {
      .tp-nondoc-box {
        grid-template-columns: 1fr;
      }
    }

    .tp-nondoc-item {
      display: flex;
      align-items: flex-start;
      gap: 10px;
      border: 1px solid rgba(2, 8, 23, .08);
      border-radius: 14px;
      padding: 12px 12px;
      background: #fff;
      cursor: pointer;
      user-select: none;
      transition: transform .14s ease, box-shadow .14s ease, border-color .14s ease;
    }

    .tp-nondoc-item:hover {
      transform: translateY(-1px);
      box-shadow: 0 12px 18px rgba(2, 8, 23, .08);
      border-color: rgba(24, 79, 97, .35);
    }

    .tp-nondoc-item input {
      display: none;
    }

    .tp-nondoc-check {
      width: 18px;
      height: 18px;
      border-radius: 6px;
      border: 2px solid var(--navy2);
      flex: 0 0 auto;
      margin-top: 1px;
      position: relative;
    }

    .tp-nondoc-text {
      font-size: 15px;
      color: #0f172a;
      line-height: 1.35;
    }

    .tp-nondoc-item.is-checked {
      background: rgba(24, 79, 97, .08);
      border-color: rgba(24, 79, 97, .35);
    }

    .tp-nondoc-item.is-checked .tp-nondoc-check::after {
      content: "";
      position: absolute;
      left: 50%;
      top: 50%;
      width: 9px;
      height: 9px;
      transform: translate(-50%, -50%);
      border-radius: 3px;
      background: var(--navy2);
    }

    .tp-nondoc-selected {
      border-top: 1px solid rgba(2, 8, 23, .06);
      padding: 12px 14px 14px;
      background: #fff;
    }

    .tp-nondoc-selected-title {
      color: var(--navy2);
      font-size: 14px;
      margin-bottom: 10px;
    }

    .tp-nondoc-chips {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
    }

    .tp-nondoc-chip {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 8px 10px;
      border-radius: 999px;
      border: 1px solid rgba(24, 79, 97, .22);
      background: #fff;
      color: var(--navy2);
      font-size: 13px;
    }

    /* =========================================================
   RESPONSIVE PATCH — Edit Arsip
========================================================= */

    /* desktop */
    .mob-topbar,
    .sidebar-drawer-backdrop {
      display: none;
    }

    @media (max-width:1024px) {

      .mob-topbar {
        display: flex;
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
        overflow: visible !important;
        padding: 20px !important;
      }
    }

    @media (max-width:768px) {

      .dash-main {
        padding: 12px !important;
      }

      .tp-grid {
        grid-template-columns: 1fr !important;
      }

      .tp-full {
        grid-column: unset !important;
      }

      .tp-actions {
        flex-direction: column !important;
      }

      .tp-btn {
        width: 100% !important;
        justify-content: center !important;
      }

      .tp-nondoc-head {
        flex-direction: column;
        align-items: stretch;
      }

      .tp-nondoc-actions {
        width: 100%;
      }

      .tp-nondoc-btn {
        width: 100%;
        justify-content: center;
      }

      .tp-acc-head {
        font-size: 14px;
      }

      .tp-preview-item {
        flex-direction: column;
        align-items: flex-start;
      }

      .tp-preview-remove {
        align-self: flex-end;
      }
    }
  </style>

  <script>
    // toggle active state untuk radio cards
    document.addEventListener('click', function(e) {
      const card = e.target.closest('.tp-radio-card');
      if (!card) return;

      const wrap = card.closest('.tp-radio-wrap');
      if (!wrap) return;

      wrap.querySelectorAll('.tp-radio-card').forEach(c => c.classList.remove('active'));
      card.classList.add('active');

      const input = card.querySelector('input[type="radio"]');
      if (input) input.checked = true;
    });

    document.addEventListener('DOMContentLoaded', function() {

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

    document.addEventListener('DOMContentLoaded', function() {
      const formEl = document.getElementById('editForm');

      // ✅ Hapus Perubahan: reload
      const resetBtn = document.getElementById('btnResetChanges');
      if (resetBtn) resetBtn.addEventListener('click', () => window.location.reload());

      // set active sesuai checked
      document.querySelectorAll('.tp-radio-wrap').forEach(wrap => {
        wrap.querySelectorAll('.tp-radio-card').forEach(c => c.classList.remove('active'));
        const checked = wrap.querySelector('input[type="radio"]:checked');
        if (checked) checked.closest('.tp-radio-card').classList.add('active');
      });

      // Inject count
      document.querySelectorAll('.tp-acc-item').forEach(item => {
        const right = item.querySelector('.tp-acc-right');
        const chev = item.querySelector('.tp-acc-ic');
        if (!right || !chev) return;

        if (!right.querySelector('.tp-acc-count')) {
          const count = document.createElement('span');
          count.className = 'tp-acc-count';
          count.hidden = true;
          count.textContent = '';
          right.insertBefore(count, chev);
        }
      });

      // accordion: default CLOSED
      document.querySelectorAll('.tp-acc-item').forEach(item => {
        const head = item.querySelector('.tp-acc-head');
        const body = item.querySelector('.tp-acc-body');
        const ic = item.querySelector('.tp-acc-ic');
        if (!head || !body) return;

        body.style.display = 'none';
        if (ic) ic.style.transform = 'rotate(-90deg)';
        head.setAttribute('aria-expanded', 'false');

        head.addEventListener('click', () => {
          const isOpen = body.style.display !== 'none';
          body.style.display = isOpen ? 'none' : 'block';
          if (ic) ic.style.transform = isOpen ? 'rotate(-90deg)' : 'rotate(0deg)';
          head.setAttribute('aria-expanded', String(!isOpen));
        });
      });

      // "Pilih File" trigger input
      document.querySelectorAll('.tp-dropzone').forEach(zone => {
        const input = zone.querySelector('input[type="file"]');
        const btn = zone.querySelector('.tp-drop-btn');

        const title = zone.querySelector('.tp-drop-title');
        const sub = zone.querySelector('.tp-drop-sub');

        if (title && !title.dataset.defaultText) title.dataset.defaultText = title.textContent.trim();
        if (sub && !sub.dataset.defaultText) sub.dataset.defaultText = sub.textContent.trim();
        if (btn && !btn.dataset.defaultText) btn.dataset.defaultText = btn.textContent.trim();

        if (input && btn) {
          btn.addEventListener('click', (ev) => {
            ev.preventDefault();
            input.click();
          });
        }
      });

      const getIconHtml = (file) => {
        const name = (file.name || '').toLowerCase();
        const type = (file.type || '').toLowerCase();
        if (type.startsWith('image/')) return '<i class="bi bi-image"></i>';
        if (name.endsWith('.pdf')) return '<i class="bi bi-file-earmark-pdf"></i>';
        if (name.endsWith('.doc') || name.endsWith('.docx')) return '<i class="bi bi-file-earmark-word"></i>';
        if (name.endsWith('.xls') || name.endsWith('.xlsx') || name.endsWith('.csv')) return '<i class="bi bi-file-earmark-excel"></i>';
        if (name.endsWith('.ppt') || name.endsWith('.pptx')) return '<i class="bi bi-file-earmark-ppt"></i>';
        return '<i class="bi bi-file-earmark"></i>';
      };

      const formatSize = (bytes) => {
        if (!bytes && bytes !== 0) return '';
        if (bytes < 1024) return bytes + ' B';
        const kb = bytes / 1024;
        if (kb < 1024) return kb.toFixed(1) + ' KB';
        const mb = kb / 1024;
        return mb.toFixed(1) + ' MB';
      };

      // Edit mode: existing + new
      document.querySelectorAll('.tp-acc-item').forEach(item => {
        const fileInput = item.querySelector('input[type="file"]');
        const zone = item.querySelector('.tp-dropzone');
        if (!fileInput || !zone) return;

        const title = zone.querySelector('.tp-drop-title');
        const sub = zone.querySelector('.tp-drop-sub');
        const btn = zone.querySelector('.tp-drop-btn');

        const previewWrap = zone.querySelector('.tp-preview-wrap');
        const previewList = zone.querySelector('.tp-preview-list');

        const headCount = item.querySelector('.tp-acc-count');

        let storedFiles = [];
        const fileKey = (f) => `${f.name}__${f.size}__${f.lastModified}`;

        const rebuildInputFiles = () => {
          const dt = new DataTransfer();
          storedFiles.forEach(f => dt.items.add(f));
          fileInput.files = dt.files;
        };

        const ensureWrapVisible = () => {
          if (previewWrap) previewWrap.hidden = false;
        };

        const clearNewPreviewOnly = () => {
          if (!previewList) return;
          previewList.querySelectorAll('.tp-preview-item:not(.tp-existing)').forEach(n => n.remove());
        };

        const renderNewPreview = () => {
          clearNewPreviewOnly();
          if (!previewList) return;

          storedFiles.forEach((file) => {
            const row = document.createElement('div');
            row.className = 'tp-preview-item';

            const left = document.createElement('div');
            left.className = 'tp-preview-left';

            const thumb = document.createElement('div');
            thumb.className = 'tp-preview-thumb';

            const type = (file.type || '').toLowerCase();
            if (type.startsWith('image/')) {
              const img = document.createElement('img');
              img.alt = file.name || 'preview';
              img.src = URL.createObjectURL(file);
              img.onload = () => {
                try {
                  URL.revokeObjectURL(img.src);
                } catch (e) {}
              };
              thumb.appendChild(img);
            } else {
              thumb.innerHTML = getIconHtml(file);
            }

            const info = document.createElement('div');
            info.className = 'tp-preview-info';

            const name = document.createElement('div');
            name.className = 'tp-preview-name';
            name.textContent = file.name || 'Dokumen';

            const meta = document.createElement('div');
            meta.className = 'tp-preview-meta';
            meta.textContent = formatSize(file.size);

            info.appendChild(name);
            info.appendChild(meta);

            left.appendChild(thumb);
            left.appendChild(info);

            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'tp-preview-remove';
            removeBtn.setAttribute('aria-label', 'Hapus file');
            removeBtn.dataset.key = fileKey(file);
            removeBtn.innerHTML = '<i class="bi bi-x-lg"></i>';

            removeBtn.addEventListener('click', (ev) => {
              ev.preventDefault();
              ev.stopPropagation();
              const k = removeBtn.dataset.key;
              storedFiles = storedFiles.filter(f => fileKey(f) !== k);
              rebuildInputFiles();
              syncUI();
            });

            row.appendChild(left);
            row.appendChild(removeBtn);
            previewList.appendChild(row);
          });

          if (previewWrap) {
            const existingAlive = item.querySelectorAll('.tp-preview-item.tp-existing[data-existing="1"]').length;
            previewWrap.hidden = (existingAlive + storedFiles.length === 0);
          }
        };

        const syncUI = () => {
          const existingAlive = item.querySelectorAll('.tp-preview-item.tp-existing[data-existing="1"]').length;
          const total = existingAlive + storedFiles.length;
          const hasFile = total > 0;

          item.classList.toggle('has-file', hasFile);

          if (headCount) {
            if (hasFile) {
              headCount.textContent = (total === 1) ? '1 file sudah terupload' : (total + ' file sudah terupload');
              headCount.hidden = false;
            } else {
              headCount.textContent = '';
              headCount.hidden = true;
            }
          }

          if (hasFile) {
            ensureWrapVisible();
            if (title) title.textContent = (total === 1 && storedFiles.length === 1) ? storedFiles[0].name : (total + ' file dipilih');
            if (sub) sub.textContent = 'File dipilih';
            if (btn) btn.textContent = 'Tambah File';
            renderNewPreview();
          } else {
            if (title && title.dataset.defaultText) title.textContent = title.dataset.defaultText;
            if (sub && sub.dataset.defaultText) sub.textContent = sub.dataset.defaultText;
            if (btn && btn.dataset.defaultText) btn.textContent = btn.dataset.defaultText;
            if (previewList) previewList.innerHTML = '';
            if (previewWrap) previewWrap.hidden = true;
          }
        };

        // ✅ remove existing (SEKARANG: buat hidden input *_remove[] supaya TERSIMPAN di DB saat submit)
        item.querySelectorAll('.js-remove-existing').forEach(btnX => {
          btnX.addEventListener('click', (ev) => {
            ev.preventDefault();
            ev.stopPropagation();

            const row = btnX.closest('.tp-preview-item.tp-existing');
            if (!row) return;

            const field = row.getAttribute('data-field') || '';
            const path = row.getAttribute('data-path') || '';

            // ✅ kirim ke server: {field}_remove[] = path
            if (formEl && field && path) {
              const hid = document.createElement('input');
              hid.type = 'hidden';
              hid.name = `${field}_remove[]`;
              hid.value = path;
              formEl.appendChild(hid);
            }

            row.remove();
            syncUI();
          });
        });

        fileInput.addEventListener('change', () => {
          const picked = (fileInput.files && fileInput.files.length) ? Array.from(fileInput.files) : [];
          if (picked.length) {
            const existing = new Set(storedFiles.map(fileKey));
            picked.forEach(f => {
              const k = fileKey(f);
              if (!existing.has(k)) {
                storedFiles.push(f);
                existing.add(k);
              }
            });
            rebuildInputFiles();
          }

          // ✅ FIX UTAMA: JANGAN kosongkan fileInput.value karena itu menghapus file yang akan di-submit
          // fileInput.value = '';

          syncUI();
        });

        storedFiles = [];
        rebuildInputFiles();
        syncUI();
      });

      /* =========================================================
         E. Dokumen Tidak Dipersyaratkan (EDIT MODE: preselect)
         ========================================================= */
      const listWrap = document.getElementById('tp-nondoc-list');
      const jsonInput = document.getElementById('tp-nondoc-json');
      const chipsWrap = document.getElementById('tp-nondoc-chips');
      const selectedBox = document.getElementById('tp-nondoc-selected');
      const btnClear = document.getElementById('tp-nondoc-clear');

      const preselectEl = document.getElementById('tp-nondoc-preselect');
      let preselect = [];
      try {
        preselect = preselectEl ? JSON.parse(preselectEl.value || '[]') : [];
        if (!Array.isArray(preselect)) preselect = [];
      } catch (e) {
        preselect = [];
      }

      const cleanText = (s) => (s || '').replace(/\s+/g, ' ').trim();

      const getDocTitlesFromD = () => {
        const titles = [];
        document.querySelectorAll('.tp-acc-item .tp-acc-left').forEach(el => {
          const text = cleanText(el.textContent);
          if (text) titles.push(text);
        });
        return titles.filter((t, i) => titles.indexOf(t) === i);
      };

      const state = {
        selected: new Set()
      };

      const syncHiddenJson = () => {
        const arr = Array.from(state.selected);
        if (jsonInput) jsonInput.value = JSON.stringify(arr);
      };

      const renderChips = () => {
        const arr = Array.from(state.selected);
        if (!chipsWrap || !selectedBox) return;

        chipsWrap.innerHTML = '';
        if (arr.length === 0) {
          selectedBox.hidden = true;
          return;
        }
        selectedBox.hidden = false;

        arr.forEach(t => {
          const chip = document.createElement('div');
          chip.className = 'tp-nondoc-chip';
          chip.textContent = t;
          chipsWrap.appendChild(chip);
        });
      };

      const toggleItem = (title, checked, itemEl, inputEl) => {
        if (checked) state.selected.add(title);
        else state.selected.delete(title);

        if (itemEl) itemEl.classList.toggle('is-checked', checked);
        if (inputEl) inputEl.checked = checked;

        syncHiddenJson();
        renderChips();
      };

      const buildChecklist = () => {
        if (!listWrap) return;

        const titles = getDocTitlesFromD();
        listWrap.innerHTML = '';

        titles.forEach((title) => {
          const label = document.createElement('label');
          label.className = 'tp-nondoc-item';

          const input = document.createElement('input');
          input.type = 'checkbox';
          input.name = 'dokumen_tidak_dipersyaratkan[]';
          input.value = title;

          const box = document.createElement('span');
          box.className = 'tp-nondoc-check';

          const txt = document.createElement('span');
          txt.className = 'tp-nondoc-text';
          txt.textContent = title;

          label.appendChild(input);
          label.appendChild(box);
          label.appendChild(txt);

          if (preselect.includes(title)) {
            state.selected.add(title);
            label.classList.add('is-checked');
            input.checked = true;
          }

          label.addEventListener('click', (ev) => {
            if (ev.target && ev.target.tagName === 'A') return;
            ev.preventDefault();

            const next = !input.checked;
            toggleItem(title, next, label, input);
          });

          listWrap.appendChild(label);
        });

        syncHiddenJson();
        renderChips();
      };

      if (btnClear) {
        btnClear.addEventListener('click', (ev) => {
          ev.preventDefault();
          state.selected.clear();

          document.querySelectorAll('#tp-nondoc-list .tp-nondoc-item').forEach(item => {
            item.classList.remove('is-checked');
            const inp = item.querySelector('input[type="checkbox"]');
            if (inp) inp.checked = false;
          });

          syncHiddenJson();
          renderChips();
        });
      }

      buildChecklist();
    });
  </script>
  @include('Partials.chatbot')
</body>

</html>