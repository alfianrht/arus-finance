<?php if (($pagination['totalPages'] ?? 1) > 1): ?>
    <div class="mt-4 flex items-center justify-between gap-3 border-t border-zinc-100 px-4 pt-4">
        <?php if (!empty($pagination['hasPrev'])): ?>
            <a
                href="<?= esc($prevUrl) ?>"
                class="inline-flex items-center gap-1 rounded-full border border-zinc-200 bg-white px-3 py-2 text-xs font-semibold text-zinc-700"
            >
                <span class="material-symbols-rounded text-sm" aria-hidden="true">chevron_left</span>
                <span>Sebelumnya</span>
            </a>
        <?php else: ?>
            <span class="inline-flex items-center gap-1 rounded-full border border-zinc-100 bg-zinc-50 px-3 py-2 text-xs font-semibold text-zinc-300">
                <span class="material-symbols-rounded text-sm" aria-hidden="true">chevron_left</span>
                <span>Sebelumnya</span>
            </span>
        <?php endif; ?>

        <p class="text-xs font-medium text-zinc-500">
            Halaman <?= esc((string) ($pagination['page'] ?? 1)) ?> dari <?= esc((string) ($pagination['totalPages'] ?? 1)) ?>
        </p>

        <?php if (!empty($pagination['hasNext'])): ?>
            <a
                href="<?= esc($nextUrl) ?>"
                class="inline-flex items-center gap-1 rounded-full border border-zinc-200 bg-white px-3 py-2 text-xs font-semibold text-zinc-700"
            >
                <span>Berikutnya</span>
                <span class="material-symbols-rounded text-sm" aria-hidden="true">chevron_right</span>
            </a>
        <?php else: ?>
            <span class="inline-flex items-center gap-1 rounded-full border border-zinc-100 bg-zinc-50 px-3 py-2 text-xs font-semibold text-zinc-300">
                <span>Berikutnya</span>
                <span class="material-symbols-rounded text-sm" aria-hidden="true">chevron_right</span>
            </span>
        <?php endif; ?>
    </div>
<?php endif; ?>
