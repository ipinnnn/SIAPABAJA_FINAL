<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Tambah Pengadaan - SIAPABAJA</title>

  {{-- Font Nunito (HANYA 400 & 600 biar tidak ada bold) --}}
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600&display=swap" rel="stylesheet">

  {{-- Bootstrap Icons --}}
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

  {{-- Pakai base dashboard yang sama --}}
  <link rel="stylesheet" href="{{ asset('css/Unit.css?v=2') }}">
</head>

{{-- ✅ Tambah class page-sa-tp supaya CSS tidak bocor ke halaman lain --}}

<body class="dash-body page-sa-tp page-unit-tp">
  {{-- MOBILE TOPBAR --}}
  <div class="mob-topbar" id="mobTopbar">
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
  $superAdminName = $superAdminName ?? (auth()->user()->name ?? "Super Admin");


  $tahunOptions = collect(
  $tahunOptions ?? [
  date('Y'),
  date('Y') - 1,
  date('Y') - 2,
  ]
  )
  ->map(fn($v) => trim((string)$v))
  ->unique()
  ->sortDesc()
  ->values()
  ->toArray();

  $jenisPengadaanOptions = $jenisPengadaanOptions ?? [];

  $metodePengadaanOptions = $metodePengadaanOptions ?? [];

  $statusPekerjaanOptions = $statusPekerjaanOptions ?? [];

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

        <a class="dash-link active {{ request()->routeIs('superadmin.pengadaan.create') ? 'active' : '' }}"
          href="{{ route('superadmin.pengadaan.create') }}">
          <span class="ic"><i class="bi bi-plus-square"></i></span>
          Tambah Pengadaan
        </a>

        <a class="dash-link {{ request()->routeIs('superadmin.kelola.menu') ? 'active' : '' }}"
          href="{{ route('superadmin.kelola.menu') }}">
          <span class="ic"><i class="bi bi-gear-fill"></i></span>
          Kelola Menu
        </a>

        <a class="dash-link {{ request()->routeIs('superadmin.kelola.akun*') ? 'active' : '' }}"
          href="{{ route('superadmin.kelola.akun') }}">
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
        <h1>Tambah Arsip Pengadaan Barang dan Jasa</h1>
        <p>Lengkapi formulir dibawah ini untuk menambahkan arsip PBJ</p>
      </header>

      <form action="{{ route('superadmin.pengadaan.store') }}" method="POST" class="tp-form" enctype="multipart/form-data">
        @csrf

        {{-- A. Informasi Umum --}}
        <section class="dash-table tp-cardbox" style="border-radius:14px; overflow:visible; margin-bottom:14px;">
          <div style="padding:18px 18px 16px;">
            <div class="tp-section">
              <div class="tp-section-title">
                <span>A. Informasi Umum</span>
              </div>
              <div class="tp-divider"></div>

              <div class="tp-grid tp-grid-2col">
                <div class="tp-field">
                  <label class="tp-label">Tahun</label>
                  <div class="tp-control tp-dd">
                    <select name="tahun" class="tp-select tp-select-native" required>
                      <option value="" selected disabled hidden>Tahun</option>
                      @foreach($tahunOptions as $t)
                      <option value="{{ $t }}" {{ old('tahun') == $t ? 'selected' : '' }}>{{ $t }}</option>
                      @endforeach
                    </select>
                    <button type="button" class="tp-dd-btn" aria-haspopup="listbox" aria-expanded="false"></button>
                    <div class="tp-dd-menu" role="listbox" tabindex="-1"></div>
                    <i class="bi bi-chevron-down tp-icon"></i>
                  </div>
                </div>

                <div class="tp-field">
                  <label class="tp-label">Unit Kerja</label>
                  <div class="tp-control tp-dd">
                    <select name="unit_id" class="tp-select tp-select-native" required>
                      <option value="" selected disabled hidden>Pilih Unit Kerja</option>

                      @foreach($units as $u)
                      <option
                        value="{{ $u->id }}"
                        {{ old('unit_id') == $u->id ? 'selected' : '' }}>
                        {{ $u->nama }}
                      </option>
                      @endforeach
                    </select>
                    <button type="button" class="tp-dd-btn" aria-haspopup="listbox" aria-expanded="false"></button>
                    <div class="tp-dd-menu" role="listbox" tabindex="-1"></div>
                    <i class="bi bi-chevron-down tp-icon"></i>
                  </div>
                </div>

                <div class="tp-field tp-full">
                  <label class="tp-label">Nama Pekerjaan</label>
                  <input type="text" name="nama_pekerjaan" class="tp-input"
                    placeholder="Nama Pekerjaan" value="{{ old('nama_pekerjaan') }}" />
                </div>

                <div class="tp-field">
                  <label class="tp-label">ID RUP</label>
                  <input type="text" name="id_rup" class="tp-input"
                    placeholder="RUP-xxxx-xxxx-xxx-xx" value="{{ old('id_rup') }}" />
                </div>

                <div class="tp-field">
                  <label class="tp-label">Jenis Pengadaan</label>
                  <div class="tp-control tp-dd">
                    <select name="jenis_pengadaan" class="tp-select tp-select-native" required>
                      <option value="" disabled {{ old('jenis_pengadaan') ? '' : 'selected' }} hidden>Pilih Jenis Pengadaan</option>
                      @foreach($jenisPengadaanOptions as $jp)
                      <option value="{{ $jp }}" {{ old('jenis_pengadaan') == $jp ? 'selected' : '' }}>{{ $jp }}</option>
                      @endforeach
                    </select>
                    <button type="button" class="tp-dd-btn"></button>
                    <div class="tp-dd-menu"></div>
                    <i class="bi bi-chevron-down tp-icon"></i>
                  </div>
                </div>

                <div class="tp-field">
                  <label class="tp-label">Metode Pengadaan</label>
                  <div class="tp-control tp-dd">
                    <select name="metode_pengadaan" class="tp-select tp-select-native" required>
                      <option value="" disabled {{ old('metode_pengadaan') ? '' : 'selected' }} hidden>Pilih Metode Pengadaan</option>
                      @foreach($metodePengadaanOptions as $mp)
                      <option value="{{ $mp }}" {{ old('metode_pengadaan') == $mp ? 'selected' : '' }}>{{ $mp }}</option>
                      @endforeach
                    </select>
                    <button type="button" class="tp-dd-btn"></button>
                    <div class="tp-dd-menu"></div>
                    <i class="bi bi-chevron-down tp-icon"></i>
                  </div>
                </div>

                <div class="tp-field">
                  <label class="tp-label">Status Pekerjaan</label>
                  <div class="tp-control tp-dd">
                    <select name="status_pekerjaan" class="tp-select tp-select-native" required>
                      <option value="" selected disabled hidden>Pilih Status Pekerjaan</option>
                      @foreach($statusPekerjaanOptions as $sp)
                      <option value="{{ $sp }}" {{ old('status_pekerjaan') == $sp ? 'selected' : '' }}>{{ $sp }}</option>
                      @endforeach
                    </select>
                    <button type="button" class="tp-dd-btn" aria-haspopup="listbox" aria-expanded="false"></button>
                    <div class="tp-dd-menu" role="listbox" tabindex="-1"></div>
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
                    <label class="tp-radio-card active">
                      <input type="radio" name="status_arsip" value="Publik" checked>
                      <span class="tp-radio-dot"></span>
                      <span class="tp-radio-text">Publik</span>
                    </label>

                    <label class="tp-radio-card">
                      <input type="radio" name="status_arsip" value="Privat">
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

              <div class="tp-grid tp-grid-2col">
                <div class="tp-field">
                  <label class="tp-label">Pagu Anggaran (Rp)</label>
                  <input type="text" name="pagu_anggaran" class="tp-input"
                    placeholder="Rp" value="{{ old('pagu_anggaran') }}" />
                </div>

                <div class="tp-field">
                  <label class="tp-label">HPS (Rp)</label>
                  <input type="text" name="hps" class="tp-input"
                    placeholder="Rp" value="{{ old('hps') }}" />
                </div>

                <div class="tp-field">
                  <label class="tp-label">Nilai Kontrak (Rp)</label>
                  <input type="text" name="nilai_kontrak" class="tp-input"
                    placeholder="Rp" value="{{ old('nilai_kontrak') }}" />
                </div>

                <div class="tp-field">
                  <label class="tp-label">Nama Rekanan</label>
                  <input type="text" name="nama_rekanan" class="tp-input"
                    placeholder="Nama Rekanan" value="{{ old('nama_rekanan') }}" />
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
                <div class="tp-acc-item">
                  <button type="button" class="tp-acc-head" aria-expanded="false">
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
                        <input type="file" name="{{ $s['key'] }}[]" class="tp-file-hidden" multiple />
                        <div class="tp-drop-ic"><i class="bi bi-upload"></i></div>
                        <div class="tp-drop-title">Upload Dokumen Anda</div>
                        <div class="tp-drop-sub">Klik untuk upload atau drag & drop</div>
                        <div class="tp-drop-meta">Format : PDF, DOC, DOCX, XLS, XLSX, JPG, PNG (Max 10MB)</div>
                        <div class="tp-drop-btn">Pilih File</div>

                        <div class="tp-preview-wrap" hidden>
                          <div class="tp-preview-title">File terpilih</div>
                          <div class="tp-preview-list"></div>
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

        <div class="tp-actions">
          <a href="{{ route('superadmin.arsip') }}" class="tp-btn tp-btn-ghost tp-btn-same">
            <i class="bi bi-arrow-left"></i>
            Kembali
          </a>

          <button type="submit" class="tp-btn tp-btn-primary tp-btn-same">
            <i class="bi bi-check2-circle"></i>
            Simpan Arsip
          </button>
        </div>
      </form>
    </main>
  </div>

  <style>
    /* =========================================================
     SCOPE ke .page-sa-tp supaya tidak bocor ke halaman lain
     IDENTIK dengan PPK tambah_pengadaan (page-ppk-tp)
     ========================================================= */
    :where(.page-sa-tp) {
      line-height: 1.6;
      font-weight: 400;
    }

    :where(.page-sa-tp) .dash-header {
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      gap: 6px;
    }

    :where(.page-sa-tp) .dash-header h1 {
      margin: 0;
      font-weight: 700;
      color: #184f61;
    }

    :where(.page-sa-tp) .dash-header p {
      margin: 10;
      color: #64748b;
    }

    :where(.page-sa-tp) .dash-role,
    :where(.page-sa-tp) .dash-unit-label,
    :where(.page-sa-tp) .dash-unit-name,
    :where(.page-sa-tp) .dash-link,
    :where(.page-sa-tp) .dash-side-btn,
    :where(.page-sa-tp) .dash-header p,
    :where(.page-sa-tp) .tp-section-title,
    :where(.page-sa-tp) .tp-badge,
    :where(.page-sa-tp) .tp-label,
    :where(.page-sa-tp) .tp-input,
    :where(.page-sa-tp) .tp-actions .tp-btn,
    :where(.page-sa-tp) .tp-help,
    :where(.page-sa-tp) .tp-radio-card,
    :where(.page-sa-tp) .tp-radio-text,
    :where(.page-sa-tp) .tp-acc-head,
    :where(.page-sa-tp) .tp-upload-label,
    :where(.page-sa-tp) .tp-drop-title,
    :where(.page-sa-tp) .tp-drop-sub,
    :where(.page-sa-tp) .tp-drop-meta,
    :where(.page-sa-tp) .tp-drop-btn,
    :where(.page-sa-tp) .tp-preview-title,
    :where(.page-sa-tp) .tp-acc-count {
      font-weight: 400 !important;
    }

    :where(.page-sa-tp) .dash-sidebar {
      display: flex;
      flex-direction: column;
    }

    :where(.page-sa-tp) .dash-side-actions {
      margin-top: auto;
      padding-top: 14px;
      border-top: 1px solid rgba(255, 255, 255, .12);
      display: grid;
      gap: 10px;
    }

    /* JUDUL SECTION A/B/C/D/E */
    :where(.page-sa-tp) .tp-section-title {
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

    :where(.page-sa-tp) .tp-divider {
      height: 1px;
      background: #eef3f6;
      margin: 12px 0 14px;
    }

    :where(.page-sa-tp) .tp-label {
      display: block;
      font-size: 15px;
      color: var(--muted);
      margin-bottom: 8px;
    }

    :where(.page-sa-tp) .tp-input,
    :where(.page-sa-tp) .tp-textarea,
    :where(.page-sa-tp) .tp-file {
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

    :where(.page-sa-tp) .tp-input:hover {
      border-color: rgba(24, 79, 97, .62);
      box-shadow: 0 8px 14px rgba(2, 8, 23, .05);
      transform: translateY(-1px);
    }

    :where(.page-sa-tp) .tp-input:focus {
      border-color: var(--navy2);
      box-shadow: 0 0 0 4px rgba(24, 79, 97, .14), 0 10px 18px rgba(2, 8, 23, .06);
      transform: translateY(-1px);
    }

    /* =========================================================
     CUSTOM DROPDOWN (sama persis dengan PPK)
     ========================================================= */
    :where(.page-sa-tp) .tp-control {
      position: relative;
    }

    :where(.page-sa-tp) .tp-select-native {
      position: absolute !important;
      left: -9999px !important;
      width: 1px !important;
      height: 1px !important;
      opacity: 0 !important;
      pointer-events: none !important;
    }

    :where(.page-sa-tp) .tp-dd-btn {
      width: 100%;
      border: 1px solid #e2e8f0;
      border-radius: 12px;
      padding: 12px 42px 12px 12px;
      font-family: inherit;
      font-size: 16px;
      background: #fff;
      text-align: left;
      cursor: pointer;
      color: var(--navy2);
      transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
    }

    :where(.page-sa-tp) .tp-dd-btn.is-placeholder {
      color: #94a3b8;
    }

    :where(.page-sa-tp) .tp-dd-btn:hover {
      border-color: rgba(24, 79, 97, .62);
      box-shadow: 0 8px 14px rgba(2, 8, 23, .05);
      transform: translateY(-1px);
    }

    :where(.page-sa-tp) .tp-dd.open .tp-dd-btn,
    :where(.page-sa-tp) .tp-dd-btn:focus {
      border-color: var(--navy2);
      box-shadow: 0 0 0 4px rgba(24, 79, 97, .14), 0 10px 18px rgba(2, 8, 23, .06);
      transform: translateY(-1px);
      outline: none;
    }

    :where(.page-sa-tp) .tp-icon {
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

    :where(.page-sa-tp) .tp-dd.open .tp-icon {
      opacity: .95;
      transform: translateY(-50%) rotate(-180deg);
    }

    :where(.page-sa-tp) .tp-dd-menu {
      position: absolute;
      left: 0;
      right: 0;
      top: calc(100% + 8px);
      background: #fff;
      border: 1px solid #e2e8f0;
      border-radius: 14px;
      box-shadow: 0 18px 30px rgba(2, 8, 23, .12);
      max-height: 360px;
      overflow: auto;
      padding: 8px;
      z-index: 50;
      display: none;
    }

    :where(.page-sa-tp) .tp-dd.open .tp-dd-menu {
      display: block;
    }

    :where(.page-sa-tp) .tp-dd-opt {
      display: flex;
      align-items: center;
      gap: 10px;
      width: 100%;
      border: 0;
      background: transparent;
      padding: 10px 10px;
      border-radius: 12px;
      cursor: pointer;
      font-family: inherit;
      font-size: 15px;
      color: #0f172a;
      text-align: left;
    }

    :where(.page-sa-tp) .tp-dd-opt:hover {
      background: rgba(24, 79, 97, .10);
    }

    :where(.page-sa-tp) .tp-dd-opt.is-selected {
      background: var(--navy2);
      color: #fff;
    }

    /* ACTIONS */
    :where(.page-sa-tp) .tp-actions {
      display: flex;
      justify-content: space-between;
      gap: 12px;
      padding: 10px 6px 2px;
      margin-top: 6px;
    }

    :where(.page-sa-tp) .tp-btn {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      border-radius: 12px;
      padding: 12px 16px;
      font-size: 16px;
      text-decoration: none;
      border: 1px solid #e2e8f0;
      cursor: pointer;
      background: #fff;
      transition: transform .14s ease, box-shadow .14s ease, border-color .14s ease;
    }

    :where(.page-sa-tp) .tp-btn:hover {
      transform: translateY(-1px);
      box-shadow: 0 12px 20px rgba(2, 8, 23, .08);
    }

    :where(.page-sa-tp) .tp-btn i {
      font-size: 18px;
    }

    :where(.page-sa-tp) .tp-btn-ghost {
      background: #fff;
      color: var(--navy2);
    }

    :where(.page-sa-tp) .tp-btn-primary {
      background: var(--yellow);
      border-color: transparent;
      color: #0f172a;
    }

    /* RADIO */
    :where(.page-sa-tp) .tp-radio-wrap {
      display: grid;
      gap: 12px;
    }

    :where(.page-sa-tp) .tp-radio-card {
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

    :where(.page-sa-tp) .tp-radio-card:hover {
      border-color: rgba(24, 79, 97, .55);
      box-shadow: 0 10px 18px rgba(2, 8, 23, .07);
      transform: translateY(-1px);
    }

    :where(.page-sa-tp) .tp-radio-card input {
      display: none;
    }

    :where(.page-sa-tp) .tp-radio-dot {
      width: 18px;
      height: 18px;
      border-radius: 999px;
      border: 2px solid var(--navy2);
      display: inline-block;
      position: relative;
      flex: 0 0 auto;
    }

    :where(.page-sa-tp) .tp-radio-card.active {
      background: #dff1ff;
      border-color: #9fd0ff;
      box-shadow: 0 0 0 4px rgba(24, 79, 97, .10);
    }

    :where(.page-sa-tp) .tp-radio-card.active .tp-radio-dot::after {
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

    /* ACCORDION */
    :where(.page-sa-tp) .tp-acc-item {
      border: 1px solid #e6eef2;
      border-radius: 14px;
      background: #fff;
      box-shadow: 0 10px 18px rgba(2, 8, 23, .05);
      overflow: hidden;
      transition: transform .16s ease, box-shadow .16s ease, border-color .16s ease;
    }

    :where(.page-sa-tp) .tp-acc-item:hover {
      transform: translateY(-1px);
      box-shadow: 0 12px 20px rgba(2, 8, 23, .07);
    }

    :where(.page-sa-tp) .tp-acc-count {
      font-size: 13px;
      opacity: .78;
      white-space: nowrap;
      margin-right: 10px;
      color: currentColor;
    }

    :where(.page-sa-tp) .tp-acc-item.has-file {
      border-color: rgba(34, 197, 94, .65);
      box-shadow: 0 14px 26px rgba(2, 8, 23, .08);
    }

    :where(.page-sa-tp) .tp-acc-item.has-file .tp-acc-head {
      background: #22c55e;
      color: #fff;
    }

    :where(.page-sa-tp) .tp-acc-item.has-file .tp-acc-left i {
      color: #fff;
      opacity: .95;
    }

    :where(.page-sa-tp) .tp-acc-item.has-file .tp-acc-ic {
      color: #fff;
      opacity: .95;
    }

    :where(.page-sa-tp) .tp-acc-head {
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

    :where(.page-sa-tp) .tp-acc-left {
      display: flex;
      align-items: center;
      gap: 10px;
      min-width: 0;
    }

    :where(.page-sa-tp) .tp-acc-left i {
      font-size: 18px;
    }

    :where(.page-sa-tp) .tp-acc-right {
      display: flex;
      align-items: center;
      gap: 10px;
      flex: 0 0 auto;
    }

    :where(.page-sa-tp) .tp-acc-ic {
      opacity: .9;
      transition: transform .16s ease;
      font-size: 18px;
    }

    :where(.page-sa-tp) .tp-acc-body {
      border-top: 1px solid #eef3f6;
      background: #fff;
      padding: 14px;
    }

    /* DROPZONE */
    :where(.page-sa-tp) .tp-dropzone {
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

    :where(.page-sa-tp) .tp-dropzone:hover {
      border-color: rgba(24, 79, 97, .70);
      box-shadow: 0 0 0 4px rgba(24, 79, 97, .12), 0 12px 20px rgba(2, 8, 23, .06);
      transform: translateY(-1px);
    }

    :where(.page-sa-tp) .tp-acc-item.has-file .tp-dropzone {
      border-style: solid;
      border-color: rgba(34, 197, 94, .90);
      background: rgba(34, 197, 94, .05);
      box-shadow: 0 0 0 4px rgba(34, 197, 94, .09), 0 12px 20px rgba(2, 8, 23, .05);
      transform: translateY(-1px);
    }

    :where(.page-sa-tp) .tp-file-hidden {
      display: none;
    }

    :where(.page-sa-tp) .tp-drop-ic {
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

    :where(.page-sa-tp) .tp-drop-title {
      color: var(--navy2);
      font-size: 16px;
    }

    :where(.page-sa-tp) .tp-drop-sub {
      color: var(--muted);
      font-size: 14px;
    }

    :where(.page-sa-tp) .tp-drop-meta {
      color: #94a3b8;
      font-size: 13px;
    }

    :where(.page-sa-tp) .tp-drop-btn {
      margin-top: 8px;
      background: var(--navy2);
      color: #fff;
      font-size: 16px;
      padding: 10px 18px;
      border-radius: 10px;
      transition: transform .14s ease, box-shadow .14s ease;
    }

    :where(.page-sa-tp) .tp-dropzone:hover .tp-drop-btn {
      transform: translateY(-1px);
      box-shadow: 0 10px 16px rgba(2, 8, 23, .08);
    }

    /* PREVIEW */
    :where(.page-sa-tp) .tp-preview-wrap {
      width: 100%;
      margin-top: 12px;
      border-top: 1px solid rgba(2, 8, 23, .06);
      padding-top: 12px;
      text-align: left;
    }

    :where(.page-sa-tp) .tp-preview-title {
      color: var(--navy2);
      font-size: 14px;
      margin-bottom: 10px;
    }

    :where(.page-sa-tp) .tp-preview-list {
      display: grid;
      gap: 10px;
    }

    :where(.page-sa-tp) .tp-preview-item {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 10px;
      padding: 10px 10px;
      border: 1px solid rgba(2, 8, 23, .08);
      border-radius: 12px;
      background: #fff;
    }

    :where(.page-sa-tp) .tp-preview-left {
      display: flex;
      align-items: center;
      gap: 10px;
      min-width: 0;
      flex: 1 1 auto;
    }

    :where(.page-sa-tp) .tp-preview-thumb {
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

    :where(.page-sa-tp) .tp-preview-thumb img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
    }

    :where(.page-sa-tp) .tp-preview-info {
      min-width: 0;
    }

    :where(.page-sa-tp) .tp-preview-name {
      font-size: 14px;
      color: #0f172a;
      word-break: break-word;
      line-height: 1.35;
    }

    :where(.page-sa-tp) .tp-preview-meta {
      font-size: 12px;
      color: #64748b;
      margin-top: 2px;
    }

    :where(.page-sa-tp) .tp-preview-remove {
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
      transition: transform .14s ease, box-shadow .14s ease, border-color .14s ease;
    }

    :where(.page-sa-tp) .tp-preview-remove i {
      font-size: 18px;
      line-height: 1;
      display: block;
      transform: translateY(0.5px);
    }

    :where(.page-sa-tp) .tp-preview-remove:hover {
      transform: translateY(-1px);
      box-shadow: 0 10px 16px rgba(2, 8, 23, .08);
      border-color: rgba(24, 79, 97, .35);
    }

    @media(max-width:1100px) {
      :where(.page-sa-tp) .tp-actions {
        flex-direction: column;
      }

      :where(.page-sa-tp) .tp-btn {
        justify-content: center;
      }
    }

    /* Spacing konsisten */
    :where(.page-sa-tp) .tp-cardbox {
      background: #fff !important;
      border-radius: 14px !important;
      box-shadow: 0 10px 20px rgba(2, 8, 23, .06) !important;
      border: 1px solid #eef3f6 !important;
      margin-bottom: 14px !important;
      overflow: hidden !important;
    }

    :where(.page-sa-tp) .tp-cardbox>div {
      padding: 18px 18px 18px !important;
    }

    :where(.page-sa-tp) .tp-grid {
      padding: 0 !important;
      gap: 14px 18px !important;

      display: grid !important;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    }

    :where(.page-sa-tp) .tp-grid-2col {
      grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    }

    :where(.page-sa-tp) .tp-full {
      grid-column: 1 / -1 !important;
    }

    :where(.page-sa-tp) .tp-divider {
      margin-left: 0 !important;
      margin-right: 0 !important;
    }

    :where(.page-sa-tp) .tp-acc {
      padding: 0 !important;
      display: grid !important;
      gap: 14px !important;
    }

    :where(.page-sa-tp) .tp-help {
      margin: 0 0 12px !important;
      font-size: 15px;
      color: #64748b;
    }

    /* E. Dokumen Tidak Dipersyaratkan */
    :where(.page-sa-tp) .tp-nondoc-wrap {
      border: 1px solid #eef3f6;
      border-radius: 14px;
      background: #fff;
      box-shadow: 0 10px 18px rgba(2, 8, 23, .05);
      overflow: hidden;
    }

    :where(.page-sa-tp) .tp-nondoc-head {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      padding: 12px 14px;
      background: #dff1ff;
      color: var(--navy2);
      border-bottom: 1px solid #eef3f6;
    }

    :where(.page-sa-tp) .tp-nondoc-title {
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 16px;
      color: var(--navy2);
    }

    :where(.page-sa-tp) .tp-nondoc-title i {
      font-size: 18px;
    }

    :where(.page-sa-tp) .tp-nondoc-actions {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    :where(.page-sa-tp) .tp-nondoc-btn {
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

    :where(.page-sa-tp) .tp-nondoc-btn:hover {
      transform: translateY(-1px);
      box-shadow: 0 12px 18px rgba(2, 8, 23, .08);
      border-color: rgba(24, 79, 97, .35);
    }

    :where(.page-sa-tp) .tp-nondoc-box {
      padding: 14px;
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px;
      max-height: 380px;
      overflow: auto;
    }

    @media(max-width:900px) {
      :where(.page-sa-tp) .tp-nondoc-box {
        grid-template-columns: 1fr;
      }
    }

    :where(.page-sa-tp) .tp-nondoc-item {
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

    :where(.page-sa-tp) .tp-nondoc-item:hover {
      transform: translateY(-1px);
      box-shadow: 0 12px 18px rgba(2, 8, 23, .08);
      border-color: rgba(24, 79, 97, .35);
    }

    :where(.page-sa-tp) .tp-nondoc-item input {
      display: none;
    }

    :where(.page-sa-tp) .tp-nondoc-check {
      width: 18px;
      height: 18px;
      border-radius: 6px;
      border: 2px solid var(--navy2);
      flex: 0 0 auto;
      margin-top: 1px;
      position: relative;
    }

    :where(.page-sa-tp) .tp-nondoc-text {
      font-size: 15px;
      color: #0f172a;
      line-height: 1.35;
    }

    :where(.page-sa-tp) .tp-nondoc-item.is-checked {
      background: rgba(24, 79, 97, .08);
      border-color: rgba(24, 79, 97, .35);
    }

    :where(.page-sa-tp) .tp-nondoc-item.is-checked .tp-nondoc-check::after {
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

    :where(.page-sa-tp) .tp-nondoc-selected {
      border-top: 1px solid rgba(2, 8, 23, .06);
      padding: 12px 14px 14px;
      background: #fff;
    }

    :where(.page-sa-tp) .tp-nondoc-selected-title {
      color: var(--navy2);
      font-size: 14px;
      margin-bottom: 10px;
    }

    :where(.page-sa-tp) .tp-nondoc-chips {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
    }

    :where(.page-sa-tp) .tp-nondoc-chip {
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

    .sa-toast-wrap {
      position: fixed;
      top: 24px;
      right: 24px;
      z-index: 999999;
    }

    .sa-toast-success {
      min-width: 320px;
      background: #198754;
      color: #fff;
      padding: 16px 18px;
      border-radius: 14px;
      display: flex;
      align-items: center;
      gap: 12px;
      font-weight: 600;
      box-shadow: 0 12px 25px rgba(0, 0, 0, .18);
      animation: saToast .25s ease;
    }

    .sa-toast-success i {
      font-size: 20px;
    }

    @keyframes saToast {
      from {
        opacity: 0;
        transform: translateX(40px);
      }

      to {
        opacity: 1;
        transform: translateX(0);
      }
    }

    @media (min-width: 1025px) {
      :where(.page-sa-tp) .mob-topbar {
        display: none !important;
      }

      :where(.page-sa-tp) .sidebar-drawer-backdrop {
        display: none !important;
      }
    }

    @media (max-width: 1024px) {

      :where(.page-sa-tp) .mob-topbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 16px;
        background: #184f61;
        position: sticky;
        top: 0;
        z-index: 200;
      }

      :where(.page-sa-tp) .mob-logo {
        display: flex;
        align-items: center;
        gap: 10px;
      }

      :where(.page-sa-tp) .mob-logo img {
        width: 32px;
        height: 32px;
      }

      :where(.page-sa-tp) .mob-logo-txt {
        color: #f4c542;
        font-size: 17px;
        font-weight: 700;
      }

      :where(.page-sa-tp) .sidebar-drawer-backdrop {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, .45);
        backdrop-filter: blur(4px);
        z-index: 300;
      }

      :where(.page-sa-tp) .sidebar-drawer-backdrop.is-open {
        display: block;
      }

      :where(.page-sa-tp) .dash-sidebar {
        position: fixed !important;
        top: 0 !important;
        left: -280px !important;
        height: 100vh !important;
        width: 260px !important;
        z-index: 400 !important;
        transition: left .25s ease !important;
        overflow-y: auto !important;
      }

      :where(.page-sa-tp) .dash-sidebar.drawer-open {
        left: 0 !important;
        box-shadow: 4px 0 24px rgba(2, 8, 23, .18) !important;
      }

      :where(.page-sa-tp) .dash-wrap {
        display: block !important;
        height: auto !important;
        min-height: 100vh !important;
        overflow: visible !important;
      }

      html,
      body {
        height: auto !important;
        overflow-y: auto !important;
        overflow-x: hidden !important;
      }

      :where(.page-sa-tp) .dash-main {
        height: auto !important;
        overflow-y: visible !important;
        padding: 20px !important;
      }
    }

    @media (max-width: 768px) {

      :where(.page-sa-tp) .dash-main {
        padding: 12px !important;
      }

      :where(.page-sa-tp) .dash-header h1 {
        font-size: 20px !important;
      }

      :where(.page-sa-tp) .dash-header p {
        font-size: 13px !important;
      }

      :where(.page-sa-tp) .tp-grid {
        grid-template-columns: 1fr !important;
      }

      :where(.page-sa-tp) .tp-full {
        grid-column: unset !important;
      }

      :where(.page-sa-tp) .tp-actions {
        flex-direction: row !important;
        justify-content: space-between !important;
      }

      :where(.page-sa-tp) .tp-btn {
        flex: 1 !important;
        justify-content: center !important;
      }
    }

    :where(.page-sa-tp) .tp-actions {
      display: flex !important;
      justify-content: space-between !important;
      flex-direction: row !important;
    }

    :where(.page-sa-tp) .mob-ham {
      width: 40px !important;
      height: 40px !important;
      border: 1px solid #e6eef2 !important;
      border-radius: 10px !important;
      background: #fff !important;
      display: flex !important;
      align-items: center !important;
      justify-content: center !important;
      cursor: pointer !important;
      font-size: 18px !important;
      color: #184f61 !important;
    }

    /* ===================================================
   RESPONSIVE SUPERADMIN TAMBAH PENGADAAN
=================================================== */

    @media (max-width: 1200px) {

      :where(.page-sa-tp) .tp-grid {
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)) !important;
      }

    }

    @media (max-width:768px) {

      :where(.page-sa-tp) .tp-actions {
        display: flex !important;
        flex-direction: row !important;
        gap: 16px !important;
      }

      :where(.page-sa-tp) .tp-btn {
        flex: 1 !important;
        width: auto !important;
        min-width: 0 !important;
        justify-content: center !important;
      }

    }

    :where(.page-sa-tp) .tp-actions {
      display: flex !important;
      flex-direction: row !important;
      justify-content: space-between !important;
      align-items: center !important;
      gap: 12px !important;
    }

    :where(.page-sa-tp) .tp-actions .tp-btn {
      flex: 0 0 auto !important;
      width: auto !important;
    }

    :where(.page-sa-tp) .tp-btn-same {
      min-width: 210px !important;
      width: auto !important;
      height: 46px !important;
      padding: 12px 16px !important;
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
    document.addEventListener('DOMContentLoaded', () => {
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
    // ✅ Custom dropdown (sama persis PPK: highlight selected pakai navy2)
    const initCustomDropdowns = () => {
      document.querySelectorAll('.tp-dd').forEach(dd => {
        const sel = dd.querySelector('select.tp-select-native');
        const btn = dd.querySelector('.tp-dd-btn');
        const menu = dd.querySelector('.tp-dd-menu');
        if (!sel || !btn || !menu) return;

        const getPlaceholderText = () => {
          const first = sel.querySelector('option[disabled][hidden]') || sel.querySelector('option[disabled]');
          return first ? (first.textContent || '').trim() : 'Pilih';
        };
        const getSelectedText = () => {
          const opt = sel.options[sel.selectedIndex];
          return opt ? (opt.textContent || '').trim() : '';
        };

        const close = () => {
          dd.classList.remove('open');
          btn.setAttribute('aria-expanded', 'false');
        };
        const open = () => {
          dd.classList.add('open');
          btn.setAttribute('aria-expanded', 'true');
          const cur = menu.querySelector('.tp-dd-opt.is-selected');
          if (cur) cur.scrollIntoView({
            block: 'nearest'
          });
        };

        const syncButton = () => {
          const val = (sel.value || '').trim();
          const isPlaceholder = (val === '');
          btn.textContent = isPlaceholder ? getPlaceholderText() : getSelectedText();
          btn.classList.toggle('is-placeholder', isPlaceholder);
        };

        const rebuildMenu = () => {
          menu.innerHTML = '';
          Array.from(sel.options).forEach((opt) => {
            if (opt.disabled && opt.hidden) return;
            const item = document.createElement('button');
            item.type = 'button';
            item.className = 'tp-dd-opt';
            item.setAttribute('role', 'option');
            item.dataset.value = opt.value;
            item.textContent = (opt.textContent || '').trim();
            const isSelected = (String(opt.value) === String(sel.value));
            item.classList.toggle('is-selected', isSelected);
            item.setAttribute('aria-selected', isSelected ? 'true' : 'false');
            item.addEventListener('click', () => {
              sel.value = opt.value;
              sel.dispatchEvent(new Event('change', {
                bubbles: true
              }));
              rebuildMenu();
              syncButton();
              close();
            });
            menu.appendChild(item);
          });
        };

        syncButton();
        rebuildMenu();

        btn.addEventListener('click', (ev) => {
          ev.preventDefault();
          const isOpen = dd.classList.contains('open');
          document.querySelectorAll('.tp-dd.open').forEach(o => {
            if (o !== dd) o.classList.remove('open');
          });
          isOpen ? close() : open();
        });
        sel.addEventListener('change', () => {
          syncButton();
          rebuildMenu();
        });
        document.addEventListener('click', (ev) => {
          if (!dd.contains(ev.target)) close();
        });
        document.addEventListener('keydown', (ev) => {
          if (ev.key === 'Escape') close();
        });
      });
    };

    document.addEventListener('DOMContentLoaded', function() {
      initCustomDropdowns();

      // set active radio sesuai checked
      document.querySelectorAll('.tp-radio-wrap').forEach(wrap => {
        wrap.querySelectorAll('.tp-radio-card').forEach(c => c.classList.remove('active'));
        const checked = wrap.querySelector('input[type="radio"]:checked');
        if (checked) checked.closest('.tp-radio-card').classList.add('active');
      });

      // Inject span count di header accordion
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

      // Accordion: default CLOSED
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
            input.value = ''; // reset supaya bisa pilih file yang sama
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
        return (kb / 1024).toFixed(1) + ' MB';
      };

      // Multi-file append + remove X (sama persis PPK)
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

        const clearPreview = () => {
          if (previewList) previewList.innerHTML = '';
          if (previewWrap) previewWrap.hidden = true;
        };

        const renderPreview = () => {
          clearPreview();
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

          if (previewWrap) previewWrap.hidden = (storedFiles.length === 0);
        };

        const syncUI = () => {
          const hasFile = storedFiles.length > 0;
          item.classList.toggle('has-file', hasFile);

          if (headCount) {
            if (hasFile) {
              const n = storedFiles.length;
              headCount.textContent = (n === 1) ? '1 file sudah terupload' : (n + ' file sudah terupload');
              headCount.hidden = false;
            } else {
              headCount.textContent = '';
              headCount.hidden = true;
            }
          }

          if (hasFile) {
            if (title) title.textContent = storedFiles.length === 1 ? storedFiles[0].name : (storedFiles.length + ' file dipilih');
            if (sub) sub.textContent = 'File dipilih';
            if (btn) btn.textContent = 'Tambah File';
            renderPreview();
          } else {
            if (title && title.dataset.defaultText) title.textContent = title.dataset.defaultText;
            if (sub && sub.dataset.defaultText) sub.textContent = sub.dataset.defaultText;
            if (btn && btn.dataset.defaultText) btn.textContent = btn.dataset.defaultText;
            clearPreview();
          }
        };

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
          syncUI();
        });

        // ✅ DRAG & DROP
        zone.addEventListener('dragover', (ev) => {
          ev.preventDefault();
          ev.stopPropagation();
          zone.classList.add('tp-dropzone--dragging');
        });

        zone.addEventListener('dragleave', (ev) => {
          ev.preventDefault();
          ev.stopPropagation();
          if (!zone.contains(ev.relatedTarget)) {
            zone.classList.remove('tp-dropzone--dragging');
          }
        });

        zone.addEventListener('drop', (ev) => {
          ev.preventDefault();
          ev.stopPropagation();
          zone.classList.remove('tp-dropzone--dragging');

          const dropped = ev.dataTransfer?.files
            ? Array.from(ev.dataTransfer.files)
            : [];

          if (dropped.length) {
            const existing = new Set(storedFiles.map(fileKey));
            dropped.forEach(f => {
              const k = fileKey(f);
              if (!existing.has(k)) {
                storedFiles.push(f);
                existing.add(k);
              }
            });
            rebuildInputFiles();
            syncUI();
          }
        });

        storedFiles = [];
        rebuildInputFiles();
        syncUI();
      });

      /* =========================================================
         E. Dokumen Tidak Dipersyaratkan (sama persis PPK)
         ========================================================= */
      const listWrap = document.getElementById('tp-nondoc-list');
      const jsonInput = document.getElementById('tp-nondoc-json');
      const chipsWrap = document.getElementById('tp-nondoc-chips');
      const selectedBox = document.getElementById('tp-nondoc-selected');
      const btnClear = document.getElementById('tp-nondoc-clear');

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

        titles.forEach((title, idx) => {
          const label = document.createElement('label');
          label.className = 'tp-nondoc-item';

          const input = document.createElement('input');
          input.type = 'checkbox';
          input.name = 'dokumen_tidak_dipersyaratkan[]';
          input.value = title;
          input.id = 'tp_nondoc_' + idx;

          const box = document.createElement('span');
          box.className = 'tp-nondoc-check';

          const txt = document.createElement('span');
          txt.className = 'tp-nondoc-text';
          txt.textContent = title;

          label.appendChild(input);
          label.appendChild(box);
          label.appendChild(txt);

          label.addEventListener('click', (ev) => {
            if (ev.target && ev.target.tagName === 'A') return;
            ev.preventDefault();
            toggleItem(title, !input.checked, label, input);
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

  @if(session('success'))
  <div class="sa-toast-wrap">
    <div class="sa-toast-success">
      <i class="bi bi-check-circle-fill"></i>
      <span>{{ session('success') }}</span>
    </div>
  </div>

  <script>
    setTimeout(() => {
      window.location.href =
        "{{ route('superadmin.arsip') }}";
    }, 1500);
  </script>
  @endif
  @include('Partials.chatbot')

</body>

</html>