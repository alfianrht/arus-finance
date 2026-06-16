<?php
/**
 * Modal Konfirmasi Hapus — Reusable Partial
 *
 * Cara pakai:
 * 1. Di halaman yang butuh, include partial ini: <?= view('partials/confirm_delete_modal') ?>
 * 2. Pada tombol hapus, tambahkan atribut data:
 *    <button
 *        onclick="openDeleteModal('<?= site_url('pengaturan/unit/slug/hapus') ?>', 'Nama Item', '<?= csrf_hash() ?>')"
 *    >Hapus</button>
 */
?>
<div id="deleteModal" class="fixed inset-0 z-50 flex items-start justify-center bg-zinc-950/40 p-4 pt-6 backdrop-blur-sm transition-opacity duration-200 opacity-0 pointer-events-none sm:pt-8" style="display:none">
    <div class="w-full max-w-md transform rounded-3xl bg-white p-6 shadow-xl transition-transform duration-200 translate-y-4 sm:translate-y-0">
        <div class="flex items-center gap-3">
            <span class="flex h-10 w-10 items-center justify-center rounded-full bg-rose-100">
                <span class="material-symbols-rounded text-rose-600 text-xl">delete</span>
            </span>
            <h3 class="text-lg font-bold text-zinc-950">Hapus Data</h3>
        </div>
        <p id="deleteModalMessage" class="mt-3 text-sm text-zinc-600">Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.</p>
        <form id="deleteModalForm" method="post" class="mt-5 flex items-center justify-end gap-3">
            <input type="hidden" name="csrf_test_name" id="deleteModalCsrf" value="">
            <button type="button" onclick="closeDeleteModal()" class="inline-flex h-11 items-center justify-center rounded-full border border-zinc-200 px-5 text-sm font-semibold text-zinc-700">Batal</button>
            <button type="submit" class="inline-flex h-11 items-center justify-center rounded-full bg-rose-600 px-5 text-sm font-semibold text-white">Ya, Hapus</button>
        </form>
    </div>
</div>

<script>
function openDeleteModal(action, itemName, csrfHash) {
    const modal = document.getElementById('deleteModal');
    const form = document.getElementById('deleteModalForm');
    const message = document.getElementById('deleteModalMessage');
    const csrfInput = document.getElementById('deleteModalCsrf');

    form.action = action;
    csrfInput.name = '<?= csrf_token() ?>';
    csrfInput.value = csrfHash;
    message.textContent = 'Apakah Anda yakin ingin menghapus «' + itemName + '»? Data yang dihapus tidak akan muncul di daftar lagi.';

    modal.style.display = 'flex';
    requestAnimationFrame(() => {
        modal.classList.remove('opacity-0', 'pointer-events-none');
        modal.querySelector('div').classList.remove('translate-y-4');
    });
}

function closeDeleteModal() {
    const modal = document.getElementById('deleteModal');
    modal.classList.add('opacity-0', 'pointer-events-none');
    modal.querySelector('div').classList.add('translate-y-4');
    setTimeout(() => { modal.style.display = 'none'; }, 200);
}

// Tutup modal saat klik backdrop
document.getElementById('deleteModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeDeleteModal();
});
</script>
