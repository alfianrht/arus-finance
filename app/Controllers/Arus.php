<?php

namespace App\Controllers;

/**
 * Compatibility shim for older references.
 *
 * Route aktif aplikasi sudah memakai SettingsController untuk area pengaturan.
 * Class ini dipertahankan sementara agar referensi lama tidak langsung putus.
 */
class Arus extends LegacySettingsController
{
}
