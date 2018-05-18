<?php
namespace Settings\Controller;

use CG_UI\View\Prototyper\ViewModelFactory;
use Settings\Api\Service;

class ApiController extends AdvancedController
{
    protected $viewModelFactory;
    protected $service;

    const ROUTE_API = 'API';

    public function __construct(
        ViewModelFactory $viewModelFactory,
        Service $service
    ) {
        $this->viewModelFactory = $viewModelFactory;
        $this->service = $service;
    }

    public function detailsAction()
    {
        $credentials = $this->service->getCredentialsForActiveUser();

        return $this->viewModelFactory->newInstance([
            'credentialsKey' => $credentials->getKey(),
            'credentialsSecret' => $credentials->getSecret(),
            'isHeaderBarVisible' => false,
            'subHeaderHide' => true,
        ]);
    }
}
