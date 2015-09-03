<?php

namespace Products\Controller;

use ArrayObject;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\PageLimit;
use Zend\Mvc\Controller\AbstractActionController;
use Products\Listing\Service as ListingService;
use Products\Listing\Filter\Service as FilterService;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG\Listing\Unimported\Filter\Mapper as FilterMapper;
use CG\Listing\Unimported\Mapper as ListingMapper;

class ListingsJsonController extends AbstractActionController
{
    const ROUTE_AJAX = 'AJAX';
    const ROUTE_HIDE = 'HIDE';
    const ROUTE_REFRESH = 'refresh';
    const ROUTE_IMPORT = 'import';

    protected $listingService;
    protected $jsonModelFactory;
    protected $filterMapper;
    protected $listingMapper;
    protected $filterService;

    public function __construct(
        ListingService $listingService,
        JsonModelFactory $jsonModelFactory,
        FilterMapper $filterMapper,
        ListingMapper $listingMapper,
        FilterService $filterService
    ) {
        $this->setListingService($listingService)
            ->setJsonModelFactory($jsonModelFactory)
            ->setFilterMapper($filterMapper)
            ->setListingMapper($listingMapper)
            ->setFilterService($filterService);
    }

    protected function getPageLimit()
    {
        $pageLimit = new PageLimit();

        if ($this->params()->fromPost('iDisplayLength') > 0) {
            $pageLimit
                ->setLimit($this->params()->fromPost('iDisplayLength'))
                ->setPageFromOffset($this->params()->fromPost('iDisplayStart'));
        }

        return $pageLimit;
    }

    protected function getDefaultJsonData()
    {
        return new ArrayObject(
            [
                'iTotalRecords' => 0,
                'iTotalDisplayRecords' => 0,
                'sEcho' => (int) $this->params()->fromPost('sEcho'),
                'Records' => [],
            ]
        );
    }

    public function ajaxAction()
    {
        $data = $this->getDefaultJsonData();
        $pageLimit = $this->getPageLimit();

        try {
            $requestFilter = $this->params()->fromPost('filter', []);

            if (!isset($requestFilter['hidden'])) {
                $requestFilter['hidden'] = [false];
            }

            foreach ($requestFilter['hidden'] as $index => $hidden) {
                if ($hidden == 'No') {
                    $requestFilter['hidden'][$index] = false;
                }
            }

            $requestFilter = $this->getFilterMapper()->fromArray($requestFilter)
                ->setPage($pageLimit->getPage())
                ->setLimit($pageLimit->getLimit());

            $this->getFilterService()->setPersistentFilter($requestFilter);

            // Must reformat dates *after* persisting otherwise it'll happen again when its reloaded
            if ($requestFilter->getCreatedDateFrom()) {
                $requestFilter->setCreatedDateFrom($this->dateFormatInput($requestFilter->getCreatedDateFrom()));
            }
            if ($requestFilter->getCreatedDateTo()) {
                $requestFilter->setCreatedDateTo($this->dateFormatInput($requestFilter->getCreatedDateTo()));
            }

            $listings = $this->getListingService()->fetchListings($requestFilter);
            $data['iTotalRecords'] = $data['iTotalDisplayRecords'] = (int) $listings->getTotal();
            $listings = $this->getListingService()->alterListingTable($listings, $this->getEvent());

            foreach ($pageLimit->getPageData($listings) as $listing) {
                $data['Records'][] = $listing;
            }
        } catch(NotFound $e) {
            //noop
        }

        return $this->getJsonModelFactory()->newInstance($data);
    }

    public function hideAction()
    {
        $view = $this->getJsonModelFactory()->newInstance();

        $listingIds = $this->params()->fromPost('listingIds');
        if (empty($listingIds)){
            $view->setVariable('hidden', false);
            return $view;
        }

        $this->getListingService()->hideListingsById($listingIds);
        $view->setVariable('hidden', true);
        return $view;
    }

    public function refreshAction()
    {
        $view = $this->getJsonModelFactory()->newInstance();
        $this->getListingService()->refresh();
        return $view;
    }

    public function importAction()
    {
        $view = $this->getJsonModelFactory()->newInstance();
        $listingIds = $this->params()->fromPost('listingIds');
        if (empty($listingIds)){
            $view->setVariable('import', false);
            return $view;
        }
        $this->getListingService()->importListingsById($listingIds);
        $view->setVariable('import', true);
        return $view;
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

    protected function getFilterService()
    {
        return $this->filterService;
    }

    protected function setFilterService($filterService)
    {
        $this->filterService = $filterService;
        return $this;
    }
}
