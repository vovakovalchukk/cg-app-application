<?php
namespace Products\Controller;

use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\OrganisationUnit\ServiceInterface;
use CG\User\ActiveUserInterface;
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

    public function saveFilterAction(): JsonModel
    {
        $rootOuId = $this->activeUserContainer->getActiveUserRootOrganisationUnitId();
        $filters = json_encode($this->params()->fromPost('filters', []));
        $currentUserOnly = $this->params()->fromPost('currentUserOnly', "true");
        $filterData = [
            'filters' => $filters,
            'userId'  => $currentUserOnly == "true" ? $this->activeUserContainer->getActiveUser()->getId() : null,
            'organisationUnitId' => $rootOuId,
            'defaultFilter' => true,
        ];
        $this->service->save($filterData);

        $jsonModel = $this->newJsonModel();
        return $jsonModel->setVariable('saved', true);
    }
}
