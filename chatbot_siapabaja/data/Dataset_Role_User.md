---
dataset: Dataset_Role_User_Baru
role: User (Guest/Publik)
role_access: user
fokus: Siapa boleh melakukan apa khusus role User publik pada sistem SIAPABAJA
total_intent: 18
catatan: Dataset ini hanya membahas cakupan akses dan batasan User publik. Langkah penggunaan detail ditempatkan pada Dataset_Workflow, sedangkan deskripsi fitur umum ditempatkan pada Dataset_Arsitektur_Fitur.
---

## 1. Intent: rbac_user_access
* **Tags:** `role_user`, `akses_publik`, `guest_view`, `tanpa_login`
* **Patterns:**
    * "Apa hak akses role User?"
    * "Apa saja yang bisa dilakukan pengunjung publik?"
    * "Apakah User perlu login?"
    * "Bisakah User melihat semua arsip?"
    * "Apakah User bisa masuk ke dashboard?"
* **Responses:** User publik adalah pengunjung umum yang tidak memiliki akun internal. User hanya dapat membuka halaman publik, melihat arsip berstatus Publik, membuka detail arsip publik, dan mengakses dokumen publik yang tersedia.

## 2. Intent: public_page_access_scope
* **Tags:** `halaman_publik`, `akses_tanpa_login`, `landing_page`, `role_user`
* **Patterns:**
    * "Halaman apa saja yang bisa diakses User publik?"
    * "Apa saja halaman yang bisa dibuka tanpa login?"
    * "Apakah pengunjung bisa membuka landing page?"
    * "Apakah pengunjung bisa melihat regulasi dan kontak?"
* **Responses:** User publik dapat mengakses halaman publik seperti Landing Page, Regulasi, Arsip PBJ publik, Kontak, serta dokumen publik yang tersedia. Halaman internal tidak tersedia untuk User publik.

## 3. Intent: user_navigation_scope
* **Tags:** `navbar_public`, `menu_user`, `sidebar_denied`, `role_user`
* **Patterns:**
    * "Menu apa saja yang tersedia untuk User publik?"
    * "Apa yang ada di navbar pengunjung?"
    * "Kenapa saya tidak punya sidebar?"
    * "Kenapa menu Dashboard tidak tampil?"
* **Responses:** User publik hanya memiliki navbar publik seperti SIAPABAJA/Home, Regulasi, Arsip PBJ, Kontak, dan Masuk. User publik tidak memiliki sidebar, Dashboard, ikon profil internal, Tambah Pengadaan, Kelola Menu, atau Kelola Akun.

## 4. Intent: public_archive_visibility
* **Tags:** `arsip_publik`, `arsip_privat`, `visibilitas_user`, `role_user`
* **Patterns:**
    * "Arsip apa yang bisa dilihat User publik?"
    * "Apakah pengunjung bisa melihat arsip privat?"
    * "Kenapa saya hanya melihat arsip publik?"
    * "Bisakah User melihat arsip Unit atau PPK yang privat?"
* **Responses:** User publik hanya dapat melihat arsip yang berstatus Publik. Arsip Privat milik Unit atau PPK tidak ditampilkan pada halaman publik.

## 5. Intent: public_archive_read_access
* **Tags:** `lihat_arsip_publik`, `detail_arsip_publik`, `metadata_publik`, `role_user`
* **Patterns:**
    * "Apa saja akses User terhadap arsip publik?"
    * "Apakah User bisa melihat daftar arsip publik?"
    * "Apakah User bisa membuka detail arsip publik?"
    * "Informasi arsip apa yang bisa dilihat pengunjung?"
* **Responses:** User publik dapat melihat daftar arsip publik, membuka detail arsip publik, dan membaca metadata publik yang tersedia. Akses ini hanya bersifat baca, bukan pengelolaan data.

## 6. Intent: public_archive_search_filter_access
* **Tags:** `cari_arsip_publik`, `filter_publik`, `sorting_publik`, `role_user`
* **Patterns:**
    * "Apakah User bisa mencari arsip publik?"
    * "Apakah pengunjung bisa menggunakan filter arsip?"
    * "Apakah User bisa filter berdasarkan tahun atau unit?"
    * "Bisakah pengunjung mengurutkan data arsip?"
* **Responses:** User publik dapat menggunakan pencarian, filter, dan pengurutan data jika tersedia pada halaman Arsip PBJ publik. Hasil yang ditampilkan tetap dibatasi pada arsip berstatus Publik.

## 7. Intent: public_document_access
* **Tags:** `dokumen_publik`, `preview_dokumen`, `unduh_dokumen`, `role_user`
* **Patterns:**
    * "Apakah User bisa membuka dokumen publik?"
    * "Apakah User bisa unduh dokumen publik?"
    * "Bisakah pengunjung melihat PDF arsip?"
    * "Kenapa dokumen tidak bisa dibuka oleh pengunjung?"
* **Responses:** User publik dapat membuka pratinjau. User tidak dapat mengunduh dokumen arsip publik, mengakses dokumen privat, atau file yang aksesnya dibatasi sistem.

## 8. Intent: internal_login_denied_user
* **Tags:** `login_denied`, `akses_internal`, `akun_internal`, `role_user`
* **Patterns:**
    * "Apakah User publik bisa login?"
    * "Kenapa saya tidak bisa login sebagai User?"
    * "Apakah pengunjung punya akun internal?"
    * "Bisa nggak saya masuk halaman internal?"
* **Responses:** User publik tidak memiliki akun login internal dan tidak memiliki sesi untuk masuk ke halaman internal. Login hanya digunakan oleh pengguna internal seperti Unit, PPK, dan Super Admin.

## 9. Intent: dashboard_access_denied_user
* **Tags:** `dashboard_denied`, `statistik_internal`, `role_user`
* **Patterns:**
    * "Apakah User bisa membuka dashboard?"
    * "Kenapa dashboard tidak muncul untuk pengunjung?"
    * "Apakah User bisa melihat statistik internal?"
    * "Apa statistik yang bisa dilihat publik?"
* **Responses:** User publik tidak dapat membuka Dashboard internal. User hanya dapat melihat ringkasan atau statistik publik yang ditampilkan pada halaman publik jika tersedia.

## 10. Intent: archive_crud_denied_user
* **Tags:** `crud_denied`, `tambah_edit_hapus_denied`, `role_user`
* **Patterns:**
    * "Apakah User bisa tambah arsip?"
    * "Apakah pengunjung bisa edit arsip?"
    * "Apakah User bisa hapus arsip?"
    * "Apakah User bisa CRUD arsip Unit atau PPK?"
* **Responses:** User publik tidak memiliki akses CRUD terhadap arsip apa pun. User tidak dapat menambah, mengedit, menghapus, atau mengubah data arsip.

## 11. Intent: document_upload_denied_user
* **Tags:** `upload_denied`, `lampiran_denied`, `dokumen_pendukung`, `role_user`
* **Patterns:**
    * "Apakah User bisa upload dokumen?"
    * "Bisakah pengunjung menambah lampiran arsip?"
    * "Apakah User bisa menghapus dokumen pendukung?"
    * "Kenapa tombol upload tidak ada untuk saya?"
* **Responses:** User publik tidak dapat mengunggah, mengganti, atau menghapus dokumen pendukung. Pengelolaan dokumen hanya tersedia untuk pengguna internal yang memiliki kewenangan arsip.

## 12. Intent: archive_status_denied_user
* **Tags:** `status_akses_denied`, `publik_privat_denied`, `role_user`
* **Patterns:**
    * "Apakah User bisa mengubah arsip publik menjadi privat?"
    * "Apakah pengunjung bisa mengatur status akses arsip?"
    * "Bisakah User membuat arsip menjadi publik?"
    * "Kenapa saya tidak bisa ubah status arsip?"
* **Responses:** User publik tidak dapat mengatur status akses arsip Publik atau Privat. Status akses arsip hanya dapat diubah oleh pengguna internal yang berwenang terhadap arsip tersebut.

## 13. Intent: export_excel_denied_user
* **Tags:** `export_denied`, `rekap_excel_denied`, `laporan_pengadaan`, `role_user`
* **Patterns:**
    * "Apakah User bisa export Excel?"
    * "Kenapa tombol Ekspor Excel tidak ada?"
    * "Bisakah pengunjung mengunduh rekap pengadaan?"
    * "Apakah User bisa membuat laporan Excel?"
* **Responses:** User publik tidak memiliki akses Ekspor Excel atau rekapitulasi internal. User hanya dapat melihat data dan dokumen publik yang tersedia pada halaman publik.

## 14. Intent: account_profile_denied_user
* **Tags:** `akun_denied`, `profil_denied`, `password_denied`, `role_user`
* **Patterns:**
    * "Apakah User publik punya akun?"
    * "Apakah saya bisa membuat akun sendiri?"
    * "Apakah pengunjung bisa ganti password?"
    * "Kenapa saya tidak punya profil?"
* **Responses:** User publik tidak memiliki akun internal, profil, password, atau fitur reset password. Akun internal hanya dibuat untuk role Unit dan PPK oleh Super Admin.

## 15. Intent: master_data_denied_user
* **Tags:** `master_data_denied`, `kelola_menu_denied`, `role_user`
* **Patterns:**
    * "Apakah User bisa kelola master data?"
    * "Kenapa menu Kelola Menu tidak tampil?"
    * "Bisakah pengunjung mengubah tahun anggaran?"
    * "Bisakah User mengubah unit kerja atau jenis pengadaan?"
* **Responses:** User publik tidak dapat mengelola master data seperti Tahun, Unit Kerja, Status Pekerjaan, atau Jenis Pengadaan. Menu Kelola Menu hanya tersedia untuk role internal yang berwenang.

## 16. Intent: chatbot_scope_user
* **Tags:** `chatbot_user`, `batasan_chatbot`, `informasi_publik`, `role_user`
* **Patterns:**
    * "Apa yang bisa ditanyakan User publik ke chatbot?"
    * "Apakah chatbot bisa membantu pengunjung?"
    * "Apakah chatbot bisa menjawab data privat?"
    * "Apakah chatbot bisa membaca isi file dokumen?"
* **Responses:** User publik dapat menggunakan chatbot untuk menanyakan informasi umum SIAPABAJA dan data publik yang tersedia. Chatbot tidak digunakan untuk membuka data privat atau membaca seluruh isi file dokumen yang tidak tersedia sebagai konteks sistem.

## 17. Intent: access_denied_context_user
* **Tags:** `akses_ditolak`, `error_403`, `private_denied`, `role_user`
* **Patterns:**
    * "Kenapa akses saya ditolak?"
    * "Apa arti akses ditolak untuk User publik?"
    * "Kenapa muncul Error 403 saat membuka halaman tertentu?"
    * "Kenapa saya diminta login?"
* **Responses:** Akses ditolak muncul jika User publik mencoba membuka halaman internal, data privat, atau aksi yang membutuhkan akun berwenang. User tetap dapat memakai halaman publik tanpa login.

## 18. Intent: activity_log_denied_user
* **Tags:** `histori_denied`, `log_aktivitas_denied`, `role_user`
* **Patterns:**
    * "Apakah User bisa melihat histori aktivitas?"
    * "Kenapa log aktivitas tidak tampil?"
    * "Bisakah pengunjung melihat siapa yang mengubah arsip?"
    * "Apakah User bisa melihat riwayat perubahan arsip?"
* **Responses:** User publik tidak dapat melihat histori aktivitas, log perubahan, atau riwayat pengelolaan arsip.
