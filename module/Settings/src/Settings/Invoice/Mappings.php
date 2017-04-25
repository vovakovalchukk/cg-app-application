<?php
namespace Settings\Invoice;

use CG\Account\Client\Service as AccountService;
use CG\Account\Shared\Collection as Accounts;
use CG\Account\Shared\Entity as Account;
use CG\Listing\Unimported\Marketplace\Collection as Marketplaces;
use CG\Listing\Unimported\Marketplace\Entity as Marketplace;
use CG\Listing\Unimported\Marketplace\Filter as MarketplaceFilter;
use CG\Listing\Unimported\Marketplace\Service as MarketplaceService;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\Settings\Invoice\Shared\Entity;
use CG\Settings\InvoiceMapping\Entity as InvoiceMapping;
use CG\Settings\InvoiceMapping\Filter as InvoiceMappingFilter;
use CG\Settings\InvoiceMapping\Mapper as InvoiceMappingMapper;
use CG\Settings\InvoiceMapping\Service as InvoiceMappingService;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\User\ActiveUserInterface;
use CG_UI\View\DataTable;

class Mappings
{
    const SITE_DEFAULT = 'UK';

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

    public function __construct(
        ActiveUserInterface $activeUserContainer,
        Helper $helper,
        DataTable $datatable,
        MarketplaceService $marketplaceService,
        InvoiceMappingService $invoiceMappingService,
        InvoiceMappingMapper $invoiceMappingMapper,
        AccountService $accountService,
        OrganisationUnitService $organisationUnitService
    ) {
        $this->activeUserContainer = $activeUserContainer;
        $this->helper = $helper;
        $this->datatable = $datatable;
        $this->marketplaceService = $marketplaceService;
        $this->invoiceMappingService = $invoiceMappingService;
        $this->invoiceMappingMapper = $invoiceMappingMapper;
        $this->accountService = $accountService;
        $this->organisationUnitService = $organisationUnitService;
    }

    public function saveInvoiceMappingFromPostData($postData)
    {
        if (!isset($postData['organisationUnitId'])) {
            $postData['organisationUnitId'] = $this->accountService->getOuIdFromAccountId($postData['accountId']);
        }

        try {
            if (!isset($postData['id'])) {
                throw new NotFound('No id - nothing to lookup');
            }

            $invoiceMapping = $this->invoiceMappingService->fetch($postData['id']);
            $entity = $this->invoiceMappingMapper->modifyEntityFromArray($invoiceMapping, $postData);
        } catch (NotFound $exception) {
            $entity = $this->invoiceMappingMapper->fromArray($postData);
        }

        return $this->invoiceMappingService->save($entity);
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
        $invoiceMappings = [];

        try {
            $existingMappings = $this->invoiceMappingService->fetchCollectionByFilter(
                (new InvoiceMappingFilter())->setAccountId($accounts->getIds())
            );

            /** @var InvoiceMapping $existingMapping */
            foreach ($existingMappings as $existingMapping) {
                $key = implode('-', [$existingMapping->getAccountId(), $existingMapping->getSite() ?: static::SITE_DEFAULT]);
                $invoiceMappings[$key] = $existingMapping;
            }
        } catch (NotFound $exception) {
            // No previous invoice mappings
        }

        $accountSiteMap = $this->getAccountSiteMap($accounts);

        /** @var Account $account */
        foreach ($accounts as $account) {
            $accountId = $account->getId();

            $accountSites = [static::SITE_DEFAULT];
            if (isset($accountSiteMap[$accountId]) && !empty($accountSiteMap[$accountId])) {
                $accountSites = $accountSiteMap[$accountId];
            }

            foreach ($accountSites as $site) {
                $key = implode('-', [$accountId, $site]);
                if (isset($invoiceMappings[$key])) {
                    continue;
                }

                $invoiceMappings[$key] = $this->invoiceMappingMapper->fromArray([
                    'organisationUnitId' => $account->getOrganisationUnitId(),
                    'accountId' => $account->getId(),
                    'site' => $site,
                ]);
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
            'tradingCompany' => $mainAccountRow ? $this->getTradingCompanyOptions($account, $tradingCompanies) : '',
            'assignedInvoice' => $this->getInvoiceOptions($invoiceMapping, $invoices),
            'sendViaEmail' => $this->getSendOptions($invoiceMapping->getSendViaEmail()),
            'sendToFba' => $account->getChannel() === 'amazon' ? $this->getSendOptions($invoiceMapping->getSendToFba()) : '',
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
                    'value' => '',
                    'selected' => $invoiceId === null,
                ],
            ],
        ];

        foreach ($invoices as $invoice) {
            $invoiceOptions['options'][] = [
                'title' => $invoice->getName(),
                'value' => $invoice->getId(),
                'selected' => $invoice->getId() === $invoiceId,
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

    protected function getSendOptions($option)
    {
        return [
            'options' => [
                [
                    'title' => 'Default',
                    'value' => '',
                    'selected' => $option === null
                ],
                [
                    'title' => 'On',
                    'value' => 'on',
                    'selected' => $option === true
                ],
                [
                    'title' => 'Off',
                    'value' => 'off',
                    'selected' => $option === false
                ],
            ],
        ];
    }

    /**
     * @return DataTable
     */
    public function getDatatable()
    {
        return $this->datatable;
    }
}
