<?php
namespace Settings\Invoice;

use CG\Account\Client\Entity as Account;
use CG\Account\Client\Service as AccountService;
use CG\Account\Shared\Collection as Accounts;
use CG\Amazon\Region\Service as AmazonRegionService;
use CG\Ebay\Site\Map as EbaySiteMap;
use CG\Ebay\Order\Marketplace\Handler as OrderMarketplaceHandler;
use CG\Http\Exception\Exception3xx\NotModified;
use CG\Listing\Unimported\Marketplace\Client\Service as MarketplaceService;
use CG\Listing\Unimported\Marketplace\Collection as Marketplaces;
use CG\Listing\Unimported\Marketplace\Entity as Marketplace;
use CG\Listing\Unimported\Marketplace\Filter as MarketplaceFilter;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\Settings\Invoice\Shared\Entity;
use CG\Settings\InvoiceMapping\Entity as InvoiceMapping;
use CG\Settings\InvoiceMapping\Filter as InvoiceMappingFilter;
use CG\Settings\InvoiceMapping\Mapper as InvoiceMappingMapper;
use CG\Settings\InvoiceMapping\Service as InvoiceMappingService;
use CG\Stdlib\DateTime;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\User\ActiveUserInterface;
use CG_UI\View\DataTable;
use CG\Order\Client\Invoice\Email\Service as InvoiceEmailService;
use CG\Settings\Invoice\Shared\Mapper as InvoiceMapper;

class Mappings
{
    const DEFAULT_VALUE_INVOICE = '-';
    const DEFAULT_VALUE_SEND_TO = '-';

    /** @var ActiveUserInterface $activeUserContainer */
    protected $activeUserContainer;
    /** @var Helper $helper */
    protected $helper;
    /** @var DataTable $datatable */
    protected $datatable;
    /** @var MarketplaceService $marketplaceService */
    protected $marketplaceService;
    /** @var InvoiceMappingService $invoiceMappingService */
    protected $invoiceMappingService;
    /** @var InvoiceMappingMapper $invoiceMappingMapper */
    protected $invoiceMappingMapper;
    /** @var AccountService $accountService */
    protected $accountService;
    /** @var OrganisationUnitService $organisationUnitService */
    protected $organisationUnitService;
    /** @var AmazonRegionService $amazonRegionService */
    protected $amazonRegionService;
    /** @var OrderMarketplaceHandler $orderMarketplaceHandler */
    protected $orderMarketplaceHandler;

    protected $invoiceMappingMethodMap = [
        'ebay' => [
            'addMarketplaceListForEbayAccount',
            'addGspCountriesForEbayAccount'
        ],
        'amazon' => [
            'addFbaCountriesForAmazonAccount'
        ]
    ];

    public function __construct(
        ActiveUserInterface $activeUserContainer,
        Helper $helper,
        DataTable $datatable,
        MarketplaceService $marketplaceService,
        InvoiceMappingService $invoiceMappingService,
        InvoiceMappingMapper $invoiceMappingMapper,
        AccountService $accountService,
        OrganisationUnitService $organisationUnitService,
        AmazonRegionService $amazonRegionService,
        OrderMarketplaceHandler $orderMarketplaceHandler
    ) {
        $this->activeUserContainer = $activeUserContainer;
        $this->helper = $helper;
        $this->datatable = $datatable;
        $this->marketplaceService = $marketplaceService;
        $this->invoiceMappingService = $invoiceMappingService;
        $this->invoiceMappingMapper = $invoiceMappingMapper;
        $this->accountService = $accountService;
        $this->organisationUnitService = $organisationUnitService;
        $this->amazonRegionService = $amazonRegionService;
        $this->orderMarketplaceHandler = $orderMarketplaceHandler;
    }

    public function saveInvoiceMappingFromPostData(array $postData)
    {
        if (isset($postData['organisationUnitId'])) {
            $this->saveAccountOu($postData['accountId'], $postData['organisationUnitId']);
        } else {
            $this->saveInvoiceMapping($postData);
        }
    }

    protected function saveAccountOu($accountId, $organisationUnitId)
    {
        /** @var Account $account */
        $account = $this->accountService->fetch($accountId);
        try {
            return $this->accountService->save($account->setOrganisationUnitId($organisationUnitId));
        } catch (NotModified $exception) {
            return $account;
        }
    }

    protected function saveInvoiceMapping(array $invoiceMapping)
    {
        $invoiceMapping['organisationUnitId'] = $this->activeUserContainer->getActiveUserRootOrganisationUnitId();

        $defaultToNull = [
            'invoiceId' => static::DEFAULT_VALUE_INVOICE,
            'sendViaEmail' => static::DEFAULT_VALUE_SEND_TO,
            'sendToFba' => static::DEFAULT_VALUE_SEND_TO,
        ];

        foreach ($defaultToNull as $key => $default) {
            if (isset($invoiceMapping[$key]) && $invoiceMapping[$key] == $default) {
                $invoiceMapping[$key] = null;
            }
        }

        $booleanDateTime = ['sendViaEmail', 'sendToFba'];
        foreach ($booleanDateTime as $key) {
            if (!isset($invoiceMapping[$key])) {
                continue;
            }

            $booleanValue = filter_var($invoiceMapping[$key], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($booleanValue === true) {
                $invoiceMapping[$key] = (new DateTime())->convertToSystemTimezone()->stdFormat();
            } else {
                $invoiceMapping[$key] = $booleanValue;
            }
        }

        try {
            if (!isset($invoiceMapping['id'])) {
                throw new NotFound('No id - nothing to lookup');
            }

            $entity = $this->invoiceMappingService->fetch($invoiceMapping['id']);
            $entity = $this->invoiceMappingMapper->modifyEntityFromArray($entity, $invoiceMapping);
        } catch (NotFound $exception) {
            $entity = $this->invoiceMappingMapper->fromArray($invoiceMapping);
        }

        try {
            return $this->invoiceMappingService->save($entity);
        } catch (NotModified $exception) {
            return $entity;
        }
    }

    public function getInvoiceMappingDataTablesData(Accounts $accounts, $invoices)
    {
        $invoiceMappings = $this->getInvoiceMappingsForAccounts($accounts);
        $tradingCompanies = $this->helper->getTradingCompanies();

        if (count($tradingCompanies) > 0) {
            $rootOu = $this->organisationUnitService->fetch($this->activeUserContainer->getActiveUserRootOrganisationUnitId());
            array_unshift($tradingCompanies, $rootOu);
        }

        $dataTablesData = [];
        /** @var Account $account */
        foreach ($accounts as $account) {
            $mainAccountRow = true;
            foreach ($invoiceMappings as $invoiceMapping) {
                if ($invoiceMapping->getAccountId() !== $account->getId()) {
                    continue;
                }
                $dataTablesData[] = $this->getInvoiceMappingDataTablesRow(
                    $account,
                    $invoiceMapping,
                    $invoices,
                    $tradingCompanies,
                    $mainAccountRow
                );
                $mainAccountRow = false;
            }
        }
        return $dataTablesData;
    }

    /**
     * @return InvoiceMapping[]
     */
    protected function getInvoiceMappingsForAccounts(Accounts $accounts)
    {
        $invoiceMappings = $this->fetchInvoiceMappings($accounts);
        $accountSiteMap = $this->getAccountSiteMap($accounts);

        /** @var Account $account */
        foreach ($accounts as $account) {
            $accountId = $account->getId();
            $accountSites = $this->getAccountSiteMapForAccount($accountSiteMap, $accountId);

            foreach (($this->invoiceMappingMethodMap[$account->getChannel()] ?? []) as $method) {
                $accountSites = $this->$method($account, $accountSites);
            }

            sort($accountSites);

            if (empty($accountSites)) {
                $accountSites[] = null;
            }

            foreach ($accountSites as $site) {
                $invoiceMapping = $this->invoiceMappingMapper->fromArray([
                    'organisationUnitId' => $this->activeUserContainer->getActiveUserRootOrganisationUnitId(),
                    'accountId' => $account->getId(),
                    'site' => $site,
                ]);

                if (!isset($invoiceMappings[$invoiceMapping->getId()])) {
                    $invoiceMappings[$invoiceMapping->getId()] = $invoiceMapping;
                }
            }
        }

        return array_values($invoiceMappings);
    }

    protected function getAccountSiteMap(Accounts $accounts)
    {
        $accountSiteMap = [];
        if ($accounts->count() == 0) {
            return $accountSiteMap;
        }

        try {
            /** @var Marketplaces $marketplaces */
            $marketplaces = $this->marketplaceService->fetchCollectionByFilter(
                (new MarketplaceFilter())->setAccountId($accounts->getIds())
            );

            /** @var Marketplace $marketplace */
            foreach ($marketplaces as $marketplace) {
                $accountId = $marketplace->getAccountId();
                if (!isset($accountSiteMap[$accountId])) {
                    $accountSiteMap[$accountId] = [];
                }
                $accountSiteMap[$accountId][] = $marketplace->getMarketplace();
            }
        } catch (NotFound $exception) {
            // No marketplaces attached to requested accounts
        }

        return $accountSiteMap;
    }

    protected function getInvoiceMappingDataTablesRow(
        Account $account,
        InvoiceMapping $invoiceMapping,
        array $invoices,
        array $tradingCompanies,
        $mainAccountRow
    ) {
        return [
            'accountId' => $account->getId(),
            'rowId' => $invoiceMapping->getId(),
            'channel' => $mainAccountRow ? $account->getChannel() : '',
            'displayName' => $mainAccountRow ? $account->getDisplayName() : '',
            'site' => $invoiceMapping->getSite(),
            'siteName' => $this->marketplaceService->mapMarketplaceIdToName($account, $invoiceMapping->getSite()),
            'tradingCompany' => $mainAccountRow ? $this->getTradingCompanyOptions($account, $tradingCompanies) : '',
            'assignedInvoice' => $this->getInvoiceOptions($invoiceMapping, $invoices),
            'sendViaEmail' => $this->getSendOptions($invoiceMapping->getId(), (bool) $invoiceMapping->getSendViaEmail()),
            'sendToFba' => $account->getChannel() === 'amazon' ? $this->getSendOptions($invoiceMapping->getId(), (bool) $invoiceMapping->getSendToFba(), 'fba-') : '',
            'emailTemplate' => $this->getEmailTemplateOptions($invoiceMapping)
        ];
    }

    /**
     * @param Entity[] $invoices
     */
    protected function getInvoiceOptions(InvoiceMapping $invoiceMapping, array $invoices)
    {
        $invoiceId = $invoiceMapping->getInvoiceId();
        $invoiceOptions = [
            'options' => [
                [
                    'title' => 'Default Invoice',
                    'value' => static::DEFAULT_VALUE_INVOICE,
                    'selected' => $invoiceId === null,
                ],
            ],
        ];

        foreach ($invoices as $invoice) {
            $invoiceOptions['options'][] = [
                'title' => $invoice->getName(),
                'value' => $invoice->getId(),
                'selected' => $invoice->getId() == $invoiceId,
            ];
        }

        return $invoiceOptions;
    }

    /**
     * @param OrganisationUnit[] $tradingCompanies
     */
    protected function getTradingCompanyOptions(Account $account, array $tradingCompanies)
    {
        $tradingCompanyOptions = ['options' => []];
        foreach ($tradingCompanies as $tradingCompany) {
            $tradingCompanyOptions['options'][] = [
                'title' => $tradingCompany->getAddressCompanyName(),
                'value' => $tradingCompany->getId(),
                'selected' => $tradingCompany->getId() === $account->getOrganisationUnitId()
            ];
        }
        return $tradingCompanyOptions;
    }

    protected function getSendOptions(string $id, bool $isChecked, string $idPrefix = ''): array
    {
        return [
            'id' => $idPrefix . $id,
            'rowId' => $id,
            'enabled' => $isChecked
        ];
    }

    protected function getEmailTemplateOptions(InvoiceMapping $invoiceMapping)
    {
        return [
            'subject' => $invoiceMapping->getEmailSubject() ?? InvoiceEmailService::EMAIL_SUBJECT,
            'template' => $invoiceMapping->getEmailTemplate() ?? InvoiceMapper::DEFAULT_EMAIL_TEMPLATE,
            'id' => $invoiceMapping->getId()
        ];
    }

    /**
     * @return DataTable
     */
    public function getDatatable()
    {
        return $this->datatable;
    }

    /**
     * @param Accounts $accounts
     * @return array
     */
    protected function fetchInvoiceMappings(Accounts $accounts): array
    {
        $invoiceMappings = [];
        try {
            $existingMappings = $this->invoiceMappingService->fetchCollectionByFilter(
                (new InvoiceMappingFilter())->setAccountId($accounts->getIds())->setLimit('all')
            );

            /** @var InvoiceMapping $existingMapping */
            foreach ($existingMappings as $existingMapping) {
                $invoiceMappings[$existingMapping->getId()] = $existingMapping;
            }
        } catch (NotFound $exception) {
            // No previous invoice mappings
        }
        return $invoiceMappings;
    }

    /**
     * @param array $accountSiteMap
     * @param int $accountId
     * @return array
     */
    protected function getAccountSiteMapForAccount(array $accountSiteMap, int $accountId): array
    {
        $accountSites = [];
        if (isset($accountSiteMap[$accountId]) && !empty($accountSiteMap[$accountId])) {
            $accountSites = $accountSiteMap[$accountId];
        }
        return $accountSites;
    }

    /**
     * @param Account $account
     * @param array $accountSites
     * @return array
     */
    protected function addMarketplaceListForEbayAccount(Account $account, array $accountSites): array
    {
        return array_unique(array_merge(
            $accountSites,
            $this->orderMarketplaceHandler->getMarketplaceList($account)
        ));
    }

    /**
     * @param Account $account
     * @param array $accountSites
     * @return array
     */
    protected function addGspCountriesForEbayAccount(Account $account, array $accountSites): array
    {
        if (!$account->getExternalData()['globalShippingProgram']) {
            return $accountSites;
        }

        return array_unique(array_merge(
            $accountSites,
            array_keys(EbaySiteMap::getSiteIdByCountryCode())
        ));
    }

    /**
     * @param Account $account
     * @param array $accountSites
     * @return array
     */
    protected function addFbaCountriesForAmazonAccount(Account $account, array $accountSites): array
    {
        if (!$account->getExternalData()['fbaOrderImport']) {
            return $accountSites;
        }

        return array_unique(array_merge(
            $accountSites,
            array_keys($this->amazonRegionService->getRegion($account)->getMarketplaces())
        ));
    }
}
