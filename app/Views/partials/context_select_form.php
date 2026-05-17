<form method="get" action="<?= esc($activeContext['switch_url']) ?>" class="rounded-3xl border border-zinc-200 bg-white p-4 shadow-sm">
    <?php foreach ($activeContext['switch_params'] as $key => $value): ?>
        <input type="hidden" name="<?= esc($key) ?>" value="<?= esc((string) $value) ?>">
    <?php endforeach; ?>

    <div class="flex items-start justify-between gap-3">
        <div>
            <p class="text-xs font-medium uppercase tracking-wide text-zinc-500">Konteks Pencatatan</p>
            <p class="mt-1 text-sm font-semibold text-zinc-950">Pilih Unit / Program dan Kegiatan sebelum lanjut mencatat.</p>
        </div>
        <span class="rounded-full bg-zinc-100 px-3 py-2 text-xs font-medium text-zinc-700">Form konteks</span>
    </div>

    <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-2">
        <div class="space-y-2">
            <label class="text-sm font-medium text-zinc-700">Unit / Program</label>
            <select name="unit" class="h-12 w-full rounded-2xl border border-zinc-100 bg-white px-4 text-sm text-zinc-950 focus:ring-2 focus:ring-lime-400">
                <?php foreach ($units as $unit): ?>
                    <option value="<?= esc($unit['slug']) ?>" <?= $unit['slug'] === $activeContext['unit_slug'] ? 'selected' : '' ?>>
                        <?= esc($unit['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="space-y-2">
            <label class="text-sm font-medium text-zinc-700">Kegiatan</label>
            <select name="kegiatan" class="h-12 w-full rounded-2xl border border-zinc-100 bg-white px-4 text-sm text-zinc-950 focus:ring-2 focus:ring-lime-400">
                <?php foreach ($activitySummaries as $activity): ?>
                    <option value="<?= esc($activity['slug']) ?>" <?= $activity['slug'] === $activeContext['activity_slug'] ? 'selected' : '' ?>>
                        <?= esc($activity['name']) ?> · <?= esc($activity['unit_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="mt-4 flex items-center justify-between gap-3">
        <p class="text-xs text-zinc-500">Nilai default form akan mengikuti pilihan ini.</p>
        <button type="submit" class="inline-flex h-10 items-center justify-center gap-2 rounded-full bg-zinc-950 px-4 text-sm font-semibold text-white">
            <span class="material-symbols-rounded text-base" aria-hidden="true">check</span>
            <span>Pakai Konteks</span>
        </button>
    </div>
</form>
