<?php
namespace BigCommerce\Controller;

use BigCommerce\App\Service as BigCommerceAppService;
use Zend\Mvc\Controller\AbstractActionController;

class AppController extends AbstractActionController
{
    /** @var BigCommerceAppService $appService */
    protected $appService;

    public function __construct(BigCommerceAppService $appService)
    {
        $this->setAppService($appService);
    }

    public function loadAction()
    {
        return $this->appService->getAppView();
    }

    public function oauthAction()
    {
        $this->appService->processOauth(
            $this->url()->fromRoute(null, $this->params()->fromRoute(), ['force_canonical' => true]),
            $this->params()->fromQuery()
        );
        return $this->appService->getAppView();
    }

    /**
     * @return self
     */
    protected function setAppService(BigCommerceAppService $appService)
    {
        $this->appService = $appService;
        return $this;
    }
}
