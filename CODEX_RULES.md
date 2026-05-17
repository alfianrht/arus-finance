# Codex Rules

Sebelum coding, baca:
1. README.md
2. docs/PRODUCT_SPEC.md
3. docs/CODEX_RULES.md

## Tech Stack

Gunakan:
- CodeIgniter 4
- Tailwind CSS / SCSS
- JavaScript ringan / Alpine.js jika perlu
- MySQL / MariaDB

Jangan gunakan Vue dulu.

## Scope

Buat sistem sebagai web app CodeIgniter mobile-first.

Menu utama hanya:
1. Beranda
2. Catat
3. Rekap

Jangan menambah menu utama lain tanpa instruksi.

## Jangan Buat Dulu

Jangan membuat:
- ERP
- Jurnal debit-kredit
- Buku besar
- Neraca
- Pajak detail
- PPh 21 otomatis
- Approval bertingkat
- Vendor/client detail
- Freelancer detail
- Kas bon kompleks
- Export SPT
- Multi lembaga dalam satu sistem
- Vue SPA
- Mobile native penuh

## Istilah UI

Gunakan:
- Uang Masuk
- Uang Keluar
- Biaya / Belanja
- Pindah Dana
- Rekap
- Saldo
- Unit / Program
- Kegiatan
- Rekening / Dompet

Jangan gunakan:
- Debit
- Kredit
- Jurnal
- Ledger
- Liability
- Advance
- Withholding
- Non-deductible
- Neraca

## Aturan Paling Penting

Pindah Dana tidak boleh dihitung sebagai biaya.

Contoh benar:
BRI PT → Dana Operasional Cago = Pindah Dana, bukan biaya.

Dana Operasional Cago → Transport = Biaya.

## UX Rules

- Mobile-first.
- Form harus pendek.
- Konteks Aktif selalu terlihat.
- User tidak perlu memilih Unit dan Kegiatan berulang-ulang.
- Kategori tampil sebagai quick chips.
- Sediakan tombol Simpan dan Simpan & Tambah Lagi.
- Tampilkan riwayat transaksi terakhir.
- Unit/program tampil sebagai card.
- Kegiatan tampil sebagai card turunan.
- Pindah Dana harus tetap terlihat di rekap, tapi tidak dihitung sebagai biaya.

## Cara Kerja

Jika ragu:
- pilih solusi paling sederhana
- jangan menambah fitur baru
- jangan menambah menu baru
- jangan membuat istilah akuntansi kompleks
- jangan rombak total layout jika tidak perlu