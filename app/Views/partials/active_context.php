<?php
$selectorUnits = array_map(
    static function (array $unit): array {
        return [
            'slug' => $unit['slug'],
            'name' => $unit['name'],
            'activities' => array_map(
                static fn(array $activity): array => [
                    'slug' => $activity['slug'],
                    'name' => $activity['name'],
                ],
                $unit['activities']
            ),
        ];
    },
    $units
);

$currentUnitActivities = [];

foreach ($selectorUnits as $selectorUnit) {
    if ($selectorUnit['slug'] === $activeContext['unit_slug']) {
        $currentUnitActivities = $selectorUnit['activities'];
        break;
    }
}
?>

<section class="space-y-3" data-context-shell>
    <div class="rounded-3xl border border-zinc-950 bg-white px-4 py-3 text-zinc-900 shadow-sm" data-context-status>
        <div class="flex items-center justify-between gap-3">
            <div class="min-w-0">
                <p class="text-xs font-medium uppercase tracking-wide text-zinc-500">Konteks Aktif</p>
                <p class="mt-1 text-sm font-semibold text-zinc-950"><?= esc($activeContext['display']) ?></p>
            </div>
            <div class="flex items-center gap-2">
                <a href="<?= esc($activeContext['activity_url']) ?>" class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-zinc-200 bg-white text-zinc-700 shadow-sm">
                    <span class="material-symbols-rounded text-base" aria-hidden="true">open_in_new</span>
                </a>
                <button type="button" class="inline-flex h-9 items-center justify-center gap-1 rounded-full bg-zinc-950 px-3 text-xs font-semibold text-white" data-context-open>
                    <span class="material-symbols-rounded text-sm" aria-hidden="true">edit</span>
                    <span>Edit</span>
                </button>
            </div>
        </div>
    </div>

    <form
        method="get"
        action="<?= esc($activeContext['switch_url']) ?>"
        class="hidden rounded-3xl border border-zinc-950 bg-white p-4 shadow-sm"
        data-context-form
        data-context-switcher
        data-units='<?= esc(json_encode($selectorUnits, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)) ?>'
    >
        <?php foreach ($activeContext['switch_params'] as $key => $value): ?>
            <input type="hidden" name="<?= esc($key) ?>" value="<?= esc((string) $value) ?>">
        <?php endforeach; ?>

        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-zinc-500">Ubah Konteks</p>
                <p class="mt-1 text-sm font-semibold text-zinc-950">Pilih Unit / Program dan Kegiatan yang ingin dipakai.</p>
            </div>
            <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-zinc-100 text-zinc-700" data-context-close>
                <span class="material-symbols-rounded text-base" aria-hidden="true">close</span>
            </button>
        </div>

        <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-2">
            <div class="space-y-2">
                <label for="context-unit" class="text-sm font-medium text-zinc-700">Unit / Program</label>
                <select
                    id="context-unit"
                    name="unit"
                    data-context-unit
                    class="h-12 w-full rounded-2xl border border-zinc-200 bg-white px-4 text-sm text-zinc-950 focus:ring-2 focus:ring-lime-400"
                >
                    <?php foreach ($selectorUnits as $selectorUnit): ?>
                        <option value="<?= esc($selectorUnit['slug']) ?>" <?= $selectorUnit['slug'] === $activeContext['unit_slug'] ? 'selected' : '' ?>>
                            <?= esc($selectorUnit['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="space-y-2">
                <label for="context-activity" class="text-sm font-medium text-zinc-700">Kegiatan</label>
                <select
                    id="context-activity"
                    name="kegiatan"
                    data-context-activity
                    class="h-12 w-full rounded-2xl border border-zinc-200 bg-white px-4 text-sm text-zinc-950 focus:ring-2 focus:ring-lime-400"
                >
                    <?php foreach ($currentUnitActivities as $activity): ?>
                        <option value="<?= esc($activity['slug']) ?>" <?= $activity['slug'] === $activeContext['activity_slug'] ? 'selected' : '' ?>>
                            <?= esc($activity['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="mt-4 flex items-center justify-between gap-3">
            <p class="text-xs text-zinc-500">Setelah disimpan, tombol aksi akan mengikuti konteks ini.</p>
            <div class="flex items-center gap-2">
                <button type="button" class="inline-flex h-10 items-center justify-center rounded-full border border-zinc-200 px-4 text-sm font-medium text-zinc-700" data-context-close>
                    Batal
                </button>
                <button type="submit" class="inline-flex h-10 items-center justify-center gap-2 rounded-full bg-zinc-950 px-4 text-sm font-semibold text-white">
                    <span class="material-symbols-rounded text-base" aria-hidden="true">check</span>
                    <span>Pakai Konteks</span>
                </button>
            </div>
        </div>
    </form>
</section>

<script>
    document.querySelectorAll('[data-context-switcher]').forEach((form) => {
        const shell = form.closest('[data-context-shell]');
        const statusCard = shell ? shell.querySelector('[data-context-status]') : null;
        const openButton = shell ? shell.querySelector('[data-context-open]') : null;
        const closeButtons = shell ? shell.querySelectorAll('[data-context-close]') : [];
        const units = JSON.parse(form.dataset.units || '[]');
        const unitSelect = form.querySelector('[data-context-unit]');
        const activitySelect = form.querySelector('[data-context-activity]');

        if (!unitSelect || !activitySelect) {
            return;
        }

        const showEdit = () => {
            if (statusCard) {
                statusCard.classList.add('hidden');
            }

            form.classList.remove('hidden');
        };

        const showStatus = () => {
            if (statusCard) {
                statusCard.classList.remove('hidden');
            }

            form.classList.add('hidden');
        };

        const renderActivities = (selectedUnit, preferredActivity) => {
            const currentUnit = units.find((unit) => unit.slug === selectedUnit) || units[0];
            const activities = currentUnit ? currentUnit.activities : [];

            activitySelect.innerHTML = '';

            activities.forEach((activity, index) => {
                const option = document.createElement('option');
                option.value = activity.slug;
                option.textContent = activity.name;

                if ((preferredActivity && preferredActivity === activity.slug) || (!preferredActivity && index === 0)) {
                    option.selected = true;
                }

                activitySelect.appendChild(option);
            });
        };

        if (openButton) {
            openButton.addEventListener('click', showEdit);
        }

        closeButtons.forEach((button) => {
            button.addEventListener('click', showStatus);
        });

        unitSelect.addEventListener('change', () => {
            renderActivities(unitSelect.value, null);
        });
    });
</script>
