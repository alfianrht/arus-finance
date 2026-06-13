<?php
$fieldName = $fieldName ?? 'project_pocket_id';
$label = $label ?? 'Kantong Proyek';
$placeholder = $placeholder ?? 'Pilih kantong proyek';
$activityFieldName = $activityFieldName ?? 'activity_id';
$readonly = $readonly ?? false;
$mode = $mode ?? 'all';
$selectedActivityId = (int) ($projectPocketField['selected_activity_id'] ?? 0);
$selectedPocketId = $fieldName === 'counter_project_pocket_id'
    ? (int) ($projectPocketField['selected_counter_pocket_id'] ?? 0)
    : (int) ($projectPocketField['selected_pocket_id'] ?? 0);
$groups = $projectPocketField['groups'] ?? [];
$currentGroup = $groups[$selectedActivityId] ?? null;
$showField = is_array($currentGroup) && ($currentGroup['is_project_mode'] ?? false);

if ($mode === 'execution') {
    $showField = $showField && (bool) ($currentGroup['requires_explicit'] ?? false);
}

$helperText = $mode === 'execution'
    ? 'Opsional. Isi jika pindah dana ini memang memindahkan saldo antar kantong di kegiatan yang sama.'
    : 'Jika kegiatan memakai model kantong proyek, transaksi ini akan masuk ke kantong yang dipilih.';

$config = [
    'fieldName' => $fieldName,
    'activityFieldName' => $activityFieldName,
    'selectedPocketId' => $selectedPocketId,
    'mode' => $mode,
    'placeholder' => $placeholder,
    'readonly' => $readonly,
    'groups' => $groups,
    'excludeFieldName' => $fieldName === 'counter_project_pocket_id' ? 'project_pocket_id' : null,
];
?>

<div
    class="space-y-2 <?= $showField ? '' : 'hidden' ?>"
    data-project-pocket-field="<?= esc(json_encode($config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), 'attr') ?>"
>
    <label class="text-sm font-semibold text-zinc-900"><?= esc($label) ?></label>
    <div class="relative">
        <select
            name="<?= esc($fieldName) ?>"
            <?= ($readonly || ! $showField) ? 'disabled' : '' ?>
            class="h-12 w-full appearance-none rounded-2xl border border-zinc-100 bg-white pl-4 pr-10 text-sm text-zinc-950 outline-none transition focus:border-zinc-300 focus:ring-2 focus:ring-lime-400 disabled:text-zinc-950 disabled:opacity-100"
        >
            <?php if ($showField): ?>
                <?php if (($mode === 'execution') || ($currentGroup['requires_explicit'] ?? false)): ?>
                    <option value=""><?= esc($placeholder) ?></option>
                <?php endif; ?>
                <?php foreach (($currentGroup['options'] ?? []) as $option): ?>
                    <option value="<?= esc($option['id']) ?>" <?= (int) $option['id'] === $selectedPocketId ? 'selected' : '' ?>>
                        <?= esc($option['name']) ?> · <?= esc($option['type_label']) ?>
                    </option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
        <span class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 material-symbols-rounded text-[20px] text-zinc-400">expand_more</span>
    </div>
    <p class="text-xs text-zinc-500" data-project-pocket-helper><?= esc($helperText) ?></p>
</div>

<script>
(function () {
    if (window.ArusProjectPocketFieldInitialized) {
        return;
    }

    window.ArusProjectPocketFieldInitialized = true;

    var parseConfig = function (root) {
        try {
            return JSON.parse(root.getAttribute('data-project-pocket-field') || '{}');
        } catch (error) {
            return {};
        }
    };

    var updateField = function (root) {
        var config = parseConfig(root);
        var form = root.closest('form');
        if (!form) {
            return;
        }

        var activitySelect = form.querySelector('[name="' + config.activityFieldName + '"]');
        var select = root.querySelector('select[name="' + config.fieldName + '"]');
        var helper = root.querySelector('[data-project-pocket-helper]');

        if (!activitySelect || !select) {
            return;
        }

        var group = (config.groups || {})[String(activitySelect.value)] || null;
        var showField = !!(group && group.is_project_mode);
        if (config.mode === 'execution') {
            showField = showField && !!group.requires_explicit;
        }

        root.classList.toggle('hidden', !showField);
        if (!showField) {
            select.required = false;
            select.disabled = true;
            select.innerHTML = '';
            return;
        }

        select.disabled = !!config.readonly;

        var existingValue = select.value || String(config.selectedPocketId || '');
        var requiresExplicit = config.mode === 'execution' || !!group.requires_explicit;
        var excludeField = config.excludeFieldName ? form.querySelector('[name="' + config.excludeFieldName + '"]') : null;
        var excludeValue = excludeField ? String(excludeField.value || '') : '';
        var options = [];

        if (requiresExplicit) {
            options.push({ value: '', label: config.placeholder || 'Pilih kantong proyek' });
        }

        (group.options || []).forEach(function (option) {
            if (excludeValue !== '' && String(option.id) === excludeValue) {
                return;
            }

            options.push({
                value: String(option.id),
                label: option.name + ' · ' + option.type_label
            });
        });

        select.innerHTML = options.map(function (option) {
            return '<option value="' + option.value.replace(/"/g, '&quot;') + '">' + option.label + '</option>';
        }).join('');

        if (!requiresExplicit && (!existingValue || existingValue === '0')) {
            existingValue = String(group.default_pocket_id || '');
        }

        if (existingValue !== '') {
            select.value = existingValue;
        }

        if (!select.value && !requiresExplicit && group.default_pocket_id) {
            select.value = String(group.default_pocket_id);
        }

        select.required = !config.readonly && requiresExplicit && select.options.length > 0;

        if (helper) {
            helper.textContent = config.mode === 'execution'
                ? 'Opsional. Isi jika pindah dana ini memang memindahkan saldo antar kantong di kegiatan yang sama.'
                : (group.requires_explicit
                    ? 'Kegiatan ini sudah memakai kantong proyek. Pilih kantong tujuan transaksi sebelum menyimpan.'
                    : 'Kegiatan proyek ini baru memakai Kantong Utama. Transaksi akan diarahkan ke sana otomatis.');
        }
    };

    var bindField = function (root) {
        var config = parseConfig(root);
        var form = root.closest('form');
        if (!form) {
            return;
        }

        var activitySelect = form.querySelector('[name="' + config.activityFieldName + '"]');
        var linkedSelect = config.excludeFieldName ? form.querySelector('[name="' + config.excludeFieldName + '"]') : null;

        if (activitySelect) {
            activitySelect.addEventListener('change', function () {
                root.querySelector('select').value = '';
                updateField(root);
                if (linkedSelect && config.excludeFieldName) {
                    form.querySelectorAll('[data-project-pocket-field]').forEach(function (fieldRoot) {
                        updateField(fieldRoot);
                    });
                }
            });
        }

        if (linkedSelect && config.fieldName === 'counter_project_pocket_id') {
            linkedSelect.addEventListener('change', function () {
                updateField(root);
            });
        }

        updateField(root);
    };

    document.querySelectorAll('[data-project-pocket-field]').forEach(bindField);
})();
</script>
