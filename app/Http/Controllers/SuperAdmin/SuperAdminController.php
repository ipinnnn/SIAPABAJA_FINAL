<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Pengadaan;
use App\Models\User;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Models\ActivityLog;
use App\Models\MasterMenu;
use Illuminate\Support\Facades\Log;

class SuperAdminController extends Controller
{
    public function dashboard()
    {
        $superAdminName = Auth::user()->name ?? 'Super Admin';

        /*
    |--------------------------------------------------------------------------
    | TAHUN DROPDOWN (DINAMIS DARI MASTER MENU)
    |--------------------------------------------------------------------------
    */
        $tahunOptions = MasterMenu::where('category', 'tahun')
            ->where('is_active', true)
            ->orderBy('order_index')
            ->pluck('nama')
            ->map(fn($t) => (int) $t)
            ->values()
            ->all();

        if (count($tahunOptions) === 0) {
            $y = (int) date('Y');
            $tahunOptions = [$y, $y - 1, $y - 2, $y - 3, $y - 4];
        }

        $defaultYear = $tahunOptions[0] ?? (int) date('Y');

        /*
    |--------------------------------------------------------------------------
    | UNIT DROPDOWN (AMBIL DARI AKUN ACTIVE)
    |--------------------------------------------------------------------------
    */
        $units = User::with('unit')
            ->whereIn('role', ['unit', 'ppk'])
            ->where('status', 'active')
            ->whereNotNull('unit_id')
            ->get()
            ->map(function ($user) {

                if (!$user->unit) {
                    return null;
                }

                return (object)[
                    'id'   => $user->unit->id,
                    'nama' => $user->unit->nama,
                ];
            })
            ->filter()
            ->unique('id')
            ->sortBy('nama')
            ->values();

        $registeredUnits = $units
            ->pluck('nama')
            ->values()
            ->all();

        $unitOptions = $units->map(function ($u) {
            return [
                'id'   => (int) $u->id,
                'name' => (string) $u->nama,
            ];
        })->values()->all();

        $totalUnitKerja = count($unitOptions);

        /*
    |--------------------------------------------------------------------------
    | TOTAL PPK ACTIVE
    |--------------------------------------------------------------------------
    */
        $totalPpk = User::whereRaw('LOWER(role) = ?', ['ppk'])
            ->where('status', 'active')
            ->count();

        /*
    |--------------------------------------------------------------------------
    | SUMMARY
    |--------------------------------------------------------------------------
    */
        $totalArsip = Pengadaan::count();

        $publik = Pengadaan::where('status_arsip', 'Publik')->count();

        $privat = Pengadaan::where('status_arsip', 'Privat')->count();

        $paketAll = Pengadaan::count();

        $nilaiAll = (int) Pengadaan::sum('nilai_kontrak');

        $summary = [
            [
                "label" => "Total Arsip",
                "value" => $totalArsip,
                "accent" => "navy",
                "icon" => "bi-file-earmark-text"
            ],
            [
                "label" => "Arsip Publik",
                "value" => $publik,
                "accent" => "green",
                "icon" => "bi-eye"
            ],
            [
                "label" => "Arsip Private",
                "value" => $privat,
                "accent" => "gray",
                "icon" => "bi-eye-slash"
            ],
            [
                "label" => "Total PPK",
                "value" => $totalPpk,
                "accent" => "green",
                "icon" => "bi-building"
            ],
            [
                "label" => "Total Unit Kerja",
                "value" => $totalUnitKerja,
                "accent" => "yellow",
                "icon" => "bi-buildings"
            ],
            [
                "label" => "Total Arsip Pengadaan",
                "value" => $paketAll,
                "accent" => "navy",
                "icon" => "bi-file-earmark-text",
                "sub" => "Paket Pengadaan Barang dan Jasa"
            ],
            [
                "label" => "Total Nilai Pengadaan",
                "value" => $this->formatRupiahNumber($nilaiAll),
                "accent" => "yellow",
                "icon" => "bi-buildings",
                "sub" => "Nilai Kontrak Pengadaan"
            ],
        ];

        /*
    |--------------------------------------------------------------------------
    | DONUT STATUS
    |--------------------------------------------------------------------------
    */
        $statusLabels = MasterMenu::where('category', 'status_pekerjaan')
            ->where('is_active', true)
            ->orderBy('order_index')
            ->pluck('nama')
            ->toArray();

        $statusValues = $this->countByStatusPekerjaan(
            null,
            null,
            $statusLabels
        );

        /*
    |--------------------------------------------------------------------------
    | BAR METODE
    |--------------------------------------------------------------------------
    */
        $barLabels = MasterMenu::where('category', 'metode_pengadaan')
            ->where('is_active', true)
            ->orderBy('order_index')
            ->pluck('nama')
            ->toArray();

        $barValues = $this->countByMetodePengadaan(
            null,
            null,
            $barLabels
        );

        return view('SuperAdmin.Dashboard', compact(
            'superAdminName',
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
        $tahun = ($tahun === null || $tahun === '') ? null : (int) $tahun;

        $unitId = $request->query('unit_id');
        $unitId = ($unitId === null || $unitId === '') ? null : (int) $unitId;

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

        $statusLabels = MasterMenu::where('category', 'status_pekerjaan')
            ->where('is_active', true)
            ->orderBy('order_index')
            ->pluck('nama')
            ->toArray();
        $statusValues = $this->countByStatusPekerjaan($unitId, $tahun, $statusLabels);

        $barLabels = MasterMenu::where('category', 'metode_pengadaan')
            ->where('is_active', true)
            ->orderBy('order_index')
            ->pluck('nama')
            ->toArray();

        $barValues = $this->countByMetodePengadaan($unitId, $tahun, $barLabels);


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

    public function arsipIndex(Request $request)
    {
        $superAdminName = Auth::user()->name ?? "Super Admin";

        $q      = trim((string) $request->query('q', ''));
        $unitQ  = trim((string) $request->query('unit', 'Semua'));
        $status = trim((string) $request->query('status', 'Semua'));
        $tahunQ = trim((string) $request->query('tahun', 'Semua'));

        $sortNilaiRaw = $request->query('sort_nilai');
        $sortNilai = null;
        if ($sortNilaiRaw !== null && $sortNilaiRaw !== '') {
            $tmp = strtolower(trim((string) $sortNilaiRaw));
            if (in_array($tmp, ['asc', 'desc'], true)) {
                $sortNilai = $tmp;
            }
        }

        $driver = DB::connection()->getDriverName();
        $likeOp = $driver === 'pgsql' ? 'ilike' : 'like';

        $unitId = null;
        $unitQNorm = mb_strtolower($unitQ, 'UTF-8');
        if ($unitQ !== '' && $unitQNorm !== 'semua' && $unitQNorm !== 'semua unit') {
            $unitId = $this->resolveUnitId($unitQ);
        }

        $statusNorm = mb_strtolower($status, 'UTF-8');
        $statusFixed = in_array($statusNorm, ['publik', 'privat'], true) ? ucfirst($statusNorm) : null;

        $tahunInt = null;
        $tahunStr = null;
        if ($tahunQ !== '' && mb_strtolower($tahunQ, 'UTF-8') !== 'semua') {
            $tahunStr = $tahunQ;
            if (ctype_digit($tahunQ)) {
                $tahunInt = (int) $tahunQ;
            }
        }

        $arsipsQuery = Pengadaan::with('unit')
            ->when($unitId !== null, fn($qq) => $qq->where('unit_id', $unitId))
            ->when($statusFixed !== null, fn($qq) => $qq->where('status_arsip', $statusFixed))
            ->when($tahunStr !== null, function ($qq) use ($tahunInt, $tahunStr, $driver) {
                $qq->where(function ($w) use ($tahunInt, $tahunStr, $driver) {
                    if ($tahunInt !== null) {
                        $w->orWhere('tahun', $tahunInt);
                    }

                    if ($driver === 'pgsql') {
                        $w->orWhereRaw("TRIM(CAST(tahun AS TEXT)) = ?", [$tahunStr]);
                    } else {
                        $w->orWhereRaw("TRIM(CAST(tahun AS CHAR)) = ?", [$tahunStr]);
                    }
                });
            })
            ->when($q !== '', function ($query) use ($q, $likeOp, $driver) {
                $like = '%' . $q . '%';

                $query->where(function ($w) use ($q, $like, $likeOp, $driver) {
                    if (ctype_digit($q)) {
                        $w->orWhere('tahun', (int) $q);
                        $w->orWhere('id', (int) $q);
                    }

                    $w->orWhere('nama_pekerjaan', $likeOp, $like)
                        ->orWhere('id_rup', $likeOp, $like)
                        ->orWhere('jenis_pengadaan', $likeOp, $like)
                        ->orWhere('status_arsip', $likeOp, $like)
                        ->orWhere('status_pekerjaan', $likeOp, $like)
                        ->orWhere('nama_rekanan', $likeOp, $like);

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

        $arsips = $arsipsQuery->paginate(10)->withQueryString();

        $mapped = $arsips->getCollection()->map(function (Pengadaan $p) {
            return [
                'id'                => $p->id,
                'pekerjaan'         => $p->nama_pekerjaan ?? '-',
                'id_rup'            => $p->id_rup ?? '-',
                'idrup'             => $p->id_rup ?? '-',
                'tahun'             => $p->tahun ?? null,
                'metode_pbj'        => $p->metode_pengadaan ?? '-',  // ← key harus metode_pbj
                'jenis_pengadaan'   => $p->jenis_pengadaan ?? '-',
                'jenis'             => $p->jenis_pengadaan ?? '-',   // ← tambahkan ini
                'status_pekerjaan'  => $p->status_pekerjaan ?? '-',
                'status_arsip'      => $p->status_arsip ?? '-',
                'nilai_kontrak'     => $this->formatRupiah($p->nilai_kontrak),
                'pagu'              => $this->formatRupiah($p->pagu_anggaran), // ← key harus pagu
                'pagu_anggaran'     => $this->formatRupiah($p->pagu_anggaran),
                'hps'               => $this->formatRupiah($p->hps),
                'nama_rekanan'      => $p->nama_rekanan ?? '-',
                'rekanan'           => $p->nama_rekanan ?? '-',      // ← tambahkan ini
                'unit'              => $p->unit?->nama ?? ($p->unit?->nama_unit ?? ($p->unit?->name ?? '-')),
                'dokumen'           => $this->buildDokumenList($p),
                'dokumen_tidak_dipersyaratkan' => $this->normalizeArray($p->dokumen_tidak_dipersyaratkan),
            ];
        });
        $arsips->setCollection($mapped);

        $years = Pengadaan::whereNotNull('tahun')
            ->select('tahun')
            ->distinct()
            ->orderBy('tahun', 'desc')
            ->pluck('tahun')
            ->map(fn($t) => (string) $t)
            ->values()
            ->all();

        if (count($years) === 0) {
            $y = (int) date('Y');
            $years = [(string) $y, (string) ($y - 1), (string) ($y - 2), (string) ($y - 3), (string) ($y - 4)];
        }

        $unitNameCol = Schema::hasColumn('units', 'nama') ? 'nama'
            : (Schema::hasColumn('units', 'nama_unit') ? 'nama_unit'
                : (Schema::hasColumn('units', 'name') ? 'name' : 'id'));

        $unitOptions = User::with('unit')
            ->whereRaw('LOWER(role) = ?', ['unit'])
            ->where('status', 'active')
            ->get()
            ->map(function ($user) use ($unitNameCol) {
                return $user->unit?->{$unitNameCol};
            })
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();

        return view('SuperAdmin.ArsipPBJ', compact('superAdminName', 'arsips', 'unitOptions', 'years'));
    }

    public function pengadaanCreate()
    {
        $superAdminName = Auth::user()->name ?? "Super Admin";

        /*
    |--------------------------------------------------------------------------
    | TAHUN (DINAMIS DARI MASTER MENU)
    |--------------------------------------------------------------------------
    */
        $tahunOptions = MasterMenu::where('category', 'tahun')
            ->where('is_active', true)
            ->orderBy('order_index')
            ->pluck('nama')
            ->toArray();

        /*
    |--------------------------------------------------------------------------
    | UNIT + PPK ACTIVE
    |--------------------------------------------------------------------------
    */
        $units = User::with('unit')
            ->whereIn('role', ['unit', 'ppk'])
            ->where('status', 'active')
            ->whereNotNull('unit_id')
            ->get()
            ->map(function ($user) {

                if (!$user->unit) {
                    return null;
                }

                return (object)[
                    'id' => $user->unit->id,
                    'nama' => $user->unit->nama,
                ];
            })
            ->filter()
            ->unique('id')
            ->sortBy('nama')
            ->values();

        $unitOptions = $units->pluck('nama')->values()->all();

        /*
    |--------------------------------------------------------------------------
    | JENIS PENGADAAN
    |--------------------------------------------------------------------------
    */
        $jenisPengadaanOptions = MasterMenu::where('category', 'jenis_pengadaan')
            ->where('is_active', true)
            ->orderBy('order_index')
            ->pluck('nama')
            ->toArray();

        /*
    |--------------------------------------------------------------------------
    | METODE PENGADAAN
    |--------------------------------------------------------------------------
    */
        $metodePengadaanOptions = MasterMenu::where('category', 'metode_pengadaan')
            ->where('is_active', true)
            ->orderBy('order_index')
            ->pluck('nama')
            ->toArray();

        /*
    |--------------------------------------------------------------------------
    | STATUS PEKERJAAN
    |--------------------------------------------------------------------------
    */
        $statusPekerjaanOptions = MasterMenu::where('category', 'status_pekerjaan')
            ->where('is_active', true)
            ->orderBy('order_index')
            ->pluck('nama')
            ->toArray();

        return view('SuperAdmin.TambahPengadaan', compact(
            'superAdminName',
            'tahunOptions',
            'units',
            'unitOptions',
            'jenisPengadaanOptions',
            'metodePengadaanOptions',
            'statusPekerjaanOptions'
        ));
    }

    public function pengadaanStore(Request $request)
    {
        $rules = [
            'tahun' => 'required|integer|min:2000|max:' . (date('Y') + 5),
            'unit_id' => 'required|integer|exists:units,id',
            'nama_pekerjaan' => 'nullable|string|max:255',
            'id_rup' => 'nullable|string|max:255',
            'jenis_pengadaan' => 'required|string|max:100',
            'metode_pengadaan' => 'required|string|max:100',
            'status_pekerjaan' => 'required|string|max:100',
            'status_arsip' => 'required|in:Publik,Privat',
            'pagu_anggaran' => 'nullable|string|max:50',
            'hps' => 'nullable|string|max:50',
            'nilai_kontrak' => 'nullable|string|max:50',
            'nama_rekanan' => 'nullable|string|max:255',
            'dokumen_tidak_dipersyaratkan_json' => 'nullable|string',
            'dokumen_tidak_dipersyaratkan' => 'nullable|array',
        ];

        $data = $request->validate($rules);

        $toInt = function ($v) {
            if ($v === null) return null;

            $num = preg_replace('/[^0-9]/', '', (string) $v);

            return $num === '' ? null : (int) $num;
        };

        $payload = [
            'tahun'            => (int) $data['tahun'],
            'unit_id'          => (int) $data['unit_id'],
            'created_by'       => Auth::id(),
            'nama_pekerjaan'   => $data['nama_pekerjaan'] ?? null,
            'id_rup'           => $data['id_rup'] ?? null,
            'jenis_pengadaan'  => $data['jenis_pengadaan'],
            'metode_pengadaan' => $data['metode_pengadaan'],
            'status_pekerjaan' => $data['status_pekerjaan'],
            'status_arsip'     => $data['status_arsip'],
            'pagu_anggaran'    => $toInt($data['pagu_anggaran'] ?? null),
            'hps'              => $toInt($data['hps'] ?? null),
            'nilai_kontrak'    => $toInt($data['nilai_kontrak'] ?? null),
            'nama_rekanan'     => $data['nama_rekanan'] ?? null,
        ];

        $docTidak = [];

        if (is_array($request->input('dokumen_tidak_dipersyaratkan'))) {

            $docTidak = $request->input('dokumen_tidak_dipersyaratkan');
        } else {

            $json = $request->input('dokumen_tidak_dipersyaratkan_json');

            if (is_string($json) && trim($json) !== '') {

                $decoded = json_decode($json, true);

                if (is_array($decoded)) {
                    $docTidak = $decoded;
                }
            }
        }

        $payload['dokumen_tidak_dipersyaratkan'] =
            array_values(
                array_filter(
                    $docTidak,
                    fn($x) => $x !== null && $x !== ''
                )
            );

        DB::beginTransaction();

        try {

            $pengadaan = Pengadaan::create($payload);

            $this->handleUploadDokumenToModel(
                $request,
                $pengadaan,
                false
            );

            $pengadaan->save();

            $this->logActivity(
                'create',
                'SuperAdmin menambahkan pengadaan: ' .
                    $pengadaan->nama_pekerjaan
            );

            DB::commit();

            return redirect()
                ->route('superadmin.arsip')
                ->with('success', 'Pengadaan baru berhasil ditambahkan!');
        } catch (\Throwable $e) {

            DB::rollBack();

            if (isset($pengadaan) && $pengadaan instanceof Pengadaan) {

                try {
                    Storage::disk('public')
                        ->deleteDirectory("pengadaan/{$pengadaan->id}");
                } catch (\Throwable $ex) {
                }

                try {
                    $pengadaan->delete();
                } catch (\Throwable $ex) {
                }
            }

            return redirect()
                ->back()
                ->withInput()
                ->withErrors([
                    'upload' => 'Gagal menyimpan pengadaan/dokumen.'
                ]);
        }
    }

    public function arsipEdit($id)
    {
        $superAdminName = Auth::user()->name ?? "Super Admin";

        $pengadaan = Pengadaan::with('unit')->findOrFail($id);
        $arsip = $pengadaan;

        /*
|--------------------------------------------------------------------------
| TAHUN (DINAMIS DARI MASTER MENU)
|--------------------------------------------------------------------------
*/
        $tahunOptions = MasterMenu::where('category', 'tahun')
            ->where('is_active', true)
            ->orderBy('order_index')
            ->pluck('nama')
            ->toArray();

        /*
|--------------------------------------------------------------------------
| UNIT + PPK ACTIVE
|--------------------------------------------------------------------------
*/
        $units = User::with('unit')
            ->whereIn('role', ['unit', 'ppk'])
            ->where('status', 'active')
            ->whereNotNull('unit_id')
            ->get()
            ->map(function ($user) {

                if (!$user->unit) {
                    return null;
                }

                return (object)[
                    'id' => $user->unit->id,
                    'nama' => $user->unit->nama,
                ];
            })
            ->filter()
            ->unique('id')
            ->sortBy('nama')
            ->values();

        $unitOptions = $units->pluck('nama')->values()->all();

        $jenisPengadaanOptions = MasterMenu::where('category', 'jenis_pengadaan')
            ->where('is_active', true)
            ->orderBy('order_index')
            ->pluck('nama')
            ->toArray();

        $metodePengadaanOptions = MasterMenu::where('category', 'metode_pengadaan')
            ->where('is_active', true)
            ->orderBy('order_index')
            ->pluck('nama')
            ->toArray();

        $statusPekerjaanOptions = MasterMenu::where('category', 'status_pekerjaan')
            ->where('is_active', true)
            ->orderBy('order_index')
            ->pluck('nama')
            ->toArray();

        // 🔹 DOKUMEN EXISTING
        $dokumenExisting = [];
        foreach ($this->dokumenFieldLabels() as $field => $label) {
            $paths = $this->normalizeArray($pengadaan->{$field} ?? null);
            if (count($paths) > 0) {
                $dokumenExisting[$field] = collect($paths)->map(function ($p) use ($label) {
                    $path = $this->normalizePublicDiskPath($p);
                    return [
                        'label' => $label,
                        'path' => $path,
                        'url' => '/storage/' . ltrim((string) $path, '/'),
                    ];
                })->all();
            }
        }

        $pengadaan->dokumen_tidak_dipersyaratkan = $this->normalizeArray($pengadaan->dokumen_tidak_dipersyaratkan);

        return view('SuperAdmin.EditArsip', compact(
            'superAdminName',
            'pengadaan',
            'arsip',
            'tahunOptions',
            'units',
            'unitOptions',
            'jenisPengadaanOptions',
            'metodePengadaanOptions',
            'statusPekerjaanOptions',
            'dokumenExisting'
        ));
    }

    public function arsipUpdate(Request $request, $id)
    {
        $pengadaan = Pengadaan::findOrFail($id);

        $rules = [
            'tahun' => 'nullable|integer|min:2000|max:' . (date('Y') + 5),
            'unit_id' => [
                'nullable',
                'integer',
                Rule::exists('units', 'id')->where(function ($query) {
                    $query->where('is_active', true);
                }),
            ],
            'nama_pekerjaan' => 'nullable|string|max:255',
            'id_rup' => 'nullable|string|max:255',
            'jenis_pengadaan' => 'nullable|string|max:100',
            'metode_pengadaan' => 'nullable|string|max:100',
            'status_pekerjaan' => 'nullable|string|max:100',
            'status_arsip' => 'nullable|in:Publik,Privat',
            'pagu_anggaran' => 'nullable|string|max:50',
            'hps' => 'nullable|string|max:50',
            'nilai_kontrak' => 'nullable|string|max:50',
            'nama_rekanan' => 'nullable|string|max:255',
            'dokumen_tidak_dipersyaratkan_json' => 'nullable|string',
            'dokumen_tidak_dipersyaratkan' => 'nullable|array',
        ];

        $data = $request->validate($rules);

        $toInt = function ($v) {
            if ($v === null) return null;
            $num = preg_replace('/[^0-9]/', '', (string) $v);
            return $num === '' ? null : (int) $num;
        };

        DB::beginTransaction();
        try {
            if ($request->filled('tahun')) $pengadaan->tahun = (int) $data['tahun'];

            if ($request->filled('unit_id')) {
                $resolvedUnitId = (int) $data['unit_id'];

                if (!$resolvedUnitId) {
                    DB::rollBack();
                    $key = 'unit_id';
                    return redirect()->back()->withInput()->withErrors([$key => 'Unit kerja tidak valid / tidak ditemukan di database.']);
                }

                $pengadaan->unit_id = (int) $resolvedUnitId;
            }

            if ($request->filled('nama_pekerjaan')) $pengadaan->nama_pekerjaan = $data['nama_pekerjaan'];
            if ($request->filled('id_rup')) $pengadaan->id_rup = $data['id_rup'];
            if ($request->filled('jenis_pengadaan')) $pengadaan->jenis_pengadaan = $data['jenis_pengadaan'];
            if ($request->filled('metode_pengadaan')) {
                $pengadaan->metode_pengadaan = $data['metode_pengadaan'];
            }
            if ($request->filled('status_pekerjaan')) $pengadaan->status_pekerjaan = $data['status_pekerjaan'];
            if ($request->filled('status_arsip')) $pengadaan->status_arsip = $data['status_arsip'];

            if (array_key_exists('pagu_anggaran', $data)) $pengadaan->pagu_anggaran = $toInt($data['pagu_anggaran']);
            if (array_key_exists('hps', $data)) $pengadaan->hps = $toInt($data['hps']);
            if (array_key_exists('nilai_kontrak', $data)) $pengadaan->nilai_kontrak = $toInt($data['nilai_kontrak']);
            if (array_key_exists('nama_rekanan', $data)) $pengadaan->nama_rekanan = $data['nama_rekanan'];

            $docTidak = [];
            if (is_array($request->input('dokumen_tidak_dipersyaratkan'))) {
                $docTidak = $request->input('dokumen_tidak_dipersyaratkan');
            } else {
                $json = $request->input('dokumen_tidak_dipersyaratkan_json');
                if (is_string($json) && trim($json) !== '') {
                    $decoded = json_decode($json, true);
                    if (is_array($decoded)) {
                        $docTidak = $decoded;
                    }
                }
            }
            $pengadaan->dokumen_tidak_dipersyaratkan = array_values(array_filter($docTidak, fn($x) => $x !== null && $x !== ''));

            $this->handleUploadDokumenToModel($request, $pengadaan, true);
            $this->handleRemoveExistingByHiddenInputs($request, $pengadaan);

            $pengadaan->save();
            // ✅ TAMBAHKAN DI SINI
            $this->logActivity(
                'update',
                'SuperAdmin mengedit pengadaan ID: ' . $pengadaan->id
            );
            DB::commit();

            return redirect()->route('superadmin.arsip')->with('success', 'Arsip berhasil diperbarui.');
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
            try {
                Storage::disk('public')->deleteDirectory("pengadaan/{$pengadaan->id}");
            } catch (\Throwable $e) {
            }

            $this->logActivity(
                'delete',
                'SuperAdmin menghapus pengadaan ID: ' . $pengadaan->id
            );

            $pengadaan->delete();

            DB::commit();

            // ✅ return JSON karena JS pakai fetch DELETE
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json(['success' => true]);
            }
            return redirect()->route('superadmin.arsip')->with('success', 'Arsip berhasil dihapus.');
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Delete arsip error: ' . $e->getMessage());

            if (request()->expectsJson()) {
                return response()->json([
                    'error' => $e->getMessage()
                ], 500);
            }

            return redirect()
                ->back()
                ->withErrors([
                    'delete' => 'Gagal menghapus arsip.'
                ]);
        }
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

    /**
     * Force-download dokumen (selalu attachment, tidak inline).
     * GET /super-admin/arsip/{id}/dokumen-download?field=xxx&file=yyy
     */
    public function downloadDokumen(Request $request, $id)
    {
        $field = $request->query('field');
        $file  = $request->query('file');

        $allowed = $this->dokumenFieldLabels();

        if (!$field || !$file || !array_key_exists($field, $allowed)) {
            abort(404);
        }

        $pengadaan = Pengadaan::findOrFail($id);
        $arr       = $this->normalizeArray($pengadaan->{$field});

        $matchPath = null;
        foreach ($arr as $p) {
            $p = ltrim((string) $p, '/');
            if (basename($p) === $file) {
                $matchPath = $p;
                break;
            }
        }

        if (!$matchPath || !Storage::disk('public')->exists($matchPath)) {
            abort(404);
        }

        return Storage::disk('public')->download($matchPath, $file);
    }

    public function kelolaMenu()
    {
        $superAdminName = Auth::user()->name ?? "Super Admin";

        $menus = MasterMenu::orderBy('category')
            ->orderBy('order_index')
            ->get()
            ->groupBy('category');

        return view('SuperAdmin.KelolaMenu', compact(
            'superAdminName',
            'menus'
        ));
    }

    public function storeMenu(Request $request, $type)
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
        ]);

        $lastOrder = MasterMenu::where('category', $type)
            ->max('order_index');

        $menu = MasterMenu::create([
            'category'    => $type,
            'nama'        => $data['nama'],
            'is_active'   => true,
            'order_index' => ($lastOrder ?? 0) + 1,
        ]);

        return response()->json([
            'success' => true,
            'data' => $menu,
        ]);
    }

    public function updateMenu(Request $request, $type, $id)
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
        ]);

        $menu = MasterMenu::where('category', $type)
            ->findOrFail($id);

        $menu->nama = $data['nama'];

        $menu->save();

        return response()->json([
            'success' => true,
            'data' => $menu,
        ]);
    }

    public function destroyMenu($type, $id)
    {
        $menu = MasterMenu::where('category', $type)
            ->findOrFail($id);

        $menu->delete();

        return response()->json([
            'success' => true,
        ]);
    }

    public function updateAkun(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user) {
            return redirect()->back()->withErrors([
                'auth' => 'Kamu belum login.'
            ])->withInput();
        }

        $wantsPasswordChange =
            $request->filled('password') ||
            $request->filled('password_confirmation');

        $rules = [
            'name'  => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id)
            ],
        ];

        if ($wantsPasswordChange) {
            $rules['current_password'] = ['required', 'string'];
            $rules['password'] = ['required', 'string', 'min:8', 'confirmed'];
        } else {
            $rules['current_password'] = ['nullable', 'string'];
            $rules['password'] = ['nullable', 'string', 'min:8', 'confirmed'];
        }

        $data = $request->validate($rules);

        $user->name  = $data['name'];
        $user->email = $data['email'];

        if ($wantsPasswordChange) {
            if (!Hash::check($data['current_password'], $user->password)) {
                return redirect()->back()->withErrors([
                    'current_password' => 'Password saat ini salah.'
                ])->withInput();
            }

            $user->password = Hash::make($data['password']);
        }

        $user->save();

        return redirect()
            ->route('superadmin.kelola.akun')
            ->with('success', 'Akun berhasil diperbarui.');
    }

    public function kelolaAkun()
    {
        $user = Auth::user();

        $superAdminName  = $user->name ?? 'Super Admin';
        $superAdminEmail = $user->email ?? 'superadmin@gmail.com';
        $roleText        = 'SUPER ADMIN';

        return view('SuperAdmin.KelolaAkun', compact(
            'superAdminName',
            'superAdminEmail',
            'roleText'
        ));
    }

    public function kelolaAkunPpk()
    {
        $superAdminName = Auth::user()->name ?? 'Super Admin';

        /*
    |--------------------------------------------------------------------------
    | Ambil semua unit untuk dropdown
    |--------------------------------------------------------------------------
    */
        $units = User::with('unit')
            ->whereRaw('LOWER(role) = ?', ['unit'])
            ->where('status', 'active')
            ->get()
            ->map(function ($user) {

                if (!$user->unit) {
                    return null;
                }

                return (object)[
                    'id' => $user->unit->id,
                    'nama' => $user->unit->nama,
                ];
            })
            ->filter()
            ->unique('id')
            ->sortBy('nama')
            ->values();

        /*
    |--------------------------------------------------------------------------
    | Ambil akun PPK
    |--------------------------------------------------------------------------
    */
        $ppkAccounts = User::with('unit')
            ->whereRaw('LOWER(role) = ?', ['ppk'])
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($user) {
                return [
                    'id'         => $user->id,
                    'username'   => $user->name,
                    'unit_id'    => $user->unit_id,
                    'unit_nama'  => $user->unit?->nama ?? '-',
                    'email'      => $user->email,
                    'password'   => '********',
                    'status'     => $user->status ?? 'active',
                ];
            })
            ->toArray();

        return view(
            'SuperAdmin.KelolaAkunPpk',
            compact(
                'superAdminName',
                'ppkAccounts',
                'units'
            )
        );
    }
    public function storePpk(Request $request)
    {
        $data = $request->validate([
            'username'     => ['required', 'string', 'max:255'],
            'unit_kerja'   => ['required', 'string', 'max:255'],
            'email'        => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'     => ['required', 'string', 'min:6'],
            'status'       => ['required', Rule::in(['active', 'inactive'])],
        ]);

        /*
    |--------------------------------------------------------------------------
    | Cari unit berdasarkan nama
    |--------------------------------------------------------------------------
    */
        $unit = Unit::whereRaw(
            'LOWER(nama) = ?',
            [strtolower($data['unit_kerja'])]
        )->first();

        /*
    |--------------------------------------------------------------------------
    | Jika unit belum ada → buat baru
    |--------------------------------------------------------------------------
    */
        if (!$unit) {
            $unit = Unit::create([
                'nama' => $data['unit_kerja'],
                'is_active' => true,
            ]);
        }

        /*
    |--------------------------------------------------------------------------
    | Buat user PPK
    |--------------------------------------------------------------------------
    */
        User::create([
            'name'      => $data['username'],
            'unit_id'   => $unit->id,
            'email'     => $data['email'],
            'password'  => Hash::make($data['password']),
            'role'      => 'ppk',
            'status'    => $data['status'],
        ]);

        $this->logActivity(
            'create_ppk',
            'SuperAdmin menambahkan akun PPK: ' . $data['username']
        );

        return redirect()
            ->route('superadmin.kelola.akun.ppk')
            ->with('success', 'Data admin PPK berhasil ditambahkan.');
    }

    public function updatePpk(Request $request, $id)
    {
        $user = User::whereRaw('LOWER(role) = ?', ['ppk'])
            ->findOrFail($id);

        $data = $request->validate([
            'username'     => ['required', 'string', 'max:255'],
            'unit_kerja'   => ['required', 'string', 'max:255'],
            'email'        => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id)
            ],
            'password'     => ['nullable', 'string', 'min:6'],
            'status'       => ['required', Rule::in(['active', 'inactive'])],
        ]);

        /*
    |--------------------------------------------------------------------------
    | Cari unit berdasarkan nama
    |--------------------------------------------------------------------------
    */
        $unit = Unit::whereRaw(
            'LOWER(nama) = ?',
            [strtolower($data['unit_kerja'])]
        )->first();

        /*
    |--------------------------------------------------------------------------
    | Jika belum ada → buat baru
    |--------------------------------------------------------------------------
    */
        if (!$unit) {
            $unit = Unit::create([
                'nama' => $data['unit_kerja'],
                'is_active' => true,
            ]);
        }

        /*
    |--------------------------------------------------------------------------
    | Update user PPK
    |--------------------------------------------------------------------------
    */
        $user->name = $data['username'];
        $user->unit_id = $unit->id;
        $user->email = $data['email'];
        $user->status = $data['status'];
        $user->role = 'ppk';

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        $this->logActivity(
            'update_ppk',
            'SuperAdmin mengupdate akun PPK ID: ' . $user->id
        );

        return redirect()
            ->route('superadmin.kelola.akun.ppk')
            ->with('success', 'Data admin PPK berhasil diperbarui.');
    }

    public function destroyPpk($id)
    {
        $user = User::whereRaw('LOWER(role) = ?', ['ppk'])->findOrFail($id);
        $this->logActivity(
            'delete_ppk',
            'SuperAdmin menghapus akun PPK ID: ' . $user->id
        );

        $user->delete();

        return redirect()
            ->route('superadmin.kelola.akun.ppk')
            ->with('success', 'Data admin PPK berhasil dihapus.');
    }



    public function kelolaAkunUnit()
    {
        $superAdminName = Auth::user()->name ?? 'Super Admin';

        $units = User::with('unit')
            ->whereRaw('LOWER(role) = ?', ['unit'])
            ->where('status', 'active')
            ->get()
            ->map(function ($user) {

                if (!$user->unit) {
                    return null;
                }

                return (object)[
                    'id' => $user->unit->id,
                    'nama' => $user->unit->nama,
                ];
            })
            ->filter()
            ->unique('id')
            ->sortBy('nama')
            ->values();
        $unitAccounts = User::with('unit')
            ->whereRaw('LOWER(role) = ?', ['unit'])
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'username' => $user->name,
                    'unit_id' => $user->unit_id,
                    'unit_nama' => $user->unit?->nama ?? '-',
                    'email' => $user->email,
                    'password' => '********',
                    'status' => $user->status ?? 'active',
                ];
            })
            ->toArray();

        return view(
            'SuperAdmin.KelolaAkunUnit',
            compact(
                'superAdminName',
                'unitAccounts',
                'units'
            )
        );
    }

    public function storeUnit(Request $request)
    {
        $data = $request->validate([
            'username'     => ['required', 'string', 'max:255'],
            'unit_kerja'   => ['required', 'string', 'max:255'],
            'email'        => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'     => ['required', 'string', 'min:6'],
            'status'       => ['required', Rule::in(['active', 'inactive'])],
        ]);

        /*
    |--------------------------------------------------------------------------
    | Cari unit berdasarkan nama
    |--------------------------------------------------------------------------
    */
        $unit = Unit::whereRaw(
            'LOWER(nama) = ?',
            [strtolower($data['unit_kerja'])]
        )->first();

        /*
    |--------------------------------------------------------------------------
    | Jika unit belum ada → buat baru
    |--------------------------------------------------------------------------
    */
        if (!$unit) {
            $unit = Unit::create([
                'nama' => $data['unit_kerja'],
                'is_active' => true,
            ]);
        }

        /*
    |--------------------------------------------------------------------------
    | Buat user unit
    |--------------------------------------------------------------------------
    */
        User::create([
            'name'      => $data['username'],
            'unit_id'   => $unit->id,
            'email'     => $data['email'],
            'password'  => Hash::make($data['password']),
            'role'      => 'unit',
            'status'    => $data['status'],
        ]);

        $this->logActivity(
            'create_unit',
            'SuperAdmin menambahkan akun Unit: ' . $data['username']
        );

        return redirect()
            ->route('superadmin.kelola.akun.unit')
            ->with('success', 'Data PIC Unit berhasil ditambahkan.');
    }

    public function updateUnit(Request $request, $id)
    {
        $user = User::whereRaw('LOWER(role) = ?', ['unit'])
            ->findOrFail($id);

        $data = $request->validate([
            'username'     => ['required', 'string', 'max:255'],
            'unit_kerja'   => ['required', 'string', 'max:255'],
            'email'        => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id)
            ],
            'password'     => ['nullable', 'string', 'min:6'],
            'status'       => ['required', Rule::in(['active', 'inactive'])],
        ]);

        /*
    |--------------------------------------------------------------------------
    | Cari unit berdasarkan nama
    |--------------------------------------------------------------------------
    */
        $unit = Unit::whereRaw(
            'LOWER(nama) = ?',
            [strtolower($data['unit_kerja'])]
        )->first();

        /*
    |--------------------------------------------------------------------------
    | Jika belum ada → buat baru
    |--------------------------------------------------------------------------
    */
        if (!$unit) {
            $unit = Unit::create([
                'nama' => $data['unit_kerja'],
                'is_active' => true,
            ]);
        }

        /*
    |--------------------------------------------------------------------------
    | Update user
    |--------------------------------------------------------------------------
    */
        $user->name = $data['username'];
        $user->unit_id = $unit->id;
        $user->email = $data['email'];
        $user->status = $data['status'];
        $user->role = 'unit';

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        $this->logActivity(
            'update_unit',
            'SuperAdmin mengupdate akun Unit ID: ' . $user->id
        );

        return redirect()
            ->route('superadmin.kelola.akun.unit')
            ->with('success', 'Data PIC Unit berhasil diperbarui.');
    }

    public function destroyUnit($id)
    {
        $user = User::whereRaw('LOWER(role) = ?', ['unit'])->findOrFail($id);
        $this->logActivity(
            'delete_unit',
            'SuperAdmin menghapus akun Unit ID: ' . $user->id
        );

        $user->delete();

        return redirect()
            ->route('superadmin.kelola.akun.unit')
            ->with('success', 'Data PIC Unit berhasil dihapus.');
    }

    private function countByStatusPekerjaan(?int $unitId, ?int $tahun, array $labels): array
    {
        $rows = Pengadaan::query()
            ->when($unitId !== null, fn($q) => $q->where('unit_id', $unitId))
            ->when($tahun !== null, fn($q) => $q->where('tahun', $tahun))
            ->select('status_pekerjaan', DB::raw('COUNT(*) as cnt'))
            ->groupBy('status_pekerjaan')
            ->pluck('cnt', 'status_pekerjaan')
            ->toArray();

        return array_map(fn($lbl) => (int) ($rows[$lbl] ?? 0), $labels);
    }

    private function countByMetodePengadaan(?int $unitId, ?int $tahun, array $labels): array
    {
        $raw = Pengadaan::query()
            ->when($unitId !== null, fn($q) => $q->where('unit_id', $unitId))
            ->when($tahun !== null, fn($q) => $q->where('tahun', $tahun))
            ->whereNotNull('metode_pengadaan')
            ->selectRaw("LOWER(TRIM(metode_pengadaan)) as metode, COUNT(*) as cnt")
            ->groupBy('metode')
            ->pluck('cnt', 'metode')
            ->toArray();

        $out = [];

        foreach ($labels as $label) {

            $normalized = strtolower(trim($label));

            $alternatives = array_unique([
                $normalized,
                str_replace('catalogue', 'catalog', $normalized),
                str_replace('catalog', 'catalogue', $normalized),
                str_replace(' / ', '/', $normalized),
                str_replace('/', ' / ', $normalized),
            ]);

            $total = 0;

            foreach ($alternatives as $alt) {
                $total += (int) ($raw[$alt] ?? 0);
            }

            $out[] = $total;
        }

        return $out;
    }

    private function handleRemoveExistingByHiddenInputs(Request $request, Pengadaan $pengadaan): void
    {
        $fileFields = array_keys($this->dokumenFieldLabels());

        foreach ($fileFields as $field) {
            $removeKey = $field . '_remove';
            $toRemove = $request->input($removeKey);

            if (!is_array($toRemove) || count($toRemove) === 0) continue;

            $current = $this->normalizeArray($pengadaan->{$field});
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
            $files = is_array($uploaded) ? $uploaded : [$uploaded];

            $paths = $append ? $this->normalizeArray($pengadaan->{$field}) : [];

            foreach ($files as $file) {
                if (!$file || !$file->isValid()) continue;

                $ext = strtolower($file->getClientOriginalExtension());
                $base = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

                $safeBase = Str::slug($base);
                if ($safeBase === '') {
                    $safeBase = 'dokumen';
                }

                $filename = $safeBase . '_' . date('Ymd_His') . '_' . Str::random(6) . '.' . $ext;
                $stored = $file->storeAs("pengadaan/{$pengadaan->id}/{$field}", $filename, 'public');

                if ($stored) {
                    $paths[] = $stored;
                }
            }

            $pengadaan->{$field} = array_values($paths);
        }
    }

   private function buildDokumenList(Pengadaan $p): array
{
    $labels = $this->dokumenFieldLabels();
    $out = [];

    foreach ($labels as $field => $label) {
        try {
            $raw = $p->getAttributes()[$field] ?? null;
            if ($raw === null) continue;

            $files = $this->normalizeArray($raw);
            if (count($files) === 0) continue;

            foreach ($files as $one) {
                $path = $this->normalizePublicDiskPath($one);
                if (!$path) continue;

                $file = basename($path);

                $out[$field][] = [
                    'label' => $label,
                    'name'  => $file,
                    'path'  => $path,
                    'url'   => route(
                        'superadmin.arsip.dokumen.show',
                        [
                            'id'    => $p->id,
                            'field' => $field,
                            'file'  => $file,
                        ]
                    ),
                ];
            }
        } catch (\Throwable $e) {
            continue;
        }
    }

    return $out;
}

    private function normalizePublicDiskPath($raw): ?string
    {
        if ($raw === null) return null;

        $s = trim((string) $raw);
        if ($s === '') return null;

        $s = str_replace('\\', '/', $s);
        $s = explode('?', $s)[0];

        if (Str::startsWith($s, ['http://', 'https://'])) {
            $u = parse_url($s);
            if (!empty($u['path'])) {
                $s = $u['path'];
            }
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
            'dokumen_kak' => 'Kerangka Acuan Kerja (KAK)',
            'dokumen_hps' => 'Harga Perkiraan Sendiri (HPS)',
            'dokumen_spesifikasi_teknis' => 'Spesifikasi Teknis',
            'dokumen_rancangan_kontrak' => 'Rancangan Kontrak',
            'dokumen_lembar_data_kualifikasi' => 'Lembar Data Kualifikasi',
            'dokumen_lembar_data_pemilihan' => 'Lembar Data Pemilihan',
            'dokumen_daftar_kuantitas_harga' => 'Daftar Kuantitas dan Harga',
            'dokumen_jadwal_lokasi_pekerjaan' => 'Jadwal & Lokasi Pekerjaan',
            'dokumen_gambar_rancangan_pekerjaan' => 'Gambar Rancangan Pekerjaan',
            'dokumen_amdal' => 'Dokumen AMDAL',
            'dokumen_penawaran' => 'Dokumen Penawaran',
            'surat_penawaran' => 'Surat Penawaran',
            'dokumen_kemenkumham' => 'Kemenkumham',
            'ba_pemberian_penjelasan' => 'BA Pemberian Penjelasan',
            'ba_pengumuman_negosiasi' => 'BA Pengumuman Negosiasi',
            'ba_sanggah_banding' => 'BA Sanggah / Sanggah Banding',
            'ba_penetapan' => 'BA Penetapan',
            'laporan_hasil_pemilihan' => 'Laporan Hasil Pemilihan',
            'dokumen_sppbj' => 'SPPBJ',
            'surat_perjanjian_kemitraan' => 'Perjanjian Kemitraan',
            'surat_perjanjian_swakelola' => 'Perjanjian Swakelola',
            'surat_penugasan_tim_swakelola' => 'Penugasan Tim Swakelola',
            'dokumen_mou' => 'MoU',
            'dokumen_kontrak' => 'Dokumen Kontrak',
            'ringkasan_kontrak' => 'Ringkasan Kontrak',
            'jaminan_pelaksanaan' => 'Jaminan Pelaksanaan',
            'jaminan_uang_muka' => 'Jaminan Uang Muka',
            'jaminan_pemeliharaan' => 'Jaminan Pemeliharaan',
            'surat_tagihan' => 'Surat Tagihan',
            'surat_pesanan_epurchasing' => 'Surat Pesanan E-Purchasing',
            'dokumen_spmk' => 'SPMK',
            'dokumen_sppd' => 'SPPD',
            'laporan_pelaksanaan_pekerjaan' => 'Laporan Hasil Pelaksanaan',
            'laporan_penyelesaian_pekerjaan' => 'Laporan Penyelesaian',
            'bap' => 'BAP',
            'bast_sementara' => 'BAST Sementara',
            'bast_akhir' => 'BAST Akhir',
            'dokumen_pendukung_lainya' => 'Dokumen Pendukung Lainnya',
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
        $num = (int) $value;
        return 'Rp ' . number_format($num, 0, ',', '.');
    }

    private function formatRupiahNumber($value): string
    {
        $num = (int) ($value ?? 0);
        return 'Rp ' . number_format($num, 0, ',', '.');
    }

    private function resolveUnitId($rawUnit): ?int
    {
        if ($rawUnit === null) return null;

        $raw = trim((string) $rawUnit);
        if ($raw === '') return null;

        if (ctype_digit($raw)) return (int) $raw;

        if (!Schema::hasTable('units')) return null;

        $hasKode = Schema::hasColumn('units', 'kode');
        $hasSlug = Schema::hasColumn('units', 'slug');
        $hasUnitIdCol = Schema::hasColumn('units', 'unit_id');

        try {
            if ($hasKode) {
                $u = Unit::whereRaw('LOWER(kode) = ?', [mb_strtolower($raw)])->first();
                if ($u) return (int) $u->id;
            }

            if ($hasSlug) {
                $u = Unit::whereRaw('LOWER(slug) = ?', [mb_strtolower($raw)])->first();
                if ($u) return (int) $u->id;
            }

            if ($hasUnitIdCol) {
                $u = Unit::whereRaw('LOWER(unit_id) = ?', [mb_strtolower($raw)])->first();
                if ($u) return (int) $u->id;
            }

            if (Schema::hasColumn('units', 'nama')) {
                $u = Unit::whereRaw('LOWER(nama) = ?', [mb_strtolower($raw)])->first();
                if ($u) return (int) $u->id;
            }

            if (Schema::hasColumn('units', 'nama_unit')) {
                $u = Unit::whereRaw('LOWER(nama_unit) = ?', [mb_strtolower($raw)])->first();
                if ($u) return (int) $u->id;
            }

            if (Schema::hasColumn('units', 'name')) {
                $u = Unit::whereRaw('LOWER(name) = ?', [mb_strtolower($raw)])->first();
                if ($u) return (int) $u->id;
            }
        } catch (\Throwable $e) {
            return null;
        }

        return null;
    }
    /**
     * Histori Aktivitas – dipanggil via AJAX dari blade ArsipPBJ.
     * GET /super-admin/histori  (Accept: application/json)
     *
     * Response:
     * {
     *   "data": [
     *     { "waktu": "...", "nama_akun": "...", "role": "...", "unit_kerja": "...", "aktivitas": "..." }
     *   ]
     * }
     */
    public function historiAktivitas(Request $request)
    {
        $logs = ActivityLog::with('user.unit')
            ->orderByDesc('created_at')
            ->limit(200)
            ->get()
            ->map(function ($log) {
                /** @var \App\Models\User|null $user */
                $user = $log->user;

                $namaAkun  = $user?->name  ?? '(akun dihapus)';
                $role      = $user?->role  ?? '-';
                $unitKerja = $user?->unit?->nama
                    ?? $user?->unit?->nama_unit
                    ?? $user?->unit?->name
                    ?? '-';

                // Format waktu Indonesia
                $waktu = $log->created_at
                    ? $log->created_at->timezone('Asia/Jakarta')->format('d M Y H:i')
                    : '-';

                // Aktivitas: gabung action + description
                $aktivitas = trim(
                    ($log->action ? "[{$log->action}] " : '') .
                        ($log->description ?? '')
                );

                return [
                    'waktu'      => $waktu,
                    'nama_akun'  => $namaAkun,
                    'role'       => strtoupper($role),
                    'unit_kerja' => $unitKerja,
                    'aktivitas'  => $aktivitas ?: '-',
                ];
            })
            ->values()
            ->all();

        return response()->json(['data' => $logs]);
    }

    /**
     * Activity Logs (alias untuk histori, bisa dipakai route lama)
     */
    public function activityLogs(Request $request)
    {
        return $this->historiAktivitas($request);
    }

    private function logActivity($action, $description)
    {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'description' => $description,
        ]);
    }

    public function toggleMenuStatus($type, $id)
    {
        $menu = MasterMenu::where('category', $type)
            ->findOrFail($id);

        $menu->is_active = !$menu->is_active;

        $menu->save();

        return response()->json([
            'success'   => true,
            'is_active' => $menu->is_active,
        ]);
    }
}
