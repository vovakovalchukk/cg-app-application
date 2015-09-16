<?php
namespace Orders\Controller;

use CG_UI\View\DataTable;
use CG_UI\View\Prototyper\ViewModelFactory;
use Orders\Module;
use Orders\Courier\Service;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class CourierController extends AbstractActionController
{
    const ROUTE = 'Courier';
    const ROUTE_URI = '/courier';
    const ROUTE_REVIEW = 'Review';
    const ROUTE_REVIEW_URI = '/review';
    const ROUTE_SPECIFICS = 'Specifics';
    const ROUTE_SPECIFICS_URI = '/specifics';

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
        $orderIds = $this->params()->fromPost('order', []);
        $view = $this->viewModelFactory->newInstance();
        $this->prepReviewTable();

        $view->setVariable('orderIds', $orderIds);
        $view->setVariable('specificsUrl', $this->url()->fromRoute(Module::ROUTE.'/'.static::ROUTE.'/'.static::ROUTE_SPECIFICS));
        $view->addChild($this->getCourierSelectView(), 'courierSelect');
        $this->addCourierServiceViews($view);
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

    protected function addCourierServiceViews(ViewModel $view)
    {
        $courierServiceOptions = $this->service->getCourierServiceOptions();
        foreach ($courierServiceOptions as $accountId => $options)
        {
            $optionsView = $this->getCourierServiceView($accountId, $options);
            $view->addChild($optionsView, 'serviceSelects', true);
        }
    }

    protected function getCourierServiceView($courierId, array $options)
    {
        $view = $this->viewModelFactory->newInstance([
            'id' => 'courier-review-service-select-'.$courierId,
            'class' => 'courier-review-courier-select',
            'blankOption' => false,
            'searchField' => false,
            'options' => $options,
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