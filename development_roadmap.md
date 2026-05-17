# Roadmap Transisi: Dari Prototype ke Backend Development

Tahap *prototyping* UI/UX dan alur navigasi telah selesai dengan sangat baik. Untuk mengubah purwarupa ini menjadi aplikasi yang 100% fungsional (berjalan dengan *database* nyata), berikut adalah **Langkah-langkah Persiapan (Step-by-Step)** yang wajib disiapkan:

---

## 1. Fase Arsitektur Database & Migrasi (Entity Relationship)
Struktur *database* harus dikunci agar tidak terjadi tambal-sulam di tengah jalan. Developer wajib menggunakan **Migration CodeIgniter 4** dengan spesifikasi tipe data yang presisi:

### 1a. Tabel `institutions` (Profil Lembaga)
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | INT AI | Primary Key |
| `name` | VARCHAR(255) | Nama lembaga: "PT Maju Pendidikan Bangsa" |
| `app_name` | VARCHAR(100) | Nama branding aplikasi: "Arusdana" |
| `type` | VARCHAR(100) | PT / Yayasan / Lembaga Non-Profit |
| `email` | VARCHAR(255) | Email operasional |
| `whatsapp` | VARCHAR(20) | Nomor WhatsApp |
| `address` | TEXT | Alamat singkat |
| `logo` | VARCHAR(255) | Path logo lembaga |
| `created_at`, `updated_at` | TIMESTAMP | Timestamps |

### 1b. Tabel `users`
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | INT AI | Primary Key |
| `institution_id` | INT FK | Relasi ke lembaga |
| `name` | VARCHAR(255) | Nama lengkap |
| `whatsapp` | VARCHAR(20) | **UNIQUE** — Dipakai sebagai identitas login |
| `otp_code` | VARCHAR(6) | Kode OTP aktif (NULL jika tidak ada) |
| `otp_expires_at` | TIMESTAMP | Waktu kadaluarsa OTP |
| `otp_attempts` | TINYINT | Counter percobaan (untuk rate-limiting) |
| `role` | ENUM('admin','operator') | Peran akses |
| `is_active` | BOOLEAN | Default TRUE |
| `last_login_at` | TIMESTAMP | Jejak login terakhir |
| `created_at`, `updated_at`, `deleted_at` | TIMESTAMP | Soft Deletes |

> [!IMPORTANT]
> **Auth menggunakan WhatsApp OTP, bukan password.** Kolom `otp_code`, `otp_expires_at`, dan `otp_attempts` adalah nyawa dari sistem login. Tidak ada kolom `password` — ini keputusan arsitektur yang disengaja.

### 1c. Tabel `units` (Unit / Program)
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | INT AI | Primary Key |
| `institution_id` | INT FK | Relasi ke lembaga |
| `name` | VARCHAR(255) | "SIMPAUD", "Konsultan Pendidikan", dll |
| `slug` | VARCHAR(255) | **UNIQUE** — URL-safe identifier |
| `short_name` | VARCHAR(20) | Singkatan visual (SPD, KP, KC) |
| `is_active` | BOOLEAN | Default TRUE |
| `sort_order` | INT | Untuk pengurutan di UI |
| `created_at`, `updated_at`, `deleted_at` | TIMESTAMP | Soft Deletes |

### 1d. Tabel `activities` (Kegiatan)
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | INT AI | Primary Key |
| `unit_id` | INT FK | Relasi ke unit |
| `name` | VARCHAR(255) | "Jualan Aplikasi Semesteran" |
| `slug` | VARCHAR(255) | **UNIQUE** — URL-safe identifier |
| `short_name` | VARCHAR(20) | Singkatan visual |
| `is_active` | BOOLEAN | Default TRUE |
| `sort_order` | INT | Untuk pengurutan di UI |
| `created_at`, `updated_at`, `deleted_at` | TIMESTAMP | Soft Deletes |

### 1e. Tabel `accounts` (Rekening / Dompet)
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | INT AI | Primary Key |
| `institution_id` | INT FK | Relasi ke lembaga |
| `name` | VARCHAR(255) | "BRI PT", "Kas Tunai" |
| `slug` | VARCHAR(255) | **UNIQUE** |
| `kind` | VARCHAR(50) | "Rekening", "Dompet Digital", "Kas Tunai" |
| `mark` | VARCHAR(20) | Label singkat visual (BRI, BCA) |
| `logo_asset` | VARCHAR(255) | Path logo bank/dompet |
| `note` | TEXT | Catatan penggunaan |
| `report_position_id` | INT FK | Relasi ke pos laporan (Kas di Bank BRI, dll) |
| `is_active` | BOOLEAN | Default TRUE |
| `sort_order` | INT | Untuk pengurutan di UI |
| `created_at`, `updated_at`, `deleted_at` | TIMESTAMP | Soft Deletes |

### 1f. Tabel `receivers` (Penerima / Kontak)
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | INT AI | Primary Key |
| `institution_id` | INT FK | Relasi ke lembaga |
| `name` | VARCHAR(255) | "Budi Santoso", "CV Maju Jaya" |
| `type` | ENUM('Tim Internal','Vendor','Klien','Lainnya') | Jenis kontak |
| `nik` | VARCHAR(20) | Opsional |
| `npwp` | VARCHAR(25) | Opsional |
| `bank_account` | VARCHAR(50) | No. rekening kontak |
| `notes` | TEXT | Catatan tambahan |
| `created_at`, `updated_at`, `deleted_at` | TIMESTAMP | Soft Deletes |

### 1g. Tabel `transaction_categories` (Kategori Transaksi)
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | INT AI | Primary Key |
| `institution_id` | INT FK | Relasi ke lembaga |
| `name` | VARCHAR(255) | "Transport", "Jasa Konsultasi" |
| `kind` | ENUM('Masuk','Keluar') | Jenis transaksi |
| `report_position_id` | INT FK | Relasi ke pos laporan |
| `is_quick` | BOOLEAN | Apakah tampil sebagai kategori cepat? |
| `chip_label` | VARCHAR(50) | Label singkat untuk UI (opsional) |
| `is_active` | BOOLEAN | Default TRUE |
| `sort_order` | INT | Untuk pengurutan |
| `created_at`, `updated_at`, `deleted_at` | TIMESTAMP | Soft Deletes |

### 1h. Tabel `report_positions` (Pos Laporan)
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | INT AI | Primary Key |
| `institution_id` | INT FK | Relasi ke lembaga |
| `name` | VARCHAR(255) | "Pendapatan Jasa", "Beban Transport" |
| `kind` | VARCHAR(50) | "Pendapatan", "Beban", "Aset", "Kewajiban", "Modal" |
| `group` | ENUM('Laba Rugi','Neraca') | Kelompok laporan |
| `sort_order` | INT | Untuk pengurutan |
| `created_at`, `updated_at` | TIMESTAMP | Timestamps |

### 1i. Tabel `book_periods` (Tahun Buku)
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | INT AI | Primary Key |
| `institution_id` | INT FK | Relasi ke lembaga |
| `name` | VARCHAR(100) | "Tahun Buku 2026" |
| `slug` | VARCHAR(100) | **UNIQUE** |
| `start_date` | DATE | Tanggal mulai (misal 1 Jan 2026) |
| `end_date` | DATE | Tanggal akhir (misal 31 Des 2026) |
| `is_active` | BOOLEAN | Hanya 1 yang aktif |
| `is_locked` | BOOLEAN | Kunci periode agar tidak bisa diubah |
| `created_at`, `updated_at` | TIMESTAMP | Timestamps |

### 1j. Tabel `opening_balances` (Saldo Awal)
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | INT AI | Primary Key |
| `book_period_id` | INT FK | Relasi ke tahun buku |
| `report_position_id` | INT FK | Relasi ke pos laporan |
| `source_label` | VARCHAR(255) | Label sumber: "BRI PT" |
| `amount` | DECIMAL(15,2) | Nilai saldo awal |
| `created_at`, `updated_at` | TIMESTAMP | Timestamps |

### 1k. Tabel `transactions` — Jantung Aplikasi
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | INT AI | Primary Key |
| `institution_id` | INT FK | Relasi ke lembaga |
| `type` | ENUM('masuk','keluar','pindah','honor') | Jenis transaksi |
| `amount` | DECIMAL(15,2) | **Wajib DECIMAL**, jangan INT/BIGINT |
| `admin_fee` | DECIMAL(15,2) | Default 0 — biaya admin bank |
| `unit_id` | INT FK | Relasi ke unit |
| `activity_id` | INT FK | Relasi ke kegiatan |
| `category_id` | INT FK | Relasi ke kategori (NULL untuk pindah) |
| `from_account_id` | INT FK NULLABLE | Rekening sumber (wajib untuk keluar/pindah/honor) |
| `to_account_id` | INT FK NULLABLE | Rekening tujuan (wajib untuk masuk/pindah) |
| `receiver_id` | INT FK NULLABLE | Kontak penerima (wajib untuk honor, opsional untuk biaya) |
| `transaction_date` | DATE | Tanggal transaksi |
| `transaction_time` | TIME | Jam transaksi |
| `notes` | TEXT | Keterangan |
| `proof_image` | VARCHAR(255) | Path file bukti struk/slip |
| `created_by` | INT FK | User yang mencatat |
| `created_at`, `updated_at`, `deleted_at` | TIMESTAMP | **Wajib Soft Deletes** |

> [!CAUTION]
> Kolom `amount` dan `admin_fee` **WAJIB** menggunakan `DECIMAL(15,2)`, bukan `INT` atau `BIGINT`. Hal ini mencegah bug pembulatan dan menyiapkan fondasi jika kelak terdapat transaksi desimal. Margin 15 digit mencukupi hingga ratusan miliar.

### 1l. Sistem Single-Entry vs Double-Entry
Untuk kompleksitas Arus Finance saat ini, **Single-Entry yang Diperkaya (Enriched Single-Entry)** sudah cukup. Artinya, setiap mutasi (masuk/keluar) dicatat dalam 1 baris di tabel `transactions`. Khusus transaksi `pindah`, baris tersebut merekam `from_account_id` dan `to_account_id` sekaligus.

### 1m. Diagram Relasi Antar Tabel (ERD Ringkas)
```
institutions
    ├── users
    ├── units
    │     └── activities
    ├── accounts ──→ report_positions
    ├── receivers
    ├── transaction_categories ──→ report_positions
    ├── book_periods
    │     └── opening_balances ──→ report_positions
    └── transactions
          ├── → units
          ├── → activities
          ├── → transaction_categories
          ├── → accounts (from & to)
          ├── → receivers
          └── → users (created_by)
```

---

## 2. Persiapan Infrastruktur & Pihak Ketiga (Third-Party)

### 2a. WhatsApp OTP Gateway
Pilih *provider* API WhatsApp untuk menangani pengiriman kode OTP:
- **Opsi:** Fonnte, Watzap, Twilio, atau Wablas.
- **Kebutuhan:** Kirim pesan berisi 4–6 digit kode OTP ke nomor WhatsApp user.
- **Budget:** Siapkan estimasi biaya per-pesan (rata-rata Rp 200–500/pesan untuk lokal).
- **Fallback:** Jika WhatsApp gateway gagal, siapkan alternatif SMS via layanan seperti Zenziva.

### 2b. Penyimpanan Berkas (File Storage)
Tentukan strategi *storage* untuk bukti transaksi (foto struk):
- **Opsi Sederhana:** Lokal di `/writable/uploads/proofs/YYYY/MM/` dengan penamaan: `{transaction_id}_{timestamp}.webp`.
- **Opsi Skala Besar:** Cloud Object Storage (AWS S3, GCS, atau DigitalOcean Spaces).
- **Kompresi Wajib:** Resize gambar ke maks 1200px lebar & konversi ke WebP sebelum menyimpan, agar server tidak penuh. Gunakan library PHP `Intervention/Image`.

### 2c. Vision AI / OCR (Opsional — Bisa Ditunda)
Jika fitur kamera (AI Scan Struk) ingin langsung diwujudkan:
- **Opsi:** Google Cloud Vision API, OpenAI Vision, atau Tesseract OCR (gratis, self-hosted).
- **Rekomendasi:** Tunda ke Sprint 5 / v2.0. Fokuskan Sprint 1–4 pada input manual terlebih dahulu.

---

## 3. Arsitektur Pola Desain (Service-Repository Pattern di CI4)

### 3a. Struktur Folder yang Direkomendasikan
```
app/
├── Controllers/
│     ├── Auth.php         ← Hanya routing & response
│     ├── Arus.php         ← Hanya routing & response (dipecah nanti)
│     └── Api/             ← (Opsional) REST endpoints
├── Models/
│     ├── TransactionModel.php
│     ├── UnitModel.php
│     ├── AccountModel.php
│     ├── ReceiverModel.php
│     └── ...
├── Services/
│     ├── TransactionService.php   ← Logika bisnis inti
│     ├── RekapService.php         ← Agregasi rekap & penerima
│     ├── AuthService.php          ← OTP generation & validation
│     └── FileUploadService.php    ← Kompresi & penyimpanan bukti
├── Filters/
│     ├── AuthFilter.php           ← Cek sesi login (middleware)
│     └── RateLimitFilter.php      ← Anti-spam OTP
└── Database/
      └── Migrations/              ← File migrasi tabel
```

### 3b. Prinsip Kunci
- **Models (Data Layer):** Hanya berisi definisi tabel, *allowed fields*, *validation rules*, dan *callbacks* murni bawaan CI4.
- **Service Layer (Business Logic):** Semua logika perhitungan yang sebelumnya kita *mock-up* (seperti `buildRekapReceivers`, kalkulasi surplus/defisit, kalkulasi mutasi rekening `transfer_in` / `transfer_out`) **HARUS** diletakkan di sini menggunakan *Raw Query / Query Builder* yang dioptimasi (GROUP BY & SUM di sisi MySQL, bukan di PHP loop).
- **Database Transactions:** Saat menyimpan transaksi `Pindah Dana` atau mengunggah gambar, wajib dibungkus dalam `$db->transStart()` dan `$db->transComplete()` agar jika terjadi kegagalan (misal gambar gagal di-upload), data di database dibatalkan (*Rollback*).

### 3c. Memecah Controller `Arus.php`
Controller `Arus.php` saat ini berukuran **2219 baris** dan menampung seluruh prototype. Saat migrasi ke backend, pecah menjadi:
- `HomeController.php` — Beranda
- `CatatController.php` — Semua form input transaksi
- `RekapController.php` — Rekap & filter
- `SettingController.php` — CRUD master data
- `DetailController.php` — Detail rekening, unit, kegiatan, transaksi

---

## 4. Keamanan & *Strict Validation* (Server-Side)

### 4a. Validasi Transaksi
| Rule | Keterangan |
|---|---|
| Saldo Negatif | Query pengecekan sebelum `INSERT`. Jika `Uang Keluar` > `Saldo Rekening Saat Ini`, *throw error*. |
| Rekening Sama | Pindah Dana: `from_account_id` ≠ `to_account_id`. |
| Integritas Kategori | Pastikan `category_id` bertipe `Keluar` jika user di form `Uang Keluar`. |
| Integritas Relasi | `activity.unit_id` harus cocok dengan `unit_id` yang dipilih di form. |
| Periode Terkunci | Tolak input jika `book_period.is_locked = true`. |
| Nominal Minimum | `amount` harus > 0. Tidak boleh nol atau negatif. |

### 4b. Validasi Auth & OTP
| Rule | Keterangan |
|---|---|
| Rate Limiting | Blokir nomor jika *request* OTP > 3x dalam 5 menit. Gunakan CI4 `cache()`. |
| OTP Expiry | Kode OTP kadaluarsa setelah 5 menit (`otp_expires_at`). |
| OTP Attempts | Maksimal 3x salah ketik OTP, lalu kode dianulir dan harus *request* ulang. |
| Nomor Format | Validasi format nomor WhatsApp Indonesia (08xx / +628xx). |

### 4c. Keamanan Umum
- **CSRF Protection:** Wajib aktifkan di `Config/Filters.php` CI4 untuk semua form POST.
- **File Upload:** Batasi *mime-types* secara ketat: hanya `image/jpeg`, `image/png`, `image/webp`. Maksimal 2MB.
- **Session Guard:** Halaman utama (Beranda, Catat, Rekap, Pengaturan) harus dilindungi oleh `AuthFilter` middleware. Halaman Auth (login/register/otp) harus bisa diakses tanpa login.
- **SQL Injection:** Gunakan *Query Builder* CI4 atau *Prepared Statements*. Jangan pernah menyusun query dengan string concatenation.

---

## 5. Manajemen Konteks Aktif (Sesi User)

> [!NOTE]
> Ini adalah **celah paling kritis** yang belum terdokumentasi. PROD_SPEC mengandalkan konsep "Konteks Aktif" (Unit + Kegiatan aktif user), tetapi belum ada penjelasan bagaimana ini *persist* di backend.

### Strategi Implementasi:
- Simpan `active_unit_id` dan `active_activity_id` di **CI4 Session** (`$session->set()`).
- Ketika user berpindah konteks (klik Unit Card / Kegiatan Card), kirim POST request ke endpoint `/api/set-context` yang meng-update session.
- Seluruh form pencatatan mengambil default value dari session ini.
- Jika session kosong (user baru login), arahkan ke pemilihan konteks terlebih dahulu.

---

## 6. Strategi Seeding Data Default

> [!TIP]
> Saat lembaga pertama kali mendaftar, sistem harus secara otomatis menyediakan data awal agar user tidak memulai dari layar kosong.

### Data yang Harus Di-*Seed* Otomatis:
- **Kategori Masuk Default:** Jasa Konsultasi, Project Client, Pelatihan/Workshop, Maintenance/Retainer.
- **Kategori Keluar Default:** Transport, Konsumsi, Cetak Dokumen, Honor, Iklan, Internet/Pulsa, Sewa/Venue, ATK, Operasional, Lainnya.
- **Pos Laporan Default:** Pendapatan Jasa, Pendapatan Pelatihan, Beban Transport, Beban Honor, Kas di Bank, Modal Awal, dll.
- **Tahun Buku Pertama:** Otomatis dibuat berdasarkan tahun pendaftaran.

Implementasikan ini sebagai `DatabaseSeeder` CI4 yang dipanggil saat proses registrasi lembaga berhasil.

---

## 7. Penanganan Edge Cases yang Kritis

### 7a. Hapus Data Berantai (Cascading)
Jika user menghapus sebuah **Unit**, apa yang terjadi pada:
- Kegiatan di bawahnya?
- Transaksi yang terkait unit tersebut?

**Keputusan Arsitektur:** Gunakan **Soft Deletes** + **Restrict**. Artinya:
- Unit/Kegiatan/Rekening yang masih memiliki transaksi **TIDAK BISA** dihapus.
- Tampilkan pesan: "Unit ini masih memiliki X transaksi. Pindahkan atau hapus transaksi terlebih dahulu."

### 7b. Saldo Rekening — Computed vs Stored?
**Rekomendasi: Computed (Dihitung Real-Time).**
- Jangan simpan kolom `balance` di tabel `accounts`.
- Hitung saldo dari `SUM(masuk) - SUM(keluar) + opening_balance` setiap kali dibutuhkan.
- Gunakan *database view* atau *cached query* untuk performa.
- **Alasan:** Menghindari data tidak sinkron (*stale balance*) jika ada transaksi yang diedit/dihapus.

### 7c. Edit/Hapus Transaksi Lama
- Jika transaksi diedit, seluruh saldo rekening harus dihitung ulang (*recompute*).
- Pertimbangkan apakah perlu *audit log* (menyimpan riwayat perubahan transaksi).

---

## 8. Rencana Eksekusi Bertahap (Sprint Breakdown)

### Sprint 1 — Fondasi & Auth (Minggu 1–2)
- [ ] Setup database MySQL/MariaDB
- [ ] Buat seluruh Migration files (11 tabel)
- [ ] Buat DatabaseSeeder untuk data default
- [ ] Implementasi `AuthService` (generate OTP, validate OTP)
- [ ] Integrasi WhatsApp Gateway
- [ ] Implementasi `AuthFilter` middleware
- [ ] Koneksi halaman Login → OTP → Redirect ke Beranda

### Sprint 2 — Master Data CRUD (Minggu 3–4)
- [ ] Buat semua Models (Unit, Activity, Account, Receiver, Category, dsb)
- [ ] Implementasi CRUD untuk setiap halaman Pengaturan
- [ ] Form validasi server-side untuk setiap master
- [ ] Halaman Profil Lembaga — Read & Edit
- [ ] Ganti data *dummy* di dropdown form dengan query database

### Sprint 3 — Pencatatan Transaksi (Minggu 5–6)
- [ ] Implementasi `TransactionService` — create, update, delete
- [ ] Form Uang Masuk → simpan ke DB
- [ ] Form Biaya/Belanja → simpan ke DB + validasi saldo
- [ ] Form Honor & Gaji → simpan ke DB + link ke Receiver
- [ ] Form Pindah Dana → simpan ke DB + DB Transaction (Rollback)
- [ ] Upload & kompresi bukti transaksi
- [ ] Implementasi Konteks Aktif via Session

### Sprint 4 — Dashboard & Laporan Live (Minggu 7–8)
- [ ] `RekapService` — query agregasi untuk Rekap
- [ ] Beranda — data real dari DB (saldo, masuk, keluar, surplus)
- [ ] Rekap — filter periode/unit/kegiatan dari DB
- [ ] Penerima Terlibat — query `GROUP BY receiver_id` dari DB
- [ ] Detail Rekening — mutasi real
- [ ] Detail Unit & Kegiatan — transaksi real
- [ ] Detail Transaksi — mode Lihat & Edit dari DB

### Sprint 5 — Polish & Hardening (Minggu 9–10)
- [ ] Audit log untuk edit/hapus transaksi
- [ ] Notifikasi WhatsApp saat transaksi besar dicatat
- [ ] Export laporan sederhana (PDF/Excel)
- [ ] Testing menyeluruh (edge cases, saldo, validasi)
- [ ] Performance optimization (index DB, query caching)

---

## 9. Checklist Teknis Sebelum Mulai Coding

- [ ] **Database engine:** MySQL 8.0+ atau MariaDB 10.6+
- [ ] **PHP version:** 8.1+ (sesuai kebutuhan CI4 terbaru)
- [ ] **Composer dependencies:** `codeigniter4/framework`, `intervention/image` (kompresi foto)
- [ ] **Environment file:** `.env` dengan konfigurasi DB, WhatsApp API key, base URL
- [ ] **Git branching:** `main` (production), `develop` (staging), `feature/*` (per-sprint)
- [ ] **Domain & SSL:** Siapkan domain dan sertifikat SSL untuk API WhatsApp callback
- [ ] **Keputusan arsitektur yang sudah dikunci:**
  - ✅ Single-Entry (bukan Double-Entry)
  - ✅ Saldo dihitung real-time (bukan stored)
  - ✅ Auth via WhatsApp OTP (bukan password)
  - ✅ Soft Deletes untuk semua data keuangan
  - ✅ Restrict delete jika masih ada relasi transaksi

---
*Dokumen ini siap digunakan sebagai **Technical Blueprint & Sprint Backlog** bagi tim Developer.*
