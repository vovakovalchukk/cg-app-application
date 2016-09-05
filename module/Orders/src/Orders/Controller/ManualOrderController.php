<?php
namespace Orders\Controller;

use CG_UI\View\Prototyper\ViewModelFactory;
use CG_Usage\Exception\Exceeded as UsageExceeded;
use CG_Usage\Service as UsageService;
use Zend\Mvc\Controller\AbstractActionController;

class ManualOrderController extends AbstractActionController
{
    /** @var ViewModelFactory */
    protected $viewModelFactory;
    /** @var UsageService */
    protected $usageService;

    public function __construct(ViewModelFactory $viewModelFactory, UsageService $usageService)
    {
        $this->setViewModelFactory($viewModelFactory)
            ->setUsageService($usageService);
    }

    public function indexAction()
    {
        if ($this->usageService->hasUsageBeenExceeded()) {
            throw new UsageExceeded();
        }

        $view = $this->viewModelFactory->newInstance();
        // TODO: CGIV-7391
        return $view;
    }

    protected function setViewModelFactory(ViewModelFactory $viewModelFactory)
    {
        $this->viewModelFactory = $viewModelFactory;
        return $this;
    }

    protected function setUsageService(UsageService $usageService)
    {
        $this->usageService = $usageService;
        return $this;
    }
}