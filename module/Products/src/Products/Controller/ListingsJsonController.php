<?php

namespace Products\Controller;

use CG\Stdlib\Exception\Runtime\NotFound;
use Zend\Mvc\Controller\AbstractActionController;
use Products\Listing\Service as ListingService;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG\Listing\Unimported\Filter\Mapper as FilterMapper;
use CG\Listing\Unimported\Mapper as ListingMapper;
use CG\Channel\ListingImportFactory;
use CG\Account\Client\Filter as AccountFilter;
use CG\Account\Client\Service as AccountService;
use CG\User\ActiveUserInterface;
use \GearmanClient;

class ListingsJsonController extends AbstractActionController
{
    const ROUTE_AJAX = 'AJAX';
    const ROUTE_REFRESH = 'refresh';
    const ACTIVE = 1;
    const DEFAULT_LIMIT = 'all';
    const DEFAULT_PAGE = 1;
    const DEFAULT_TYPE = 'sales';
    const ONE_SECOND_DELAY = 1;

    protected $listingService;
    protected $jsonModelFactory;
    protected $filterMapper;
    protected $listingMapper;
    protected $listingImportFactory;
    protected $accountService;
    protected $activeUserContainer;
    protected $gearmanClient;

    public function __construct(
        ListingService $listingService,
        JsonModelFactory $jsonModelFactory,
        FilterMapper $filterMapper,
        ListingMapper $listingMapper,
        ListingImportFactory $listingImportFactory,
        AccountService $accountService,
        ActiveUserInterface $activeUserContainer,
        \GearmanClient $gearmanClient
    ) {
        $this->setListingService($listingService)
            ->setJsonModelFactory($jsonModelFactory)
            ->setFilterMapper($filterMapper)
            ->setListingMapper($listingMapper)
            ->setListingImportFactory($listingImportFactory)
            ->setAccountService($accountService)
            ->setActiveUserContainer($activeUserContainer)
            ->setGearmanClient($gearmanClient);
    }

    public function ajaxAction()
    {
        $data = [
            'iTotalRecords' => 0,
            'iTotalDisplayRecords' => 0,
            'sEcho' => (int) $this->params()->fromPost('sEcho'),
            'Records' => [],
        ];
        try {
            $requestFilter = $this->getFilterMapper()->fromArray($this->params()->fromPost('filter', []));
            $limit = 'all';
            $page = 1;
            if ($this->params()->fromPost('iDisplayLength') > 0) {
                $limit = $this->params()->fromPost('iDisplayLength');
                $page += floor($this->params()->fromPost('iDisplayStart') / $limit);
            }
            $requestFilter->setPage($page)
                ->setLimit($limit);
            $listings = $this->getListingService()->fetchListings($requestFilter);
            $data['iTotalRecords'] = $data['iTotalDisplayRecords'] = (int) $listings->getTotal();
            foreach ($listings as $listing) {
                $data['Records'][] = $this->getListingMapper()->toDataTableArray($listing);
            }
        } catch(NotFound $e) {
            //noop
        }
        return $this->getJsonModelFactory()->newInstance($data);
    }

    public function refreshAction()
    {
        $view = $this->getJsonModelFactory()->newInstance();
        $filter = new AccountFilter();
        $filter->setActive(static::ACTIVE)
            ->setLimit(static::DEFAULT_LIMIT)
            ->setPage(static::DEFAULT_PAGE)
            ->setType(static::DEFAULT_TYPE)
            ->setOus($this->getActiveUserContainer()->getActiveUser()->getOuList());
        $accounts = $this->getAccountService()->fetchByFilter($filter);
        $gearmanJobs = [];
        foreach ($accounts as $account) {
            $importer = $this->getListingImportFactory()->createListingImport($account);
            $gearmanJobs[] = $importer($account);
        }
        do {
            sleep(static::ONE_SECOND_DELAY);
        } while($this->checkGearmanJobStatus($gearmanJobs));
        return $view;
    }

    protected function checkGearmanJobStatus(array $gearmanJobs)
    {
        foreach ($gearmanJobs as $gearmanJob) {
            if ($this->getGearmanClient()->jobStatus($gearmanJob)[0]) {
                return true;
            }
        }
        return false;
    }

    protected function setJsonModelFactory(JsonModelFactory $jsonModelFactory)
    {
        $this->jsonModelFactory = $jsonModelFactory;
        return $this;
    }

    protected function getJsonModelFactory()
    {
        return $this->jsonModelFactory;
    }

    protected function setListingService(ListingService $listingService)
    {
        $this->listingService = $listingService;
        return $this;
    }

    protected function getListingService()
    {
        return $this->listingService;
    }

    protected function setFilterMapper(FilterMapper $filterMapper)
    {
        $this->filterMapper = $filterMapper;
        return $this;
    }

    protected function getFilterMapper()
    {
        return $this->filterMapper;
    }

    protected function setListingMapper(ListingMapper $listingMapper)
    {
        $this->listingMapper = $listingMapper;
        return $this;
    }

    protected function getListingMapper()
    {
        return $this->listingMapper;
    }

    protected function setListingImportFactory(ListingImportFactory $listingImportFactory)
    {
        $this->listingImportFactory = $listingImportFactory;
        return $this;
    }

    protected function getListingImportFactory()
    {
        return $this->listingImportFactory;
    }

    protected function setAccountService(AccountService $accountService)
    {
        $this->accountService = $accountService;
        return $this;
    }

    protected function getAccountService()
    {
        return $this->accountService;
    }

    protected function setActiveUserContainer(ActiveUserInterface $activeUserContainer)
    {
        $this->activeUserContainer = $activeUserContainer;
        return $this;
    }

    protected function getActiveUserContainer()
    {
        return $this->activeUserContainer;
    }

    protected function setGearmanClient(\GearmanClient $gearmanClient)
    {
        $this->gearmanClient = $gearmanClient;
        return $this;
    }

    protected function getGearmanClient()
    {
        return $this->gearmanClient;
    }
}
