<?php
$items = $items ?? [];
if ($items === []) {
    return;
}
?>

<section class="rounded-3xl bg-white p-3 shadow-sm">
    <style>
        .project-scope-scroll {
            scrollbar-width: none;
        }

        .project-scope-scroll::-webkit-scrollbar {
            display: none;
        }
    </style>

    <div class="project-scope-scroll flex flex-nowrap gap-2 overflow-x-auto px-1 pb-1 pt-0.5 snap-x snap-mandatory scroll-pl-1">

        <?php foreach ($items as $item): ?>
            <?php
            $scopeType = $item['scope_type'] ?? 'execution_pocket';
            $isActivity = $scopeType === 'activity';
            $isActive = (bool) ($item['is_active'] ?? false);
            $isInactive = (bool) ($item['is_inactive'] ?? false);
            $baseClass = $isActivity
                ? 'bg-zinc-950 text-white border-zinc-950'
                : 'bg-white text-zinc-950 border-zinc-950/10';
            $stateClass = $isActive
                ? ($isActivity ? ' ring-2 ring-lime-400 ring-offset-1' : ' border-lime-400 ring-2 ring-lime-400 ring-offset-1')
                : '';
            $metaClass = $isActivity ? 'text-zinc-400' : 'text-zinc-500';
            $badgeClass = $isActivity
                ? 'bg-white/10 text-white border-white/10'
                : 'bg-zinc-50 text-zinc-700 border-zinc-950/10';
            ?>
            <a href="<?= esc($item['target_url'] ?? '#') ?>" class="group <?= esc($baseClass . $stateClass) ?> relative flex w-[13.5rem] shrink-0 snap-start flex-col rounded-[1.35rem] border px-3.5 py-3 shadow-sm transition duration-150 hover:-translate-y-0.5 sm:w-56">
                <div class="flex items-start justify-between gap-2">
                    <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.12em] <?= esc($badgeClass) ?>">
                        <?= esc($item['label'] ?? '') ?>
                    </span>
                    <?php if ($isActive): ?>
                        <span class="inline-flex items-center rounded-full bg-lime-400 px-2 py-1 text-[10px] font-semibold text-zinc-950">Aktif</span>
                    <?php elseif ($isInactive): ?>
                        <span class="inline-flex items-center rounded-full bg-rose-50 px-2 py-1 text-[10px] font-semibold text-rose-600">Nonaktif</span>
                    <?php endif; ?>
                </div>

                <p class="mt-2 line-clamp-2 text-sm font-semibold leading-snug"><?= esc($item['title'] ?? '') ?></p>
                <p class="mt-1 text-[11px] <?= esc($metaClass) ?>"><?= esc($item['meta'] ?? '') ?></p>
            </a>
        <?php endforeach; ?>
    </div>
</section>
