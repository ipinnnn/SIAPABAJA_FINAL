<?php

namespace App\Http\Controllers\Unit;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Pengadaan;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use App\Models\MasterMenu;
use App\Models\User;

class UnitController extends Controller
{
    public function dashboard()
    {
        $unitName = auth()->user()->name ?? 'Unit Kerja';
        $unitId   = auth()->user()->unit_id;

        if (!$unitId) {
            abort(403, 'Akun unit belum terhubung ke unit_id.');
        }

        /*
    |--------------------------------------------------------------------------
    | DROPDOWN DARI MASTER MENU
    |--------------------------------------------------------------------------
    */

        $tahunOptions = MasterMenu::where('category', 'tahun')
            ->where('is_active', true)
            ->orderByDesc('nama')
            ->pluck('nama')
            ->values()
            ->all();

        $statusLabels = MasterMenu::where('category', 'status_pekerjaan')
            ->where('is_active', true)
            ->orderBy('order_index')
            ->pluck('nama')
            ->values()
            ->all();

        $barLabels = MasterMenu::where('category', 'metode_pengadaan')
            ->where('is_active', true)
            ->orderBy('order_index')
            ->pluck('nama')
            ->map(function ($item) {
                return str_replace(' ', "\n", $item);
            })
            ->values()
            ->all();

        /*
    |--------------------------------------------------------------------------
    | DEFAULT YEAR
    |--------------------------------------------------------------------------
    */

        $defaultYear = $tahunOptions[0] ?? date('Y');

        /*
    |--------------------------------------------------------------------------
    | SUMMARY
    |--------------------------------------------------------------------------
    */

        $totalArsip = Pengadaan::where('unit_id', $unitId)->count();

        $publik = Pengadaan::where('unit_id', $unitId)
            ->where('status_arsip', 'Publik')
            ->count();

        $privat = Pengadaan::where('unit_id', $unitId)
            ->where('status_arsip', 'Privat')
            ->count();

        $paketYear = Pengadaan::where('unit_id', $unitId)
            ->where('tahun', $defaultYear)
            ->count();

        $nilaiYear = (int) Pengadaan::where('unit_id', $unitId)
            ->where('tahun', $defaultYear)
            ->sum('nilai_kontrak');

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
                "accent" => "yellow",
                "icon" => "bi-eye"
            ],

            [
                "label" => "Arsip Private",
                "value" => $privat,
                "accent" => "gray",
                "icon" => "bi-eye-slash"
            ],

            [
                "label" => "Total Arsip Pengadaan",
                "value" => $paketYear,
                "accent" => "navy",
                "icon" => "bi-file-earmark-text",
                "sub" => "Paket Pengadaan Barang dan Jasa"
            ],

            [
                "label" => "Total Nilai Pengadaan",
                "value" => $this->formatRupiahNumber($nilaiYear),
                "accent" => "yellow",
                "icon" => "bi-buildings",
                "sub" => "Nilai Kontrak Pengadaan"
            ],
        ];

        /*
    |--------------------------------------------------------------------------
    | CHART DATA
    |--------------------------------------------------------------------------
    */

        $statusValues = $this->countByStatusPekerjaan(
            $unitId,
            null,
            $statusLabels
        );

        $barValues = $this->countByMetodePengadaan(
            $unitId,
            null,
            $barLabels
        );

        return view('Unit.Dashboard', compact(
            'unitName',
            'summary',
            'tahunOptions',
            'statusLabels',
            'statusValues',
            'barLabels',
            'barValues',
            'defaultYear'
        ));
    }

    public function dashboardStats(Request $request)
    {
        $unitId = auth()->user()->unit_id;

        if (!$unitId) {
            return response()->json([
                'message' => 'Akun unit belum terhubung ke unit_id.'
            ], 403);
        }

        $tahun = $request->query('tahun');
        $tahun = ($tahun === null || $tahun === '')
            ? null
            : (int)$tahun;

        /*
    |--------------------------------------------------------------------------
    | DROPDOWN DINAMIS
    |--------------------------------------------------------------------------
    */

        $statusLabels = MasterMenu::where('category', 'status_pekerjaan')
            ->where('is_active', true)
            ->orderBy('order_index')
            ->pluck('nama')
            ->values()
            ->all();

        $barLabels = MasterMenu::where('category', 'metode_pengadaan')
            ->where('is_active', true)
            ->orderBy('order_index')
            ->pluck('nama')
            ->map(function ($item) {
                return str_replace(' ', "\n", $item);
            })
            ->values()
            ->all();

        /*
    |--------------------------------------------------------------------------
    | SUMMARY FILTER
    |--------------------------------------------------------------------------
    */

        $paket = Pengadaan::where('unit_id', $unitId)
            ->when($tahun !== null, function ($q) use ($tahun) {
                $q->where('tahun', $tahun);
            })
            ->count();

        $nilai = (int) Pengadaan::where('unit_id', $unitId)
            ->when($tahun !== null, function ($q) use ($tahun) {
                $q->where('tahun', $tahun);
            })
            ->sum('nilai_kontrak');

        /*
    |--------------------------------------------------------------------------
    | CHART FILTER
    |--------------------------------------------------------------------------
    */

        $statusValues = $this->countByStatusPekerjaan(
            $unitId,
            $tahun,
            $statusLabels
        );

        $barValues = $this->countByMetodePengadaan(
            $unitId,
            $tahun,
            $barLabels
        );

        return response()->json([
            'tahun' => $tahun,

            'paket' => [
                'count' => $paket
            ],

            'nilai' => [
                'sum' => $nilai,
                'formatted' => $this->formatRupiahNumber($nilai)
            ],

            'status' => [
                'labels' => $statusLabels,
                'values' => $statusValues
            ],

            'metode' => [
                'labels' => $barLabels,
                'values' => $barValues
            ],
        ]);
    }

    public function dashboardData(Request $request)
    {
        return $this->dashboardStats($request);
    }

    private function countByStatusPekerjaan(int $unitId, ?int $tahun, array $labels): array
    {
        $rows = Pengadaan::where('unit_id', $unitId)
            ->when($tahun !== null, fn($q) => $q->where('tahun', $tahun))
            ->select('status_pekerjaan', DB::raw('COUNT(*) as cnt'))
            ->groupBy('status_pekerjaan')
            ->pluck('cnt', 'status_pekerjaan')
            ->toArray();

        return array_map(fn($lbl) => (int)($rows[$lbl] ?? 0), $labels);
    }

    private function countByMetodePengadaan(int $unitId, ?int $tahun, array $labels): array
    {
        $raw = Pengadaan::where('unit_id', $unitId)
            ->when($tahun !== null, fn($q) => $q->where('tahun', $tahun))
            ->whereNotNull('metode_pengadaan')
            ->selectRaw("LOWER(TRIM(metode_pengadaan)) as metode, COUNT(*) as cnt")
            ->groupBy('metode')
            ->pluck('cnt', 'metode')
            ->toArray();

        $out = [];
        foreach ($labels as $lbl) {
            // labels dari MasterMenu mungkin sudah mengandung \n (dari str_replace di dashboard)
            // normalisasi: buang \n untuk matching
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

    /* =========================================================
     * ✅ HELPER QUERY ARSIP (dipakai Index + Export)
     * ========================================================= */
    private function buildArsipQuery(Request $request, int $unitId)
    {
        $q      = trim((string)$request->query('q', ''));
        $tahun  = (string)$request->query('tahun', 'Semua');
        $status = (string)$request->query('status', 'Semua');

        $sortNilai = strtolower((string)$request->query('sort_nilai', ''));
        if (!in_array($sortNilai, ['asc', 'desc'], true)) {
            $sortNilai = '';
        }

        $query = Pengadaan::query()
            ->with('unit')
            ->where('unit_id', $unitId);

        if ($status !== '' && $status !== 'Semua') {
            $query->where('status_arsip', $status);
        }

        if ($tahun !== '' && $tahun !== 'Semua') {
            if (ctype_digit($tahun)) {
                $query->where('tahun', (int)$tahun);
            } else {
                $query->where('tahun', $tahun);
            }
        }

        if ($q !== '') {
            $qLower = mb_strtolower($q);

            $query->where(function ($w) use ($q, $qLower) {
                if (ctype_digit($q)) {
                    $w->orWhere('tahun', (int)$q)
                        ->orWhere('id', (int)$q);
                }

                $w->orWhereRaw('LOWER(COALESCE(nama_pekerjaan, \'\')) LIKE ?', ['%' . $qLower . '%'])
                    ->orWhereRaw('LOWER(COALESCE(id_rup, \'\')) LIKE ?', ['%' . $qLower . '%'])
                    ->orWhereRaw('LOWER(COALESCE(jenis_pengadaan, \'\')) LIKE ?', ['%' . $qLower . '%'])
                    ->orWhereRaw('LOWER(COALESCE(status_arsip, \'\')) LIKE ?', ['%' . $qLower . '%'])
                    ->orWhereRaw('LOWER(COALESCE(status_pekerjaan, \'\')) LIKE ?', ['%' . $qLower . '%'])
                    ->orWhereRaw('LOWER(COALESCE(nama_rekanan, \'\')) LIKE ?', ['%' . $qLower . '%']);

                $w->orWhereRaw('CAST(COALESCE(nilai_kontrak,0) AS TEXT) LIKE ?', ['%' . $q . '%']);

                $w->orWhereHas('unit', function ($u) use ($qLower) {
                    $u->whereRaw('LOWER(COALESCE(nama, \'\')) LIKE ?', ['%' . $qLower . '%']);
                });
            });
        }

        if ($sortNilai !== '') {
            $query->orderBy('nilai_kontrak', $sortNilai);
            $query->orderByDesc('updated_at')->orderByDesc('id');
        } else {
            $query->orderByDesc('updated_at')->orderByDesc('id');
        }

        return $query;
    }

    public function arsipIndex(Request $request)
    {
        $unitName = auth()->user()->name ?? 'Unit Kerja';
        $unitId   = auth()->user()->unit_id;

        if (!$unitId) {
            abort(403, 'Akun unit belum terhubung ke unit_id.');
        }

        $tahunOptions = MasterMenu::where('category', 'tahun')
            ->where('is_active', true)
            ->orderByDesc('nama')
            ->pluck('nama')
            ->toArray();

        $arsips = $this->buildArsipQuery($request, (int)$unitId)
            ->paginate(10)
            ->withQueryString();

        // Ambil role semua creator sekaligus (1 query) agar tahu siapa yg membuat arsip
        $creatorIds   = $arsips->getCollection()->pluck('created_by')->filter()->unique()->values();
        $creatorUsers = User::whereIn('id', $creatorIds)->get(['id', 'name', 'role'])->keyBy('id');

        $mapped = $arsips->getCollection()->map(function (Pengadaan $p) use ($creatorUsers) {
            $creator         = $creatorUsers->get($p->created_by);
            $creatorName     = $creator?->name ?? null;
            $creatorRole     = strtolower(trim((string)($creator?->role ?? '')));
            $dibuat          = $creatorName
                ? ($creatorName . ($creatorRole ? ' (' . strtoupper(str_replace('_', ' ', $creatorRole)) . ')' : ''))
                : null;

            return [
                'id'                           => $p->id,
                'pekerjaan'                    => ($p->nama_pekerjaan ?? '-'),
                'id_rup'                       => $p->id_rup ?? '-',
                'tahun'                        => $p->tahun ?? null,
                'metode_pbj'                   => $p->metode_pengadaan ?? '-',
                'jenis_pengadaan'              => $p->jenis_pengadaan ?? '-',
                'status_pekerjaan'             => $p->status_pekerjaan ?? '-',
                'status_arsip'                 => $p->status_arsip ?? '-',
                'nilai_kontrak'                => $this->formatRupiah($p->nilai_kontrak),
                'pagu_anggaran'                => $this->formatRupiah($p->pagu_anggaran),
                'hps'                          => $this->formatRupiah($p->hps),
                'nama_rekanan'                 => $p->nama_rekanan ?? '-',
                'unit'                         => $p->unit?->nama ?? '-',
                'dokumen'                      => $this->buildDokumenList($p),
                'dokumen_tidak_dipersyaratkan' => $this->normalizeArray($p->dokumen_tidak_dipersyaratkan),
                'created_by'                   => $p->created_by,
                'created_by_name'              => $dibuat,  // info pembuat (PPK/SuperAdmin)
            ];
        });

        $arsips->setCollection($mapped);

        return view('Unit.ArsipPBJ', compact('unitName', 'arsips', 'tahunOptions'));
    }

    /* =========================================================
     * ✅ EXPORT ARSIP (EXCEL) - TANPA ZipArchive
     * - output: .xlsx asli (Zip + SpreadsheetML)
     * ========================================================= */
    public function arsipExport(Request $request)
    {
        $unitId = auth()->user()->unit_id;
        if (!$unitId) {
            abort(403, 'Akun unit belum terhubung ke unit_id.');
        }

        $items = $this->buildArsipQuery($request, (int)$unitId)->get();

        $filename = 'Arsip_PBJ_' . date('Y-m-d_His') . '.xlsx';

        $headers = [
            'Nama Pekerjaan',
            'Unit Kerja',
            'Tahun Anggaran',
            'ID RUP',
            'Status Pekerjaan',
            'Nama Rekanan',
            'Jenis Pengadaan',
            'Pagu Anggaran',
            'HPs',
            'Nilai Kontrak',
            'Dokumen Pengadaan',
            'Dokumen tidak dipersyaratkan',
        ];

        $xmlEscape = function ($s): string {
            $s = (string)($s ?? '');
            $s = str_replace(["\r\n", "\r"], "\n", $s);
            return htmlspecialchars($s, ENT_QUOTES | ENT_XML1, 'UTF-8');
        };

        $colLetter = function (int $n): string {
            $s = '';
            while ($n > 0) {
                $n--;
                $s  = chr(65 + ($n % 26)) . $s;
                $n  = intdiv($n, 26);
            }
            return $s;
        };

        // ✅ PERUBAHAN: flattenDokumen dibuat kompatibel dengan output buildDokumenList($p)
        $flattenDokumen = function ($dokumen): string {
            if (empty($dokumen)) return '';

            if (is_string($dokumen)) {
                $decoded = json_decode($dokumen, true);
                if (json_last_error() === JSON_ERROR_NONE) $dokumen = $decoded;
            }
            if (!is_array($dokumen)) return '';

            $isAssoc = array_keys($dokumen) !== range(0, count($dokumen) - 1);

            $basename = function ($p) {
                $p     = trim((string)$p);
                if ($p === '') return '';
                $parts = preg_split('#[\/\\\\]#', $p);
                return end($parts) ?: $p;
            };

            $normalizeItemName = function ($item) use ($basename) {
                if (is_string($item)) return $basename($item);

                if (is_array($item)) {
                    $name = $item['name'] ?? $item['filename'] ?? $item['file'] ?? $item['path'] ?? $item['url'] ?? '';
                    return $basename($name);
                }

                if (is_object($item)) {
                    $name = $item->name ?? $item->filename ?? $item->file ?? $item->path ?? $item->url ?? '';
                    return $basename($name);
                }

                return '';
            };

            $parts = [];

            if ($isAssoc) {
                foreach ($dokumen as $field => $list) {
                    $arr = is_array($list) ? $list : [$list];

                    // ✅ PERUBAHAN: pakai label manusiawi jika tersedia dari buildDokumenList()
                    $prefix = $field;
                    if (isset($arr[0]) && is_array($arr[0]) && !empty($arr[0]['label'])) {
                        $prefix = (string)$arr[0]['label'];
                    }

                    $names = array_values(array_filter(array_map($normalizeItemName, $arr)));
                    if (!empty($names)) $parts[] = $prefix . ': ' . implode(', ', $names);
                }
            } else {
                $names = array_values(array_filter(array_map($normalizeItemName, $dokumen)));
                if (!empty($names)) $parts[] = implode(', ', $names);
            }

            return implode(' | ', $parts);
        };

        $buildDocNote = function ($p): string {
            $rawE = $p->dokumen_tidak_dipersyaratkan ?? ($p->kolom_e ?? ($p->doc_note ?? null));

            if (is_array($rawE) && count($rawE) > 0) {
                return implode(', ', array_map(fn($x) => is_string($x) ? $x : json_encode($x), $rawE));
            }

            if (is_string($rawE)) {
                $try = json_decode($rawE, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($try)) {
                    return implode(', ', array_map(fn($x) => is_string($x) ? $x : json_encode($x), $try));
                }
            }

            $eVal = is_string($rawE) ? trim($rawE) : $rawE;

            if ($eVal === true || $eVal === 1 || $eVal === "1" || (is_string($eVal) && in_array(strtolower($eVal), ["ya", "iya", "true", "yes"], true))) {
                return "Dokumen pada Kolom E bersifat opsional (tidak dipersyaratkan).";
            }

            if (is_string($eVal) && $eVal !== "") return $eVal;

            return '';
        };

        $dataRows = [];
        foreach ($items as $p) {
            $namaPekerjaan = (string)($p->nama_pekerjaan ?? $p->pekerjaan ?? $p->judul ?? '');
            $unitKerja     = (string)($p->unit?->nama ?? (auth()->user()->name ?? 'Unit Kerja'));
            $tahunAnggaran = (string)($p->tahun ?? '');
            $idRup         = (string)($p->id_rup ?? $p->idrup ?? '');
            $statusPek     = (string)($p->status_pekerjaan ?? '');
            $rekanan       = (string)($p->nama_rekanan ?? $p->rekanan ?? '');
            $jenisPeng     = (string)($p->jenis_pengadaan ?? $p->jenis ?? '');
            $paguAnggaran  = $this->formatRupiah($p->pagu_anggaran);
            $hps           = $this->formatRupiah($p->hps);
            $nilaiKontrak  = $this->formatRupiah($p->nilai_kontrak);

            // ✅ PERUBAHAN UTAMA: ambil dokumen dari seluruh field dokumen_* via buildDokumenList()
            $dokumenText   = $flattenDokumen($this->buildDokumenList($p));

            $docNote       = $buildDocNote($p);

            $dataRows[] = [
                $namaPekerjaan,
                $unitKerja,
                $tahunAnggaran,
                $idRup,
                $statusPek,
                $rekanan,
                $jenisPeng,
                $paguAnggaran,
                $hps,
                $nilaiKontrak,
                $dokumenText,
                $docNote,
            ];
        }

        $sheetXml = (function () use ($headers, $dataRows, $xmlEscape, $colLetter) {
            $xml  = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
            $xml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" ';
            $xml .= 'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">';
            $xml .= '<sheetData>';

            // header row (POLOS) s="0"
            $xml .= '<row r="1">';
            $c = 1;
            foreach ($headers as $h) {
                $ref  = $colLetter($c) . '1';
                $xml .= '<c r="' . $ref . '" t="inlineStr" s="0"><is><t>' . $xmlEscape($h) . '</t></is></c>';
                $c++;
            }
            $xml .= '</row>';

            $r = 2;
            foreach ($dataRows as $row) {
                $xml .= '<row r="' . $r . '">';
                $c = 1;
                foreach ($row as $val) {
                    $ref  = $colLetter($c) . $r;
                    $xml .= '<c r="' . $ref . '" t="inlineStr" s="0"><is><t>' . $xmlEscape($val) . '</t></is></c>';
                    $c++;
                }
                $xml .= '</row>';
                $r++;
            }

            $xml .= '</sheetData>';
            $xml .= '</worksheet>';
            return $xml;
        })();

        $stylesXml =
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<fonts count="1">'
            . '<font><sz val="11"/><color theme="1"/><name val="Calibri"/><family val="2"/></font>'
            . '</fonts>'
            . '<fills count="1">'
            . '<fill><patternFill patternType="none"/></fill>'
            . '</fills>'
            . '<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="1">'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            . '</cellXfs>'
            . '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            . '</styleSheet>';

        $contentTypesXml =
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . '</Types>';

        $relsXml =
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>';

        $workbookXml =
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" '
            . 'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="Arsip PBJ" sheetId="1" r:id="rId1"/></sheets>'
            . '</workbook>';

        $workbookRelsXml =
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            . '</Relationships>';

        $zipBinary = $this->zipBuildStore([
            '[Content_Types].xml'        => $contentTypesXml,
            '_rels/.rels'                => $relsXml,
            'xl/workbook.xml'            => $workbookXml,
            'xl/_rels/workbook.xml.rels' => $workbookRelsXml,
            'xl/styles.xml'              => $stylesXml,
            'xl/worksheets/sheet1.xml'   => $sheetXml,
        ]);

        return response()->streamDownload(function () use ($zipBinary) {
            echo $zipBinary;
        }, $filename, [
            'Content-Type'  => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
            'Pragma'        => 'public',
        ]);
    }

    private function zipBuildStore(array $files): string
    {
        $data   = '';
        $cd     = '';
        $offset = 0;
        $i      = 0;

        [$dosTime, $dosDate] = $this->zipDosTimeDate(time());

        foreach ($files as $name => $content) {
            $i++;
            $name    = str_replace('\\', '/', (string)$name);
            $content = (string)$content;

            $crc = crc32($content);
            if ($crc < 0) $crc = $crc + 4294967296;

            $size    = strlen($content);
            $nameLen = strlen($name);

            $localHeader =
                pack('V', 0x04034b50) .
                pack('v', 20) .
                pack('v', 0) .
                pack('v', 0) .
                pack('v', $dosTime) .
                pack('v', $dosDate) .
                $this->zipUInt32($crc) .
                $this->zipUInt32($size) .
                $this->zipUInt32($size) .
                pack('v', $nameLen) .
                pack('v', 0) .
                $name;

            $data .= $localHeader . $content;

            $centralHeader =
                pack('V', 0x02014b50) .
                pack('v', 20) .
                pack('v', 20) .
                pack('v', 0) .
                pack('v', 0) .
                pack('v', $dosTime) .
                pack('v', $dosDate) .
                $this->zipUInt32($crc) .
                $this->zipUInt32($size) .
                $this->zipUInt32($size) .
                pack('v', $nameLen) .
                pack('v', 0) .
                pack('v', 0) .
                pack('v', 0) .
                pack('v', 0) .
                $this->zipUInt32(0) .
                $this->zipUInt32($offset) .
                $name;

            $cd    .= $centralHeader;
            $offset = strlen($data);
        }

        $cdSize   = strlen($cd);
        $cdOffset = strlen($data);

        $eocd =
            pack('V', 0x06054b50) .
            pack('v', 0) .
            pack('v', 0) .
            pack('v', $i) .
            pack('v', $i) .
            $this->zipUInt32($cdSize) .
            $this->zipUInt32($cdOffset) .
            pack('v', 0);

        return $data . $cd . $eocd;
    }

    private function zipDosTimeDate(int $unixTime): array
    {
        $d     = getdate($unixTime);
        $year  = max(1980, (int)$d['year']);
        $month = (int)$d['mon'];
        $day   = (int)$d['mday'];
        $hour  = (int)$d['hours'];
        $min   = (int)$d['minutes'];
        $sec   = (int)$d['seconds'];

        $dosTime = (($hour & 0x1F) << 11) | (($min & 0x3F) << 5) | ((int)($sec / 2) & 0x1F);
        $dosDate = ((($year - 1980) & 0x7F) << 9) | (($month & 0x0F) << 5) | ($day & 0x1F);

        return [$dosTime, $dosDate];
    }

    private function zipUInt32(int $v): string
    {
        return pack('V', $v & 0xFFFFFFFF);
    }

    public function arsipEdit($id)
    {
        $unitName = auth()->user()->name ?? 'Unit Kerja';
        $unitId   = auth()->user()->unit_id;

        if (!$unitId) {
            abort(403, 'Akun unit belum terhubung ke unit_id.');
        }

        $pengadaan = Pengadaan::where('id', $id)
            ->where('unit_id', $unitId)
            ->firstOrFail();

        $selectedUnitId   = $unitId;
        $selectedUnitName = $unitId ? Unit::find($unitId)?->nama : null;
        $units            = Unit::orderBy('nama')->get();

        $pengadaan->dokumen_tidak_dipersyaratkan = $this->normalizeArray($pengadaan->dokumen_tidak_dipersyaratkan);
        $dokumenExisting = $this->buildDokumenList($pengadaan);

        $tahunOptions = MasterMenu::where('category', 'tahun')
            ->where('is_active', true)
            ->orderByDesc('nama')
            ->pluck('nama')
            ->unique()
            ->values()
            ->toArray();

        $jenisPengadaanOptions = MasterMenu::where('category', 'jenis_pengadaan')
            ->where('is_active', true)
            ->orderBy('order_index')
            ->pluck('nama')
            ->unique()
            ->values()
            ->toArray();

        $metodePengadaanOptions = MasterMenu::where('category', 'metode_pengadaan')
            ->where('is_active', true)
            ->orderBy('order_index')
            ->pluck('nama')
            ->unique()
            ->values()
            ->toArray();

        $statusPekerjaanOptions = MasterMenu::where('category', 'status_pekerjaan')
            ->where('is_active', true)
            ->orderBy('order_index')
            ->pluck('nama')
            ->unique()
            ->values()
            ->toArray();

        return view('Unit.EditArsip', compact(
            'pengadaan',
            'tahunOptions',
            'jenisPengadaanOptions',
            'metodePengadaanOptions',
            'statusPekerjaanOptions',
            'units',
            'selectedUnitId',
            'selectedUnitName',
            'dokumenExisting'
        ));
    }

    public function arsipUpdate(Request $request, $id)
    {
        $unitId = auth()->user()->unit_id;
        if (!$unitId) {
            abort(403, 'Akun unit belum terhubung ke unit_id.');
        }

        $pengadaan = Pengadaan::where('id', $id)
            ->where('unit_id', $unitId)
            ->firstOrFail();

        $payload = $this->normalizedPengadaanPayload($request, (int)$unitId);
        Validator::make($payload, $this->rulesPengadaan())->validate();

        DB::beginTransaction();
        try {
            $pengadaan->unit_id          = (int)$unitId;
            $pengadaan->tahun            = (int)$payload['tahun'];
            $pengadaan->nama_pekerjaan   = $payload['nama_pekerjaan'];
            $pengadaan->id_rup           = $payload['id_rup'];
            $pengadaan->jenis_pengadaan  = $payload['jenis_pengadaan'];
            $pengadaan->metode_pengadaan = $payload['metode_pengadaan'];
            $pengadaan->status_pekerjaan = $payload['status_pekerjaan'];
            $pengadaan->status_arsip     = $payload['status_arsip'];

            $pengadaan->pagu_anggaran = $payload['pagu_anggaran'];
            $pengadaan->hps           = $payload['hps'];
            $pengadaan->nilai_kontrak = $payload['nilai_kontrak'];
            $pengadaan->nama_rekanan  = $payload['nama_rekanan'];

            $pengadaan->dokumen_tidak_dipersyaratkan = $payload['dokumen_tidak_dipersyaratkan'];

            $this->handleUploadDokumenToModel($request, $pengadaan, true);

            $pengadaan->save();

            // ✅ LOG: edit pengadaan
            $this->logActivity(
                'update',
                'Unit mengedit pengadaan: ' . ($pengadaan->nama_pekerjaan ?? 'ID ' . $pengadaan->id)
            );

            // ✅ LOG: upload/update dokumen
            if ($request->allFiles()) {
                $this->logActivity(
                    'upload_file',
                    'Unit mengupload/memperbarui dokumen pada: ' . ($pengadaan->nama_pekerjaan ?? 'ID ' . $pengadaan->id)
                );
            }

            DB::commit();

            return redirect()
                ->route('unit.arsip')
                ->with('success', 'Arsip berhasil diperbarui.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['update' => 'Gagal memperbarui arsip. Silakan coba lagi.']);
        }
    }

    public function arsipDestroy(Request $request, $id)
    {
        $unitId = auth()->user()->unit_id;
        if (!$unitId) {
            return response()->json(['message' => 'Akun unit belum terhubung ke unit_id.'], 403);
        }

        $pengadaan = Pengadaan::where('id', $id)
            ->where('unit_id', $unitId)
            ->firstOrFail();

        DB::beginTransaction();
        try {
            // ✅ LOG: hapus pengadaan (sebelum delete agar data masih tersedia)
            $this->logActivity(
                'delete',
                'Unit menghapus pengadaan: ' . ($pengadaan->nama_pekerjaan ?? 'ID ' . $pengadaan->id)
            );

            Storage::disk('public')->deleteDirectory("pengadaan/{$pengadaan->id}");
            $pengadaan->delete();

            DB::commit();
            return response()->json([
                'message'     => 'Arsip berhasil dihapus.',
                'deleted_ids' => [(string)$id],
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal menghapus arsip.'], 500);
        }
    }

    public function arsipBulkDestroy(Request $request)
    {
        $unitId = auth()->user()->unit_id;
        if (!$unitId) {
            return response()->json(['message' => 'Akun unit belum terhubung ke unit_id.'], 403);
        }

        $data = $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'integer',
        ]);

        $ids = array_values(array_unique($data['ids']));

        $items = Pengadaan::where('unit_id', $unitId)
            ->whereIn('id', $ids)
            ->get();

        if ($items->count() === 0) {
            return response()->json(['message' => 'Tidak ada data yang bisa dihapus.'], 404);
        }

        DB::beginTransaction();
        try {
            $deleted = [];
            foreach ($items as $p) {

                // ✅ LOG: bulk delete
                $this->logActivity(
                    'bulk_delete',
                    'Unit menghapus pengadaan: ' . $p->nama_pekerjaan
                );

                Storage::disk('public')->deleteDirectory("pengadaan/{$p->id}");

                $deleted[] = (string)$p->id;
            }

            Pengadaan::where('unit_id', $unitId)
                ->whereIn('id', $items->pluck('id')->all())
                ->delete();

            DB::commit();

            return response()->json([
                'message'     => 'Arsip terpilih berhasil dihapus.',
                'deleted_ids' => $deleted,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal menghapus arsip terpilih.'], 500);
        }
    }

    public function pengadaanCreate()
    {
        $unitName = auth()->user()->name ?? 'Unit Kerja';
        $unitId   = auth()->user()->unit_id;

        $selectedUnitId   = $unitId;
        $selectedUnitName = $unitId ? Unit::find($unitId)?->nama : null;

        $units = Unit::orderBy('nama')->get();

        // =========================
        // DROPDOWN MASTER MENU
        // =========================
        $tahunOptions = \App\Models\MasterMenu::where('category', 'tahun')
            ->where('is_active', true)
            ->orderBy('order_index')
            ->pluck('nama')
            ->toArray();

        $jenisPengadaanOptions = \App\Models\MasterMenu::where('category', 'jenis_pengadaan')
            ->where('is_active', true)
            ->orderBy('order_index')
            ->pluck('nama')
            ->toArray();

        $metodePengadaanOptions = \App\Models\MasterMenu::where('category', 'metode_pengadaan')
            ->where('is_active', true)
            ->orderBy('order_index')
            ->pluck('nama')
            ->toArray();

        $statusPekerjaanOptions = \App\Models\MasterMenu::where('category', 'status_pekerjaan')
            ->where('is_active', true)
            ->orderBy('order_index')
            ->pluck('nama')
            ->toArray();

        // =========================
        // DOKUMEN
        // =========================
        $docSessions = [
            ['key' => 'dokumen_kak', 'label' => 'Kerangka Acuan Kerja atau KAK'],
            ['key' => 'dokumen_hps', 'label' => 'Harga Perkiraan Sendiri atau HPS'],
            ['key' => 'dokumen_spesifikasi_teknis', 'label' => 'Spesifikasi Teknis'],
            ['key' => 'dokumen_rancangan_kontrak', 'label' => 'Rancangan Kontrak'],
            ['key' => 'dokumen_lembar_data_kualifikasi', 'label' => 'Lembar Data Kualifikasi'],
            ['key' => 'dokumen_lembar_data_pemilihan', 'label' => 'Lembar Data Pemilihan'],
            ['key' => 'dokumen_daftar_kuantitas_harga', 'label' => 'Daftar Kuantitas dan Harga'],
            ['key' => 'dokumen_jadwal_lokasi_pekerjaan', 'label' => 'Jadwal dan Lokasi Pekerjaan'],
            ['key' => 'dokumen_gambar_rancangan_pekerjaan', 'label' => 'Gambar Rancangan Pekerjaan'],
            ['key' => 'dokumen_amdal', 'label' => 'Dokumen Analisis Mengenai Dampak Lingkungan atau AMDAL'],
            ['key' => 'dokumen_penawaran', 'label' => 'Dokumen Penawaran'],
            ['key' => 'surat_penawaran', 'label' => 'Surat Penawaran'],
            ['key' => 'dokumen_kemenkumham', 'label' => 'Sertifikat atau Lisensi Kemenkumham'],
            ['key' => 'ba_pemberian_penjelasan', 'label' => 'Berita Acara Pemberian Penjelasan'],
            ['key' => 'ba_pengumuman_negosiasi', 'label' => 'Berita Acara Pengumuman Negosiasi'],
            ['key' => 'ba_sanggah_banding', 'label' => 'Berita Acara Sanggah dan Sanggah Banding'],
            ['key' => 'ba_penetapan', 'label' => 'Berita Acara Penetapan'],
            ['key' => 'laporan_hasil_pemilihan', 'label' => 'Laporan Hasil Pemilihan Penyedia'],
            ['key' => 'dokumen_sppbj', 'label' => 'Surat Penunjukan Penyedia Barang Jasa atau SPPBJ'],
            ['key' => 'surat_perjanjian_kemitraan', 'label' => 'Surat Perjanjian Kemitraan'],
            ['key' => 'surat_perjanjian_swakelola', 'label' => 'Surat Perjanjian Swakelola'],
            ['key' => 'surat_penugasan_tim_swakelola', 'label' => 'Surat Penugasan Tim Swakelola'],
            ['key' => 'dokumen_mou', 'label' => 'Nota Kesepahaman atau MoU'],
            ['key' => 'dokumen_kontrak', 'label' => 'Dokumen Kontrak'],
            ['key' => 'ringkasan_kontrak', 'label' => 'Ringkasan Kontrak'],
            ['key' => 'jaminan_pelaksanaan', 'label' => 'Surat Jaminan Pelaksanaan'],
            ['key' => 'jaminan_uang_muka', 'label' => 'Surat Jaminan Uang Muka'],
            ['key' => 'jaminan_pemeliharaan', 'label' => 'Surat Jaminan Pemeliharaan'],
            ['key' => 'surat_tagihan', 'label' => 'Surat Tagihan'],
            ['key' => 'surat_pesanan_epurchasing', 'label' => 'Surat Pesanan Elektronik atau E-Purchasing'],
            ['key' => 'dokumen_spmk', 'label' => 'Surat Perintah Mulai Kerja atau SPMK'],
            ['key' => 'dokumen_sppd', 'label' => 'Surat Perintah Perjalanan Dinas atau SPPD'],
            ['key' => 'laporan_pelaksanaan_pekerjaan', 'label' => 'Laporan Pelaksanaan Pekerjaan'],
            ['key' => 'laporan_penyelesaian_pekerjaan', 'label' => 'Laporan Penyelesaian Pekerjaan'],
            ['key' => 'bap', 'label' => 'Berita Acara Pembayaran atau BAP'],
            ['key' => 'bast_sementara', 'label' => 'Berita Acara Serah Terima Sementara atau BAST Sementara'],
            ['key' => 'bast_akhir', 'label' => 'Berita Acara Serah Terima Final atau BAST Final'],
            ['key' => 'dokumen_pendukung_lainya', 'label' => 'Dokumen Pendukung Lainya'],
        ];

        return view('Unit.TambahPengadaan', compact(
            'unitName',
            'units',
            'selectedUnitId',
            'selectedUnitName',
            'tahunOptions',
            'jenisPengadaanOptions',
            'metodePengadaanOptions',
            'statusPekerjaanOptions',
            'docSessions'
        ));
    }

    public function pengadaanStore(Request $request)
    {
        $unitId = auth()->user()->unit_id;
        if (!$unitId) {
            abort(403, 'Akun unit belum terhubung ke unit_id.');
        }

        $payload               = $this->normalizedPengadaanPayload($request, (int)$unitId);
        $payload['created_by'] = auth()->id();

        Validator::make($payload, $this->rulesPengadaan())->validate();

        DB::beginTransaction();
        try {
            $pengadaan = Pengadaan::create($payload);

            $this->handleUploadDokumenToModel($request, $pengadaan, false);
            $pengadaan->save();

            // ✅ LOG: tambah pengadaan
            // ✅ LOG: tambah pengadaan
            $this->logActivity(
                'create',
                'Unit menambahkan pengadaan: ' . $pengadaan->nama_pekerjaan
            );

            // ✅ LOG: upload dokumen
            if ($request->allFiles()) {
                $this->logActivity(
                    'upload_file',
                    'Unit mengupload dokumen pada pengadaan: ' . $pengadaan->nama_pekerjaan
                );
            }

            DB::commit();

            return redirect()
                ->route('unit.arsip')
                ->with('success', 'Pengadaan berhasil disimpan.');
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

            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['upload' => 'Gagal menyimpan pengadaan/dokumen. Silakan coba lagi.']);
        }
    }

    public function showDokumen($id, $field, $file)
    {
        $unitId = auth()->user()->unit_id;
        if (!$unitId) abort(403, 'Akun unit belum terhubung ke unit_id.');

        $allowed = $this->dokumenFieldLabels();
        if (!array_key_exists($field, $allowed)) abort(404);

        $pengadaan = Pengadaan::where('id', $id)->where('unit_id', $unitId)->firstOrFail();
        $arr       = $this->normalizeArray($pengadaan->{$field});

        $matchPath = null;
        foreach ($arr as $p) {
            $p = ltrim((string)$p, '/');
            if (basename($p) === $file) {
                $matchPath = $p;
                break;
            }
        }

        dd([
            'unitId' => $unitId,
            'id' => $id,
            'field' => $field,
            'file' => $file,
            'arr' => $arr,
            'matchPath' => $matchPath,
            'exists' => $matchPath ? Storage::disk('public')->exists($matchPath) : false,
        ]);

        $ext    = strtolower(pathinfo($matchPath, PATHINFO_EXTENSION));
        $mime   = Storage::disk('public')->mimeType($matchPath) ?: 'application/octet-stream';
        $stream = Storage::disk('public')->readStream($matchPath);

        // PDF & gambar: inline (preview di browser), lainnya: download
        $inline      = in_array($ext, ['pdf', 'png', 'jpg', 'jpeg', 'webp', 'gif'], true);
        $disposition = $inline ? 'inline' : 'attachment';

        return response()->stream(function () use ($stream) {
            fpassthru($stream);
        }, 200, [
            'Content-Type'        => $mime,
            'Content-Disposition' => $disposition . '; filename="' . $file . '"',
            'Cache-Control'       => 'private, no-store',
            'X-Frame-Options'     => 'SAMEORIGIN',
        ]);
    }

    public function fileViewer(Request $request)
    {
        $raw  = (string)$request->query('file', '');
        $file = $raw;

        for ($i = 0; $i < 2; $i++) {
            $dec = urldecode($file);
            if ($dec === $file) break;
            $file = $dec;
        }

        for ($i = 0; $i < 2; $i++) {
            $parts = parse_url($file);
            if (!is_array($parts)) break;

            $path = $parts['path'] ?? '';
            if (str_contains($path, 'file-viewer') && !empty($parts['query'])) {
                parse_str($parts['query'], $q);
                if (!empty($q['file'])) {
                    $file = (string)$q['file'];
                    continue;
                }
            }
            break;
        }

        $file = trim($file);
        for ($i = 0; $i < 2; $i++) {
            $dec = urldecode($file);
            if ($dec === $file) break;
            $file = $dec;
        }

        $ok       = false;
        $finalUrl = $file;

        if (str_starts_with($file, '/storage/')) {
            $ok       = true;
            $finalUrl = $file;
        } else {
            $u = parse_url($file);
            if (is_array($u) && !empty($u['host'])) {
                $host    = strtolower($u['host']);
                $curHost = strtolower($request->getHost());
                $path    = $u['path'] ?? '';

                $allowedHosts = array_unique(array_filter([$curHost, 'localhost', '127.0.0.1']));
                if (in_array($host, $allowedHosts, true) && str_starts_with($path, '/storage/')) {
                    $ok       = true;
                    $finalUrl = $path . (isset($u['query']) ? ('?' . $u['query']) : '');
                }
            }
        }

        if (!$ok) {
            abort(403, 'FILE TIDAK DIIZINKAN.');
        }

        $publicPath = ltrim($finalUrl, '/');
        $diskPath   = preg_replace('#^storage/#', '', $publicPath);

        if (!$diskPath || !Storage::disk('public')->exists($diskPath)) {
            abort(404);
        }

        return view('Viewer.FileViewer', ['file' => $finalUrl]);
    }

    public function downloadDokumen($id, Request $request)
    {
        $unitId = auth()->user()->unit_id;
        if (!$unitId) abort(403, 'Akun unit belum terhubung ke unit_id.');

        $request->validate([
            'field' => 'required|string|max:100',
            'path'  => 'required|string',
        ]);

        $field = $request->query('field');
        $path  = ltrim($request->query('path'), '/');

        $allowed = $this->dokumenFieldLabels();
        if (!array_key_exists($field, $allowed)) abort(404);

        $pengadaan = Pengadaan::where('id', $id)->where('unit_id', $unitId)->firstOrFail();
        $arr       = $this->normalizeArray($pengadaan->{$field});

        $ok = false;
        foreach ($arr as $p) {
            if (ltrim((string)$p, '/') === $path) {
                $ok = true;
                break;
            }
        }

        if (!$ok || !Storage::disk('public')->exists($path)) abort(404);

        return Storage::disk('public')->download($path, basename($path));
    }

    public function hapusDokumenFile(Request $request, $id)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $unitId = $user?->unit_id;
        if (!$unitId) abort(403, 'Akun unit belum terhubung ke unit_id.');

        $request->validate([
            'field' => 'required|string|max:100',
            'path'  => 'required|string',
        ]);

        $pengadaan = Pengadaan::where('id', $id)->where('unit_id', $unitId)->firstOrFail();

        $field   = $request->input('field');
        $path    = ltrim($request->input('path'), '/');

        $allowed = $this->dokumenFieldLabels();
        if (!array_key_exists($field, $allowed)) {
            return response()->json(['message' => 'Field dokumen tidak valid.'], 422);
        }

        $arr = $this->normalizeArray($pengadaan->{$field});
        $arr = array_values(array_filter($arr, fn($p) => (string)$p !== (string)$path));

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }

        $pengadaan->{$field} = $arr;
        $pengadaan->save();

        // ✅ LOG: hapus file dokumen
        $this->logActivity(
            'delete_file',
            'Unit menghapus file dokumen pada: ' . ($pengadaan->nama_pekerjaan ?? 'ID ' . $pengadaan->id)
        );

        return response()->json([
            'message'   => 'File berhasil dihapus.',
            'field'     => $field,
            'remaining' => $arr,
        ]);
    }

    public function kelolaAkun()
    {
        $unitName = auth()->user()->name ?? 'Unit Kerja';
        return view('Unit.KelolaAkun', compact('unitName'));
    }

    public function updateAkun(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->back()->withErrors(['auth' => 'Kamu belum login. Silakan login dulu.'])->withInput();
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

        // ✅ LOG: update akun
        $this->logActivity(
            'update_account',
            'Unit memperbarui akun'
        );

        return redirect()->route('unit.kelola.akun')->with('success', 'Akun berhasil diperbarui.');
    }

    // =========================
    // HELPERS
    // =========================

    private function rulesPengadaan(): array
    {
        return [
            'tahun'            => 'required|integer|min:2000|max:' . (date('Y') + 5),
            'nama_pekerjaan'   => 'nullable|string|max:255',
            'id_rup'           => 'nullable|string|max:255',
            'jenis_pengadaan'  => 'required|string|max:100',
            'metode_pengadaan' => 'required|string|max:100',
            'status_pekerjaan' => 'required|string|max:100',
            'status_arsip'     => 'required|in:Publik,Privat',

            'pagu_anggaran' => 'nullable|integer|min:0',
            'hps'           => 'nullable|integer|min:0',
            'nilai_kontrak' => 'nullable|integer|min:0',
            'nama_rekanan'  => 'nullable|string|max:255',

            'dokumen_tidak_dipersyaratkan' => 'nullable|array',
        ];
    }

    private function normalizedPengadaanPayload(Request $request, int $unitId): array
    {
        $pick = function (array $keys, $default = null) use ($request) {
            foreach ($keys as $k) {
                $v = $request->input($k);
                if ($v !== null && $v !== '') return $v;
            }
            return $default;
        };

        $toIntMoney = function ($v) {
            if ($v === null) return null;
            $num = preg_replace('/[^0-9]/', '', (string)$v);
            return $num === '' ? null : (int)$num;
        };

        $docTidak = [];
        $arrE     = $request->input('dokumen_tidak_dipersyaratkan');
        if (is_array($arrE)) {
            $docTidak = $arrE;
        } else {
            $jsonE = $request->input('dokumen_tidak_dipersyaratkan_json');
            if (is_string($jsonE) && trim($jsonE) !== '') {
                $decoded = json_decode($jsonE, true);
                if (is_array($decoded)) $docTidak = $decoded;
            } else {
                $fallbackText = $pick(['doc_note', 'dokumen_e', 'kolom_e'], '');
                if (is_string($fallbackText) && trim($fallbackText) !== '') {
                    $docTidak = [trim($fallbackText)];
                }
            }
        }
        $docTidak = array_values(array_filter(array_map(function ($x) {
            $s = is_string($x) ? trim($x) : $x;
            return ($s === '' || $s === null) ? null : $s;
        }, $docTidak)));

        $statusArsip = (string)$pick(['status_arsip', 'akses', 'statusAkses'], 'Privat');
        if (strtolower($statusArsip) === 'private') $statusArsip = 'Privat';

        return [
            'unit_id' => (int)$unitId,
            'tahun'   => (int)$pick(['tahun', 'year'], (int)date('Y')),

            'nama_pekerjaan'   => $pick(['nama_pekerjaan', 'pekerjaan', 'judul', 'namaPekerjaan'], null),
            'id_rup'           => $pick(['id_rup', 'idrup', 'id_rup_paket', 'idRup'], null),
            'jenis_pengadaan'  => $pick(['jenis_pengadaan', 'jenis_pbj'], null),
            'metode_pengadaan' => $pick(['metode_pengadaan', 'metode_pbj', 'metode'], null),
            'status_pekerjaan' => $pick(['status_pekerjaan', 'status', 'statusPekerjaan'], null),

            'status_arsip' => $statusArsip,

            'pagu_anggaran' => $toIntMoney($pick(['pagu_anggaran', 'pagu'], null)),
            'hps'           => $toIntMoney($pick(['hps'], null)),
            'nilai_kontrak' => $toIntMoney($pick(['nilai_kontrak', 'kontrak', 'nilai'], null)),
            'nama_rekanan'  => $pick(['nama_rekanan', 'rekanan'], null),

            'dokumen_tidak_dipersyaratkan' => $docTidak,
        ];
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

        $out = [];
        foreach ($labels as $field => $label) {
            $files = $this->normalizeArray($p->{$field});
            if (count($files) === 0) continue;

            $items = [];
            foreach ($files as $path) {
                if (!$path) continue;

                $path = ltrim((string)$path, '/');
                if (!Storage::disk('public')->exists($path)) continue;

                $file = basename($path);

                $items[] = [
                    'label' => $label,
                    'name'  => $file,
                    'path'  => $path,
                    'url'   => route('unit.arsip.dokumen.show', [
                        'id'    => $p->id,
                        'field' => $field,
                        'file'  => $file,
                    ]),
                ];
            }

            if (count($items)) $out[$field] = $items;
        }

        return $out;
    }

    private function dokumenFieldLabels(): array
    {
        return [
            'dokumen_kak'                       => 'Kerangka Acuan Kerja (KAK)',
            'dokumen_hps'                       => 'Harga Perkiraan Sendiri (HPS)',
            'dokumen_spesifikasi_teknis'         => 'Spesifikasi Teknis',
            'dokumen_rancangan_kontrak'          => 'Rancangan Kontrak',
            'dokumen_lembar_data_kualifikasi'    => 'Lembar Data Kualifikasi',
            'dokumen_lembar_data_pemilihan'      => 'Lembar Data Pemilihan',
            'dokumen_daftar_kuantitas_harga'     => 'Daftar Kuantitas dan Harga',
            'dokumen_jadwal_lokasi_pekerjaan'    => 'Jadwal & Lokasi Pekerjaan',
            'dokumen_gambar_rancangan_pekerjaan' => 'Gambar Rancangan Pekerjaan',
            'dokumen_amdal'                     => 'Dokumen AMDAL',
            'dokumen_penawaran'                 => 'Dokumen Penawaran',
            'surat_penawaran'                   => 'Surat Penawaran',
            'dokumen_kemenkumham'               => 'Kemenkumham',
            'ba_pemberian_penjelasan'           => 'BA Pemberian Penjelasan',
            'ba_pengumuman_negosiasi'           => 'BA Pengumuman Negosiasi',
            'ba_sanggah_banding'                => 'BA Sanggah / Sanggah Banding',
            'ba_penetapan'                      => 'BA Penetapan',
            'laporan_hasil_pemilihan'           => 'Laporan Hasil Pemilihan',
            'dokumen_sppbj'                     => 'SPPBJ',
            'surat_perjanjian_kemitraan'        => 'Perjanjian Kemitraan',
            'surat_perjanjian_swakelola'        => 'Perjanjian Swakelola',
            'surat_penugasan_tim_swakelola'     => 'Penugasan Tim Swakelola',
            'dokumen_mou'                       => 'MoU',
            'dokumen_kontrak'                   => 'Dokumen Kontrak',
            'ringkasan_kontrak'                 => 'Ringkasan Kontrak',
            'jaminan_pelaksanaan'               => 'Jaminan Pelaksanaan',
            'jaminan_uang_muka'                 => 'Jaminan Uang Muka',
            'jaminan_pemeliharaan'              => 'Jaminan Pemeliharaan',
            'surat_tagihan'                     => 'Surat Tagihan',
            'surat_pesanan_epurchasing'         => 'Surat Pesanan E-Purchasing',
            'dokumen_spmk'                      => 'SPMK',
            'dokumen_sppd'                      => 'SPPD',
            'laporan_pelaksanaan_pekerjaan'     => 'Laporan Hasil Pelaksanaan',
            'laporan_penyelesaian_pekerjaan'    => 'Laporan Penyelesaian',
            'bap'                               => 'BAP',
            'bast_sementara'                    => 'BAST Sementara',
            'bast_akhir'                        => 'BAST Akhir',
            'dokumen_pendukung_lainya'          => 'Dokumen Pendukung Lainnya',
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

    public function historiAktivitas()
    {
        $currentUser = auth()->user();
        $unitId      = $currentUser->unit_id;

        // Kumpulkan user_id yang relevan:
        // 1. User unit itu sendiri
        // 2. Semua PPK / Super Admin yang pernah membuat/mengedit pengadaan
        //    milik unit ini (via kolom created_by pada tabel pengadaan)
        $relatedUserIds = collect([$currentUser->id]);

        if ($unitId) {
            // Ambil created_by dari pengadaan yang unit_id-nya milik unit ini
            $creatorIds = Pengadaan::where('unit_id', $unitId)
                ->whereNotNull('created_by')
                ->pluck('created_by')
                ->unique()
                ->values();

            $relatedUserIds = $relatedUserIds->merge($creatorIds)->unique()->values();
        }

        $logs = ActivityLog::with('user.unit')
            ->whereIn('user_id', $relatedUserIds)
            ->latest()
            ->limit(200)
            ->get();

        $data = $logs->map(function ($log) {
            return [
                'waktu' => optional($log->created_at)
                    ->timezone('Asia/Jakarta')
                    ->format('d M Y H:i'),

                'nama' => $log->user->name ?? '-',

                'role' => strtoupper(
                    str_replace('_', ' ', $log->user->role ?? '-')
                ),

                'unit' => optional($log->user->unit)->nama
                    ?? ($log->user->name ?? '-'),

                'aktivitas' => $log->description ?? '-',
            ];
        });

        // Return array langsung, bukan object { data: [...] }
        return response()->json($data->values());
    }
}
