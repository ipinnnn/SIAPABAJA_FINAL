<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Unit;
use App\Models\User;

class Pengadaan extends Model
{
    use HasFactory;

    protected $table = 'pengadaans';

    protected $fillable = [
        // A. Informasi Umum
        'tahun',
        'unit_id',
        'nama_pekerjaan',
        'id_rup',
        'jenis_pengadaan',
        'status_pekerjaan',
        'metode_pengadaan',
        
        // B. Status Akses Arsip
        'status_arsip',

        // C. Anggaran
        'pagu_anggaran',
        'hps',
        'nilai_kontrak',
        'nama_rekanan',

        // D. Dokumen Pengadaan (json/jsonb)
        'dokumen_kak',
        'dokumen_hps',
        'dokumen_spesifikasi_teknis',
        'dokumen_rancangan_kontrak',
        'dokumen_lembar_data_kualifikasi',
        'dokumen_lembar_data_pemilihan',
        'dokumen_daftar_kuantitas_harga',
        'dokumen_jadwal_lokasi_pekerjaan',
        'dokumen_gambar_rancangan_pekerjaan',
        'dokumen_amdal',
        'dokumen_penawaran',
        'surat_penawaran',
        'dokumen_kemenkumham',
        'ba_pemberian_penjelasan',
        'ba_pengumuman_negosiasi',
        'ba_sanggah_banding',
        'ba_penetapan',
        'laporan_hasil_pemilihan',
        'dokumen_sppbj',
        'surat_perjanjian_kemitraan',
        'surat_perjanjian_swakelola',
        'surat_penugasan_tim_swakelola',
        'dokumen_mou',
        'dokumen_kontrak',
        'ringkasan_kontrak',
        'jaminan_pelaksanaan',
        'jaminan_uang_muka',
        'jaminan_pemeliharaan',
        'surat_tagihan',
        'surat_pesanan_epurchasing',
        'dokumen_spmk',
        'dokumen_sppd',
        'laporan_pelaksanaan_pekerjaan',
        'laporan_penyelesaian_pekerjaan',
        'bap',
        'bast_sementara',
        'bast_akhir',
        'dokumen_pendukung_lainya',

        // E. Dokumen Tidak Dipersyaratkan (json/jsonb)
        'dokumen_tidak_dipersyaratkan',

        // Audit
        'created_by',
    ];

    /**
     * 
     * - angka -> integer 
     * - json/jsonb -> array
     */
    protected $casts = [
        'tahun'         => 'integer',
        'unit_id'       => 'integer',
        'created_by'    => 'integer',

        'pagu_anggaran' => 'integer',
        'hps'           => 'integer',
        'nilai_kontrak' => 'integer',

        // json/jsonb dokumen
        'dokumen_kak' => 'array',
        'dokumen_hps' => 'array',
        'dokumen_spesifikasi_teknis' => 'array',
        'dokumen_rancangan_kontrak' => 'array',
        'dokumen_lembar_data_kualifikasi' => 'array',
        'dokumen_lembar_data_pemilihan' => 'array',
        'dokumen_daftar_kuantitas_harga' => 'array',
        'dokumen_jadwal_lokasi_pekerjaan' => 'array',
        'dokumen_gambar_rancangan_pekerjaan' => 'array',
        'dokumen_amdal' => 'array',
        'dokumen_penawaran' => 'array',
        'surat_penawaran' => 'array',
        'dokumen_kemenkumham' => 'array',
        'ba_pemberian_penjelasan' => 'array',
        'ba_pengumuman_negosiasi' => 'array',
        'ba_sanggah_banding' => 'array',
        'ba_penetapan' => 'array',
        'laporan_hasil_pemilihan' => 'array',
        'dokumen_sppbj' => 'array',
        'surat_perjanjian_kemitraan' => 'array',
        'surat_perjanjian_swakelola' => 'array',
        'surat_penugasan_tim_swakelola' => 'array',
        'dokumen_mou' => 'array',
        'dokumen_kontrak' => 'array',
        'ringkasan_kontrak' => 'array',
        'jaminan_pelaksanaan' => 'array',
        'jaminan_uang_muka' => 'array',
        'jaminan_pemeliharaan' => 'array',
        'surat_tagihan' => 'array',
        'surat_pesanan_epurchasing' => 'array',
        'dokumen_spmk' => 'array',
        'dokumen_sppd' => 'array',
        'laporan_pelaksanaan_pekerjaan' => 'array',
        'laporan_penyelesaian_pekerjaan' => 'array',
        'bap' => 'array',
        'bast_sementara' => 'array',
        'bast_akhir' => 'array',
        'dokumen_pendukung_lainya' => 'array',

        // json/jsonb kolom E
        'dokumen_tidak_dipersyaratkan' => 'array',
    ];

    /**
     * Relasi: Pengadaan milik Unit (FK unit_id)
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    /**
     * Relasi: Pengadaan dibuat oleh User (FK created_by)
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
