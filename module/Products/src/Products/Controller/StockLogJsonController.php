<?php
namespace Products\Controller;

use CG\Stock\Audit\Combined\Filter;
use CG\Stock\Audit\Combined\Filter\Mapper as FilterMapper;
use CG_UI\View\Prototyper\JsonModelFactory;
use Products\Stock\Log\FilterManager;
use Products\Stock\Log\Service;
use Zend\Mvc\Controller\AbstractActionController;

class StockLogJsonController extends AbstractActionController
{
    const ROUTE_AJAX = 'AJAX';

    /** @var JsonModelFactory */
    protected $jsonModelFactory;
    /** @var FilterMapper */
    protected $filterMapper;
    /** @var FilterManager */
    protected $filterManager;
    /** @var Service */
    protected $service;

    public function __construct(
        JsonModelFactory $jsonModelFactory,
        FilterMapper $filterMapper,
        FilterManager $filterManager,
        Service $service
    ) {
        $this->setJsonModelFactory($jsonModelFactory)
            ->setFilterMapper($filterMapper)
            ->setFilterManager($filterManager)
            ->setService($service);
    }

    public function ajaxAction()
    {
        $data = $this->getDefaultJsonData();
        $productId = $this->params()->fromRoute('productId');
        $productDetails = $this->service->getProductDetails($productId);
        $requestFilter = $this->params()->fromPost('filter', []);
        if (!isset($requestFilter['sku']) || $requestFilter['sku'] == '') {
            $requestFilter['sku'] = [$productDetails['sku']];
        }

        $filter = $this->filterMapper->fromArray($requestFilter)
            ->setPage(1)
            ->setLimit('all');
        $this->filterManager->setPersistentFilter($filter);

        // Must reformat dates *after* persisting otherwise it'll happen again when its reloaded
        $this->formatDates($filter);

        $stocklogs = $this->service->fetchCollectionByFilter($filter);
        $data['iTotalRecords'] = $data['iTotalDisplayRecords'] = (int)$stocklogs->getTotal();
        $data['Records'] = $this->service->stockLogsToUiData($stocklogs, $this->getEvent());

        return $this->jsonModelFactory->newInstance($data);
    }

    protected function getDefaultJsonData()
    {
        return [
            'iTotalRecords' => 0,
            'iTotalDisplayRecords' => 0,
            'sEcho' => (int) $this->params()->fromPost('sEcho'),
            'Records' => [],
        ];
    }

    protected function formatDates(Filter $filter)
    {
        if ($filter->getDateTimeFrom()) {
            $filter->setDateTimeFrom($this->dateFormatInput($filter->getDateTimeFrom()));
        }
        if ($filter->getDateTimeTo()) {
            $filter->setDateTimeTo($this->dateFormatInput($filter->getDateTimeTo()));
        }
    }

    protected function setJsonModelFactory(JsonModelFactory $jsonModelFactory)
    {
        $this->jsonModelFactory = $jsonModelFactory;
        return $this;
    }

    protected function setFilterMapper(FilterMapper $filterMapper)
    {
        $this->filterMapper = $filterMapper;
        return $this;
    }

    protected function setFilterManager(FilterManager $filterManager)
    {
        $this->filterManager = $filterManager;
        return $this;
    }

    protected function setService(Service $service)
    {
        $this->service = $service;
        return $this;
    }
}