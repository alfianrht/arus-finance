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
$selectorAccounts = $activeContext['account_options'] ?? [];

$currentUnitActivities = [];

foreach ($selectorUnits as $selectorUnit) {
    if ($selectorUnit['slug'] === $activeContext['unit_slug']) {
        $currentUnitActivities = $selectorUnit['activities'];
        break;
    }
}
?>

<section class="space-y-3" data-context-shell>
    <div class="rounded-3xl bg-white px-4 py-3 text-zinc-900 shadow-sm" data-context-status>
        <div class="flex items-center justify-between gap-3">
            <div class="min-w-0">
                <p class="text-xs font-medium uppercase tracking-wide text-zinc-500">Konteks Aktif</p>
                <div class="mt-2 flex flex-wrap gap-2">
                    <span class="inline-flex items-center rounded-full border border-zinc-950 bg-white px-3 py-1.5 text-xs font-semibold text-zinc-900"><?= esc($activeContext['unit_name']) ?></span>
                    <span class="inline-flex items-center rounded-full border border-zinc-950 bg-white px-3 py-1.5 text-xs font-semibold text-zinc-900"><?= esc($activeContext['activity_name']) ?></span>
                    <span class="inline-flex items-center rounded-full border border-zinc-950 bg-white px-3 py-1.5 text-xs font-semibold text-zinc-900"><?= esc($activeContext['account_name']) ?></span>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="<?= esc($activeContext['activity_url']) ?>" class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-zinc-200 bg-white text-zinc-700 shadow-sm">
                    <span class="material-symbols-rounded text-base" aria-hidden="true">open_in_new</span>
                </a>
                <button type="button" class="inline-flex h-9 items-center justify-center gap-1.5 rounded-full border border-zinc-200 bg-white px-3.5 text-xs font-semibold text-zinc-700 shadow-sm" data-context-open>
                    <span class="material-symbols-rounded text-sm" aria-hidden="true">edit</span>
                    <span>Ubah</span>
                </button>
            </div>
        </div>
    </div>

    <form
        method="post"
        action="<?= esc($activeContext['switch_url']) ?>"
        class="hidden overflow-hidden rounded-3xl bg-white shadow-sm"
        data-context-form
        data-context-switcher
        data-units='<?= esc(json_encode($selectorUnits, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)) ?>'
    >
        <?= csrf_field() ?>
        <input type="hidden" name="redirect_to" value="<?= esc($activeContext['switch_redirect'] ?? current_url()) ?>">
        <?php foreach ($activeContext['switch_params'] as $key => $value): ?>
            <input type="hidden" name="<?= esc($key) ?>" value="<?= esc((string) $value) ?>">
        <?php endforeach; ?>

        <div class="border-b border-zinc-100 bg-white px-4 py-3">
            <div class="flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-xs font-medium uppercase tracking-wide text-zinc-500">Ubah Aktif</p>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <span class="inline-flex items-center rounded-full border border-zinc-950 bg-white px-3 py-1.5 text-xs font-semibold text-zinc-900"><?= esc($activeContext['unit_name']) ?></span>
                        <span class="inline-flex items-center rounded-full border border-zinc-950 bg-white px-3 py-1.5 text-xs font-semibold text-zinc-900"><?= esc($activeContext['activity_name']) ?></span>
                        <span class="inline-flex items-center rounded-full border border-zinc-950 bg-white px-3 py-1.5 text-xs font-semibold text-zinc-900"><?= esc($activeContext['account_name']) ?></span>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-zinc-200 bg-white text-zinc-700 shadow-sm" data-context-close>
                        <span class="material-symbols-rounded text-base" aria-hidden="true">close</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="px-4 py-4 sm:px-5 sm:py-5">
            <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
            <div class="space-y-2">
                <label for="context-unit" class="text-sm font-medium text-zinc-700">Unit / Program</label>
                <select
                    id="context-unit"
                    name="unit"
                    data-context-unit
                    class="h-12 w-full rounded-2xl border border-zinc-100 bg-white px-4 text-sm text-zinc-950 outline-none placeholder:text-zinc-400 focus:ring-2 focus:ring-lime-400"
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
                    class="h-12 w-full rounded-2xl border border-zinc-100 bg-white px-4 text-sm text-zinc-950 outline-none placeholder:text-zinc-400 focus:ring-2 focus:ring-lime-400"
                >
                    <?php foreach ($currentUnitActivities as $activity): ?>
                        <option value="<?= esc($activity['slug']) ?>" <?= $activity['slug'] === $activeContext['activity_slug'] ? 'selected' : '' ?>>
                            <?= esc($activity['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="space-y-2">
                <label for="context-account" class="text-sm font-medium text-zinc-700">Rekening</label>
                <select
                    id="context-account"
                    name="rekening"
                    class="h-12 w-full rounded-2xl border border-zinc-100 bg-white px-4 text-sm text-zinc-950 outline-none placeholder:text-zinc-400 focus:ring-2 focus:ring-lime-400"
                >
                    <?php foreach ($selectorAccounts as $account): ?>
                        <option value="<?= esc($account['slug']) ?>" <?= $account['slug'] === $activeContext['account_slug'] ? 'selected' : '' ?>>
                            <?= esc($account['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            </div>

            <div class="mt-4 flex items-center justify-end gap-2 border-t border-zinc-100 pt-4">
                    <button type="button" class="inline-flex h-11 items-center justify-center rounded-full border border-zinc-200 bg-white px-4 text-sm font-medium text-zinc-700 shadow-sm" data-context-close>
                        Batal
                    </button>
                    <button type="submit" class="inline-flex h-11 items-center justify-center gap-2 rounded-full bg-lime-400 px-5 text-sm font-semibold text-zinc-950 shadow-sm">
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
