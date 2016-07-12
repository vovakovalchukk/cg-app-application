<?php
namespace SetupWizard\Channels;

use CG\Channel\Creation\SetupViewInterface;
use CG\Zend\Stdlib\Mvc\Model\Helper\Url as UrlHelper;
use CG_UI\View\Prototyper\ViewModelFactory;
use SetupWizard\Controller\ChannelsController;
use SetupWizard\Module;

class ConnectViewFactory
{
    const CHANNEL_CONNECT_TEMPLATE_PATH = 'settings/channel/';

    /** @var ViewModelFactory $viewModelFactory */
    protected $viewModelFactory;
    /** @var UrlHelper $urlHelper */
    protected $urlHelper;
    /** @var array $services */
    protected $services = [];

    public function __construct(ViewModelFactory $viewModelFactory, UrlHelper $urlHelper)
    {
        $this->setViewModelFactory($viewModelFactory)->setUrlHelper($urlHelper);
    }

    public function addChannelService($channel, $region, SetupViewInterface $service)
    {
        if (!isset($this->services[$channel])) {
            $this->services[$channel] = [];
        }
        $this->services[$channel][$region] = $service;
    }

    public function __invoke($channel, $region)
    {
        if (isset($this->services[$channel][$region])) {
            return $this->getServiceView($channel, $region, $this->services[$channel][$region]);
        }
        return $this->getDefaultView($channel, $region);
    }

    protected function getServiceView($channel, $region, SetupViewInterface $service)
    {
        $route = implode('/', [Module::ROUTE, ChannelsController::ROUTE_CHANNELS, ChannelsController::ROUTE_CHANNEL_PICK]);
        return $service->getSetupView(null, $this->urlHelper->fromRoute($route));
    }

    protected function getDefaultView($channel, $region)
    {
        $template = static::CHANNEL_CONNECT_TEMPLATE_PATH . str_replace(['-', '_'], '', strtolower($channel));
        $view = $this->viewModelFactory->newInstance(
            [
                'region' => $region,
                'accountId' => null,
            ]
        );
        return $view->setTemplate($template);
    }

    /**
     * @return self
     */
    protected function setViewModelFactory(ViewModelFactory $viewModelFactory)
    {
        $this->viewModelFactory = $viewModelFactory;
        return $this;
    }

    /**
     * @return self
     */
    protected function setUrlHelper(UrlHelper $urlHelper)
    {
        $this->urlHelper = $urlHelper;
        return $this;
    }
} 
