# Design Guide — Arus

## Tujuan Dokumen

Dokumen ini menjadi acuan visual dan UX untuk seluruh implementasi antarmuka Arus agar:

- konsisten dengan arah produk `finance-lite`
- tetap mobile-first
- tetap nyaman saat dibuka di desktop
- tidak keluar dari karakter visual referensi
- mudah diterjemahkan ke utilitas standar Tailwind CSS

Dokumen ini bukan sekadar inspirasi visual. Isinya harus dipakai sebagai batas desain agar halaman baru, komponen baru, dan revisi UI tetap satu bahasa.

## Sumber Arah Visual

Referensi yang dipakai menunjukkan beberapa karakter yang sangat jelas:

- permukaan putih yang dominan
- abu-abu muda sebagai latar luar
- aksen lime terang yang tajam dan modern
- teks sangat gelap, bukan hitam murni yang kasar
- sudut besar dan lembut
- kartu besar sebagai unit interaksi utama
- tombol aksi berbentuk pil, tebal, dan mudah disentuh
- hierarki kuat pada nominal uang
- komposisi bersih, tidak ramai, tidak terasa seperti dashboard enterprise

Arus harus mengambil DNA visual tersebut, tetapi diterapkan ke konteks produk pencatatan keuangan lembaga:

- tetap sederhana
- tetap operasional
- tetap cepat dipakai
- tetap terasa modern
- tidak berubah menjadi aplikasi bank konsumen

## Visual Thesis

Arus harus terasa seperti aplikasi pencatatan keuangan yang:

- ringan saat dibuka
- cepat dipahami dalam 3 detik
- fokus pada nominal, saldo, dan aksi
- bersih, rapi, dan modern
- profesional untuk PT/Yayasan, tetapi tidak kaku

Kalimat kerja desain:

`white-first, soft-gray support, bold lime accent, large rounded surfaces, strong money hierarchy`

## Interaction Thesis

Interaksi utama Arus harus mengikuti prinsip berikut:

- user selalu tahu sedang berada di konteks unit/kegiatan apa
- aksi utama selalu dekat dengan jempol
- nominal adalah elemen paling menonjol saat mencatat
- ringkasan harus langsung terlihat tanpa membaca banyak teks
- perbedaan antara `Biaya / Belanja` dan `Pindah Dana` harus sangat jelas

## Design Principles

### 1. Mobile-first nyata

Default desain dibuat untuk lebar kecil dulu, bukan versi desktop yang dikecilkan.

Artinya:

- semua halaman harus nyaman di lebar `360px` sampai `430px`
- navigasi utama harus mengutamakan jangkauan jempol
- form harus pendek, bertahap, dan fokus pada satu tugas
- informasi diringkas menjadi blok yang mudah dipindai

### 2. Card is interaction

Kartu dipakai hanya ketika kartu itu memang area interaksi atau area rangkuman penting.

Dipakai untuk:

- saldo utama
- unit/program
- kegiatan
- rekening/dompet
- transaksi terbaru
- pilihan mode input

Jangan dipakai berlebihan untuk:

- pembungkus setiap baris kecil
- setiap filter kecil
- setiap label sederhana

### 3. Money first

Nominal, saldo, total masuk, total keluar, dan surplus harus menjadi fokus visual pertama.

Nama, deskripsi, dan metadata hanya mendukung pembacaan nilai tersebut.

### 4. One accent color

Aksen utama hanya satu keluarga warna: `lime`.

Warna lain hanya untuk status:

- hijau untuk positif
- merah/rose untuk negatif
- biru sangat terbatas hanya jika benar-benar perlu untuk info netral

### 5. Calm surface hierarchy

Hierarki dibangun dengan:

- ukuran teks
- berat font
- warna teks
- jarak
- bentuk permukaan

Bukan dengan:

- border tebal di mana-mana
- shadow berat
- gradient berlebihan
- banyak warna bersaing

## Tailwind Color System

Gunakan warna standar Tailwind CSS. Hindari hex custom kecuali benar-benar dibutuhkan nanti untuk branding lanjutan.

### Palet Utama

#### 1. Background luar aplikasi

Untuk area pembungkus keseluruhan, preview shell, atau desktop canvas:

- `bg-zinc-200`
- alternatif lebih lembut: `bg-stone-200`

Gunakan ini untuk menciptakan rasa seperti perangkat berada di atas bidang abu muda, sesuai referensi.

#### 2. Surface utama

Untuk permukaan halaman:

- `bg-white`
- alternatif lembut untuk blok sekunder: `bg-zinc-50`

#### 3. Text utama

Untuk judul, nominal, CTA dark, dan label inti:

- `text-zinc-950`

Untuk body text biasa:

- `text-zinc-800`

Untuk text pendukung:

- `text-zinc-500`
- `text-zinc-400` hanya untuk placeholder atau metadata yang benar-benar sekunder

#### 4. Aksen brand

Warna aksen utama:

- `lime-400` untuk highlight utama
- `lime-300` untuk surface highlight lembut
- `lime-500` untuk state aktif yang butuh kontras lebih tegas

Rekomendasi pemakaian:

- tombol primer terang: `bg-lime-400 text-zinc-950`
- chip aktif: `bg-lime-400 text-zinc-950`
- highlight angka atau indikator aktif: `text-lime-600` atau `bg-lime-100`
- accent divider kecil atau caret: `bg-lime-400`

#### 5. Surface gelap

Untuk bottom nav, tombol utama gelap, promo strip, atau panel aksi kuat:

- `bg-zinc-950`
- text di atasnya: `text-white`

#### 6. Status warna

Uang masuk / positif:

- text: `text-emerald-600`
- badge/background lembut: `bg-emerald-50`

Uang keluar / negatif:

- text: `text-rose-500`
- badge/background lembut: `bg-rose-50`

Pindah Dana:

- netral, bukan merah
- gunakan `text-zinc-700` atau `text-sky-700` secara hemat
- badge lembut: `bg-zinc-100` atau `bg-sky-50`

Warning ringan:

- `text-amber-600`
- `bg-amber-50`

### Warna yang Harus Dihindari

Jangan menjadikan warna berikut sebagai aksen dominan:

- `purple`
- `indigo`
- `pink`
- `cyan`
- gradient multiwarna

Alasannya: akan menggeser karakter Arus keluar dari referensi yang tenang, modern, dan finance-lite.

## Surface, Border, Radius, Shadow

### Radius

Karakter referensi memakai sudut besar dan lembut. Maka gunakan:

- kartu utama besar: `rounded-3xl`
- kartu sekunder: `rounded-2xl`
- tombol pil utama: `rounded-full`
- input field: `rounded-2xl`
- chip kategori: `rounded-full`

Hindari:

- `rounded-md` untuk elemen utama
- sudut tajam

### Border

Gunakan border yang sangat halus:

- `border border-zinc-100`
- atau `border border-white/60` jika surface di atas bidang gelap

Jangan pakai border tebal kecuali untuk state terpilih.

State terpilih bisa gunakan:

- `ring-2 ring-lime-400`
- atau `border-lime-400`

### Shadow

Shadow harus nyaris tidak terasa.

Rekomendasi:

- `shadow-sm`
- `shadow-[0_8px_30px_rgba(0,0,0,0.04)]` hanya jika perlu satu kartu hero

Jangan gunakan shadow berat ala dashboard template.

## Typography

Gunakan sistem tipografi yang sederhana dan tegas. Jika tidak ada font khusus, pakai `font-sans` default Tailwind.

Jika nanti ingin sedikit lebih premium tanpa keluar dari konteks, bisa dipertimbangkan `Inter`, tetapi default dokumen ini tetap aman dengan stack Tailwind standar.

### Prinsip Tipografi

- judul pendek
- angka besar
- body copy singkat
- metadata tipis
- jarang memakai huruf kapital penuh

### Skala Tipografi Rekomendasi

#### App title / page title

- `text-2xl font-semibold tracking-tight text-zinc-950`

#### Section title

- `text-base font-semibold text-zinc-950`

#### Card title

- `text-sm font-medium text-zinc-700`

#### Hero balance / nominal utama

- `text-4xl font-semibold tracking-tight text-zinc-950`
- pada layar lebih besar boleh naik ke `sm:text-5xl`

#### Nilai ringkasan menengah

- `text-xl font-semibold text-zinc-950`

#### Body standard

- `text-sm text-zinc-700`

#### Caption / meta

- `text-xs text-zinc-500`

### Angka dan Nominal

Untuk area nominal dan laporan, prioritaskan keterbacaan angka:

- gunakan `tabular-nums` pada angka uang
- hindari warna abu terlalu pucat untuk nominal penting
- pemisah ribuan harus konsisten

## Spacing System

Gunakan ritme yang lapang. Referensi terlihat premium justru karena tidak padat.

### Padding halaman mobile

- horizontal utama: `px-4`
- pada perangkat agak besar: `sm:px-5`

### Gap antar section

- `space-y-4` untuk section rapat
- `space-y-3` atau `space-y-6` untuk halaman beranda

### Padding kartu

- kartu utama: `p-5`
- kartu sekunder: `p-4`
- chip kecil: `px-3 py-1.5`

### Tinggi minimum sentuh

- tombol dan item interaktif utama minimal `min-h-11`
- ideal `h-12` atau `h-14` untuk CTA penting

## Layout System

### Mobile Layout Baseline

Ini adalah baseline utama Arus.

### App shell

- halaman dibungkus container tunggal dengan `min-h-screen`
- background halaman utama `bg-zinc-50` atau `bg-white`
- konten utama center-safe dengan `max-w-md mx-auto`
- ada ruang bawah untuk bottom navigation

Rekomendasi struktur:

```html
<body class="bg-zinc-200">
  <div class="mx-auto min-h-screen max-w-md bg-zinc-50">
    <main class="px-4 pt-4 pb-28">
      ...
    </main>
    <nav class="fixed inset-x-0 bottom-0 mx-auto max-w-md px-4 pb-4">
      ...
    </nav>
  </div>
</body>
```

### Safe area

Karena target bisa WebView APK:

- bottom nav harus mempertimbangkan `env(safe-area-inset-bottom)`
- tombol fixed bottom juga harus punya tambahan padding bawah

Rekomendasi:

- `pb-[calc(1rem+env(safe-area-inset-bottom))]`

### Header mobile

Header harus ringkas:

- kiri: judul halaman atau nama lembaga
- kanan: tombol profil, pengaturan kecil, atau switch konteks

Header tidak boleh terlalu tinggi.

Target:

- tinggi visual sekitar `56px` sampai `64px`

### Desktop Adaptation

Desktop tidak boleh diubah menjadi dashboard enterprise yang penuh panel dan sidebar tebal. DNA mobile harus tetap terasa.

### Prinsip desktop

- pertahankan kartu besar dan ruang kosong
- pertahankan fokus satu kolom utama
- jika menambah kolom, maksimal dua kolom utama
- jangan mengubah bottom-nav logic menjadi menu admin kompleks

### Breakpoint Strategy

#### `< 640px`

Mode utama:

- satu kolom
- bottom navigation fixed
- semua aksi utama dekat bawah

#### `sm >= 640px`

- konten tetap satu kolom
- jarak bisa sedikit diperbesar
- kartu hero boleh lebih lega

#### `md >= 768px`

- halaman bisa memakai `max-w-3xl`
- beberapa section seperti ringkasan dapat jadi dua kolom
- form tetap fokus satu kolom untuk pencatatan

#### `lg >= 1024px`

- boleh ada dua area: konten utama + panel ringkasan
- bottom nav bisa tetap terlihat sebagai floating dock, atau berpindah ke side rail tipis
- tetap hindari sidebar admin tradisional yang berat

### Desktop patterns yang direkomendasikan

#### Opsi A — centered application

Cocok untuk fase awal:

- seluruh app tetap `max-w-md` atau `max-w-lg`
- terasa seperti aplikasi mobile yang rapi di desktop

#### Opsi B — expanded workspace

Untuk halaman rekap:

- kiri: filter + ringkasan
- kanan: daftar transaksi / detail rekap

Tetap gunakan:

- kartu besar
- latar ringan
- jarak luas

Jangan gunakan:

- tabel rapat sebagai tampilan default mobile
- layout empat kolom kecil

## Navigation System

### Menu Utama

Sesuai product scope, hanya ada:

- Beranda
- Catat
- Rekap

### Mobile navigation

Gunakan bottom navigation berbentuk dock gelap seperti referensi.

Karakter:

- `bg-zinc-950`
- `rounded-full`
- item icon + label pendek atau icon saja dengan tooltip/aria-label
- tinggi nyaman disentuh
- mengapung sedikit di atas bawah layar

Rekomendasi kelas:

- wrapper: `rounded-full bg-zinc-950 p-2 shadow-lg`
- item aktif: `bg-white text-zinc-950`
- item nonaktif: `text-zinc-400`

Jika memakai label:

- label harus singkat
- jangan lebih dari 1 kata

### Desktop navigation

Pada desktop, ada dua opsi aman:

- pertahankan floating bottom dock untuk identitas visual
- ubah menjadi side rail tipis kiri dengan bentuk pil dan warna yang sama

Jangan pindah ke top navbar web klasik jika tidak diperlukan.

## Core Components

### 1. Hero Balance Card

Komponen ini adalah pusat beranda.

### Fungsi

- menampilkan saldo total
- menampilkan konteks aktif atau lembaga aktif
- menonjolkan kondisi keuangan saat ini

### Karakter visual

- surface putih atau sangat terang
- angka sangat besar
- subtitle kecil
- ada aksen lime kecil, bukan kartu penuh lime

### Struktur isi

- label konteks kecil
- nominal saldo besar
- info pendukung seperti `Masuk bulan ini`, `Keluar bulan ini`, `Surplus`
- quick action row di bawahnya

### Tailwind guidance

- `rounded-3xl bg-white p-5 shadow-sm`
- nominal: `text-4xl font-semibold tracking-tight tabular-nums`

### 2. Quick Action Buttons

Digunakan untuk:

- Uang Masuk
- Uang Keluar

### Karakter visual

- besar
- bentuk pil
- mudah disentuh
- terlihat sebagai aksi utama

### Rekomendasi

Tombol terang:

- `bg-white text-zinc-950 border border-zinc-100`

Tombol primer:

- `bg-lime-400 text-zinc-950`

Tombol gelap:

- `bg-zinc-950 text-white`

### Aturan

- `Uang Masuk` dan `Uang Keluar` harus selalu terlihat jelas
- jangan sembunyikan di menu overflow

### 3. Active Context Banner

Karena Arus memakai konsep `Konteks Aktif`, komponen ini wajib kuat.

### Fungsi

- menunjukkan unit/program dan kegiatan yang sedang aktif
- mengurangi pengulangan input
- memberi rasa aman pada user sebelum mencatat transaksi

### Karakter visual

- bukan alert merah
- terasa seperti info aktif
- bisa berupa kartu tipis atau chip besar

### Style

- `rounded-2xl bg-lime-50 text-zinc-900`
- title kecil: `text-xs font-medium text-zinc-500`
- isi konteks: `text-sm font-semibold text-zinc-950`

### Aturan

- selalu terlihat di halaman `Catat`
- sebaiknya terlihat juga di `Beranda`
- pada `Rekap`, konteks boleh berubah menjadi filter aktif

### 4. Unit / Program Card

Ini komponen inti yang mewakili struktur produk.

### Isi minimum

- nama unit/program
- ringkasan masuk
- ringkasan biaya
- surplus
- tombol `+ Masuk`
- tombol `- Keluar`

### Karakter visual

- kartu besar
- tidak ramai
- angka lebih penting dari deskripsi

### Layout mobile

- satu kartu per baris
- tombol aksi di bagian bawah kartu

### Layout desktop

- bisa dua kolom maksimum
- tetap hindari grid kecil yang padat

### 5. Kegiatan Card

Kegiatan adalah turunan dari unit/program.

Gunakan visual yang lebih ringan dari kartu unit/program agar hirarki terbaca.

### Style

- `rounded-2xl bg-zinc-50 p-4 border border-zinc-100`

### Aturan

- tampil di bawah unit/program
- jangan lebih dominan dari kartu unit/program

### 6. Transaction Row / Transaction Card

Digunakan untuk:

- transaksi terbaru
- histori di halaman Catat
- daftar pada Rekap

### Informasi minimum

- ikon/avatar kategori ringan
- nama transaksi atau keterangan
- tanggal/jam
- nominal di kanan
- warna nominal sesuai tipe

### Nominal rule

- masuk: `+` dan `text-emerald-600`
- keluar: `-` dan `text-rose-500`
- pindah dana: netral, tidak merah

### Bentuk

Di mobile sebaiknya berupa list row dengan pemisah halus, bukan selalu kartu besar.

Contoh:

- wrapper list: `rounded-3xl bg-white p-3`
- row: `flex items-center gap-3 py-3`

### 7. Account / Wallet Card

Dipakai untuk pilihan `Rekening / Dompet`.

### Karakter visual

- kompak
- mudah dipindai
- nama account jelas
- saldo terlihat

### Aturan nama

Gunakan istilah:

- `Rekening / Dompet`
- `Dana Operasional [Nama PIC]`

Jangan gunakan:

- `rekening pribadi` sebagai label utama

### 8. Category Chips

Dipakai pada `Biaya / Belanja`.

### Karakter visual

- ringan
- dapat discan cepat
- aktif jelas

### Style default

- `rounded-full bg-zinc-100 px-3 py-2 text-sm text-zinc-700`

### Style aktif

- `bg-lime-400 text-zinc-950`

### Aturan

- tampil sebagai quick chips
- wrap ke baris baru bila perlu
- jangan terlalu kecil

### 9. Input Field

Input Arus harus terasa lembut dan tidak teknis.

### Style

- `h-12 rounded-2xl border border-zinc-100 bg-white px-4 text-sm text-zinc-950`
- focus: `ring-2 ring-lime-400 outline-none`

### Placeholder

- `text-zinc-400`

### Aturan

- label singkat
- keterangan bantu bila perlu, maksimal 1 baris
- hindari placeholder sebagai satu-satunya label

### 10. Amount Input

Nominal adalah fokus utama saat input transaksi.

### Mobile behavior

Tampilan nominal harus besar, di tengah atau dekat atas, dengan rasa fokus seperti referensi.

### Style

- `text-4xl font-semibold tracking-tight text-zinc-950 tabular-nums`

### Numeric keypad

Jika ingin mengikuti referensi lebih dekat:

- boleh dibuat keypad custom di mobile untuk pengalaman cepat
- angka `0-9`, `000`, hapus
- tombol submit besar di bawah

Namun untuk efisiensi implementasi web:

- fallback aman adalah `inputmode="numeric"` atau `decimal`
- keyboard native tetap diterima

Keputusan desain:

- secara visual, layar input nominal tetap harus terasa fokus dan besar
- secara teknis, keypad custom adalah enhancement, bukan kewajiban fase awal

## Page-by-Page Direction

### Beranda

### Tujuan

Memberi jawaban cepat atas pertanyaan:

- saldo sekarang berapa
- bulan ini masuk berapa
- bulan ini keluar berapa
- konteks aktif apa
- unit mana yang butuh perhatian

### Susunan mobile

1. Header lembaga
2. Hero balance card
3. Quick actions `Uang Masuk` dan `Uang Keluar`
4. Konteks aktif
5. Ringkasan unit/program
6. Transaksi terbaru

### Aturan

- hero balance harus muncul sebelum daftar lain
- unit/program tidak boleh tenggelam di bawah elemen dekoratif
- transaksi terbaru tampil ringkas, 3 sampai 6 item cukup

### Desktop

Beranda bisa menjadi dua kolom:

- kiri: saldo utama + quick actions + konteks
- kanan: unit/program summary atau transaksi terbaru

Tetap utamakan ruang kosong dan kartu besar.

### Catat

### Tujuan

Memudahkan user mencatat transaksi secepat mungkin.

### Susunan mobile

1. Header singkat
2. Konteks aktif yang selalu terlihat
3. Pilihan aksi: `Uang Masuk` / `Uang Keluar`
4. Jika `Uang Keluar`, tampil pilihan `Biaya / Belanja` atau `Pindah Dana`
5. Area nominal besar
6. Form pendukung
7. Tombol `Simpan` dan `Simpan & Tambah Lagi`
8. Riwayat transaksi terakhir

### Aturan UX

- user tidak memilih unit dan kegiatan berulang
- perbedaan `Biaya / Belanja` vs `Pindah Dana` harus dijelaskan secara visual dan teks singkat
- beri catatan jelas:

`Pindah Dana tidak dihitung sebagai biaya`

### Desktop

Tetap pertahankan fokus form.

Gunakan:

- satu kolom utama untuk form
- satu kolom pendamping kecil untuk riwayat atau bantuan konteks

Jangan ubah menjadi form horizontal yang panjang dan melelahkan.

### Rekap

### Tujuan

Menyediakan ringkasan dan detail tanpa terasa seperti software akuntansi yang rumit.

### Susunan mobile

1. Header
2. Filter periode
3. Filter unit/program
4. Filter kegiatan
5. Ringkasan angka utama
6. Ringkasan per unit/program
7. Ringkasan per kegiatan
8. Saldo rekening/dompet
9. Transaksi terbaru
10. Detail pindah dana

### Rules

- `Pindah Dana` harus terlihat
- `Pindah Dana` tidak dihitung ke biaya
- pada mobile, gunakan stack cards dan list
- tabel hanya opsional di desktop

### Desktop

Halaman ini paling cocok untuk perluasan dua kolom:

- kiri: filter dan summary cards
- kanan: daftar/detail

Namun styling tetap harus mengikuti permukaan lembut dan angka besar, bukan tabel enterprise kaku.

## State System

### Default

- surface putih
- teks gelap
- border halus

### Active / Selected

- `bg-lime-400`
- atau `ring-2 ring-lime-400`

### Hover

Desktop hover harus halus:

- sedikit perubahan background
- sedikit penguatan border

Jangan mengandalkan hover untuk fungsi inti karena mobile-first.

### Disabled

- `opacity-50`
- tetap terbaca
- jangan terlalu pucat sampai ambigu

### Error

- text bantu `text-rose-500`
- border `border-rose-300`
- jangan seluruh halaman menjadi merah

### Success feedback

- gunakan `emerald`
- toast atau inline message tetap singkat

## Motion Direction

Motion harus terasa modern tetapi singkat.

### Prinsip

- cepat
- halus
- tidak memantul berlebihan
- membantu orientasi

### Rekomendasi durasi

- tap feedback: `100ms - 150ms`
- drawer/sheet/modal: `180ms - 240ms`
- section reveal: `200ms - 300ms`

### Motion yang direkomendasikan

- bottom nav active state halus
- sheet form naik dari bawah
- angka nominal atau summary fade/slide ringan saat berubah
- chip selection dengan transisi lembut

### Hindari

- animasi spring berlebihan
- efek bounce seperti game
- paralaks dekoratif

## Content Rules

Bahasa UI harus mengikuti aturan produk:

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
- Neraca
- Liability

## Accessibility Rules

Walau visualnya lembut, aksesibilitas tetap wajib dijaga.

### Kontras

- teks utama di atas putih harus cukup kontras
- jangan gunakan lime terang untuk teks kecil di atas putih

### Touch target

- minimal area sentuh `44x44px`

### Form

- label harus nyata
- error message harus spesifik
- focus state harus terlihat jelas

### Numeric readability

- angka besar harus tidak terpotong
- gunakan `tabular-nums` untuk daftar nominal

## Do / Don't

### Do

- gunakan putih sebagai surface utama
- gunakan `zinc`/`stone` sebagai penyangga netral
- gunakan `lime` sebagai aksen inti
- gunakan kartu besar dan lembut
- buat nominal sebagai hero visual
- pertahankan bottom navigation yang sederhana
- buat form ringkas dan fokus
- jaga agar konteks aktif selalu jelas

### Don't

- jangan membuat UI terasa seperti admin panel
- jangan memakai banyak warna aksen
- jangan memenuhi layar dengan kartu kecil-kecil
- jangan menambahkan statistik dekoratif yang tidak membantu
- jangan membuat tabel kompleks sebagai default mobile
- jangan menyamakan `Pindah Dana` dengan biaya secara visual
- jangan memakai border tebal di semua komponen

## Tailwind Class Recipes

Bagian ini bukan komponen final, tetapi baseline cepat agar implementasi tetap konsisten.

### App page wrapper

```html
<div class="mx-auto min-h-screen max-w-md bg-zinc-50 text-zinc-950 md:max-w-3xl">
  ...
</div>
```

### Primary hero card

```html
<section class="rounded-3xl bg-white p-5 shadow-sm">
  ...
</section>
```

### Primary dark CTA

```html
<button class="inline-flex h-14 items-center justify-center rounded-full bg-zinc-950 px-6 text-sm font-medium text-white">
  Simpan
</button>
```

### Bright accent CTA

```html
<button class="inline-flex h-14 items-center justify-center rounded-full bg-lime-400 px-6 text-sm font-medium text-zinc-950">
  Uang Masuk
</button>
```

### Secondary pill button

```html
<button class="inline-flex h-12 items-center justify-center rounded-full border border-zinc-100 bg-white px-5 text-sm font-medium text-zinc-900">
  Uang Keluar
</button>
```

### Input field

```html
<input class="h-12 w-full rounded-2xl border border-zinc-100 bg-white px-4 text-sm text-zinc-950 outline-none ring-0 placeholder:text-zinc-400 focus:ring-2 focus:ring-lime-400" />
```

### Active chip

```html
<button class="rounded-full bg-lime-400 px-3 py-2 text-sm font-medium text-zinc-950">
  Transport
</button>
```

### Inactive chip

```html
<button class="rounded-full bg-zinc-100 px-3 py-2 text-sm font-medium text-zinc-700">
  Konsumsi
</button>
```

### Floating bottom nav

```html
<nav class="rounded-full bg-zinc-950 p-2 shadow-lg">
  ...
</nav>
```

## Final Guardrail

Jika nanti ada keputusan UI yang meragukan, pakai urutan keputusan berikut:

1. pilih versi yang paling sederhana
2. pilih versi yang paling cepat dipindai di mobile
3. pilih versi yang paling menonjolkan nominal dan aksi
4. pilih versi yang paling konsisten dengan putih + zinc + lime
5. pilih versi yang tidak mengubah Arus menjadi dashboard enterprise

Kalau sebuah elemen terasa terlalu ramai, terlalu berwarna, terlalu banyak kartu kecil, atau terlalu mirip template admin, berarti elemen itu keluar dari konteks desain Arus dan harus disederhanakan.
