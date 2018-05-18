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
        $responseArray = [
            'isHeaderBarVisible' => false,
            'subHeaderHide' => true,
        ];
        $accessResponse = $this->service->isAccessAllowedForActiveUser();
        $responseArray = array_merge($responseArray, $accessResponse->toArray());

        if ($accessResponse->isAllowed()) {
            $credentials = $this->service->getCredentialsForActiveUser();
            $responseArray['credentialsKey'] = $credentials->getKey();
            $responseArray['credentialsSecret'] = $credentials->getSecret();
        }

        return $this->viewModelFactory->newInstance($responseArray);
    }
}
