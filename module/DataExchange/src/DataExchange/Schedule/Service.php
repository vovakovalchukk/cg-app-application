<?php
namespace DataExchange\Schedule;

use CG\DataExchangeSchedule\Entity as Schedule;
use CG\DataExchangeSchedule\Filter as ScheduleFilter;
use CG\DataExchangeSchedule\Mapper as ScheduleMapper;
use CG\DataExchangeSchedule\Options\Stock\Import as StockImportOptions;
use CG\DataExchangeSchedule\Service as ScheduleService;
use CG\DataExchangeTemplate\Collection as TemplateCollection;
use CG\DataExchangeTemplate\Entity as Template;
use CG\DataExchangeTemplate\Filter as TemplateFilter;
use CG\DataExchangeTemplate\Service as TemplateService;
use CG\EmailAccount\Collection as EmailAccountCollection;
use CG\EmailAccount\Entity as EmailAccount;
use CG\EmailAccount\Filter as EmailAccountFilter;
use CG\EmailAccount\Service as EmailAccountService;
use CG\FtpAccount\Collection as FtpAccountCollection;
use CG\FtpAccount\Entity as FtpAccount;
use CG\FtpAccount\Filter as FtpAccountFilter;
use CG\FtpAccount\Service as FtpAccountService;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\User\ActiveUserInterface;
use CG\UserPreference\Client\Service as UserPreferenceService;

class Service
{
    /** @var ScheduleService */
    protected $scheduleService;
    /** @var ScheduleMapper */
    protected $scheduleMapper;
    /** @var TemplateService */
    protected $templateService;
    /** @var FtpAccountService */
    protected $ftpAccountService;
    /** @var EmailAccountService */
    protected $emailAccountService;
    /** @var UserPreferenceService */
    protected $userPreferenceService;
    /** @var ActiveUserInterface */
    protected $activeUserContainer;

    public function __construct(
        ScheduleService $scheduleService,
        ScheduleMapper $scheduleMapper,
        TemplateService $templateService,
        FtpAccountService $ftpAccountService,
        EmailAccountService $emailAccountService,
        UserPreferenceService $userPreferenceService,
        ActiveUserInterface $activeUserContainer
    ) {
        $this->scheduleService = $scheduleService;
        $this->scheduleMapper = $scheduleMapper;
        $this->templateService = $templateService;
        $this->ftpAccountService = $ftpAccountService;
        $this->emailAccountService = $emailAccountService;
        $this->userPreferenceService = $userPreferenceService;
        $this->activeUserContainer = $activeUserContainer;
    }

    public function fetchStockImportsForActiveUser(): array
    {
        return $this->fetchForActiveUser(Schedule::TYPE_STOCK, Schedule::OPERATION_IMPORT);
    }

    public function fetchStockExportsForActiveUser(): array
    {
        return $this->fetchForActiveUser(Schedule::TYPE_STOCK, Schedule::OPERATION_EXPORT);
    }

    public function fetchOrderExportsForActiveUser(): array
    {
        return $this->fetchForActiveUser(Schedule::TYPE_ORDER, Schedule::OPERATION_EXPORT);
    }

    protected function fetchForActiveUser(string $type, string $operation): array
    {
        try {
            $rootOuId = $this->activeUserContainer->getActiveUserRootOrganisationUnitId();
            $filter = $this->buildScheduleFilter($rootOuId, $type, $operation);
            $schedules = $this->scheduleService->fetchCollectionByFilter($filter);
            $schedulesArray = $schedules->toArray();
            return $this->collapseScheduleArrays($schedulesArray);
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

    protected function collapseScheduleArrays(array $schedulesArray): array
    {
        // Merge the options in to the main array
        foreach ($schedulesArray as &$scheduleArray) {
            if (!isset($scheduleArray['options'])) {
                continue;
            }
            unset($scheduleArray['options']['version']);
            $scheduleArray = array_merge($scheduleArray, $scheduleArray['options']);
            unset($scheduleArray['options']);
        }
        return $schedulesArray;
    }

    public function fetchStockTemplateOptionsForActiveUser(): array
    {
        return $this->fetchTemplateOptionsForActiveUser(Template::TYPE_STOCK);
    }

    public function fetchOrderTemplateOptionsForActiveUser(): array
    {
        return $this->fetchTemplateOptionsForActiveUser(Template::TYPE_ORDER);
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

    public function fetchEmailFromAccountOptionsForActiveUser(): array
    {
        $verified = true;
        return $this->fetchEmailAccountOptionsForActiveUser(EmailAccount::TYPE_FROM, $verified);
    }

    public function fetchEmailToAccountOptionsForActiveUser(): array
    {
        return $this->fetchEmailAccountOptionsForActiveUser(EmailAccount::TYPE_TO);
    }

    protected function fetchEmailAccountOptionsForActiveUser(string $type, ?bool $verified = null): array
    {
        try {
            $rootOuId = $this->activeUserContainer->getActiveUserRootOrganisationUnitId();
            $filter = $this->buildEmailAccountFilter($rootOuId, $type, $verified);
            $emailAccounts = $this->emailAccountService->fetchCollectionByFilter($filter);
            return $this->emailAccountsToOptions($emailAccounts);
        } catch (NotFound $e) {
            return [];
        }
    }

    protected function buildEmailAccountFilter(int $rootOuId, string $type, ?bool $verified = null): EmailAccountFilter
    {
        return (new EmailAccountFilter())
            ->setLimit('all')
            ->setPage(1)
            ->setOrganisationUnitId([$rootOuId])
            ->setType([$type])
            ->setVerified($verified);
    }

    protected function emailAccountsToOptions(EmailAccountCollection $emailAccounts): array
    {
        $options = [];
        /** @var EmailAccount $emailAccount */
        foreach ($emailAccounts as $emailAccount) {
            $options[$emailAccount->getId()] = $emailAccount->getAddress();
        }
        return $options;
    }

    public function fetchEmailToAndFtpAccountOptionsForActiveUser(): array
    {
        $emailToAccounts = $this->fetchEmailToAccountOptionsForActiveUser();
        $ftpAccounts = $this->fetchFtpAccountOptionsForActiveUser();
        $combined = [];
        $type = Schedule::ACCOUNT_TYPE_EMAIL;
        foreach ($emailToAccounts as $id => $emailToAccount) {
            $combined[$type.'-'.$id] = $emailToAccount;
        }
        $type = Schedule::ACCOUNT_TYPE_FTP;
        foreach ($ftpAccounts as $id => $ftpAccount) {
            $combined[$type.'-'.$id] = $ftpAccount;
        }
        return $combined;
    }

    public function fetchSavedFilterOptionsForActiveUser(): array
    {
        $userPreference = $this->userPreferenceService->fetch($this->activeUserContainer->getActiveUser()->getId());
        $preference = $userPreference->getPreference();
        if (!isset($preference['order-saved-filters'])) {
            return [];
        }
        $filterNames = array_keys($preference['order-saved-filters']);
        return array_combine($filterNames, $filterNames);
    }

    public function saveStockImportForActiveUser(array $data): Schedule
    {
        $data['fromDataExchangeAccountType'] = Schedule::ACCOUNT_TYPE_FTP;
        if (isset($data['action'])) {
            $data['options'] = ['action' => $data['action']];
            unset($data['action']);
        }
        return $this->saveForActiveUser($data, Schedule::TYPE_STOCK, Schedule::OPERATION_IMPORT);
    }

    public function saveStockExportForActiveUser(array $data): Schedule
    {
        $data = $this->prepareExportDataForSaving($data);
        return $this->saveForActiveUser($data, Schedule::TYPE_STOCK, Schedule::OPERATION_EXPORT);
    }

    public function saveOrderExportForActiveUser(array $data): Schedule
    {
        $data = $this->prepareExportDataForSaving($data);
        if (isset($data['savedFilterName'])) {
            $data['options'] = ['savedFilterName' => $data['savedFilterName']];
            unset($data['savedFilterName']);
        }
        return $this->saveForActiveUser($data, Schedule::TYPE_ORDER, Schedule::OPERATION_EXPORT);
    }

    protected function prepareExportDataForSaving(array $data): array
    {
        if (isset($data['toDataExchangeAccountId'])) {
            [$type, $id] = explode('-', $data['toDataExchangeAccountId']);
            $data['toDataExchangeAccountId'] = $id;
            $data['toDataExchangeAccountType'] = $type;
        }
        if (isset($data['fromDataExchangeAccountId'])) {
            $data['fromDataExchangeAccountType'] = Schedule::ACCOUNT_TYPE_EMAIL;
        }
        return $data;
    }

    protected function saveForActiveUser(array $data, string $type, string $operation): Schedule
    {
        $rootOuId = $this->activeUserContainer->getActiveUserRootOrganisationUnitId();
        $data['organisationUnitId'] = $rootOuId;
        $data['type'] = $type;
        $data['operation'] = $operation;
        if (!isset($data['id'])) {
            return $this->saveNew($data);
        }
        return $this->saveExisting($data);
    }

    protected function saveNew(array $data): Schedule
    {
        $entity = $this->scheduleMapper->fromArray($data);
        return $this->save($entity);
    }

    protected function saveExisting(array $data): Schedule
    {
        $fetchedEntity = $this->scheduleService->fetch($data['id']);
        $entityArray = array_merge($fetchedEntity->toArray(), $data);
        $updatedEntity = $this->scheduleMapper->fromArray($entityArray);
        $updatedEntity->setStoredETag($data['etag'] ?? $fetchedEntity->getStoredETag());
        return $this->save($updatedEntity);
    }

    protected function save(Schedule $entity): Schedule
    {
        $entityHal = $this->scheduleService->save($entity);
        $entity = $this->scheduleMapper->fromHal($entityHal);
        if (!$entity->getStoredETag()) {
            return $this->scheduleService->fetch($entity->getId());
        }
        return $entity;
    }

    public function remove(int $id): void
    {
        $entity = $this->scheduleService->fetch($id);
        $this->scheduleService->remove($entity);
    }
}