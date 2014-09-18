<?php

namespace Products\Controller;

use CG\Stdlib\Exception\Runtime\NotFound;
use Zend\Mvc\Controller\AbstractActionController;
use Products\Listing\Service as ListingService;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG\Listing\Unimported\Filter\Mapper as FilterMapper;
use CG\Listing\Unimported\Mapper as ListingMapper;

class ListingsJsonController extends AbstractActionController
{
    const ROUTE_AJAX = 'AJAX';

    protected $listingService;
    protected $jsonModelFactory;
    protected $filterMapper;
    protected $listingMapper;

    public function __construct(
        ListingService $listingService,
        JsonModelFactory $jsonModelFactory,
        FilterMapper $filterMapper,
        ListingMapper $listingMapper
    ) {
        $this->setListingService($listingService)
            ->setJsonModelFactory($jsonModelFactory)
            ->setFilterMapper($filterMapper)
            ->setListingMapper($listingMapper);
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
            $requestFilter = $this->params()->fromPost('filter', []);
            if (!isset($requestFilter['hidden']) || $requestFilter['hidden'] == 'No') {
                $requestFilter['hidden'] = [false];
            }

            $requestFilter = $this->getFilterMapper()->fromArray($requestFilter);
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
            $listings = $this->getListingService()->alterListingTable($listings, $this->getEvent());
            foreach ($listings as $listing) {
                $data['Records'][] = $listing;
            }
        } catch(NotFound $e) {
            //noop
        }
        return $this->getJsonModelFactory()->newInstance($data);
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
}
