<?php

namespace Products\Controller;

use CG\Stdlib\Exception\Runtime\NotFound;
use Zend\Mvc\Controller\AbstractActionController;
use Products\Listing\Service as ListingService;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG\Listing\Unimported\Filter\Mapper as FilterMapper;

class ListingsJsonController extends AbstractActionController
{
    const ROUTE_AJAX = 'AJAX';

    protected $listingService;
    protected $jsonModelFactory;
    protected $filterMapper;

    public function __construct(
        ListingService $listingService,
        JsonModelFactory $jsonModelFactory,
        FilterMapper $filterMapper
    ) {
        $this->setListingService($listingService)
            ->setJsonModelFactory($jsonModelFactory)
            ->setFilterMapper($filterMapper);
    }

    public function ajaxAction()
    {
        $view = $this->getJsonModelFactory()->newInstance();
        $requestFilter = $this->getFilterMapper()->fromArray($this->params()->fromPost('filter', []));
        $listingArray = [];
        try {
            $listings = $this->getListingService()->fetchListings($requestFilter);
            $listingArray = $listings->toArray();
        } catch(NotFound $e) {
            //noop
        }
        return $view->setVariable('listings', $listingArray);
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
}
