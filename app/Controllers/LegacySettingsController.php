<?php

namespace App\Controllers;

use App\Controllers\Concerns\LegacySettingsMasterDataTrait;
use App\Controllers\Concerns\LegacySettingsDashboardTrait;
use App\Controllers\Concerns\LegacySettingsFinanceMasterTrait;
use App\Controllers\Concerns\LegacySettingsPrototypeFlowTrait;
use App\Controllers\Concerns\LegacySettingsReportMasterTrait;
use App\Controllers\Concerns\LegacySettingsUnitActivityTrait;
use App\Models\AccountModel;
use App\Models\ActivityModel;
use App\Models\BookPeriodModel;
use App\Models\InstitutionModel;
use App\Models\OpeningBalanceModel;
use App\Models\ReceiverModel;
use App\Models\ReportPositionModel;
use App\Models\TransactionCategoryModel;
use App\Models\UnitModel;
use App\Models\UserModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

class LegacySettingsController extends BaseController
{
    use LegacySettingsMasterDataTrait;
    use LegacySettingsPrototypeFlowTrait;

    private function currentInstitution(): array
    {
        $institution = (new InstitutionModel())->find($this->currentInstitutionId());

        if (is_array($institution)) {
            return $institution;
        }

        return [
            'id' => 1,
            'name' => 'PT Maju Pendidikan Bangsa',
            'app_name' => 'Arus',
            'type' => 'Lembaga',
            'email' => '',
            'whatsapp' => '',
            'address' => '',
            'logo' => '',
        ];
    }

    private function currentUser(): array
    {
        $userId = (int) ($this->session->get('auth_user_id') ?? 0);
        $user = $userId > 0 ? (new UserModel())->find($userId) : null;

        if (is_array($user)) {
            return $user;
        }

        return [
            'id' => $userId,
            'name' => (string) ($this->session->get('auth_user_name') ?? 'Pengguna Arus'),
            'email' => '',
            'whatsapp' => '',
            'google_id' => '',
            'auth_provider' => '',
            'avatar_url' => '',
            'role' => (string) ($this->session->get('auth_role') ?? 'admin'),
            'institution_id' => $this->currentInstitutionId(),
        ];
    }

    private function buildSelectOptions(array $rows, string $valueKey = 'id', string $labelKey = 'name'): array
    {
        return array_map(
            static fn(array $row): array => [
                'value' => (string) ($row[$valueKey] ?? ''),
                'label' => (string) ($row[$labelKey] ?? ''),
            ],
            $rows
        );
    }

    private function uniqueSlug(object $model, string $name, ?int $ignoreId = null): string
    {
        $baseSlug = url_title($name, '-', true);
        $finalSlug = $baseSlug;
        $suffix = 2;

        while (true) {
            $conflict = $model->where('slug', $finalSlug)->first();
            if (! is_array($conflict) || ($ignoreId !== null && (int) $conflict['id'] === $ignoreId)) {
                return $finalSlug;
            }

            $finalSlug = $baseSlug . '-' . $suffix;
            $suffix++;
        }
    }

    private function reportPositionNameMap(): array
    {
        $map = [];
        foreach ((new ReportPositionModel())->where('institution_id', $this->currentInstitutionId())->findAll() as $position) {
            $map[(int) $position['id']] = $position['name'];
        }

        return $map;
    }

    private function openingBalanceTotalByPosition(): array
    {
        $totals = [];
        foreach ((new OpeningBalanceModel())->findAll() as $row) {
            $positionId = (int) $row['report_position_id'];
            $totals[$positionId] = ($totals[$positionId] ?? 0) + (float) $row['amount'];
        }

        return $totals;
    }

    private function normalBalanceForKind(string $kind): string
    {
        return in_array($kind, ['Pendapatan', 'Kewajiban', 'Modal'], true) ? 'Kredit' : 'Debit';
    }

    private function normalizeMoney(string $raw): float
    {
        $normalized = preg_replace('/[^0-9,.-]/', '', $raw) ?? '0';
        $normalized = str_replace('.', '', $normalized);
        $normalized = str_replace(',', '.', $normalized);

        return (float) $normalized;
    }

}
