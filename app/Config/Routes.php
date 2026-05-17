<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Arus::home');
$routes->get('beranda', 'Arus::home');
$routes->get('catat', 'Arus::catat');
$routes->get('catat/masuk', 'Arus::catatMasuk');
$routes->get('catat/keluar', 'Arus::catatKeluar');
$routes->get('catat/keluar/biaya', 'Arus::catatBiaya');
$routes->get('catat/keluar/pindah-dana', 'Arus::catatPindahDana');
$routes->get('rekap', 'Arus::rekap');
$routes->get('pengaturan', 'Arus::pengaturan');
$routes->get('pengaturan/profil-lembaga', 'Arus::profilLembaga');
$routes->get('pengaturan/profil-lembaga/edit', 'Arus::editProfilLembaga');
$routes->get('pengaturan/unit-program', 'Arus::masterUnitProgram');
$routes->get('pengaturan/unit-program/tambah', 'Arus::tambahUnitProgram');
$routes->get('pengaturan/unit-program/(:segment)/edit', 'Arus::editUnitProgram/$1');
$routes->get('pengaturan/kegiatan', 'Arus::masterKegiatan');
$routes->get('pengaturan/kegiatan/tambah', 'Arus::tambahKegiatan');
$routes->get('pengaturan/kegiatan/(:segment)/edit', 'Arus::editKegiatan/$1');
$routes->get('pengaturan/rekening-dompet', 'Arus::masterRekeningDompet');
$routes->get('pengaturan/rekening-dompet/tambah', 'Arus::tambahRekeningDompet');
$routes->get('pengaturan/rekening-dompet/(:segment)/edit', 'Arus::editRekeningDompet/$1');
$routes->get('pengaturan/kategori-biaya', 'Arus::masterKategoriBiaya');
$routes->get('pengaturan/kategori-biaya/tambah', 'Arus::tambahKategoriBiaya');
$routes->get('pengaturan/kategori-biaya/(:segment)/edit', 'Arus::editKategoriBiaya/$1');
$routes->get('pengaturan/pos-laporan', 'Arus::masterPosLaporan');
$routes->get('pengaturan/pos-laporan/tambah', 'Arus::tambahPosLaporan');
$routes->get('pengaturan/pos-laporan/(:segment)/edit', 'Arus::editPosLaporan/$1');
$routes->get('pengaturan/tahun-buku', 'Arus::masterTahunBuku');
$routes->get('pengaturan/tahun-buku/tambah', 'Arus::tambahTahunBuku');
$routes->get('pengaturan/tahun-buku/(:segment)/edit', 'Arus::editTahunBuku/$1');
$routes->get('pengaturan/saldo-awal', 'Arus::masterSaldoAwal');
$routes->get('pengaturan/saldo-awal/tambah', 'Arus::tambahSaldoAwal');
$routes->get('pengaturan/saldo-awal/(:segment)/edit', 'Arus::editSaldoAwal/$1');
$routes->get('transaksi/(:segment)', 'Arus::transaksi/$1');
$routes->get('transaksi/(:segment)/edit', 'Arus::editTransaksi/$1');
$routes->get('rekening/(:segment)', 'Arus::rekening/$1');
$routes->get('unit/(:segment)', 'Arus::unit/$1');
$routes->get('kegiatan/(:segment)', 'Arus::kegiatan/$1');
