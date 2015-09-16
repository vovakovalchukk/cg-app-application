<?php
namespace Orders\Controller;

use CG_UI\View\DataTable;
use CG_UI\View\Prototyper\ViewModelFactory;
use Orders\Module;
use Orders\Courier\Service;
use Zend\Mvc\Controller\AbstractActionController;

class CourierController extends AbstractActionController
{
    const ROUTE = 'Courier';
    const ROUTE_URI = '/courier';
    const ROUTE_REVIEW = 'Review';
    const ROUTE_REVIEW_URI = '/review';

    protected $viewModelFactory;
    protected $reviewTable;
    /** @var Service */
    protected $service;

    public function __construct(
        ViewModelFactory $viewModelFactory,
        DataTable $reviewTable,
        Service $service
    ) {
        $this->setViewModelFactory($viewModelFactory)
            ->setReviewTable($reviewTable)
            ->setService($service);
    }

    public function indexAction()
    {
        return $this->redirect()->toRoute(Module::ROUTE.'/'.static::ROUTE.'/'.static::ROUTE_REVIEW);
    }

    public function reviewAction()
    {
        $orderIds = $this->params('order', []);
$orderIds = ['2-286', '2-143']; // TEST
        $view = $this->viewModelFactory->newInstance();
        $this->prepReviewTable();

        $view->setVariable('orderIds', $orderIds);
        $view->addChild($this->getCourierSelectView(), 'courierSelect');
        $view->addChild($this->reviewTable, 'reviewTable');
        $view->addChild($this->getReviewContinueButton(), 'continueButton');
        $view->setVariable('isHeaderBarVisible', false);
        $view->setVariable('subHeaderHide', true);

        return $view;
    }

    protected function prepReviewTable()
    {
        $settings = $this->reviewTable->getVariable('settings');
        $settings->setSource(
            $this->url()->fromRoute(
                Module::ROUTE . '/' . static::ROUTE . '/' . static::ROUTE_REVIEW . '/' . CourierJsonController::ROUTE_REVIEW_LIST
            )
        );
        $settings->setTemplateUrlMap($this->mustacheTemplateMap('courierReview'));
    }

    protected function getCourierSelectView()
    {
        $courierOptions = $this->service->getCourierOptions();
        $view = $this->viewModelFactory->newInstance([
            'id' => 'courier-review-courier-select',
            'class' => 'courier-review-courier-select',
            'blankOption' => false,
            'searchField' => false,
            'options' => $courierOptions,
        ]);
        $view->setTemplate('elements/custom-select.mustache');
        return $view;
    }

    protected function getReviewContinueButton()
    {
        $view = $this->viewModelFactory->newInstance([
            'buttons' => [
                'value' => 'Continue',
                'id' => 'continue-button',
                'disabled' => false,
            ]
        ]);
        $view->setTemplate('elements/buttons.mustache');
        return $view;
    }

    protected function setViewModelFactory(ViewModelFactory $viewModelFactory)
    {
        $this->viewModelFactory = $viewModelFactory;
        return $this;
    }

    protected function setReviewTable(DataTable $reviewTable)
    {
        $this->reviewTable = $reviewTable;
        return $this;
    }

    protected function setService(Service $service)
    {
        $this->service = $service;
        return $this;
    }
}