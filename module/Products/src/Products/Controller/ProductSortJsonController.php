<?php
namespace Products\Controller;

use CG\User\ActiveUserInterface;
use CG_UI\View\Prototyper\JsonModelFactory;
use Products\Product\ProductSort\Service;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

class ProductSortJsonController extends AbstractActionController
{
    const ROUTE_SAVE = 'Sort save';

    /** @var Service $service */
    protected $service;
    /** @var JsonModelFactory $jsonModelFactory */
    protected $jsonModelFactory;
    /** @var ActiveUserInterface */
    protected $activeUserContainer;

    public function __construct(
        Service $service,
        JsonModelFactory $jsonModelFactory,
        ActiveUserInterface $activeUserContainer
    ) {
        $this->service = $service;
        $this->jsonModelFactory = $jsonModelFactory;
        $this->activeUserContainer = $activeUserContainer;
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

    public function saveSortAction(): JsonModel
    {
        $rootOuId = $this->activeUserContainer->getActiveUserRootOrganisationUnitId();
        $sortingData = json_encode($this->params()->fromPost('sortingData', []));
        $currentUserOnly = $this->params()->fromPost('currentUserOnly', "true");
        $filterData = [
            'data' => $sortingData,
            'userId'  => $currentUserOnly == "true" ? $this->activeUserContainer->getActiveUser()->getId() : null,
            'organisationUnitId' => $rootOuId,
        ];
        $this->service->save($filterData);

        $jsonModel = $this->newJsonModel();
        return $jsonModel->setVariable('saved', true);
    }
}
