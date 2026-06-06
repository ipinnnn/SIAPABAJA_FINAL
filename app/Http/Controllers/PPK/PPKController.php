<?php

namespace App\Http\Controllers\PPK;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\MasterMenu;
use App\Models\Pengadaan;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PpkController extends Controller
{
    // =========================
    // ACTIVITY LOG HELPER
    // =========================
    private function logActivity($action, $description)
    {
        ActivityLog::create([
            'user_id'     => auth()->id(),
            'action'      => $action,
            'description' => $description,
        ]);
    }

    public function dashboard()
    {
        $ppkName = auth()->user()->name ?? 'PPK Utama';

        // ✅ Tahun dari MasterMenu (tersinkron dengan Kelola Menu Super Admin)
        $tahunOptions = MasterMenu::where('category', 'tahun')
            ->where('is_active', true)
            ->orderByDesc('nama')
            ->pluck('nama')
            ->map(fn($t) => (int)$t)
            ->values()
            ->all();

        // Fallback jika MasterMenu kosong
        if (count($tahunOptions) === 0) {
            $tahunOptions = Pengadaan::whereNotNull('tahun')
                ->select('tahun')->distinct()
                ->orderBy('tahun', 'desc')
                ->pluck('tahun')
                ->map(fn($t) => (int)$t)
                ->values()
                ->all();
        }

        if (count($tahunOptions) === 0) {
            $y = (int)date('Y');
            $tahunOptions = [$y, $y - 1, $y - 2, $y - 3, $y - 4];
        }

        $defaultYear = $tahunOptions[0] ?? (int)date('Y');

        // ✅ kolom nama unit fleksibel
        $unitNameCol = Schema::hasColumn('units', 'nama') ? 'nama'
            : (Schema::hasColumn('units', 'nama_unit') ? 'nama_unit'
                : (Schema::hasColumn('units', 'name') ? 'name' : 'id'));

        // ✅ Dropdown Unit = dari tabel units (id + nama)
        $units = Unit::orderBy($unitNameCol, 'asc')->get(['id', $unitNameCol]);

        $registeredUnits = $units->pluck($unitNameCol)->values()->all();

        $unitOptions = $units->map(function ($u) use ($unitNameCol) {
            return [
                'id'   => (int)$u->id,
                'name' => (string)$u->{$unitNameCol},
            ];
        })->values()->all();

        $totalUnitKerja = count($unitOptions);

        $totalArsip = Pengadaan::count();
        $publik     = Pengadaan::where('status_arsip', 'Publik')->count();
        $privat     = Pengadaan::where('status_arsip', 'Privat')->count();

        // ✅ Default kartu mengikuti "Semua Tahun" + "Semua Unit"
        $paketAll = Pengadaan::count();
        $nilaiAll = (int) Pengadaan::sum('nilai_kontrak');

        $summary = [
            ["label" => "Total Arsip",             "value" => $totalArsip,                           "accent" => "navy",   "icon" => "bi-file-earmark-text"],
            ["label" => "Arsip Publik",             "value" => $publik,                               "accent" => "yellow", "icon" => "bi-eye"],
            ["label" => "Arsip Private",            "value" => $privat,                               "accent" => "gray",   "icon" => "bi-eye-slash"],
            ["label" => "Total Arsip Pengadaan",    "value" => $paketAll,                             "accent" => "navy",   "icon" => "bi-file-earmark-text", "sub" => "Paket Pengadaan Barang dan Jasa"],
            ["label" => "Total Nilai Pengadaan",    "value" => $this->formatRupiahNumber($nilaiAll),  "accent" => "yellow", "icon" => "bi-buildings",         "sub" => "Nilai Kontrak Pengadaan"],
        ];

        // ✅ Labels dari MasterMenu
        $statusLabels = MasterMenu::where('category', 'status_pekerjaan')
            ->where('is_active', true)
            ->orderBy('order_index')
            ->pluck('nama')
            ->values()
            ->all();
        if (empty($statusLabels)) $statusLabels = ["Perencanaan", "Pemilihan", "Pelaksanaan", "Selesai"];

        $statusValues = $this->countByStatusPekerjaanPPK(null, null, $statusLabels);

        $barLabels = MasterMenu::where('category', 'metode_pengadaan')
            ->where('is_active', true)
            ->orderBy('order_index')
            ->pluck('nama')
            ->map(fn($item) => str_replace(' ', "\n", $item))
            ->values()
            ->all();
        if (empty($barLabels)) {
            $barLabels = ["Pengadaan\nLangsung", "Penunjukan\nLangsung", "E-Purchasing /\nE-Catalog", "Tender\nTerbatas", "Tender\nTerbuka", "Swakelola"];
        }
        $barValues = $this->countByMetodePengadaanPPK(null, null, $barLabels);

        return view('PPK.Dashboard', compact(
            'ppkName',
            'summary',
            'tahunOptions',
            'unitOptions',
            'defaultYear',
            'totalUnitKerja',
            'registeredUnits',
            'statusLabels',
            'statusValues',
            'barLabels',
            'barValues'
        ));
    }

    public function dashboardStats(Request $request)
    {
        $tahun = $request->query('tahun');
        $tahun = ($tahun === null || $tahun === '') ? null : (int)$tahun;

        // ✅ prefer unit_id
        $unitId = $request->query('unit_id');
        $unitId = ($unitId === null || $unitId === '') ? null : (int)$unitId;

        // ✅ backward-compat (kalau frontend lama masih kirim "unit" string)
        $rawUnit = $request->query('unit');
        if ($unitId === null && $rawUnit !== null && $rawUnit !== '' && $rawUnit !== 'Semua Unit') {
            $unitId = $this->resolveUnitId($rawUnit);
        }

        $paket = Pengadaan::query()
            ->when($unitId !== null, fn($q) => $q->where('unit_id', $unitId))
            ->when($tahun !== null, fn($q) => $q->where('tahun', $tahun))
            ->count();

        $nilai = (int) Pengadaan::query()
            ->when($unitId !== null, fn($q) => $q->where('unit_id', $unitId))
            ->when($tahun !== null, fn($q) => $q->where('tahun', $tahun))
            ->sum('nilai_kontrak');

        // ✅ Labels dari MasterMenu
        $statusLabels = MasterMenu::where('category', 'status_pekerjaan')
            ->where('is_active', true)
            ->orderBy('order_index')
            ->pluck('nama')
            ->values()
            ->all();
        if (empty($statusLabels)) $statusLabels = ["Perencanaan", "Pemilihan", "Pelaksanaan", "Selesai"];

        $statusValues = $this->countByStatusPekerjaanPPK($unitId, $tahun, $statusLabels);

        $barLabels = MasterMenu::where('category', 'metode_pengadaan')
            ->where('is_active', true)
            ->orderBy('order_index')
            ->pluck('nama')
            ->map(fn($item) => str_replace(' ', "\n", $item))
            ->values()
            ->all();
        if (empty($barLabels)) {
            $barLabels = ["Pengadaan\nLangsung", "Penunjukan\nLangsung", "E-Purchasing /\nE-Catalog", "Tender\nTerbatas", "Tender\nTerbuka", "Swakelola"];
        }
        $barValues = $this->countByMetodePengadaanPPK($unitId, $tahun, $barLabels);

        return response()->json([
            'tahun'   => $tahun,
            'unit_id' => $unitId,
            'paket'   => ['count' => $paket],
            'nilai'   => ['sum' => $nilai, 'formatted' => $this->formatRupiahNumber($nilai)],
            'status'  => ['labels' => $statusLabels, 'values' => $statusValues],
            'metode'  => ['labels' => $barLabels, 'values' => $barValues],
        ]);
    }

    public function dashboardData(Request $request)
    {
        return $this->dashboardStats($request);
    }

    private function countByStatusPekerjaanPPK(?int $unitId, ?int $tahun, array $labels): array
    {
        $rows = Pengadaan::query()
            ->when($unitId !== null, fn($q) => $q->where('unit_id', $unitId))
            ->when($tahun !== null, fn($q) => $q->where('tahun', $tahun))
            ->select('status_pekerjaan', DB::raw('COUNT(*) as cnt'))
            ->groupBy('status_pekerjaan')
            ->pluck('cnt', 'status_pekerjaan')
            ->toArray();

        return array_map(fn($lbl) => (int)($rows[$lbl] ?? 0), $labels);
    }

    private function countByMetodePengadaanPPK(?int $unitId, ?int $tahun, array $labels): array
    {
        $raw = Pengadaan::query()
            ->when($unitId !== null, fn($q) => $q->where('unit_id', $unitId))
            ->when($tahun !== null, fn($q) => $q->where('tahun', $tahun))
            ->whereNotNull('metode_pengadaan')
            ->whereRaw("TRIM(metode_pengadaan) <> ''")
            ->selectRaw("LOWER(TRIM(metode_pengadaan)) as metode, COUNT(*) as cnt")
            ->groupBy('metode')
            ->pluck('cnt', 'metode')
            ->toArray();

        $out = [];
        foreach ($labels as $lbl) {
            // Normalisasi label (buang \n untuk matching)
            $normalized = strtolower(trim(str_replace("\n", ' ', $lbl)));

            $alternatives = array_unique([
                $normalized,
                str_replace('catalogue', 'catalog', $normalized),
                str_replace('catalog', 'catalogue', $normalized),
                str_replace(' / ', '/', $normalized),
                str_replace('/', ' / ', $normalized),
            ]);

            $total = 0;
            foreach ($alternatives as $alt) {
                $total += (int)($raw[$alt] ?? 0);
            }
            $out[] = $total;
        }
        return $out;
    }

    private function formatRupiahNumber($value): string
    {
        $num = (int)($value ?? 0);
        return 'Rp ' . number_format($num, 0, ',', '.');
    }

    /**
     * Arsip PBJ (PPK) - FILTER + SEARCH aman untuk PostgreSQL
     * ✅ FINAL: sort nilai_kontrak server-side (sort_nilai=asc|desc)
     * ✅ FIX: default TIDAK sort nilai kontrak (biar refresh balik "sedia kala")
     */
    public function arsipIndex(Request $request)
    {
        $ppkName = auth()->user()->name ?? "PPK Utama";

        $q      = trim((string)$request->query('q', ''));
        $unitQ  = trim((string)$request->query('unit', 'Semua'));
        $status = trim((string)$request->query('status', 'Semua'));
        $tahunQ = trim((string)$request->query('tahun', 'Semua'));

        // ✅ sort nilai kontrak (server-side) - aktif hanya jika param ada
        $sortNilaiRaw = $request->query('sort_nilai'); // null jika tidak ada
        $sortNilai = null;
        if ($sortNilaiRaw !== null && $sortNilaiRaw !== '') {
            $tmp = strtolower(trim((string)$sortNilaiRaw));
            if (in_array($tmp, ['asc', 'desc'], true)) $sortNilai = $tmp;
        }

        $driver = DB::connection()->getDriverName();       // 'pgsql' / 'mysql'
        $likeOp = $driver === 'pgsql' ? 'ilike' : 'like'; // pgsql pakai ILIKE

        // resolve unit_id
        $unitId = null;
        $unitQNorm = mb_strtolower($unitQ, 'UTF-8');
        if ($unitQ !== '' && $unitQNorm !== 'semua' && $unitQNorm !== 'semua unit') {
            $unitId = $this->resolveUnitId($unitQ);
        }

        // status
        $statusNorm  = mb_strtolower($status, 'UTF-8');
        $statusFixed = in_array($statusNorm, ['publik', 'privat'], true) ? ucfirst($statusNorm) : null;

        // tahun robust
        $tahunInt = null;
        $tahunStr = null;
        if ($tahunQ !== '' && mb_strtolower($tahunQ, 'UTF-8') !== 'semua') {
            $tahunStr = $tahunQ;
            if (ctype_digit($tahunQ)) $tahunInt = (int)$tahunQ;
        }

        $arsipsQuery = Pengadaan::with('unit')
            ->when($unitId !== null, fn($qq) => $qq->where('unit_id', $unitId))
            ->when($statusFixed !== null, fn($qq) => $qq->where('status_arsip', $statusFixed))

            // tahun (aman untuk pgsql/mysql)
            ->when($tahunStr !== null, function ($qq) use ($tahunInt, $tahunStr, $driver) {
                $qq->where(function ($w) use ($tahunInt, $tahunStr, $driver) {
                    if ($tahunInt !== null) $w->orWhere('tahun', $tahunInt);

                    if ($driver === 'pgsql') {
                        $w->orWhereRaw("TRIM(CAST(tahun AS TEXT)) = ?", [$tahunStr]);
                    } else {
                        $w->orWhereRaw("TRIM(CAST(tahun AS CHAR)) = ?", [$tahunStr]);
                    }
                });
            })

            // ✅ SEARCH q
            ->when($q !== '', function ($query) use ($q, $likeOp, $driver) {
                $like = '%' . $q . '%';

                $query->where(function ($w) use ($q, $like, $likeOp, $driver) {
                    if (ctype_digit($q)) {
                        $w->orWhere('tahun', (int)$q);
                        $w->orWhere('id', (int)$q);
                    }

                    $w->orWhere('nama_pekerjaan',   $likeOp, $like)
                        ->orWhere('id_rup',          $likeOp, $like)
                        ->orWhere('jenis_pengadaan', $likeOp, $like)
                        ->orWhere('status_arsip',    $likeOp, $like)
                        ->orWhere('status_pekerjaan', $likeOp, $like)
                        ->orWhere('nama_rekanan',    $likeOp, $like);

                    if ($driver === 'pgsql') {
                        $w->orWhereRaw("CAST(COALESCE(nilai_kontrak,0) AS TEXT) ILIKE ?", [$like]);
                    } else {
                        $w->orWhereRaw("CAST(COALESCE(nilai_kontrak,0) AS CHAR) LIKE ?", [$like]);
                    }

                    $w->orWhereHas('unit', function ($u) use ($like, $likeOp) {
                        $u->where(function ($uu) use ($like, $likeOp) {
                            if (Schema::hasColumn('units', 'nama')) {
                                $uu->orWhere('nama', $likeOp, $like);
                            }
                            if (Schema::hasColumn('units', 'nama_unit')) {
                                $uu->orWhere('nama_unit', $likeOp, $like);
                            }
                            if (Schema::hasColumn('units', 'name')) {
                                $uu->orWhere('name', $likeOp, $like);
                            }
                        });
                    });
                });
            });

        /**
         * ✅ SORTING FINAL
         * - DEFAULT (tanpa sort_nilai): updated_at desc, id desc (sedia kala)
         * - Kalau sort_nilai ada: nilai_kontrak asc/desc lalu tie-breaker updated_at/id
         */
        $arsipsQuery->orderByDesc('updated_at')->orderByDesc('id');

        if ($sortNilai !== null) {
            $arsipsQuery->reorder();

            if ($sortNilai === 'asc') {
                $arsipsQuery->orderByRaw('COALESCE(nilai_kontrak, 0) ASC');
            } else {
                $arsipsQuery->orderByRaw('COALESCE(nilai_kontrak, 0) DESC');
            }

            $arsipsQuery->orderByDesc('updated_at')->orderByDesc('id');
        }

        $arsips = $arsipsQuery
            ->paginate(10)
            ->withQueryString();

        // ✅ ambil role semua creator sekaligus (1 query, bukan N query)
        $creatorIds = $arsips->getCollection()->pluck('created_by')->filter()->unique()->values();
        $creatorRoles = \App\Models\User::whereIn('id', $creatorIds)
            ->pluck('role', 'id');

        $mapped = $arsips->getCollection()->map(function (Pengadaan $p) use ($creatorRoles) {
            $createdByRole = strtolower(trim((string)($creatorRoles[$p->created_by] ?? '')));
            return [
                'id'                            => $p->id,
                'pekerjaan'                     => ($p->nama_pekerjaan ?? '-'),
                'id_rup'                        => $p->id_rup ?? '-',
                'tahun'                         => $p->tahun ?? null,
                'metode_pbj'                    => $p->metode_pengadaan ?? '-',
                'jenis_pengadaan'               => $p->jenis_pengadaan ?? '-',
                'status_pekerjaan'              => $p->status_pekerjaan ?? '-',
                'status_arsip'                  => $p->status_arsip ?? '-',
                'nilai_kontrak'                 => $this->formatRupiah($p->nilai_kontrak),
                'pagu_anggaran'                 => $this->formatRupiah($p->pagu_anggaran),
                'hps'                           => $this->formatRupiah($p->hps),
                'nama_rekanan'                  => $p->nama_rekanan ?? '-',
                'unit'                          => $p->unit?->nama ?? ($p->unit?->nama_unit ?? ($p->unit?->name ?? '-')),
                'dokumen'                       => $this->buildDokumenList($p),
                'dokumen_tidak_dipersyaratkan'  => $this->normalizeArray($p->dokumen_tidak_dipersyaratkan),
                'created_by'                    => $p->created_by,
                'created_by_role'               => $createdByRole, // ✅ role pembuat arsip
            ];
        });

        $arsips->setCollection($mapped);

        // ✅ Tahun dari MasterMenu
        $years = MasterMenu::where('category', 'tahun')
            ->where('is_active', true)
            ->orderByDesc('nama')
            ->pluck('nama')
            ->map(fn($t) => (string)$t)
            ->values()
            ->all();

        if (count($years) === 0) {
            $years = Pengadaan::whereNotNull('tahun')
                ->select('tahun')->distinct()
                ->orderBy('tahun', 'desc')
                ->pluck('tahun')
                ->map(fn($t) => (string)$t)
                ->values()
                ->all();
        }

        if (count($years) === 0) {
            $y     = (int)date('Y');
            $years = [(string)$y, (string)($y - 1), (string)($y - 2), (string)($y - 3), (string)($y - 4)];
        }

        $unitNameCol = Schema::hasColumn('units', 'nama') ? 'nama'
            : (Schema::hasColumn('units', 'nama_unit') ? 'nama_unit'
                : (Schema::hasColumn('units', 'name') ? 'name' : 'id'));

        $unitOptions = Unit::orderBy($unitNameCol, 'asc')
            ->pluck($unitNameCol)
            ->values()
            ->all();

        return view('PPK.ArsipPBJ', compact('ppkName', 'arsips', 'unitOptions', 'years'));
    }

    public function pengadaanCreate()
    {
        $ppkName = auth()->user()->name ?? "PPK Utama";

        // ✅ Dropdown dari MasterMenu
        $tahunOptions = MasterMenu::where('category', 'tahun')
            ->where('is_active', true)
            ->orderByDesc('nama')
            ->pluck('nama')
            ->map(fn($t) => (int)$t)
            ->values()
            ->all();

        if (count($tahunOptions) === 0) {
            $tahunOptions = Pengadaan::whereNotNull('tahun')
                ->select('tahun')->distinct()
                ->orderBy('tahun', 'desc')
                ->pluck('tahun')
                ->map(fn($t) => (int)$t)
                ->values()
                ->all();
        }

        if (count($tahunOptions) === 0) {
            $y = (int)date('Y');
            $tahunOptions = [$y, $y - 1, $y - 2, $y - 3, $y - 4];
        }

        $jenisPengadaanOptions = MasterMenu::where('category', 'jenis_pengadaan')
            ->where('is_active', true)
            ->orderBy('order_index')
            ->pluck('nama')
            ->values()
            ->all();

        if (empty($jenisPengadaanOptions)) {
            $jenisPengadaanOptions = ["Pengadaan Barang", "Pengadaan Pekerjaan Konstruksi", "Pengadaan Jasa Konsultasi", "Pengadaan Jasa Lainnya"];
        }

        $metodePengadaanOptions = MasterMenu::where('category', 'metode_pengadaan')
            ->where('is_active', true)
            ->orderBy('order_index')
            ->pluck('nama')
            ->values()
            ->all();

        if (empty($metodePengadaanOptions)) {
            $metodePengadaanOptions = ["Pengadaan Langsung", "Penunjukan Langsung", "E-Purchasing / E-Catalog", "Tender Terbatas", "Tender Terbuka", "Swakelola"];
        }

        $statusPekerjaanOptions = MasterMenu::where('category', 'status_pekerjaan')
            ->where('is_active', true)
            ->orderBy('order_index')
            ->pluck('nama')
            ->values()
            ->all();

        if (empty($statusPekerjaanOptions)) {
            $statusPekerjaanOptions = ["Perencanaan", "Pemilihan", "Pelaksanaan", "Selesai"];
        }

        // ✅ Unit dropdown
        $unitNameCol = Schema::hasColumn('units', 'nama') ? 'nama'
            : (Schema::hasColumn('units', 'nama_unit') ? 'nama_unit'
                : (Schema::hasColumn('units', 'name') ? 'name' : 'id'));

        $units = Unit::query()
            ->select(['id', $unitNameCol])
            ->orderBy($unitNameCol, 'asc')
            ->get();

        $unitOptions = $units->pluck($unitNameCol)->values()->all();

        return view('PPK.TambahPengadaan', compact(
            'ppkName',
            'tahunOptions',
            'units',
            'unitNameCol',
            'unitOptions', // backward-compat
            'jenisPengadaanOptions',
            'statusPekerjaanOptions',
            'metodePengadaanOptions',
        ));
    }

    public function pengadaanStore(Request $request)
    {
        // ✅ utamakan unit_id, tapi tetap dukung unit_kerja (fallback lama) biar tidak error
        $rules = [
            'tahun'      => 'required|integer|min:2000|max:' . (date('Y') + 5),

            // baru
            'unit_id'    => 'nullable|integer|exists:units,id',
            // lama (fallback)
            'unit_kerja' => 'nullable|string|max:255',

            'nama_pekerjaan' => 'nullable|string|max:255',
            'id_rup'         => 'nullable|string|max:255',
            'jenis_pengadaan' => 'required|string|max:100',
            'metode_pengadaan' => 'required|string|max:100',
            'status_pekerjaan' => 'required|string|max:100',
            'status_arsip'   => 'required|in:Publik,Privat',

            'pagu_anggaran'  => 'nullable|string|max:50',
            'hps'            => 'nullable|string|max:50',
            'nilai_kontrak'  => 'nullable|string|max:50',
            'nama_rekanan'   => 'nullable|string|max:255',

            'dokumen_tidak_dipersyaratkan_json' => 'nullable|string',
            'dokumen_tidak_dipersyaratkan'      => 'nullable|array',
        ];

        $data = $request->validate($rules);

        // ✅ resolve unit_id: prefer unit_id, else resolve dari unit_kerja (lama)
        $resolvedUnitId = null;
        if (!empty($data['unit_id'])) {
            $resolvedUnitId = (int)$data['unit_id'];
        } elseif (!empty($data['unit_kerja'])) {
            $resolvedUnitId = $this->resolveUnitId($data['unit_kerja']);
        }

        if (!$resolvedUnitId) {
            $key = !empty($data['unit_id']) ? 'unit_id' : 'unit_kerja';
            return redirect()->back()->withInput()->withErrors([$key => 'Unit kerja tidak valid / tidak ditemukan di database.']);
        }

        $toInt = function ($v) {
            if ($v === null) return null;
            $num = preg_replace('/[^0-9]/', '', (string)$v);
            return $num === '' ? null : (int)$num;
        };

        $payload = [
            'tahun'      => (int)$data['tahun'],
            'unit_id'    => (int)$resolvedUnitId,
            'created_by' => Auth::id(),

            'nama_pekerjaan'   => $data['nama_pekerjaan'] ?? null,
            'id_rup'           => $data['id_rup'] ?? null,
            'jenis_pengadaan'  => $data['jenis_pengadaan'],
            'metode_pengadaan' => $data['metode_pengadaan'],
            'status_pekerjaan' => $data['status_pekerjaan'],
            'status_arsip'     => $data['status_arsip'],

            'pagu_anggaran' => $toInt($data['pagu_anggaran'] ?? null),
            'hps'           => $toInt($data['hps'] ?? null),
            'nilai_kontrak' => $toInt($data['nilai_kontrak'] ?? null),
            'nama_rekanan'  => $data['nama_rekanan'] ?? null,
        ];

        $docTidak = [];
        if (is_array($request->input('dokumen_tidak_dipersyaratkan'))) {
            $docTidak = $request->input('dokumen_tidak_dipersyaratkan');
        } else {
            $json = $request->input('dokumen_tidak_dipersyaratkan_json');
            if (is_string($json) && trim($json) !== '') {
                $decoded = json_decode($json, true);
                if (is_array($decoded)) $docTidak = $decoded;
            }
        }
        $payload['dokumen_tidak_dipersyaratkan'] = array_values(array_filter($docTidak, fn($x) => $x !== null && $x !== ''));

        DB::beginTransaction();
        try {
            $pengadaan = Pengadaan::create($payload);

            // ✅ LOG: tambah pengadaan (sertakan nama unit agar histori unit bisa menampilkannya)
            $unitNamaLog = optional(\App\Models\Unit::find($resolvedUnitId))->nama ?? ('Unit #' . $resolvedUnitId);
            $this->logActivity(
                'create',
                'PPK menambahkan pengadaan: ' . ($payload['nama_pekerjaan'] ?? 'Tanpa Nama')
                    . ' (Unit: ' . $unitNamaLog . ')'
            );

            $this->handleUploadDokumenToModel($request, $pengadaan, false);
            $pengadaan->save();

            DB::commit();

            return redirect()->route('ppk.arsip')->with('success', 'Pengadaan baru berhasil ditambahkan!');
        } catch (\Throwable $e) {
            DB::rollBack();

            if (isset($pengadaan) && $pengadaan instanceof Pengadaan) {
                try {
                    Storage::disk('public')->deleteDirectory("pengadaan/{$pengadaan->id}");
                } catch (\Throwable $ex) {
                }
                try {
                    $pengadaan->delete();
                } catch (\Throwable $ex) {
                }
            }

            return redirect()->back()->withInput()->withErrors(['upload' => 'Gagal menyimpan pengadaan/dokumen.']);
        }
    }

    public function arsipEdit($id)
    {
        $ppkName = auth()->user()->name ?? "PPK Utama";

        $pengadaan = Pengadaan::with('unit')->findOrFail($id);
        $arsip     = $pengadaan;

        // ✅ Dropdown dari MasterMenu
        $tahunOptions = MasterMenu::where('category', 'tahun')
            ->where('is_active', true)
            ->orderByDesc('nama')
            ->pluck('nama')
            ->map(fn($t) => (int)$t)
            ->values()
            ->all();

        if (count($tahunOptions) === 0) {
            $tahunOptions = Pengadaan::whereNotNull('tahun')
                ->select('tahun')->distinct()
                ->orderBy('tahun', 'desc')
                ->pluck('tahun')
                ->map(fn($t) => (int)$t)
                ->values()
                ->all();
        }
        if (count($tahunOptions) === 0) {
            $y = (int)date('Y');
            $tahunOptions = [$y, $y - 1, $y - 2, $y - 3, $y - 4];
        }

        $jenisPengadaanOptions = MasterMenu::where('category', 'jenis_pengadaan')
            ->where('is_active', true)
            ->orderBy('order_index')
            ->pluck('nama')
            ->values()
            ->all();
        if (empty($jenisPengadaanOptions)) {
            $jenisPengadaanOptions = ["Pengadaan Barang", "Pengadaan Pekerjaan Konstruksi", "Pengadaan Jasa Konsultasi", "Pengadaan Jasa Lainnya"];
        }

        $metodePengadaanOptions = MasterMenu::where('category', 'metode_pengadaan')
            ->where('is_active', true)
            ->orderBy('order_index')
            ->pluck('nama')
            ->values()
            ->all();
        if (empty($metodePengadaanOptions)) {
            $metodePengadaanOptions = ["Pengadaan Langsung", "Penunjukan Langsung", "E-Purchasing / E-Catalogue", "Tender Terbatas", "Tender Terbuka", "Swakelola"];
        }

        $statusPekerjaanOptions = MasterMenu::where('category', 'status_pekerjaan')
            ->where('is_active', true)
            ->orderBy('order_index')
            ->pluck('nama')
            ->values()
            ->all();
        if (empty($statusPekerjaanOptions)) {
            $statusPekerjaanOptions = ["Perencanaan", "Pemilihan", "Pelaksanaan", "Selesai"];
        }

        $unitNameCol = Schema::hasColumn('units', 'nama') ? 'nama'
            : (Schema::hasColumn('units', 'nama_unit') ? 'nama_unit'
                : (Schema::hasColumn('units', 'name') ? 'name' : 'id'));

        $units       = Unit::query()->select(['id', $unitNameCol])->orderBy($unitNameCol, 'asc')->get();
        $unitOptions = $units->pluck($unitNameCol)->values()->all();

        $pengadaan->dokumen_tidak_dipersyaratkan = $this->normalizeArray($pengadaan->dokumen_tidak_dipersyaratkan);

        return view('PPK.EditArsip', compact(
            'ppkName',
            'pengadaan',
            'arsip',
            'tahunOptions',
            'units',
            'unitNameCol',
            'unitOptions',
            'jenisPengadaanOptions',
            'statusPekerjaanOptions',
            'metodePengadaanOptions',
        ));
    }

    public function arsipUpdate(Request $request, $id)
    {
        $pengadaan = Pengadaan::findOrFail($id);

        $rules = [
            'tahun'      => 'nullable|integer|min:2000|max:' . (date('Y') + 5),

            // baru
            'unit_id'    => 'nullable|integer|exists:units,id',
            // lama (fallback)
            'unit_kerja' => 'nullable|string|max:255',

            'nama_pekerjaan'   => 'nullable|string|max:255',
            'id_rup'           => 'nullable|string|max:255',
            'jenis_pengadaan'  => 'nullable|string|max:100',
            'metode_pengadaan' => 'nullable|string|max:100',
            'status_pekerjaan' => 'nullable|string|max:100',
            'status_arsip'     => 'nullable|in:Publik,Privat',

            'pagu_anggaran' => 'nullable|string|max:50',
            'hps'           => 'nullable|string|max:50',
            'nilai_kontrak' => 'nullable|string|max:50',
            'nama_rekanan'  => 'nullable|string|max:255',

            'dokumen_tidak_dipersyaratkan_json' => 'nullable|string',
            'dokumen_tidak_dipersyaratkan'      => 'nullable|array',
        ];

        $data = $request->validate($rules);

        $toInt = function ($v) {
            if ($v === null) return null;
            $num = preg_replace('/[^0-9]/', '', (string)$v);
            return $num === '' ? null : (int)$num;
        };

        DB::beginTransaction();
        try {
            if ($request->filled('tahun')) $pengadaan->tahun = (int)$data['tahun'];

            // ✅ update unit: prefer unit_id, else unit_kerja (fallback lama)
            if ($request->filled('unit_id') || $request->filled('unit_kerja')) {
                $resolvedUnitId = null;

                if ($request->filled('unit_id')) {
                    $resolvedUnitId = (int)$data['unit_id'];
                } elseif ($request->filled('unit_kerja')) {
                    $resolvedUnitId = $this->resolveUnitId($data['unit_kerja']);
                }

                if (!$resolvedUnitId) {
                    DB::rollBack();
                    $key = $request->filled('unit_id') ? 'unit_id' : 'unit_kerja';
                    return redirect()->back()->withInput()->withErrors([$key => 'Unit kerja tidak valid / tidak ditemukan di database.']);
                }

                $pengadaan->unit_id = (int)$resolvedUnitId;
            }

            if ($request->filled('nama_pekerjaan'))  $pengadaan->nama_pekerjaan  = $data['nama_pekerjaan'];
            if ($request->filled('id_rup'))           $pengadaan->id_rup          = $data['id_rup'];
            if ($request->filled('jenis_pengadaan'))  $pengadaan->jenis_pengadaan  = $data['jenis_pengadaan'];
            if ($request->filled('metode_pengadaan')) $pengadaan->metode_pengadaan = $data['metode_pengadaan'];
            if ($request->filled('status_pekerjaan')) $pengadaan->status_pekerjaan = $data['status_pekerjaan'];
            if ($request->filled('status_arsip'))     $pengadaan->status_arsip    = $data['status_arsip'];

            if (array_key_exists('pagu_anggaran', $data)) $pengadaan->pagu_anggaran = $toInt($data['pagu_anggaran']);
            if (array_key_exists('hps', $data))           $pengadaan->hps           = $toInt($data['hps']);
            if (array_key_exists('nilai_kontrak', $data)) $pengadaan->nilai_kontrak  = $toInt($data['nilai_kontrak']);
            if (array_key_exists('nama_rekanan', $data))  $pengadaan->nama_rekanan   = $data['nama_rekanan'];

            $docTidak = [];
            if (is_array($request->input('dokumen_tidak_dipersyaratkan'))) {
                $docTidak = $request->input('dokumen_tidak_dipersyaratkan');
            } else {
                $json = $request->input('dokumen_tidak_dipersyaratkan_json');
                if (is_string($json) && trim($json) !== '') {
                    $decoded = json_decode($json, true);
                    if (is_array($decoded)) $docTidak = $decoded;
                }
            }
            $pengadaan->dokumen_tidak_dipersyaratkan = array_values(array_filter($docTidak, fn($x) => $x !== null && $x !== ''));

            $this->handleUploadDokumenToModel($request, $pengadaan, true);
            $this->handleRemoveExistingByHiddenInputs($request, $pengadaan);

            $pengadaan->save();

            // ✅ LOG: edit pengadaan (sertakan nama unit agar histori unit bisa menampilkannya)
            $unitNamaEdit = optional($pengadaan->unit)->nama ?? ('Unit #' . $pengadaan->unit_id);
            $this->logActivity(
                'update',
                'PPK mengedit pengadaan: ' . ($pengadaan->nama_pekerjaan ?? 'ID ' . $pengadaan->id)
                    . ' (Unit: ' . $unitNamaEdit . ')'
            );

            DB::commit();

            return redirect()->route('ppk.arsip')->with('success', 'Arsip berhasil diperbarui.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->withErrors(['update' => 'Gagal memperbarui arsip.']);
        }
    }

    public function arsipDelete($id)
    {
        $pengadaan = Pengadaan::findOrFail($id);

        DB::beginTransaction();
        try {
            // ✅ LOG: hapus pengadaan (sebelum delete agar data masih tersedia, sertakan nama unit)
            $unitNamaDel = optional($pengadaan->unit)->nama ?? ('Unit #' . $pengadaan->unit_id);
            $this->logActivity(
                'delete',
                'PPK menghapus pengadaan: ' . ($pengadaan->nama_pekerjaan ?? 'ID ' . $pengadaan->id)
                    . ' (Unit: ' . $unitNamaDel . ')'
            );

            try {
                Storage::disk('public')->deleteDirectory("pengadaan/{$pengadaan->id}");
            } catch (\Throwable $e) {
            }
            $pengadaan->delete();

            DB::commit();
            return redirect()->route('ppk.arsip')->with('success', 'Arsip berhasil dihapus.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['delete' => 'Gagal menghapus arsip.']);
        }
    }

    public function hapusDokumenFile(Request $request, $id)
    {
        $pengadaan = Pengadaan::findOrFail($id);

        $field = (string)$request->input('field', '');
        $path  = (string)$request->input('path', '');

        if ($field === '' || $path === '') {
            return response()->json(['message' => 'Field/path tidak valid.'], 422);
        }

        if (!array_key_exists($field, $pengadaan->getAttributes())) {
            return response()->json(['message' => 'Field dokumen tidak ditemukan.'], 404);
        }

        $arr      = $this->normalizeArray($pengadaan->{$field});
        $normPath = $this->normalizePublicDiskPath($path);

        if (!$normPath) {
            return response()->json(['message' => 'Path tidak valid.'], 422);
        }

        $new = array_values(array_filter($arr, function ($x) use ($normPath) {
            $p = $this->normalizePublicDiskPath($x);
            return $p !== $normPath;
        }));

        DB::beginTransaction();
        try {
            try {
                Storage::disk('public')->delete($normPath);
            } catch (\Throwable $e) {
            }

            // ✅ LOG: hapus file dokumen (sebelum save)
            $this->logActivity(
                'delete_file',
                'PPK menghapus file dokumen pada pengadaan ID: ' . $pengadaan->id
            );

            $pengadaan->{$field} = $new;
            $pengadaan->save();

            DB::commit();
            return response()->json(['ok' => true]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal menghapus file.'], 500);
        }
    }

    public function kelolaAkun()
    {
        $ppkName = auth()->user()->name ?? "PPK Utama";
        return view('PPK.KelolaAkun', compact('ppkName'));
    }

    public function updateAkun(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return redirect()->back()->withErrors(['auth' => 'Kamu belum login.'])->withInput();
        }

        $wantsPasswordChange = $request->filled('password') || $request->filled('password_confirmation');

        $rules = [
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ];

        if ($wantsPasswordChange) {
            $rules['current_password'] = ['required', 'string'];
            $rules['password']         = ['required', 'string', 'min:8', 'confirmed'];
        } else {
            $rules['current_password'] = ['nullable', 'string'];
            $rules['password']         = ['nullable', 'string', 'min:8', 'confirmed'];
        }

        $data = $request->validate($rules);

        $user->name  = $data['name'];
        $user->email = $data['email'];

        if ($wantsPasswordChange) {
            if (!Hash::check($data['current_password'], $user->password)) {
                return redirect()->back()->withErrors(['current_password' => 'Password saat ini salah.'])->withInput();
            }
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        return redirect()->route('ppk.kelola.akun')->with('success', 'Akun berhasil diperbarui.');
    }

    public function showDokumen($id, $field, $file)
    {
        $allowed = $this->dokumenFieldLabels();

        if (!array_key_exists($field, $allowed)) {
            abort(404);
        }

        $pengadaan = Pengadaan::findOrFail($id);

        $arr = $this->normalizeArray($pengadaan->{$field});

        $matchPath = null;

        foreach ($arr as $p) {

            $p = ltrim((string)$p, '/');

            if (basename($p) === $file) {
                $matchPath = $p;
                break;
            }
        }

        if (
            !$matchPath ||
            !Storage::disk('public')->exists($matchPath)
        ) {
            abort(404);
        }

        $ext = strtolower(
            pathinfo($matchPath, PATHINFO_EXTENSION)
        );

        $mime = Storage::disk('public')
            ->mimeType($matchPath)
            ?: 'application/octet-stream';

        $stream = Storage::disk('public')
            ->readStream($matchPath);

        // PDF & gambar → preview browser
        $inline = in_array(
            $ext,
            ['pdf', 'png', 'jpg', 'jpeg', 'webp', 'gif'],
            true
        );

        $disposition = $inline
            ? 'inline'
            : 'attachment';

        return response()->stream(
            function () use ($stream) {
                fpassthru($stream);
            },
            200,
            [
                'Content-Type' => $mime,

                'Content-Disposition' =>
                $disposition .
                    '; filename="' . $file . '"',

                'Cache-Control' => 'private, no-store',

                'X-Frame-Options' => 'SAMEORIGIN',
            ]
        );
    }
    public function downloadDokumen($id, Request $request)
    {
        $request->validate([
            'field' => 'required|string|max:100',
            'path'  => 'required|string',
        ]);

        $field = $request->field;

        $path = ltrim(
            str_replace('\\', '/', $request->path),
            '/'
        );

        $pengadaan = Pengadaan::findOrFail($id);

        $allowed = $this->dokumenFieldLabels();

        // validasi field dokumen
        if (!array_key_exists($field, $allowed)) {
            abort(404);
        }

        // validasi file exists
        if (!Storage::disk('public')->exists($path)) {
            abort(404, 'File tidak ditemukan.');
        }

        // force download
        return Storage::disk('public')->download(
            $path,
            basename($path)
        );
    }

    // =========================
    // HELPERS
    // =========================
    private function handleRemoveExistingByHiddenInputs(Request $request, Pengadaan $pengadaan): void
    {
        $fileFields = array_keys($this->dokumenFieldLabels());

        foreach ($fileFields as $field) {
            $removeKey = $field . '_remove';
            $toRemove  = $request->input($removeKey);

            if (!is_array($toRemove) || count($toRemove) === 0) continue;

            $current    = $this->normalizeArray($pengadaan->{$field});
            $removeNorm = array_values(array_filter(array_map(fn($x) => $this->normalizePublicDiskPath($x), $toRemove)));

            if (count($removeNorm) === 0) continue;

            $new = array_values(array_filter($current, function ($x) use ($removeNorm) {
                $p = $this->normalizePublicDiskPath($x);
                return $p && !in_array($p, $removeNorm, true);
            }));

            foreach ($removeNorm as $p) {
                try {
                    Storage::disk('public')->delete($p);
                } catch (\Throwable $e) {
                }
            }

            $pengadaan->{$field} = $new;
        }
    }

    private function handleUploadDokumenToModel(Request $request, Pengadaan $pengadaan, bool $append = true): void
    {
        $fileFields = array_keys($this->dokumenFieldLabels());

        foreach ($fileFields as $field) {
            if (!$request->hasFile($field)) continue;

            $uploaded = $request->file($field);
            $files    = is_array($uploaded) ? $uploaded : [$uploaded];

            $paths = $append ? $this->normalizeArray($pengadaan->{$field}) : [];

            foreach ($files as $file) {
                if (!$file || !$file->isValid()) continue;

                $ext      = strtolower($file->getClientOriginalExtension());
                $base     = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeBase = Str::slug($base);
                if ($safeBase === '') $safeBase = 'dokumen';

                $filename = $safeBase . '_' . date('Ymd_His') . '_' . Str::random(6) . '.' . $ext;

                $stored = $file->storeAs("pengadaan/{$pengadaan->id}/{$field}", $filename, 'public');
                if ($stored) $paths[] = $stored;
            }

            $pengadaan->{$field} = array_values($paths);
        }
    }

    private function buildDokumenList(Pengadaan $p): array
    {
        $labels = $this->dokumenFieldLabels();
        $attrs  = $p->getAttributes();

        $out = [];

        foreach ($attrs as $field => $rawValue) {
            $lk = strtolower((string)$field);

            if (!(str_contains($lk, 'dokumen') || str_contains($lk, 'file') || str_contains($lk, 'lampiran'))) {
                continue;
            }

            if (in_array($field, ['dokumen_tidak_dipersyaratkan', 'dokumen_tidak_dipersyaratkan_json'], true)) {
                continue;
            }

            $files = $this->normalizeArray($rawValue);
            if (count($files) === 0) continue;

            $label = $labels[$field] ?? Str::title(str_replace('_', ' ', $field));

            foreach ($files as $one) {
                $path = $this->normalizePublicDiskPath($one);
                if (!$path) continue;

                $file = basename($path);

                $out[$field][] = [
                    'label' => $label,
                    'name'  => $file,
                    'path'  => $path,
                    'url' => route(
                        'ppk.arsip.dokumen.show',
                        [
                            'id'    => $p->id,
                            'field' => $field,
                            'file'  => basename($path),
                        ]
                    ),
                ];
            }
        }

        return $out;
    }

    private function normalizePublicDiskPath($raw): ?string
    {
        if ($raw === null) return null;

        $s = trim((string)$raw);
        if ($s === '') return null;

        $s = str_replace('\\', '/', $s);
        $s = explode('?', $s)[0];

        if (Str::startsWith($s, ['http://', 'https://'])) {
            $u = parse_url($s);
            if (!empty($u['path'])) $s = $u['path'];
        }

        $s = ltrim($s, '/');

        if (Str::startsWith($s, 'public/'))  $s = Str::after($s, 'public/');
        if (Str::startsWith($s, 'storage/')) $s = Str::after($s, 'storage/');
        $s = preg_replace('#^storage/#', '', $s);

        return $s !== '' ? $s : null;
    }

    private function dokumenFieldLabels(): array
    {
        return [
            'dokumen_kak'                      => 'Kerangka Acuan Kerja (KAK)',
            'dokumen_hps'                      => 'Harga Perkiraan Sendiri (HPS)',
            'dokumen_spesifikasi_teknis'        => 'Spesifikasi Teknis',
            'dokumen_rancangan_kontrak'         => 'Rancangan Kontrak',
            'dokumen_lembar_data_kualifikasi'   => 'Lembar Data Kualifikasi',
            'dokumen_lembar_data_pemilihan'     => 'Lembar Data Pemilihan',
            'dokumen_daftar_kuantitas_harga'    => 'Daftar Kuantitas dan Harga',
            'dokumen_jadwal_lokasi_pekerjaan'   => 'Jadwal & Lokasi Pekerjaan',
            'dokumen_gambar_rancangan_pekerjaan' => 'Gambar Rancangan Pekerjaan',
            'dokumen_amdal'                    => 'Dokumen AMDAL',
            'dokumen_penawaran'                => 'Dokumen Penawaran',
            'surat_penawaran'                  => 'Surat Penawaran',
            'dokumen_kemenkumham'              => 'Kemenkumham',
            'ba_pemberian_penjelasan'          => 'BA Pemberian Penjelasan',
            'ba_pengumuman_negosiasi'          => 'BA Pengumuman Negosiasi',
            'ba_sanggah_banding'               => 'BA Sanggah / Sanggah Banding',
            'ba_penetapan'                     => 'BA Penetapan',
            'laporan_hasil_pemilihan'          => 'Laporan Hasil Pemilihan',
            'dokumen_sppbj'                    => 'SPPBJ',
            'surat_perjanjian_kemitraan'       => 'Perjanjian Kemitraan',
            'surat_perjanjian_swakelola'       => 'Perjanjian Swakelola',
            'surat_penugasan_tim_swakelola'    => 'Penugasan Tim Swakelola',
            'dokumen_mou'                      => 'MoU',
            'dokumen_kontrak'                  => 'Dokumen Kontrak',
            'ringkasan_kontrak'                => 'Ringkasan Kontrak',
            'jaminan_pelaksanaan'              => 'Jaminan Pelaksanaan',
            'jaminan_uang_muka'                => 'Jaminan Uang Muka',
            'jaminan_pemeliharaan'             => 'Jaminan Pemeliharaan',
            'surat_tagihan'                    => 'Surat Tagihan',
            'surat_pesanan_epurchasing'        => 'Surat Pesanan E-Purchasing',
            'dokumen_spmk'                     => 'SPMK',
            'dokumen_sppd'                     => 'SPPD',
            'laporan_pelaksanaan_pekerjaan'    => 'Laporan Hasil Pelaksanaan',
            'laporan_penyelesaian_pekerjaan'   => 'Laporan Penyelesaian',
            'bap'                              => 'BAP',
            'bast_sementara'                   => 'BAST Sementara',
            'bast_akhir'                       => 'BAST Akhir',
            'dokumen_pendukung_lainya'         => 'Dokumen Pendukung Lainnya',
        ];
    }

    private function normalizeArray($value): array
    {
        if ($value === null) return [];

        if (is_array($value)) {
            return array_values(array_filter($value, fn($v) => $v !== null && $v !== ''));
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                return array_values(array_filter($decoded, fn($v) => $v !== null && $v !== ''));
            }
            return $value !== '' ? [$value] : [];
        }

        return [];
    }

    private function formatRupiah($value): string
    {
        if ($value === null || $value === '') return '-';
        $num = (int)$value;
        return 'Rp ' . number_format($num, 0, ',', '.');
    }

    private function resolveUnitId($rawUnit): ?int
    {
        if ($rawUnit === null) return null;

        $raw = trim((string)$rawUnit);
        if ($raw === '') return null;

        if (ctype_digit($raw)) return (int)$raw;

        if (!Schema::hasTable('units')) return null;

        $hasKode      = Schema::hasColumn('units', 'kode');
        $hasSlug      = Schema::hasColumn('units', 'slug');
        $hasUnitIdCol = Schema::hasColumn('units', 'unit_id');

        try {
            if ($hasKode) {
                $u = Unit::whereRaw('LOWER(kode) = ?', [mb_strtolower($raw)])->first();
                if ($u) return (int)$u->id;
            }
            if ($hasSlug) {
                $u = Unit::whereRaw('LOWER(slug) = ?', [mb_strtolower($raw)])->first();
                if ($u) return (int)$u->id;
            }
            if ($hasUnitIdCol) {
                $u = Unit::whereRaw('LOWER(unit_id) = ?', [mb_strtolower($raw)])->first();
                if ($u) return (int)$u->id;
            }

            if (Schema::hasColumn('units', 'nama')) {
                $u = Unit::whereRaw('LOWER(nama) = ?', [mb_strtolower($raw)])->first();
                if ($u) return (int)$u->id;
            }

            if (Schema::hasColumn('units', 'nama_unit')) {
                $u = Unit::whereRaw('LOWER(nama_unit) = ?', [mb_strtolower($raw)])->first();
                if ($u) return (int)$u->id;
            }

            if (Schema::hasColumn('units', 'name')) {
                $u2 = Unit::whereRaw('LOWER(name) = ?', [mb_strtolower($raw)])->first();
                if ($u2) return (int)$u2->id;
            }
        } catch (\Throwable $e) {
            return null;
        }

        return null;
    }

    // =========================
    // HISTORI AKTIVITAS
    // =========================
    public function historiAktivitas()
    {
        $logs = ActivityLog::with('user.unit')
            ->latest()
            ->limit(200)
            ->get();

        $data = $logs->map(function ($log) {
            return [
                'waktu'      => optional($log->created_at)
                    ->timezone('Asia/Jakarta')
                    ->format('d M Y H:i'),
                'nama'       => $log->user->name ?? '-',
                'nama_akun'  => $log->user->name ?? '-',
                'role'       => strtoupper(str_replace('_', ' ', $log->user->role ?? '-')),
                'unit'       => optional($log->user->unit)->nama ?? '-',
                'unit_kerja' => optional($log->user->unit)->nama ?? '-',
                'aktivitas'  => $log->description ?? '-',
            ];
        });

        // ✅ return array langsung (bukan { data: [...] }) agar blade JS bisa baca langsung
        return response()->json($data->values());
    }
}
