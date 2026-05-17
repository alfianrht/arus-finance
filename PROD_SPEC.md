# Product Spec — Arus

## Konsep

Arus adalah aplikasi pencatatan uang masuk dan keluar harian berbasis lembaga, unit/program, dan kegiatan.

Struktur:
Profil Lembaga → Unit/Program → Kegiatan → Transaksi

Contoh PT:
- PT Maju Pendidikan Bangsa
  - SIMPAUD
    - Jualan Aplikasi Semesteran
    - Pelatihan SIMPAUD
  - Konsultan Pendidikan
    - Perizinan LSP/LKP
  - KebagusanCode
    - Project Softwarehouse

Contoh Yayasan:
- Yayasan
  - Program Pendidikan
  - Kegiatan Sosial
  - Operasional Yayasan

## Menu Utama

Menu hanya:
1. Beranda
2. Catat
3. Rekap

Pengaturan profil, unit, kegiatan, rekening/dompet, dan kategori boleh ada, tapi jangan menjadi menu utama besar.

## Konteks Aktif

Gunakan konsep Konteks Aktif.

User memilih dulu:
Unit/Program + Kegiatan

Contoh:
Sedang mencatat: Konsultan Pendidikan / Perizinan LKP Tax Session

Setelah konteks aktif dipilih, form transaksi tidak perlu meminta user memilih unit dan kegiatan berulang-ulang.

## Beranda

Beranda menampilkan:
- Nama lembaga
- Saldo total
- Uang masuk bulan ini
- Uang keluar bulan ini
- Laba/surplus sementara
- Konteks aktif
- Tombol cepat Uang Masuk
- Tombol cepat Uang Keluar
- Card ringkasan per unit/program

Unit/program tampil sebagai card, dengan ringkasan:
- Masuk
- Biaya
- Laba/surplus
- Tombol + Masuk
- Tombol - Keluar

## Catat

Halaman Catat menampilkan:
- Konteks aktif
- Tombol Uang Masuk
- Tombol Uang Keluar
- Riwayat transaksi terakhir

### Uang Masuk

Form:
- Nominal
- Masuk ke rekening/dompet
- Tanggal
- Keterangan
- Upload bukti

Unit dan kegiatan mengikuti Konteks Aktif.

### Uang Keluar

Saat klik Uang Keluar, tampilkan pilihan:
1. Biaya / Belanja
2. Pindah Dana

#### Biaya / Belanja

Dipakai ketika uang benar-benar habis untuk kebutuhan kegiatan.

Form:
- Nominal
- Kategori
- Keluar dari rekening/dompet
- Tanggal
- Keterangan
- Upload bukti

Efek:
- Masuk sebagai biaya
- Mengurangi laba/surplus
- Mengurangi saldo rekening/dompet sumber

#### Pindah Dana

Dipakai ketika uang hanya berpindah tempat.

Contoh:
BRI PT → Dana Operasional Cago

Form:
- Nominal
- Dari rekening/dompet
- Ke rekening/dompet
- Tanggal
- Keterangan
- Upload bukti

Efek:
- Saldo asal berkurang
- Saldo tujuan bertambah
- Tidak masuk biaya
- Tidak mengurangi laba/surplus

Catatan UI wajib:
"Pindah Dana tidak dihitung sebagai biaya."

## Rekap

Rekap cukup satu halaman dengan filter:
- Periode
- Unit/Program
- Kegiatan

Tampilkan:
- Uang masuk
- Biaya
- Laba/surplus sementara
- Saldo total
- Ringkasan per unit/program
- Ringkasan per kegiatan
- Saldo per rekening/dompet
- Transaksi terbaru
- Detail pindah dana

Pindah Dana harus terlihat di rekap, tapi tidak dihitung sebagai biaya.

## Rekening/Dompet

Contoh:
- BRI PT
- BCA PT
- Dana Operasional Cago
- Kas Tunai

Jangan tampilkan istilah "rekening pribadi" sebagai istilah utama.
Gunakan "Dana Operasional [Nama PIC]".

## Kategori Default

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

## Design Direction

Arah visual:
- Modern clean finance-lite
- Mobile-first
- Card-based
- White-dominant
- Soft gray background
- Near black text
- Lime/green accent
- Rounded large cards
- Bottom navigation
- Tombol besar dan mudah disentuh

UI terinspirasi dari aplikasi finance modern berbasis card:
- Unit/program sebagai card utama
- Kegiatan sebagai card turunan
- Ada tombol cepat Masuk/Keluar di card
- Ada riwayat transaksi terbaru