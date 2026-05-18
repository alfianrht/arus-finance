<?php

namespace App\Controllers;

use App\Models\BookPeriodModel;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Session\Session;
use Psr\Log\LoggerInterface;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    protected const ACTIVE_CONTEXT_SESSION_KEY = 'arus_active_context';
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var list<string>
     */
    protected $helpers = ['url', 'arus'];

    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */
    protected Session $session;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.

        $this->session = service('session');
        service('renderer')->setVar('bookPeriodLabel', $this->activeBookPeriodLabel());
    }

    /**
     * @param array<int, array<string, mixed>> $units
     * @return array<string, mixed>
     */
    protected function resolveActiveContextSelection(array $units, array $accounts = []): array
    {
        $stored = $this->session->get(self::ACTIVE_CONTEXT_SESSION_KEY);
        $stored = is_array($stored) ? $stored : [];

        $requestedUnitSlug = trim((string) ($this->request->getGet('unit') ?? ''));
        $requestedActivitySlug = trim((string) ($this->request->getGet('kegiatan') ?? ''));
        $requestedAccountSlug = trim((string) ($this->request->getGet('rekening') ?? ''));

        $selectedUnitSlug = $requestedUnitSlug !== ''
            ? $requestedUnitSlug
            : trim((string) ($stored['unit_slug'] ?? ''));

        $selectedUnit = null;
        foreach ($units as $unit) {
            if (($unit['slug'] ?? '') === $selectedUnitSlug) {
                $selectedUnit = $unit;
                break;
            }
        }

        $selectedUnit ??= $units[0] ?? null;
        $activities = is_array($selectedUnit['activities'] ?? null) ? $selectedUnit['activities'] : [];

        $selectedActivitySlug = $requestedActivitySlug !== ''
            ? $requestedActivitySlug
            : trim((string) ($stored['activity_slug'] ?? ''));

        $selectedActivity = null;
        foreach ($activities as $activity) {
            if (($activity['slug'] ?? '') === $selectedActivitySlug) {
                $selectedActivity = $activity;
                break;
            }
        }

        $selectedActivity ??= $activities[0] ?? null;

        $selectedAccountSlug = $requestedAccountSlug !== ''
            ? $requestedAccountSlug
            : trim((string) ($stored['account_slug'] ?? ''));

        $selectedAccount = null;
        foreach ($accounts as $account) {
            if (($account['slug'] ?? '') === $selectedAccountSlug) {
                $selectedAccount = $account;
                break;
            }
            if ($selectedAccountSlug !== '' && (int) $selectedAccountSlug > 0 && (int) ($account['id'] ?? 0) === (int) $selectedAccountSlug) {
                $selectedAccount = $account;
                break;
            }
        }

        if ($selectedAccount === null) {
            $storedAccountId = (int) ($stored['account_id'] ?? 0);
            foreach ($accounts as $account) {
                if ((int) ($account['id'] ?? 0) === $storedAccountId) {
                    $selectedAccount = $account;
                    break;
                }
            }
        }

        $selectedAccount ??= $accounts[0] ?? null;

        $selection = [
            'unit_id' => (int) ($selectedUnit['id'] ?? 0),
            'activity_id' => (int) ($selectedActivity['id'] ?? 0),
            'account_id' => (int) ($selectedAccount['id'] ?? 0),
            'unit_slug' => (string) ($selectedUnit['slug'] ?? ''),
            'activity_slug' => (string) ($selectedActivity['slug'] ?? ''),
            'account_slug' => (string) ($selectedAccount['slug'] ?? ''),
            'unit_name' => (string) ($selectedUnit['name'] ?? 'Tanpa Unit'),
            'activity_name' => (string) ($selectedActivity['name'] ?? 'Tanpa Kegiatan'),
            'account_name' => (string) ($selectedAccount['name'] ?? 'Tanpa Rekening'),
        ];

        if ($selection['unit_id'] > 0 || $selection['activity_id'] > 0 || $selection['account_id'] > 0) {
            $this->session->set(self::ACTIVE_CONTEXT_SESSION_KEY, $selection);
        }

        return $selection;
    }

    /**
     * @param array<string, mixed> $selection
     */
    protected function storeActiveContextSelection(array $selection): void
    {
        $payload = [
            'unit_id' => (int) ($selection['unit_id'] ?? 0),
            'activity_id' => (int) ($selection['activity_id'] ?? 0),
            'account_id' => (int) ($selection['account_id'] ?? 0),
            'unit_slug' => (string) ($selection['unit_slug'] ?? ''),
            'activity_slug' => (string) ($selection['activity_slug'] ?? ''),
            'account_slug' => (string) ($selection['account_slug'] ?? ''),
            'unit_name' => (string) ($selection['unit_name'] ?? 'Tanpa Unit'),
            'activity_name' => (string) ($selection['activity_name'] ?? 'Tanpa Kegiatan'),
            'account_name' => (string) ($selection['account_name'] ?? 'Tanpa Rekening'),
        ];

        if ($payload['unit_id'] > 0 || $payload['activity_id'] > 0 || $payload['account_id'] > 0) {
            $this->session->set(self::ACTIVE_CONTEXT_SESSION_KEY, $payload);
        }
    }

    protected function currentInstitutionId(): int
    {
        return (int) ($this->session->get('auth_institution_id') ?? 1);
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function activeBookPeriod(): ?array
    {
        $period = (new BookPeriodModel())
            ->where('institution_id', $this->currentInstitutionId())
            ->where('is_active', 1)
            ->first();

        return is_array($period) ? $period : null;
    }

    protected function activeBookPeriodLabel(): string
    {
        $period = $this->activeBookPeriod();
        if ($period === null) {
            return 'Tahun Buku Aktif';
        }

        $startYear = date('Y', strtotime((string) $period['start_date']));
        $endYear = date('Y', strtotime((string) $period['end_date']));

        return trim('TB ' . $startYear . '/' . $endYear . ((int) ($period['is_active'] ?? 0) === 1 ? ' Aktif' : ''));
    }
}
