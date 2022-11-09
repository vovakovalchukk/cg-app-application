<?php
namespace Products\Controller;

use CG_UI\View\Prototyper\JsonModelFactory;
use Products\Product\ProductFilters\Service;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

class ProductFiltersJsonController extends AbstractActionController
{
    const ROUTE_SAVE = 'Filter save';

    /** @var Service $service */
    protected $service;
    /** @var JsonModelFactory $jsonModelFactory */
    protected $jsonModelFactory;

    public function __construct(
        Service $service,
        JsonModelFactory $jsonModelFactory
    ) {
        $this->service = $service;
        $this->jsonModelFactory = $jsonModelFactory;
    }

    /**
     * @param $variables
     * @param $options
     * @return JsonModel
     */
    protected function newJsonModel($variables = null, $options = null): JsonModel
    {
        return $this->jsonModelFactory->newInstance($variables, $options);
    }

    public function saveFilterAction(): JsonModel
    {
        $filter = json_decode($this->params()->fromPost('filter', []), true);
        $this->service->save($filter);

        $jsonModel = $this->newJsonModel();
        return $jsonModel->setVariable('saved', true);
    }
}
