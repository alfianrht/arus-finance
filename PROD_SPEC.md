# Product Spec — Arus

## Konsep

Arus adalah aplikasi pencatatan uang masuk dan keluar harian berbasis lembaga, unit/program, kegiatan, rekening/dompet, dan transaksi.

Struktur utama:

Profil Lembaga → Unit/Program → Kegiatan → Transaksi

Contoh struktur:
- PT Maju Pendidikan Bangsa
  - SIMPAUD
    - Jualan Aplikasi Semesteran
    - Pelatihan SIMPAUD
    - Operasional SIMPAUD
  - Konsultan Pendidikan
    - Perizinan LKP Tax Session
    - Perizinan LSP
    - Pendampingan Sekolah
  - KebagusanCode
    - Project Website Client
    - Project Aplikasi Client
    - Maintenance Sistem

Arus bukan ERP dan bukan sistem akuntansi penuh. Fokus utama produk adalah pencatatan operasional harian yang cepat, ringan, dan mudah ditinjau kembali.

## Menu Utama

Menu utama hanya:
1. Beranda
2. Catat
3. Rekap

Pengaturan dan master data boleh ada, tetapi bukan menu utama besar.

## Konteks Aktif

Arus memakai konsep `Konteks Aktif`.

Konteks aktif terdiri dari:
- Unit / Program
- Kegiatan

Contoh:

Sedang mencatat:
Konsultan Pendidikan / Perizinan LKP Tax Session

Prinsip UX:
- Konteks aktif tampil jelas di Beranda.
- Halaman `Catat` tetap menampilkan konteks aktif dalam bentuk ringkas dengan opsi edit.
- Pada halaman form transaksi, konteks tidak ditampilkan sebagai card terpisah di atas.
- Pada halaman form transaksi, `Unit / Program` dan `Kegiatan` tampil sebagai field select di dalam form.
- Nilai default kedua select tersebut mengikuti konteks aktif saat user masuk ke form.

Tujuan konsep ini:
- user tidak perlu berpindah konteks berulang-ulang
- pencatatan tetap cepat
- transaksi tetap terikat ke unit dan kegiatan yang benar

## Beranda

Beranda menampilkan:
- Nama aplikasi: `Arus`
- Nama lembaga
- Saldo total
- Uang masuk bulan ini
- Uang keluar bulan ini
- Laba / surplus sementara
- Konteks aktif
- Tombol cepat `Uang Masuk`
- Tombol cepat `Uang Keluar`
- Shortcut ke `Pengaturan`
- Ringkasan `Unit / Program`
- Transaksi terakhir

### Card Unit / Program

Card unit dibuat seperti kartu finansial modern:
- warna utama lime / hijau terang
- bentuk padat, tidak terlalu tinggi
- menampilkan singkatan unit sebagai elemen visual besar
- menampilkan:
  - nama unit
  - laba sementara
  - uang masuk
  - biaya
  - jumlah kegiatan
  - tombol `Uang Masuk`
  - tombol `Uang Keluar`

### Transaksi Terakhir

Setiap baris transaksi di Beranda harus bisa diklik dan masuk ke halaman detail transaksi.

## Catat

Halaman `Catat` adalah hub pencatatan cepat.

Yang ditampilkan:
- konteks aktif
- tombol `Uang Masuk`
- tombol `Uang Keluar`
- kategori cepat
- riwayat terakhir

### Kategori Cepat

Kategori cepat diambil dari master kategori transaksi bertipe `Keluar` yang ditandai sebagai kategori cepat.

Contoh:
- Transport
- Konsumsi
- Honor
- Cetak
- Lainnya

### Riwayat Terakhir

Setiap baris transaksi di halaman `Catat` harus bisa diklik dan masuk ke halaman detail transaksi.

## Transaksi

Ada 3 jenis transaksi utama:
- Uang Masuk
- Biaya / Belanja
- Pindah Dana

### Uang Masuk

Dipakai ketika uang benar-benar masuk ke rekening atau dompet.

Field form:
- Unit / Program
- Kegiatan
- Nominal
- Kategori Pemasukan
- Masuk ke rekening / dompet
- Tanggal
- Keterangan
- Upload bukti

Prinsip:
- kategori pemasukan berasal dari master kategori transaksi bertipe `Masuk`
- kategori ini juga terkait ke `Pos Laporan`

### Uang Keluar

Saat klik `Uang Keluar`, tampilkan 3 pilar opsi pengeluaran:
1. Biaya Operasional
2. Honor & Gaji
3. Pindah Dana

#### Biaya Operasional

Dipakai ketika uang benar-benar habis untuk kebutuhan operasional rutin (transport, konsumsi, atk, dsb).

Field form:
- Nominal utama
- Biaya Admin Bank (Opsional, otomatis jadi transaksi beban terpisah)
- Kategori Pengeluaran
- Keluar dari rekening / dompet
- Unit / Program & Kegiatan
- Tanggal & Keterangan
- Upload bukti (Camera AI)

Efek:
- masuk sebagai biaya
- mengurangi laba / surplus
- mengurangi saldo rekening / dompet sumber

Prinsip:
- kategori pengeluaran berasal dari master kategori transaksi bertipe `Keluar`
- kategori ini juga terkait ke `Pos Laporan`

#### Honor & Gaji

Form terspesialisasi untuk pembayaran SDM, tim internal, narasumber, atau insentif.

Field form:
- Penerima / Karyawan (Dari Master Kontak)
- Total Dibayarkan (THP)
- Biaya Admin Bank (Opsional, otomatis jadi transaksi beban terpisah)
- Kategori (Otomatis terkunci ke Beban Honor & Gaji)
- Keluar dari rekening / dompet
- Unit / Program & Kegiatan
- Tanggal & Keterangan (Periode)
- Bukti Dokumen (Slip Gaji/Tanda Terima)

Prinsip:
- Mencegah kesalahan input kategori pengeluaran karena sudah dikunci otomatis.
- Terintegrasi langsung dengan master Penerima / Kontak bertipe Tim Internal.

#### Pindah Dana

Dipakai ketika uang hanya berpindah tempat.

Contoh:
- BRI PT → Dana Operasional Cago
- BRI PT → BCA PT

Field form:
- Nominal Transfer
- Biaya Admin Bank (Opsional, memisahkan biaya admin ke kategori Beban Operasional agar neraca balance)
- Unit / Program & Kegiatan
- Dari rekening / dompet
- Ke rekening / dompet
- Tanggal & Keterangan
- Upload bukti transfer

Efek:
- saldo asal berkurang (Nominal Transfer + Biaya Admin)
- saldo tujuan bertambah (Nominal Transfer)
- Nominal Transfer tidak masuk biaya & laba/rugi
- Biaya Admin masuk sebagai Beban Operasional
- **Biaya Admin:** Untuk Pindah Dana beda bank/layanan, terdapat field "Biaya Admin (Beban Operasional)". Input ini hybrid (dropdown otomatis / manual ketik). Biaya admin pada pindah dana akan tampil secara global di subline transaksi daftar riwayat.

Label visual yang digunakan:
- `Pindah Dana`
- `Tidak dihitung sebagai biaya`
- `[Unit/Program] · [Tanggal] · [Waktu]`

Catatan UI wajib:

`Pindah Dana tidak dihitung sebagai biaya.`

## Input Berbasis Bukti

Arus dirancang dengan pendekatan `camera-first` untuk mempercepat input transaksi.

Pada form transaksi baru:
- user bisa `Buka Kamera`
- user bisa `Upload File`
- form manual tetap tersedia penuh di bawahnya

Tujuan fitur ini:
- nanti AI membaca bukti transaksi
- AI membantu mengisi field tertentu secara otomatis

Contoh field yang akan di-auto-fill:
- nominal
- kategori
- rekening asal / tujuan
- tanggal
- keterangan

Untuk tahap prototype saat ini:
- ini masih sebatas UI / view
- belum ada parsing AI sungguhan

## Detail Transaksi

Setiap item transaksi pada daftar berikut harus bisa diklik:
- Beranda
- Catat
- Rekap
- Detail Unit
- Detail Kegiatan
- Detail Rekening / Dompet

Halaman detail transaksi memakai pola form yang sama dengan halaman pencatatan.

Mode yang tersedia:
- `Lihat`
- `Edit`

### Detail Transaksi — Mode Lihat

Menampilkan form dalam keadaan read-only.

Field yang tampil menyesuaikan jenis transaksi:

Untuk `Uang Masuk`:
- Unit / Program
- Kegiatan
- Nominal
- Kategori Pemasukan
- Masuk ke rekening / dompet
- Tanggal
- Keterangan
- Bukti transaksi

Untuk `Biaya Operasional`:
- Unit / Program
- Kegiatan
- Nominal
- Biaya Admin
- Kategori Pengeluaran
- Keluar dari rekening / dompet
- Tanggal
- Keterangan
- Bukti transaksi

Untuk `Honor & Gaji`:
- Penerima / Karyawan
- Total Dibayarkan (THP)
- Biaya Admin
- Kategori Pengeluaran (Terkunci Beban Honor)
- Keluar dari rekening / dompet
- Tanggal
- Keterangan
- Bukti Dokumen

Untuk `Pindah Dana`:
- Rekening asal akan berkurang saldonya (sebesar nominal + biaya admin).
- Rekening tujuan akan bertambah saldonya (sebesar nominal murni).
- Transaksi pindah dananya sendiri **TIDAK** memotong surplus/laba-rugi, namun **Biaya Admin**-nya (jika ada) akan memotong surplus/laba-rugi sebagai Beban Operasional.

### Pelacakan Penerima Terlibat (Penerima Terlibat)
Sistem memiliki fitur context-aware yang merangkum *Kontak/Penerima* mana saja yang teraliri dana pada rentang waktu atau unit/kegiatan yang sedang aktif difilter.
- Menggunakan arsitektur "Horizontal Scroll (Card Carousel)".
- Ditampilkan di 4 level: Dashboard Rekap (`/rekap`), Detail Rekening (`/rekening/...`), Detail Unit (`/unit/...`), dan Detail Kegiatan (`/kegiatan/...`).
- Fitur ini menghitung `total_received` per kontak berdasarkan transaksi `Biaya` atau `Honor & Gaji` yang terekam.
- Tanggal
- Keterangan
- Bukti transaksi

### Detail Transaksi — Mode Edit

Mode edit memakai form yang sama, tetapi field bisa diubah.

Prinsip:
- tidak ada blok `Konteks Aktif` di atas
- `Unit / Program` dan `Kegiatan` tetap menjadi bagian dari form
- area `Bukti Transaksi` tetap tersedia
- preview bukti boleh memakai placeholder sementara
- tidak perlu pola kamera-first seperti halaman create

## Rekap

Rekap tetap satu halaman dengan filter:
- Periode
- Unit / Program
- Kegiatan

Yang ditampilkan:
- Uang masuk
- Biaya
- Laba / surplus sementara
- Saldo total
- Saldo per rekening / dompet
- Ringkasan per unit / program
- Ringkasan per kegiatan
- Pindah dana
- Transaksi terbaru

Pindah Dana harus terlihat di rekap, tetapi tidak dihitung sebagai biaya.

### Rekening / Dompet di Rekap

`Saldo per Rekening / Dompet` bukan hanya angka saldo.

Drill-down rekening perlu mengarah ke detail yang membantu menjawab:
- saldo rekening terbentuk dari apa
- transaksi apa saja yang melewati rekening itu
- kegiatan mana saja yang menggunakan rekening itu

Kartu rekening di rekap dibuat ringkas:
- putih
- border hitam
- tetap punya bentuk khas / notch visual
- bisa diklik ke detail rekening

## Rekening / Dompet

Contoh:
- BRI PT
- BCA PT
- Dana Operasional Cago
- Kas Tunai

Gunakan istilah:
- `Rekening`
- `Dompet`
- `Kas Tunai`

Jangan gunakan istilah utama seperti `rekening pribadi`.

Jika ada dana yang dipegang PIC, gunakan pola:
- `Dana Operasional [Nama PIC]`

Setiap rekening / dompet harus punya:
- nama
- jenis
- label / singkatan / identitas visual
- pos laporan terkait
- catatan penggunaan

## Master Data

Master data yang dipakai saat ini:
- Profil Lembaga
- Unit / Program
- Kegiatan
- Rekening / Dompet
- Penerima / Kontak
- Kategori Transaksi
- Pos Laporan
- Tahun Buku
- Saldo Awal

### Catatan Penyederhanaan Master

Keputusan produk saat ini:
- tidak memakai menu terpisah `Sumber Pemasukan`
- tidak memakai menu terpisah `Mapping Kategori`
- tidak memakai menu terpisah `Mapping Rekening`

Sebagai gantinya:
- `Kategori Transaksi` langsung punya:
  - nama kategori
  - jenis transaksi: `Masuk` / `Keluar`
  - pos laporan terkait
  - status
  - penanda kategori cepat
- `Rekening / Dompet` langsung punya:
  - pos laporan terkait
- `Penerima / Kontak` langsung punya:
  - nama
  - jenis kontak (Tim Internal, Vendor, Klien, Lainnya)
  - informasi pelengkap (NIK, NPWP, Rekening, Catatan)

Dengan begitu:
- struktur lebih sederhana
- menu pengaturan tidak terlalu banyak
- fondasi laporan tahunan tetap siap
- pengelolaan karyawan dan vendor terpusat di satu pintu (Penerima)

## Kategori Transaksi Default

### Kategori Masuk

- Jasa Konsultasi
- Project Client
- Pelatihan / Workshop
- Maintenance / Retainer

### Kategori Keluar

- Transport
- Konsumsi
- Cetak Dokumen
- Honor
- Iklan
- Internet/Pulsa
- Sewa/Venue
- ATK
- Operasional
- Lainnya

## Pos Laporan

`Pos Laporan` adalah master inti untuk fondasi laporan tahunan.

Master lain yang dikaitkan ke sini:
- Kategori Transaksi
- Rekening / Dompet
- Saldo Awal

Contoh pos laporan:
- Pendapatan Jasa
- Pendapatan Pelatihan
- Beban Transport
- Beban Konsumsi
- Beban Honor
- Kas di Bank BRI
- Kas di Bank BCA
- Kas Operasional
- Kas Tunai
- Piutang Usaha
- Hutang Usaha
- Modal Awal

## Tahun Buku dan Saldo Awal

Walau fitur laporan tahunan belum dibuat, struktur berikut sudah harus ada:
- Tahun Buku
- Saldo Awal

Tujuan:
- agar nanti laporan tahunan bisa dibangun tanpa bongkar struktur data lagi
- saldo rekening, kas, hutang, piutang, dan modal bisa punya titik awal

## Pengaturan

Pengaturan tidak masuk menu utama, tetapi bisa diakses dari Beranda.

Isi pengaturan:
- Profil Lembaga
- Unit / Program
- Kegiatan
- Rekening / Dompet
- Kategori Transaksi
- Pos Laporan
- Tahun Buku
- Saldo Awal

Setiap master sebaiknya punya:
- halaman daftar
- tombol tambah
- item yang bisa diklik ke edit
- form dummy untuk validasi struktur input

## Design Direction

Arah visual:
- modern finance mobile app
- mobile-first
- card-based
- white / soft gray dominant
- near black text
- lime / green accent
- rounded large cards
- bottom navigation floating
- angka utama besar dan tegas
- spacing rapat tetapi tetap lega untuk layar HP

### Bahasa Visual Card

Unit / Program:
- surface lime / hijau terang
- menampilkan singkatan besar
- padat dan terasa seperti kartu finansial

Kegiatan:
- surface hitam
- menampilkan singkatan besar
- tetap padat dan mudah dipindai di mobile

Rekening / Dompet:
- surface putih
- border hitam
- bentuk khas / notch bawah
- identitas visual seperti kartu saldo ringkas

### Ikon

Gunakan `Material Symbols` sebagai sumber ikon utama.

### Catatan Implementasi UI Saat Ini

Prototype saat ini mengikuti prinsip:
- Tailwind utility langsung di markup
- mobile-first
- data dummy / statis
- route bisa diklik dan diuji
- view-only untuk sebagian besar flow
- belum ada backend CRUD / database / akuntansi dinamis
- **Global Dropdown Fix:** Element `<select>` tidak menggunakan *native OS default arrow* (yang rentan bug rendering/layout berganda), melainkan dibungkus khusus dan menggunakan Material Symbols `expand_more` murni.
- **Icon Formatting:** Render icon seperti `arrow_right_alt` dalam inline-text (headline Pindah Dana) dikonfigurasi ukurannya agar setara `text-[1em]` dan memiliki `align-middle` untuk perataan tipografi yang sempurna lintas-device.

## Modul Autentikasi (Auth)

Aplikasi memiliki alur login yang didesain dengan konsep mobile-first terisolasi (tanpa navigasi utama). Rute-rute yang tersedia:
- `/auth/login` : Memasukkan No. WhatsApp.
- `/auth/register` : Mendaftarkan akun baru (Nama & No. WhatsApp).
- `/auth/forgot-password` : Form pemulihan akun/sandi.
- `/auth/otp` : Layar verifikasi 4-digit angka (*placeholder* keamanan berbasis pesan WhatsApp).

Desain *auth* memanfaatkan border zinc yang halus, focus ring berwarna lime khas, dan layout form padat vertikal (`space-y`).
