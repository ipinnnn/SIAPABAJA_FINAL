{{-- resources/views/unit/tambah_pengadaan.blade.php --}}
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

{{-- ✅ Tambah 1 class khusus page ini biar CSS inline TIDAK ngubah UI page lain --}}

<body class="dash-body page-unit-tp">
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

  {{-- BACKDROP DRAWER --}}
  <div class="sidebar-drawer-backdrop" id="sidebarBackdrop"></div>
  @php
  // Nama yang tampil di sidebar (fallback)
  $unitName = $unitName ?? (auth()->user()->name ?? 'Unit Kerja');

  // Unit default dari akun login (role unit)
  $selectedUnitId = $selectedUnitId ?? (auth()->check() ? auth()->user()->unit_id : null);
  $selectedUnitName = $selectedUnitName ?? null;

  // Fallback ambil nama unit dari DB bila perlu
  if (empty($selectedUnitName) && !empty($selectedUnitId)) {
  try {
  $selectedUnitName = \App\Models\Unit::find($selectedUnitId)?->nama;
  } catch (\Throwable $e) {}
  }

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
  @endphp

  <div class="dash-wrap">
    {{-- SIDEBAR (SAMA PERSIS DENGAN DASHBOARD UNIT) Pure Unit --}}
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
        <a class="dash-link" href="{{ url('/unit/dashboard') }}">
          <span class="ic"><i class="bi bi-grid-fill"></i></span>
          Dashboard
        </a>

        <a class="dash-link" href="{{ url('/unit/arsip') }}">
          <span class="ic"><i class="bi bi-archive"></i></span>
          Arsip PBJ
        </a>

        <a class="dash-link active" href="{{ url('/unit/pengadaan/tambah') }}">
          <span class="ic"><i class="bi bi-plus-square"></i></span>
          Tambah Pengadaan
        </a>

        <a class="dash-link" href="{{ route('unit.kelola.akun') }}">
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

      <form action="{{ url('/unit/pengadaan/store') }}" method="POST" class="tp-form" enctype="multipart/form-data">
        @csrf

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
                      <option value="" selected disabled hidden>Tahun</option>
                      @foreach($tahunOptions as $t)
                      <option value="{{ $t }}">{{ $t }}</option>
                      @endforeach
                    </select>
                    <i class="bi bi-chevron-down tp-icon"></i>
                  </div>
                </div>

                <div class="tp-field">
                  <label class="tp-label">Unit Kerja</label>
                  <div class="tp-control">
                    @if(auth()->check() && auth()->user()->role === 'unit')
                    {{-- ✅ ROLE UNIT: unit otomatis dari akun (tidak bisa diganti) --}}
                    <select name="unit_kerja" class="tp-select" disabled>
                      <option value="{{ $selectedUnitName ?? $unitName }}" selected>{{ $selectedUnitName ?? $unitName }}</option>
                    </select>
                    {{-- karena disabled tidak ikut terkirim saat submit --}}
                    <input type="hidden" name="unit_id" value="{{ $selectedUnitId ?? auth()->user()->unit_id }}">
                    <input type="hidden" name="unit_kerja" value="{{ $selectedUnitName ?? $unitName }}">
                    @else
                    {{-- fallback / jika bukan role unit: tetap bisa pilih --}}
                    <select name="unit_kerja" class="tp-select" required>
                      <option value="" selected disabled hidden>Pilih Unit</option>
                      @foreach($unitOptions as $u)
                      <option value="{{ $u }}">{{ $u }}</option>
                      @endforeach
                    </select>
                    @endif
                  </div>
                </div>

                <div class="tp-field tp-full">
                  <label class="tp-label">Nama Pekerjaan</label>
                  <input type="text" name="nama_pekerjaan" class="tp-input" placeholder="Nama Pekerjaan" />
                </div>

                <div class="tp-field">
                  <label class="tp-label">ID RUP</label>
                  <input type="text" name="id_rup" class="tp-input" placeholder="RUP-xxxx-xxxx-xxx-xx" />
                </div>

                <div class="tp-field">
                  <label class="tp-label">Jenis Pengadaan</label>
                  <div class="tp-control">
                    <select name="jenis_pengadaan" class="tp-select" required>

                      <option value="" {{ old('jenis_pengadaan') ? '' : 'selected' }} disabled hidden>
                        Pilih Jenis Pengadaan
                      </option>

                      @foreach($jenisPengadaanOptions as $jp)
                      <option value="{{ $jp }}" {{ old('jenis_pengadaan') == $jp ? 'selected' : '' }}>
                        {{ $jp }}
                      </option>
                      @endforeach

                    </select>
                    <i class="bi bi-chevron-down tp-icon"></i>
                  </div>
                </div>

                <div class="tp-field">
                  <label class="tp-label">Metode Pengadaan</label>
                  <div class="tp-control">
                    <select name="metode_pengadaan" class="tp-select" required>

                      <option value="" {{ old('metode_pengadaan') ? '' : 'selected' }} disabled hidden>
                        Pilih Metode Pengadaan
                      </option>

                      @foreach($metodePengadaanOptions as $mp)
                      <option value="{{ $mp }}" {{ old('metode_pengadaan') == $mp ? 'selected' : '' }}>
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
                      <option value="" selected disabled hidden>Pilih Status Pekerjaan</option>
                      @foreach($statusPekerjaanOptions as $sp)
                      <option value="{{ $sp }}">{{ $sp }}</option>
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

              <div class="tp-grid">
                <div class="tp-field">
                  <label class="tp-label">Pagu Anggaran (Rp)</label>
                  <input type="text" name="pagu_anggaran" class="tp-input" placeholder="Rp" />
                </div>

                <div class="tp-field">
                  <label class="tp-label">HPS (Rp)</label>
                  <input type="text" name="hps" class="tp-input" placeholder="Rp" />
                </div>

                <div class="tp-field">
                  <label class="tp-label">Nilai Kontrak (Rp)</label>
                  <input type="text" name="nilai_kontrak" class="tp-input" placeholder="Rp" />
                </div>

                <div class="tp-field">
                  <label class="tp-label">Nama Rekanan</label>
                  <input type="text" name="nama_rekanan" class="tp-input" placeholder="Nama Rekanan" />
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

                {{-- 1 --}}
                <div class="tp-acc-item">
                  <button type="button" class="tp-acc-head" aria-expanded="true">
                    <span class="tp-acc-left">
                      <i class="bi bi-file-earmark-text"></i>
                      Kerangka Acuan Kerja atau KAK
                    </span>
                    <span class="tp-acc-right">
                      <i class="bi bi-chevron-down tp-acc-ic"></i>
                    </span>
                  </button>
                  <div class="tp-acc-body">
                    <div class="tp-upload-row" style="margin-bottom:0;">
                      <label class="tp-dropzone">
                        <input type="file" name="dokumen_kak[]" class="tp-file-hidden" multiple />
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

                {{-- 2 --}}
                <div class="tp-acc-item">
                  <button type="button" class="tp-acc-head" aria-expanded="true">
                    <span class="tp-acc-left">
                      <i class="bi bi-file-earmark-text"></i>
                      Harga Perkiraan Sendiri atau HPS
                    </span>
                    <span class="tp-acc-right">
                      <i class="bi bi-chevron-down tp-acc-ic"></i>
                    </span>
                  </button>
                  <div class="tp-acc-body">
                    <div class="tp-upload-row" style="margin-bottom:0;">
                      <label class="tp-dropzone">
                        <input type="file" name="dokumen_hps[]" class="tp-file-hidden" multiple />
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

                {{-- 3 --}}
                <div class="tp-acc-item">
                  <button type="button" class="tp-acc-head" aria-expanded="true">
                    <span class="tp-acc-left">
                      <i class="bi bi-file-earmark-text"></i>
                      Spesifikasi Teknis
                    </span>
                    <span class="tp-acc-right">
                      <i class="bi bi-chevron-down tp-acc-ic"></i>
                    </span>
                  </button>
                  <div class="tp-acc-body">
                    <div class="tp-upload-row" style="margin-bottom:0;">
                      <label class="tp-dropzone">
                        <input type="file" name="dokumen_spesifikasi_teknis[]" class="tp-file-hidden" multiple />
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

                {{-- 4 --}}
                <div class="tp-acc-item">
                  <button type="button" class="tp-acc-head" aria-expanded="true">
                    <span class="tp-acc-left">
                      <i class="bi bi-file-earmark-text"></i>
                      Rancangan Kontrak
                    </span>
                    <span class="tp-acc-right">
                      <i class="bi bi-chevron-down tp-acc-ic"></i>
                    </span>
                  </button>
                  <div class="tp-acc-body">
                    <div class="tp-upload-row" style="margin-bottom:0;">
                      <label class="tp-dropzone">
                        <input type="file" name="dokumen_rancangan_kontrak[]" class="tp-file-hidden" multiple />
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

                {{-- 5 --}}
                <div class="tp-acc-item">
                  <button type="button" class="tp-acc-head" aria-expanded="true">
                    <span class="tp-acc-left">
                      <i class="bi bi-file-earmark-text"></i>
                      Lembar Data Kualifikasi
                    </span>
                    <span class="tp-acc-right">
                      <i class="bi bi-chevron-down tp-acc-ic"></i>
                    </span>
                  </button>
                  <div class="tp-acc-body">
                    <div class="tp-upload-row" style="margin-bottom:0;">
                      <label class="tp-dropzone">
                        <input type="file" name="dokumen_lembar_data_kualifikasi[]" class="tp-file-hidden" multiple />
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

                {{-- 6 --}}
                <div class="tp-acc-item">
                  <button type="button" class="tp-acc-head" aria-expanded="true">
                    <span class="tp-acc-left">
                      <i class="bi bi-file-earmark-text"></i>
                      Lembar Data Pemilihan
                    </span>
                    <span class="tp-acc-right">
                      <i class="bi bi-chevron-down tp-acc-ic"></i>
                    </span>
                  </button>
                  <div class="tp-acc-body">
                    <div class="tp-upload-row" style="margin-bottom:0;">
                      <label class="tp-dropzone">
                        <input type="file" name="dokumen_lembar_data_pemilihan[]" class="tp-file-hidden" multiple />
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

                {{-- 7 --}}
                <div class="tp-acc-item">
                  <button type="button" class="tp-acc-head" aria-expanded="true">
                    <span class="tp-acc-left">
                      <i class="bi bi-file-earmark-text"></i>
                      Daftar Kuantitas dan Harga
                    </span>
                    <span class="tp-acc-right">
                      <i class="bi bi-chevron-down tp-acc-ic"></i>
                    </span>
                  </button>
                  <div class="tp-acc-body">
                    <div class="tp-upload-row" style="margin-bottom:0;">
                      <label class="tp-dropzone">
                        <input type="file" name="dokumen_daftar_kuantitas_harga[]" class="tp-file-hidden" multiple />
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

                {{-- 8 --}}
                <div class="tp-acc-item">
                  <button type="button" class="tp-acc-head" aria-expanded="true">
                    <span class="tp-acc-left">
                      <i class="bi bi-file-earmark-text"></i>
                      Jadwal dan Lokasi Pekerjaan
                    </span>
                    <span class="tp-acc-right">
                      <i class="bi bi-chevron-down tp-acc-ic"></i>
                    </span>
                  </button>
                  <div class="tp-acc-body">
                    <div class="tp-upload-row" style="margin-bottom:0;">
                      <label class="tp-dropzone">
                        <input type="file" name="dokumen_jadwal_lokasi_pekerjaan[]" class="tp-file-hidden" multiple />
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

                {{-- 9 --}}
                <div class="tp-acc-item">
                  <button type="button" class="tp-acc-head" aria-expanded="true">
                    <span class="tp-acc-left">
                      <i class="bi bi-file-earmark-text"></i>
                      Gambar Rancangan Pekerjaan
                    </span>
                    <span class="tp-acc-right">
                      <i class="bi bi-chevron-down tp-acc-ic"></i>
                    </span>
                  </button>
                  <div class="tp-acc-body">
                    <div class="tp-upload-row" style="margin-bottom:0;">
                      <label class="tp-dropzone">
                        <input type="file" name="dokumen_gambar_rancangan_pekerjaan[]" class="tp-file-hidden" multiple />
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

                {{-- 10 --}}
                <div class="tp-acc-item">
                  <button type="button" class="tp-acc-head" aria-expanded="true">
                    <span class="tp-acc-left">
                      <i class="bi bi-file-earmark-text"></i>
                      Dokumen Analisis Mengenai Dampak Lingkungan atau AMDAL
                    </span>
                    <span class="tp-acc-right">
                      <i class="bi bi-chevron-down tp-acc-ic"></i>
                    </span>
                  </button>
                  <div class="tp-acc-body">
                    <div class="tp-upload-row" style="margin-bottom:0;">
                      <label class="tp-dropzone">
                        <input type="file" name="dokumen_amdal[]" class="tp-file-hidden" multiple />
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

                {{-- 11 --}}
                <div class="tp-acc-item">
                  <button type="button" class="tp-acc-head" aria-expanded="true">
                    <span class="tp-acc-left">
                      <i class="bi bi-file-earmark-text"></i>
                      Dokumen Penawaran
                    </span>
                    <span class="tp-acc-right">
                      <i class="bi bi-chevron-down tp-acc-ic"></i>
                    </span>
                  </button>
                  <div class="tp-acc-body">
                    <div class="tp-upload-row" style="margin-bottom:0;">
                      <label class="tp-dropzone">
                        <input type="file" name="dokumen_penawaran[]" class="tp-file-hidden" multiple />
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

                {{-- 12 --}}
                <div class="tp-acc-item">
                  <button type="button" class="tp-acc-head" aria-expanded="true">
                    <span class="tp-acc-left">
                      <i class="bi bi-file-earmark-text"></i>
                      Surat Penawaran
                    </span>
                    <span class="tp-acc-right">
                      <i class="bi bi-chevron-down tp-acc-ic"></i>
                    </span>
                  </button>
                  <div class="tp-acc-body">
                    <div class="tp-upload-row" style="margin-bottom:0;">
                      <label class="tp-dropzone">
                        <input type="file" name="surat_penawaran[]" class="tp-file-hidden" multiple />
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

                {{-- 13 --}}
                <div class="tp-acc-item">
                  <button type="button" class="tp-acc-head" aria-expanded="true">
                    <span class="tp-acc-left">
                      <i class="bi bi-file-earmark-text"></i>
                      Sertifikat atau Lisensi Kemenkumham
                    </span>
                    <span class="tp-acc-right">
                      <i class="bi bi-chevron-down tp-acc-ic"></i>
                    </span>
                  </button>
                  <div class="tp-acc-body">
                    <div class="tp-upload-row" style="margin-bottom:0;">
                      <label class="tp-dropzone">
                        <input type="file" name="dokumen_kemenkumham[]" class="tp-file-hidden" multiple />
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

                {{-- 14 --}}
                <div class="tp-acc-item">
                  <button type="button" class="tp-acc-head" aria-expanded="true">
                    <span class="tp-acc-left">
                      <i class="bi bi-file-earmark-text"></i>
                      Berita Acara Pemberian Penjelasan
                    </span>
                    <span class="tp-acc-right">
                      <i class="bi bi-chevron-down tp-acc-ic"></i>
                    </span>
                  </button>
                  <div class="tp-acc-body">
                    <div class="tp-upload-row" style="margin-bottom:0;">
                      <label class="tp-dropzone">
                        <input type="file" name="ba_pemberian_penjelasan[]" class="tp-file-hidden" multiple />
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

                {{-- 15 --}}
                <div class="tp-acc-item">
                  <button type="button" class="tp-acc-head" aria-expanded="true">
                    <span class="tp-acc-left">
                      <i class="bi bi-file-earmark-text"></i>
                      Berita Acara Pengumuman Negosiasi
                    </span>
                    <span class="tp-acc-right">
                      <i class="bi bi-chevron-down tp-acc-ic"></i>
                    </span>
                  </button>
                  <div class="tp-acc-body">
                    <div class="tp-upload-row" style="margin-bottom:0;">
                      <label class="tp-dropzone">
                        <input type="file" name="ba_pengumuman_negosiasi[]" class="tp-file-hidden" multiple />
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

                {{-- 16 --}}
                <div class="tp-acc-item">
                  <button type="button" class="tp-acc-head" aria-expanded="true">
                    <span class="tp-acc-left">
                      <i class="bi bi-file-earmark-text"></i>
                      Berita Acara Sanggah dan Sanggah Banding
                    </span>
                    <span class="tp-acc-right">
                      <i class="bi bi-chevron-down tp-acc-ic"></i>
                    </span>
                  </button>
                  <div class="tp-acc-body">
                    <div class="tp-upload-row" style="margin-bottom:0;">
                      <label class="tp-dropzone">
                        <input type="file" name="ba_sanggah_banding[]" class="tp-file-hidden" multiple />
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

                {{-- 17 --}}
                <div class="tp-acc-item">
                  <button type="button" class="tp-acc-head" aria-expanded="true">
                    <span class="tp-acc-left">
                      <i class="bi bi-file-earmark-text"></i>
                      Berita Acara Penetapan
                    </span>
                    <span class="tp-acc-right">
                      <i class="bi bi-chevron-down tp-acc-ic"></i>
                    </span>
                  </button>
                  <div class="tp-acc-body">
                    <div class="tp-upload-row" style="margin-bottom:0;">
                      <label class="tp-dropzone">
                        <input type="file" name="ba_penetapan[]" class="tp-file-hidden" multiple />
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

                {{-- 18 --}}
                <div class="tp-acc-item">
                  <button type="button" class="tp-acc-head" aria-expanded="true">
                    <span class="tp-acc-left">
                      <i class="bi bi-file-earmark-text"></i>
                      Laporan Hasil Pemilihan Penyedia
                    </span>
                    <span class="tp-acc-right">
                      <i class="bi bi-chevron-down tp-acc-ic"></i>
                    </span>
                  </button>
                  <div class="tp-acc-body">
                    <div class="tp-upload-row" style="margin-bottom:0;">
                      <label class="tp-dropzone">
                        <input type="file" name="laporan_hasil_pemilihan[]" class="tp-file-hidden" multiple />
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

                {{-- 19 --}}
                <div class="tp-acc-item">
                  <button type="button" class="tp-acc-head" aria-expanded="true">
                    <span class="tp-acc-left">
                      <i class="bi bi-file-earmark-text"></i>
                      Surat Penunjukan Penyedia Barang Jasa atau SPPBJ
                    </span>
                    <span class="tp-acc-right">
                      <i class="bi bi-chevron-down tp-acc-ic"></i>
                    </span>
                  </button>
                  <div class="tp-acc-body">
                    <div class="tp-upload-row" style="margin-bottom:0;">
                      <label class="tp-dropzone">
                        <input type="file" name="dokumen_sppbj[]" class="tp-file-hidden" multiple />
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

                {{-- 20 --}}
                <div class="tp-acc-item">
                  <button type="button" class="tp-acc-head" aria-expanded="true">
                    <span class="tp-acc-left">
                      <i class="bi bi-file-earmark-text"></i>
                      Surat Perjanjian Kemitraan
                    </span>
                    <span class="tp-acc-right">
                      <i class="bi bi-chevron-down tp-acc-ic"></i>
                    </span>
                  </button>
                  <div class="tp-acc-body">
                    <div class="tp-upload-row" style="margin-bottom:0;">
                      <label class="tp-dropzone">
                        <input type="file" name="surat_perjanjian_kemitraan[]" class="tp-file-hidden" multiple />
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

                {{-- 21 --}}
                <div class="tp-acc-item">
                  <button type="button" class="tp-acc-head" aria-expanded="true">
                    <span class="tp-acc-left">
                      <i class="bi bi-file-earmark-text"></i>
                      Surat Perjanjian Swakelola
                    </span>
                    <span class="tp-acc-right">
                      <i class="bi bi-chevron-down tp-acc-ic"></i>
                    </span>
                  </button>
                  <div class="tp-acc-body">
                    <div class="tp-upload-row" style="margin-bottom:0;">
                      <label class="tp-dropzone">
                        <input type="file" name="surat_perjanjian_swakelola[]" class="tp-file-hidden" multiple />
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

                {{-- 22 --}}
                <div class="tp-acc-item">
                  <button type="button" class="tp-acc-head" aria-expanded="true">
                    <span class="tp-acc-left">
                      <i class="bi bi-file-earmark-text"></i>
                      Surat Penugasan Tim Swakelola
                    </span>
                    <span class="tp-acc-right">
                      <i class="bi bi-chevron-down tp-acc-ic"></i>
                    </span>
                  </button>
                  <div class="tp-acc-body">
                    <div class="tp-upload-row" style="margin-bottom:0;">
                      <label class="tp-dropzone">
                        <input type="file" name="surat_penugasan_tim_swakelola[]" class="tp-file-hidden" multiple />
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

                {{-- 23 --}}
                <div class="tp-acc-item">
                  <button type="button" class="tp-acc-head" aria-expanded="true">
                    <span class="tp-acc-left">
                      <i class="bi bi-file-earmark-text"></i>
                      Nota Kesepahaman atau MoU
                    </span>
                    <span class="tp-acc-right">
                      <i class="bi bi-chevron-down tp-acc-ic"></i>
                    </span>
                  </button>
                  <div class="tp-acc-body">
                    <div class="tp-upload-row" style="margin-bottom:0;">
                      <label class="tp-dropzone">
                        <input type="file" name="dokumen_mou[]" class="tp-file-hidden" multiple />
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

                {{-- 24 --}}
                <div class="tp-acc-item">
                  <button type="button" class="tp-acc-head" aria-expanded="true">
                    <span class="tp-acc-left">
                      <i class="bi bi-file-earmark-text"></i>
                      Dokumen Kontrak
                    </span>
                    <span class="tp-acc-right">
                      <i class="bi bi-chevron-down tp-acc-ic"></i>
                    </span>
                  </button>
                  <div class="tp-acc-body">
                    <div class="tp-upload-row" style="margin-bottom:0;">
                      <label class="tp-dropzone">
                        <input type="file" name="dokumen_kontrak[]" class="tp-file-hidden" multiple />
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

                {{-- 25 --}}
                <div class="tp-acc-item">
                  <button type="button" class="tp-acc-head" aria-expanded="true">
                    <span class="tp-acc-left">
                      <i class="bi bi-file-earmark-text"></i>
                      Ringkasan Kontrak
                    </span>
                    <span class="tp-acc-right">
                      <i class="bi bi-chevron-down tp-acc-ic"></i>
                    </span>
                  </button>
                  <div class="tp-acc-body">
                    <div class="tp-upload-row" style="margin-bottom:0;">
                      <label class="tp-dropzone">
                        <input type="file" name="ringkasan_kontrak[]" class="tp-file-hidden" multiple />
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

                {{-- 26 --}}
                <div class="tp-acc-item">
                  <button type="button" class="tp-acc-head" aria-expanded="true">
                    <span class="tp-acc-left">
                      <i class="bi bi-file-earmark-text"></i>
                      Surat Jaminan Pelaksanaan
                    </span>
                    <span class="tp-acc-right">
                      <i class="bi bi-chevron-down tp-acc-ic"></i>
                    </span>
                  </button>
                  <div class="tp-acc-body">
                    <div class="tp-upload-row" style="margin-bottom:0;">
                      <label class="tp-dropzone">
                        <input type="file" name="jaminan_pelaksanaan[]" class="tp-file-hidden" multiple />
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

                {{-- 27 --}}
                <div class="tp-acc-item">
                  <button type="button" class="tp-acc-head" aria-expanded="true">
                    <span class="tp-acc-left">
                      <i class="bi bi-file-earmark-text"></i>
                      Surat Jaminan Uang Muka
                    </span>
                    <span class="tp-acc-right">
                      <i class="bi bi-chevron-down tp-acc-ic"></i>
                    </span>
                  </button>
                  <div class="tp-acc-body">
                    <div class="tp-upload-row" style="margin-bottom:0;">
                      <label class="tp-dropzone">
                        <input type="file" name="jaminan_uang_muka[]" class="tp-file-hidden" multiple />
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

                {{-- 28 --}}
                <div class="tp-acc-item">
                  <button type="button" class="tp-acc-head" aria-expanded="true">
                    <span class="tp-acc-left">
                      <i class="bi bi-file-earmark-text"></i>
                      Surat Jaminan Pemeliharaan
                    </span>
                    <span class="tp-acc-right">
                      <i class="bi bi-chevron-down tp-acc-ic"></i>
                    </span>
                  </button>
                  <div class="tp-acc-body">
                    <div class="tp-upload-row" style="margin-bottom:0;">
                      <label class="tp-dropzone">
                        <input type="file" name="jaminan_pemeliharaan[]" class="tp-file-hidden" multiple />
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

                {{-- 29 --}}
                <div class="tp-acc-item">
                  <button type="button" class="tp-acc-head" aria-expanded="true">
                    <span class="tp-acc-left">
                      <i class="bi bi-file-earmark-text"></i>
                      Surat Tagihan
                    </span>
                    <span class="tp-acc-right">
                      <i class="bi bi-chevron-down tp-acc-ic"></i>
                    </span>
                  </button>
                  <div class="tp-acc-body">
                    <div class="tp-upload-row" style="margin-bottom:0;">
                      <label class="tp-dropzone">
                        <input type="file" name="surat_tagihan[]" class="tp-file-hidden" multiple />
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

                {{-- 30 --}}
                <div class="tp-acc-item">
                  <button type="button" class="tp-acc-head" aria-expanded="true">
                    <span class="tp-acc-left">
                      <i class="bi bi-file-earmark-text"></i>
                      Surat Pesanan Elektronik atau E-Purchasing
                    </span>
                    <span class="tp-acc-right">
                      <i class="bi bi-chevron-down tp-acc-ic"></i>
                    </span>
                  </button>
                  <div class="tp-acc-body">
                    <div class="tp-upload-row" style="margin-bottom:0;">
                      <label class="tp-dropzone">
                        <input type="file" name="surat_pesanan_epurchasing[]" class="tp-file-hidden" multiple />
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

                {{-- 31 --}}
                <div class="tp-acc-item">
                  <button type="button" class="tp-acc-head" aria-expanded="true">
                    <span class="tp-acc-left">
                      <i class="bi bi-file-earmark-text"></i>
                      Surat Perintah Mulai Kerja atau SPMK
                    </span>
                    <span class="tp-acc-right">
                      <i class="bi bi-chevron-down tp-acc-ic"></i>
                    </span>
                  </button>
                  <div class="tp-acc-body">
                    <div class="tp-upload-row" style="margin-bottom:0;">
                      <label class="tp-dropzone">
                        <input type="file" name="dokumen_spmk[]" class="tp-file-hidden" multiple />
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

                {{-- 32 --}}
                <div class="tp-acc-item">
                  <button type="button" class="tp-acc-head" aria-expanded="true">
                    <span class="tp-acc-left">
                      <i class="bi bi-file-earmark-text"></i>
                      Surat Perintah Perjalanan Dinas atau SPPD
                    </span>
                    <span class="tp-acc-right">
                      <i class="bi bi-chevron-down tp-acc-ic"></i>
                    </span>
                  </button>
                  <div class="tp-acc-body">
                    <div class="tp-upload-row" style="margin-bottom:0;">
                      <label class="tp-dropzone">
                        <input type="file" name="dokumen_sppd[]" class="tp-file-hidden" multiple />
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

                {{-- 33 --}}
                <div class="tp-acc-item">
                  <button type="button" class="tp-acc-head" aria-expanded="true">
                    <span class="tp-acc-left">
                      <i class="bi bi-file-earmark-text"></i>
                      Laporan Pelaksanaan Pekerjaan
                    </span>
                    <span class="tp-acc-right">
                      <i class="bi bi-chevron-down tp-acc-ic"></i>
                    </span>
                  </button>
                  <div class="tp-acc-body">
                    <div class="tp-upload-row" style="margin-bottom:0;">
                      <label class="tp-dropzone">
                        <input type="file" name="laporan_pelaksanaan_pekerjaan[]" class="tp-file-hidden" multiple />
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

                {{-- 34 --}}
                <div class="tp-acc-item">
                  <button type="button" class="tp-acc-head" aria-expanded="true">
                    <span class="tp-acc-left">
                      <i class="bi bi-file-earmark-text"></i>
                      Laporan Penyelesaian Pekerjaan
                    </span>
                    <span class="tp-acc-right">
                      <i class="bi bi-chevron-down tp-acc-ic"></i>
                    </span>
                  </button>
                  <div class="tp-acc-body">
                    <div class="tp-upload-row" style="margin-bottom:0;">
                      <label class="tp-dropzone">
                        <input type="file" name="laporan_penyelesaian_pekerjaan[]" class="tp-file-hidden" multiple />
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

                {{-- 35 --}}
                <div class="tp-acc-item">
                  <button type="button" class="tp-acc-head" aria-expanded="true">
                    <span class="tp-acc-left">
                      <i class="bi bi-file-earmark-text"></i>
                      Berita Acara Pembayaran atau BAP
                    </span>
                    <span class="tp-acc-right">
                      <i class="bi bi-chevron-down tp-acc-ic"></i>
                    </span>
                  </button>
                  <div class="tp-acc-body">
                    <div class="tp-upload-row" style="margin-bottom:0;">
                      <label class="tp-dropzone">
                        <input type="file" name="bap[]" class="tp-file-hidden" multiple />
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

                {{-- 36 --}}
                <div class="tp-acc-item">
                  <button type="button" class="tp-acc-head" aria-expanded="true">
                    <span class="tp-acc-left">
                      <i class="bi bi-file-earmark-text"></i>
                      Berita Acara Serah Terima Sementara atau BAST Sementara
                    </span>
                    <span class="tp-acc-right">
                      <i class="bi bi-chevron-down tp-acc-ic"></i>
                    </span>
                  </button>
                  <div class="tp-acc-body">
                    <div class="tp-upload-row" style="margin-bottom:0;">
                      <label class="tp-dropzone">
                        <input type="file" name="bast_sementara[]" class="tp-file-hidden" multiple />
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

                {{-- 37 --}}
                <div class="tp-acc-item">
                  <button type="button" class="tp-acc-head" aria-expanded="true">
                    <span class="tp-acc-left">
                      <i class="bi bi-file-earmark-text"></i>
                      Berita Acara Serah Terima Final atau BAST Final
                    </span>
                    <span class="tp-acc-right">
                      <i class="bi bi-chevron-down tp-acc-ic"></i>
                    </span>
                  </button>
                  <div class="tp-acc-body">
                    <div class="tp-upload-row" style="margin-bottom:0;">
                      <label class="tp-dropzone">
                        <input type="file" name="bast_akhir[]" class="tp-file-hidden" multiple />
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

                {{-- 38 --}}
                <div class="tp-acc-item">
                  <button type="button" class="tp-acc-head" aria-expanded="true">
                    <span class="tp-acc-left">
                      <i class="bi bi-file-earmark-text"></i>
                      Dokumen Pendukung Lainya
                    </span>
                    <span class="tp-acc-right">
                      <i class="bi bi-chevron-down tp-acc-ic"></i>
                    </span>
                  </button>
                  <div class="tp-acc-body">
                    <div class="tp-upload-row" style="margin-bottom:0;">
                      <label class="tp-dropzone">
                        <input type="file" name="dokumen_pendukung_lainya[]" class="tp-file-hidden" multiple />
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

              {{-- Hidden (untuk kebutuhan popup detail / backend) --}}
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

                <div class="tp-nondoc-box" id="tp-nondoc-list">
                  {{-- di-inject oleh JS --}}
                </div>

                <div class="tp-nondoc-selected" id="tp-nondoc-selected" hidden>
                  <div class="tp-nondoc-selected-title">Terpilih</div>
                  <div class="tp-nondoc-chips" id="tp-nondoc-chips"></div>
                </div>
              </div>

            </div>
          </div>
        </section>

        <div class="tp-actions tp-actions-split" style="justify-content:space-between;">
          <a href="{{ url('/unit/dashboard') }}" class="tp-btn tp-btn-ghost tp-btn-same">
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
     ✅ PENTING: SEMUA CSS DI-SCOPE KE .page-ppk-tp
     Biar tidak “ngerusak” UI halaman lain yang juga pakai Unit.css
     ========================================================= */
    :where(.page-unit-tp) {
      /* jangan paksa font-size global; biarkan Unit.css yang atur */
      line-height: 1.6;
      font-weight: 400;
    }

    :where(.page-unit-tp) .dash-app {
      font-weight: 600 !important;
    }

    /* khusus halaman unit */
    .page-unit-tp .dash-header {
      margin-bottom: 18px;
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      text-align: left;
      gap: 6px;
    }

    .page-unit-tp .dash-header h1 {
      margin: 0;
      font-size: 34px;
      font-weight: 700;
      color: #184f61;
    }

    .page-unit-tp .dash-header p {
      margin: 10;
      font-size: 15px;
      color: #184f61;
      opacity: 0.85;
    }

    :where(.page-unit-tp) .dash-role,
    :where(.page-unit-tp) .dash-unit-label,
    :where(.page-unit-tp) .dash-unit-name,
    :where(.page-unit-tp) .dash-link,
    :where(.page-unit-tp) .dash-side-btn,
    :where(.page-unit-tp) .dash-header p,
    :where(.page-unit-tp) .tp-section-title,
    :where(.page-unit-tp) .tp-badge,
    :where(.page-unit-tp) .tp-label,
    :where(.page-unit-tp) .tp-input,
    :where(.page-unit-tp) .tp-select,
    :where(.page-unit-tp) .tp-actions .tp-btn,
    :where(.page-unit-tp) .tp-help,
    :where(.page-unit-tp) .tp-radio-card,
    :where(.page-unit-tp) .tp-radio-text,
    :where(.page-unit-tp) .tp-acc-head,
    :where(.page-unit-tp) .tp-upload-label,
    :where(.page-unit-tp) .tp-drop-title,
    :where(.page-unit-tp) .tp-drop-sub,
    :where(.page-unit-tp) .tp-drop-meta,
    :where(.page-unit-tp) .tp-drop-btn,
    :where(.page-unit-tp) .tp-preview-title,
    :where(.page-unit-tp) .tp-acc-count {
      font-weight: 400 !important;
    }

    :where(.page-unit-tp) .dash-sidebar {
      display: flex;
      flex-direction: column;
    }

    :where(.page-unit-tp) .dash-side-actions {
      margin-top: auto;
      padding-top: 14px;
      border-top: 1px solid rgba(255, 255, 255, .12);
      display: grid;
      gap: 10px;
    }

    /* =============================
     JUDUL A/B/C/D/E: BG DIHILANGIN
  ============================= */
    :where(.page-unit-tp) .tp-section-title {
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

    :where(.page-unit-tp) .tp-badge {
      width: 30px;
      height: 30px;
      border-radius: 10px;
      display: grid;
      place-items: center;
      background: transparent;
      border: 1px solid rgba(24, 79, 97, .25);
      color: var(--navy2);
      font-size: 15px;
      flex: 0 0 auto;
    }

    :where(.page-unit-tp) .tp-divider {
      height: 1px;
      background: #eef3f6;
      margin: 12px 0 14px;
    }

    :where(.page-unit-tp) .tp-label {
      display: block;
      font-size: 15px;
      color: var(--muted);
      margin-bottom: 8px;
    }

    :where(.page-unit-tp) .tp-input,
    :where(.page-unit-tp) .tp-select,
    :where(.page-unit-tp) .tp-textarea,
    :where(.page-unit-tp) .tp-file {
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

    :where(.page-unit-tp) .tp-control {
      position: relative;
    }

    :where(.page-unit-tp) .tp-control .tp-select {
      appearance: none;
      padding-right: 42px;
    }

    /* =============================
     DROPDOWN:
     - value terpilih biru Unsoed
     - placeholder saja yang abu
  ============================= */
    :where(.page-unit-tp) .tp-select {
      color: var(--navy2) !important;
      background-color: #fff !important;
    }

    :where(.page-unit-tp) .tp-select:focus {
      color: var(--navy2) !important;
    }

    :where(.page-unit-tp) .tp-select:invalid {
      color: #94a3b8 !important;
    }

    :where(.page-unit-tp) .tp-select option:checked {
      background: var(--navy2) !important;
      color: #fff !important;
    }

    :where(.page-unit-tp) .tp-select option:hover {
      background: rgba(24, 79, 97, .12) !important;
    }

    :where(.page-unit-tp) .tp-icon {
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

    :where(.page-unit-tp) .tp-input:hover,
    :where(.page-unit-tp) .tp-select:hover {
      border-color: rgba(24, 79, 97, .62);
      box-shadow: 0 8px 14px rgba(2, 8, 23, .05);
      transform: translateY(-1px);
    }

    :where(.page-unit-tp) .tp-input:focus,
    :where(.page-unit-tp) .tp-select:focus,
    :where(.page-unit-tp) .tp-textarea:focus {
      border-color: var(--navy2);
      box-shadow: 0 0 0 4px rgba(24, 79, 97, .14), 0 10px 18px rgba(2, 8, 23, .06);
      transform: translateY(-1px);
    }

    :where(.page-unit-tp) .tp-control:focus-within .tp-icon {
      opacity: .95;
      color: var(--navy2);
      transform: translateY(-50%) rotate(-180deg);
    }

    :where(.page-unit-tp) .tp-actions {
      display: flex;
      justify-content: space-between;
      gap: 12px;
      padding: 10px 6px 2px;
      margin-top: 6px;
    }

    :where(.page-unit-tp) .tp-btn {
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

    :where(.page-unit-tp) .tp-btn:hover {
      transform: translateY(-1px);
      box-shadow: 0 12px 20px rgba(2, 8, 23, .08);
    }

    :where(.page-unit-tp) .tp-btn i {
      font-size: 18px;
    }

    :where(.page-unit-tp) .tp-btn-ghost {
      background: #fff;
      color: var(--navy2);
    }

    :where(.page-unit-tp) .tp-btn-primary {
      background: var(--yellow);
      border-color: transparent;
      color: #0f172a;
    }

    /* RADIO */
    :where(.page-unit-tp) .tp-radio-wrap {
      display: grid;
      gap: 12px;
    }

    :where(.page-unit-tp) .tp-radio-card {
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

    :where(.page-unit-tp) .tp-radio-card:hover {
      border-color: rgba(24, 79, 97, .55);
      box-shadow: 0 10px 18px rgba(2, 8, 23, .07);
      transform: translateY(-1px);
    }

    :where(.page-unit-tp) .tp-radio-card input {
      display: none;
    }

    :where(.page-unit-tp) .tp-radio-dot {
      width: 18px;
      height: 18px;
      border-radius: 999px;
      border: 2px solid var(--navy2);
      display: inline-block;
      position: relative;
      flex: 0 0 auto;
    }

    :where(.page-unit-tp) .tp-radio-card.active {
      background: #dff1ff;
      border-color: #9fd0ff;
      box-shadow: 0 0 0 4px rgba(24, 79, 97, .10);
    }

    :where(.page-unit-tp) .tp-radio-card.active .tp-radio-dot::after {
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
    :where(.page-unit-tp) .tp-acc-item {
      border: 1px solid #e6eef2;
      border-radius: 14px;
      background: #fff;
      box-shadow: 0 10px 18px rgba(2, 8, 23, .05);
      overflow: hidden;
      transition: transform .16s ease, box-shadow .16s ease, border-color .16s ease;
    }

    :where(.page-unit-tp) .tp-acc-item:hover {
      transform: translateY(-1px);
      box-shadow: 0 12px 20px rgba(2, 8, 23, .07);
    }

    :where(.page-unit-tp) .tp-acc-head {
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

    /* ✅ text tipis jumlah file (di header sesi) */
    :where(.page-unit-tp) .tp-acc-count {
      font-size: 13px;
      opacity: .78;
      white-space: nowrap;
      margin-right: 10px;
      color: currentColor;
    }

    /* kalau sesi sudah ada file: header jadi hijau */
    :where(.page-unit-tp) .tp-acc-item.has-file {
      border-color: rgba(34, 197, 94, .65);
      box-shadow: 0 14px 26px rgba(2, 8, 23, .08);
    }

    :where(.page-unit-tp) .tp-acc-item.has-file .tp-acc-head {
      background: #22c55e;
      color: #fff;
    }

    :where(.page-unit-tp) .tp-acc-item.has-file .tp-acc-left i {
      color: #fff;
      opacity: .95;
    }

    :where(.page-unit-tp) .tp-acc-item.has-file .tp-acc-ic {
      color: #fff;
      opacity: .95;
    }

    :where(.page-unit-tp) .tp-acc-left {
      display: flex;
      align-items: center;
      gap: 10px;
      min-width: 0;
    }

    :where(.page-unit-tp) .tp-acc-left i {
      font-size: 18px;
    }

    :where(.page-unit-tp) .tp-acc-right {
      display: flex;
      align-items: center;
      gap: 10px;
      flex: 0 0 auto;
    }

    :where(.page-unit-tp) .tp-acc-ic {
      opacity: .9;
      transition: transform .16s ease;
      font-size: 18px;
    }

    :where(.page-unit-tp) .tp-acc-body {
      border-top: 1px solid #eef3f6;
      background: #fff;
      padding: 14px;
    }

    :where(.page-unit-tp) .tp-upload-row {
      margin-bottom: 16px;
    }

    /* DROPZONE */
    :where(.page-unit-tp) .tp-dropzone {
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

    :where(.page-unit-tp) .tp-dropzone:hover {
      border-color: rgba(24, 79, 97, .70);
      box-shadow: 0 0 0 4px rgba(24, 79, 97, .12), 0 12px 20px rgba(2, 8, 23, .06);
      transform: translateY(-1px);
    }

    :where(.page-unit-tp) .tp-acc-item.has-file .tp-dropzone {
      border-style: solid;
      border-color: rgba(34, 197, 94, .90);
      background: rgba(34, 197, 94, .05);
      box-shadow: 0 0 0 4px rgba(34, 197, 94, .09), 0 12px 20px rgba(2, 8, 23, .05);
      transform: translateY(-1px);
    }

    :where(.page-unit-tp) .tp-file-hidden {
      display: none;
    }

    :where(.page-unit-tp) .tp-drop-ic {
      width: 48px;
      height: 48px;
      border-radius: 999px;
      border: 1px solid #e2e8f0;
      display: grid;
      place-items: center;
      color: var(--navy2);
      font-size: 24px;
      background: #fff;
      transition: color .18s ease, background .18s ease, border-color .18s ease;
    }

    :where(.page-unit-tp) .tp-drop-title {
      color: var(--navy2);
      font-size: 16px;
    }

    :where(.page-unit-tp) .tp-drop-sub {
      color: var(--muted);
      font-size: 14px;
    }

    :where(.page-unit-tp) .tp-drop-meta {
      color: #94a3b8;
      font-size: 13px;
    }

    :where(.page-unit-tp) .tp-drop-btn {
      margin-top: 8px;
      background: var(--navy2);
      color: #fff;
      font-size: 16px;
      padding: 10px 18px;
      border-radius: 10px;
      transition: transform .14s ease, box-shadow .14s ease, background .14s ease;
    }

    :where(.page-unit-tp) .tp-dropzone:hover .tp-drop-btn {
      transform: translateY(-1px);
      box-shadow: 0 10px 16px rgba(2, 8, 23, .08);
    }

    /* PREVIEW LIST */
    :where(.page-unit-tp) .tp-preview-wrap {
      width: 100%;
      margin-top: 12px;
      border-top: 1px solid rgba(2, 8, 23, .06);
      padding-top: 12px;
      text-align: left;
    }

    :where(.page-unit-tp) .tp-preview-title {
      color: var(--navy2);
      font-size: 14px;
      margin-bottom: 10px;
    }

    :where(.page-unit-tp) .tp-preview-list {
      display: grid;
      gap: 10px;
    }

    :where(.page-unit-tp) .tp-preview-item {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 10px;
      padding: 10px 10px;
      border: 1px solid rgba(2, 8, 23, .08);
      border-radius: 12px;
      background: #fff;
    }

    :where(.page-unit-tp) .tp-preview-left {
      display: flex;
      align-items: center;
      gap: 10px;
      min-width: 0;
      flex: 1 1 auto;
    }

    :where(.page-unit-tp) .tp-preview-thumb {
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

    :where(.page-unit-tp) .tp-preview-thumb img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
    }

    :where(.page-unit-tp) .tp-preview-info {
      min-width: 0;
    }

    :where(.page-unit-tp) .tp-preview-name {
      font-size: 14px;
      color: #0f172a;
      word-break: break-word;
      line-height: 1.35;
    }

    :where(.page-unit-tp) .tp-preview-meta {
      font-size: 12px;
      color: #64748b;
      margin-top: 2px;
    }

    /* ✅ X CENTER FIX */
    :where(.page-unit-tp) .tp-preview-remove {
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
      transition: transform .14s ease, box-shadow .14s ease, border-color .14s ease;
      color: #0f172a;
    }

    :where(.page-unit-tp) .tp-preview-remove i {
      font-size: 18px;
      line-height: 1;
      display: block;
      transform: translateY(0.5px);
    }

    :where(.page-unit-tp) .tp-preview-remove:hover {
      transform: translateY(-1px);
      box-shadow: 0 10px 16px rgba(2, 8, 23, .08);
      border-color: rgba(24, 79, 97, .35);
    }

    @media(max-width:1100px) {
      :where(.page-unit-tp) .tp-btn {
        justify-content: center;
      }
    }

    /* PATCH spacing konsisten */
    :where(.page-unit-tp) .tp-cardbox {
      background: #fff !important;
      border-radius: 14px !important;
      box-shadow: 0 10px 20px rgba(2, 8, 23, .06) !important;
      border: 1px solid #eef3f6 !important;
      margin-bottom: 14px !important;
      overflow: hidden !important;
    }

    :where(.page-unit-tp) .tp-cardbox>div {
      padding: 18px 18px 18px !important;
    }

    :where(.page-unit-tp) .tp-grid {
      padding: 0 !important;
      gap: 14px 18px !important;
    }

    :where(.page-unit-tp) .tp-divider {
      margin-left: 0 !important;
      margin-right: 0 !important;
    }

    :where(.page-unit-tp) .tp-acc {
      padding: 0 !important;
      display: grid !important;
      gap: 14px !important;
    }

    :where(.page-unit-tp) .tp-help {
      margin: 0 0 12px !important;
      font-size: 15px;
      color: #64748b;
    }

    /* =============================
     E. Dokumen Tidak Dipersyaratkan
  ============================= */
    :where(.page-unit-tp) .tp-nondoc-wrap {
      border: 1px solid #eef3f6;
      border-radius: 14px;
      background: #fff;
      box-shadow: 0 10px 18px rgba(2, 8, 23, .05);
      overflow: hidden;
    }

    :where(.page-unit-tp) .tp-nondoc-head {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      padding: 12px 14px;
      background: #dff1ff;
      color: var(--navy2);
      border-bottom: 1px solid #eef3f6;
    }

    :where(.page-unit-tp) .tp-nondoc-title {
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 16px;
      color: var(--navy2);
    }

    :where(.page-unit-tp) .tp-nondoc-title i {
      font-size: 18px;
    }

    :where(.page-unit-tp) .tp-nondoc-actions {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    :where(.page-unit-tp) .tp-nondoc-btn {
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

    :where(.page-unit-tp) .tp-nondoc-btn:hover {
      transform: translateY(-1px);
      box-shadow: 0 12px 18px rgba(2, 8, 23, .08);
      border-color: rgba(24, 79, 97, .35);
    }

    :where(.page-unit-tp) .tp-nondoc-box {
      padding: 14px;
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px;
      max-height: 380px;
      overflow: auto;
    }

    @media(max-width:900px) {
      :where(.page-unit-tp) .tp-nondoc-box {
        grid-template-columns: 1fr;
      }
    }

    :where(.page-unit-tp) .tp-nondoc-item {
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

    :where(.page-unit-tp) .tp-nondoc-item:hover {
      transform: translateY(-1px);
      box-shadow: 0 12px 18px rgba(2, 8, 23, .08);
      border-color: rgba(24, 79, 97, .35);
    }

    :where(.page-unit-tp) .tp-nondoc-item input {
      display: none;
    }

    :where(.page-unit-tp) .tp-nondoc-check {
      width: 18px;
      height: 18px;
      border-radius: 6px;
      border: 2px solid var(--navy2);
      flex: 0 0 auto;
      margin-top: 1px;
      position: relative;
    }

    :where(.page-unit-tp) .tp-nondoc-text {
      font-size: 15px;
      color: #0f172a;
      line-height: 1.35;
    }

    :where(.page-unit-tp) .tp-nondoc-item.is-checked {
      background: rgba(24, 79, 97, .08);
      border-color: rgba(24, 79, 97, .35);
    }

    :where(.page-unit-tp) .tp-nondoc-item.is-checked .tp-nondoc-check::after {
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

    :where(.page-unit-tp) .tp-nondoc-selected {
      border-top: 1px solid rgba(2, 8, 23, .06);
      padding: 12px 14px 14px;
      background: #fff;
    }

    :where(.page-unit-tp) .tp-nondoc-selected-title {
      color: var(--navy2);
      font-size: 14px;
      margin-bottom: 10px;
    }

    :where(.page-unit-tp) .tp-nondoc-chips {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
    }

    :where(.page-unit-tp) .tp-nondoc-chip {
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
   RESPONSIVE PATCH — Tambah Pengadaan Unit
========================================================= */

    @media (min-width: 1025px) {
      :where(.page-unit-tp) .mob-topbar {
        display: none !important;
      }

      :where(.page-unit-tp) .sidebar-drawer-backdrop {
        display: none !important;
      }
    }

    @media (max-width: 1024px) {

      :where(.page-unit-tp) .mob-topbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 16px;
        background: #184f61;
        position: sticky;
        top: 0;
        z-index: 200;
      }

      :where(.page-unit-tp) .mob-logo {
        display: flex;
        align-items: center;
        gap: 10px;
      }

      :where(.page-unit-tp) .mob-logo img {
        width: 32px;
        height: 32px;
      }

      :where(.page-unit-tp) .mob-logo-txt {
        color: #f4c542;
        font-size: 17px;
        font-weight: 700;
      }

      :where(.page-unit-tp) .sidebar-drawer-backdrop {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, .45);
        backdrop-filter: blur(4px);
        z-index: 300;
      }

      :where(.page-unit-tp) .sidebar-drawer-backdrop.is-open {
        display: block;
      }

      :where(.page-unit-tp) .dash-sidebar {
        position: fixed !important;
        top: 0 !important;
        left: -280px !important;
        height: 100vh !important;
        width: 260px !important;
        z-index: 400 !important;
        transition: left .25s ease !important;
        overflow-y: auto !important;
      }

      :where(.page-unit-tp) .dash-sidebar.drawer-open {
        left: 0 !important;
        box-shadow: 4px 0 24px rgba(2, 8, 23, .18) !important;
      }

      :where(.page-unit-tp) .dash-wrap {
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

      :where(.page-unit-tp) .dash-main {
        height: auto !important;
        overflow-y: visible !important;
        padding: 20px !important;
      }
    }

    @media (max-width: 768px) {

      :where(.page-unit-tp) .dash-main {
        padding: 12px !important;
      }

      :where(.page-unit-tp) .dash-header h1 {
        font-size: 20px !important;
      }

      :where(.page-unit-tp) .dash-header p {
        font-size: 13px !important;
      }

      :where(.page-unit-tp) .tp-grid {
        grid-template-columns: 1fr !important;
      }

      :where(.page-unit-tp) .tp-full {
        grid-column: unset !important;
      }

      :where(.page-unit-tp) .tp-actions {
        flex-direction: row !important;
        justify-content: space-between !important;
      }

      :where(.page-unit-tp) .tp-btn {
        flex: 1 !important;
        justify-content: center !important;
      }
    }

    :where(.page-unit-tp) .tp-actions {
      display: flex !important;
      justify-content: space-between !important;
      gap: 12px !important;
      padding: 10px 6px 2px !important;
      margin-top: 6px !important;
      flex-direction: row !important;
    }

    :where(.page-unit-tp) .tp-btn {
      height: 46px !important;
      padding: 12px 16px !important;
      min-width: 140px !important;
    }

    :where(.page-unit-tp) .mob-ham {
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

    :where(.page-unit-tp) .tp-btn-same {
      min-width: 210px !important;
      height: 46px !important;
      padding: 12px 16px !important;
    }

    :where(.page-unit-tp) .tp-actions-split {
      display: flex !important;
      flex-direction: row !important;
      justify-content: space-between !important;
      align-items: center !important;
    }

    :where(.page-unit-tp) .tp-actions .tp-btn {
      flex: 0 0 auto !important;
      width: auto !important;
    }

    :where(.page-unit-tp) .tp-btn-same {
      min-width: 210px !important;
      width: auto !important;
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
      document.querySelectorAll('.tp-radio-wrap').forEach(wrap => {
        wrap.querySelectorAll('.tp-radio-card').forEach(c => c.classList.remove('active'));
        const checked = wrap.querySelector('input[type="radio"]:checked');
        if (checked) checked.closest('.tp-radio-card').classList.add('active');
      });

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

      // ✅ Inject elemen "text tipis jumlah file" ke header sesi (tanpa ubah HTML statis)
      document.querySelectorAll('.tp-acc-item').forEach(item => {
        const right = item.querySelector('.tp-acc-right');
        const chev = item.querySelector('.tp-acc-ic');
        if (!right || !chev) return;

        // kalau belum ada, tambahin span count
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

      // =========================================================
      // ✅ FIX: MATIKAN picker lama (yang memanggil input.click()).
      // Kita hanya SIMPAN default text di sini.
      // =========================================================
      document.querySelectorAll('.tp-dropzone').forEach(zone => {
        const btn = zone.querySelector('.tp-drop-btn');
        const title = zone.querySelector('.tp-drop-title');
        const sub = zone.querySelector('.tp-drop-sub');

        if (title && !title.dataset.defaultText) title.dataset.defaultText = title.textContent.trim();
        if (sub && !sub.dataset.defaultText) sub.dataset.defaultText = sub.textContent.trim();
        if (btn && !btn.dataset.defaultText) btn.dataset.defaultText = btn.textContent.trim();

        // ❌ jangan bind btn -> input.click() lagi
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

      // MULTI FILE APPEND + remove X (SAFE: cancel tidak menghapus file lama)
      document.querySelectorAll('.tp-acc-item').forEach(item => {
        const fileInput = item.querySelector('input[type="file"]'); // input asli (buat submit)
        const zone = item.querySelector('.tp-dropzone');
        if (!fileInput || !zone) return;

        const title = zone.querySelector('.tp-drop-title');
        const sub = zone.querySelector('.tp-drop-sub');
        const btn = zone.querySelector('.tp-drop-btn');

        if (title && !title.dataset.defaultText) title.dataset.defaultText = title.textContent.trim();
        if (sub && !sub.dataset.defaultText) sub.dataset.defaultText = sub.textContent.trim();
        if (btn && !btn.dataset.defaultText) btn.dataset.defaultText = btn.textContent.trim();

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
              if (ev.stopImmediatePropagation) ev.stopImmediatePropagation();
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

        // ✅ picker aman: pakai input sementara (cancel tidak menghapus file lama)
        const openPicker = (ev) => {
          ev.preventDefault();
          ev.stopPropagation();
          if (ev.stopImmediatePropagation) ev.stopImmediatePropagation(); // ✅ cegah handler lain ikut jalan

          const tmp = document.createElement('input');
          tmp.type = 'file';
          tmp.multiple = fileInput.multiple; // ikut multiple
          tmp.accept = fileInput.accept || ''; // ikut accept jika ada
          tmp.style.display = 'none';

          tmp.addEventListener('change', () => {
            const picked = tmp.files ? Array.from(tmp.files) : [];
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
              syncUI();
            }
            tmp.remove();
          });

          document.body.appendChild(tmp);
          tmp.click();
        };

        if (btn) btn.addEventListener('click', openPicker);

        zone.addEventListener('click', (ev) => {
          if (ev.target.closest('.tp-preview-remove')) return;
          openPicker(ev);
        });

        // init
        storedFiles = [];
        rebuildInputFiles();
        syncUI();
      });

      /* =========================================================
         E. Dokumen Tidak Dipersyaratkan
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
          const id = 'tp_nondoc_' + idx;

          const label = document.createElement('label');
          label.className = 'tp-nondoc-item';

          const input = document.createElement('input');
          input.type = 'checkbox';
          input.name = 'dokumen_tidak_dipersyaratkan[]';
          input.value = title;
          input.id = id;

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