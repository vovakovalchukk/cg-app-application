<?php
namespace DataExchange\Schedule;

use CG\DataExchangeSchedule\Entity as Schedule;
use CG\DataExchangeSchedule\Filter as ScheduleFilter;
use CG\DataExchangeSchedule\Options\Stock\Import as StockImportOptions;
use CG\DataExchangeSchedule\Service as ScheduleService;
use CG\DataExchangeTemplate\Collection as TemplateCollection;
use CG\DataExchangeTemplate\Entity as Template;
use CG\DataExchangeTemplate\Filter as TemplateFilter;
use CG\DataExchangeTemplate\Service as TemplateService;
use CG\FtpAccount\Collection as FtpAccountCollection;
use CG\FtpAccount\Entity as FtpAccount;
use CG\FtpAccount\Filter as FtpAccountFilter;
use CG\FtpAccount\Service as FtpAccountService;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\User\ActiveUserInterface;

class Service
{
    /** @var ScheduleService */
    protected $scheduleService;
    /** @var TemplateService */
    protected $templateService;
    /** @var FtpAccountService */
    protected $ftpAccountService;
    /** @var ActiveUserInterface */
    protected $activeUserContainer;

    public function __construct(
        ScheduleService $scheduleService,
        TemplateService $templateService,
        FtpAccountService $ftpAccountService,
        ActiveUserInterface $activeUserContainer
    ) {
        $this->scheduleService = $scheduleService;
        $this->templateService = $templateService;
        $this->ftpAccountService = $ftpAccountService;
        $this->activeUserContainer = $activeUserContainer;
    }

    public function fetchStockImportsForActiveUser(): array
    {
        return $this->fetchForActiveUser(Schedule::TYPE_STOCK, Schedule::OPERATION_IMPORT);
    }

    protected function fetchForActiveUser(string $type, string $operation): array
    {
        try {
            $rootOuId = $this->activeUserContainer->getActiveUserRootOrganisationUnitId();
            $filter = $this->buildScheduleFilter($rootOuId, $type, $operation);
            $schedules = $this->scheduleService->fetchCollectionByFilter($filter);
            return $schedules->toArray();
        } catch (NotFound $e) {
            return [];
        }
    }

    protected function buildScheduleFilter(int $rootOuId, string $type, string $operation): ScheduleFilter
    {
        return (new ScheduleFilter())
            ->setLimit('all')
            ->setPage(1)
            ->setOrganisationUnitId([$rootOuId])
            ->setType([$type])
            ->setOperation([$operation]);
    }

    public function fetchStockTemplateOptionsForActiveUser(): array
    {
        return $this->fetchTemplateOptionsForActiveUser(Template::TYPE_STOCK);
    }

    protected function fetchTemplateOptionsForActiveUser(string $type): array
    {
        try {
            $rootOuId = $this->activeUserContainer->getActiveUserRootOrganisationUnitId();
            $filter = $this->buildTemplateFilter($rootOuId, $type);
            $templates = $this->templateService->fetchCollectionByFilter($filter);
            return $this->templatesToOptions($templates);
        } catch (NotFound $e) {
            return [];
        }
    }

    protected function buildTemplateFilter(int $rootOuId, string $type): TemplateFilter
    {
        return (new TemplateFilter())
            ->setLimit('all')
            ->setPage(1)
            ->setOrganisationUnitId([$rootOuId])
            ->setType([$type]);
    }

    protected function templatesToOptions(TemplateCollection $templates): array
    {
        $options = [];
        /** @var Template $template */
        foreach ($templates as $template) {
            $options[$template->getId()] = $template->getName();
        }
        return $options;
    }

    public function getStockImportActionOptions(): array
    {
        return [
            StockImportOptions::ACTION_SET => 'Set stock level',
            StockImportOptions::ACTION_ADD => 'Add to stock level',
            StockImportOptions::ACTION_REMOVE => 'Remove from stock level',
        ];
    }

    public function fetchFtpAccountOptionsForActiveUser(): array
    {
        try {
            $rootOuId = $this->activeUserContainer->getActiveUserRootOrganisationUnitId();
            $filter = $this->buildFtpAccountFilter($rootOuId);
            $ftpAccounts = $this->ftpAccountService->fetchCollectionByFilter($filter);
            return $this->ftpAccountsToOptions($ftpAccounts);
        } catch (NotFound $e) {
            return [];
        }
    }

    protected function buildFtpAccountFilter(int $rootOuId): FtpAccountFilter
    {
        return (new FtpAccountFilter())
            ->setLimit('all')
            ->setPage(1)
            ->setOrganisationUnitId([$rootOuId]);
    }

    protected function ftpAccountsToOptions(FtpAccountCollection $ftpAccounts): array
    {
        $options = [];
        /** @var FtpAccount $ftpAccount */
        foreach ($ftpAccounts as $ftpAccount) {
            $options[$ftpAccount->getId()] = $ftpAccount->getName();
        }
        return $options;
    }
}