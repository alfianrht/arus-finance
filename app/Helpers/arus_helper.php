<?php

if (! function_exists('rupiah')) {
    function rupiah($amount): string
    {
        return 'Rp ' . number_format((float) $amount, 0, ',', '.');
    }
}

if (! function_exists('route_query')) {
    function route_query(string $path, array $params = []): string
    {
        $filtered = array_filter(
            $params,
            static fn($value): bool => $value !== null && $value !== ''
        );

        if ($filtered === []) {
            return site_url($path);
        }

        return site_url($path) . '?' . http_build_query($filtered);
    }
}

if (! function_exists('surface_tail')) {
    function surface_tail(string $value): string
    {
        return str_pad((string) (abs(crc32($value)) % 10000), 4, '0', STR_PAD_LEFT);
    }
}

if (! function_exists('surface_label')) {
    function surface_label(?string $value, string $fallback = 'ARS'): string
    {
        if ($value === null || trim($value) === '') {
            return $fallback;
        }

        $clean = preg_replace('/[^A-Za-z0-9 ]/', ' ', $value) ?? '';
        $parts = preg_split('/\s+/', trim($clean)) ?: [];
        $parts = array_values(array_filter($parts, static fn(string $part): bool => $part !== ''));

        if ($parts === []) {
            return $fallback;
        }

        if (count($parts) === 1) {
            $single = preg_replace('/([a-z])([A-Z])/', '$1 $2', $parts[0]) ?? $parts[0];
            $segments = preg_split('/\s+/', trim($single)) ?: [];

            if (count($segments) > 1) {
                $parts = $segments;
            } else {
                return strtoupper(substr($parts[0], 0, 4));
            }
        }

        $label = '';

        foreach ($parts as $part) {
            $label .= strtoupper(substr($part, 0, 1));
            if (strlen($label) >= 4) {
                break;
            }
        }

        return $label !== '' ? $label : $fallback;
    }
}

if (! function_exists('paginate_items')) {
    function paginate_items(array $items, int $page = 1, int $perPage = 10): array
    {
        $page = max(1, $page);
        $perPage = max(1, $perPage);
        $total = count($items);
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page = min($page, $totalPages);
        $offset = ($page - 1) * $perPage;

        return [
            'items' => array_slice($items, $offset, $perPage),
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => $totalPages,
            'hasPrev' => $page > 1,
            'hasNext' => $page < $totalPages,
            'prevPage' => max(1, $page - 1),
            'nextPage' => min($totalPages, $page + 1),
        ];
    }
}
