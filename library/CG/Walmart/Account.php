<?php
namespace CG\Walmart;

use CG\Account\Client\Entity as AccountEntity;
use CG\Channel\AccountInterface;
use CG\Zend\Stdlib\Mvc\Model\Helper\Url as UrlHelper;
use Walmart\Controller\AccountController;
use Walmart\Module;

class Account implements AccountInterface
{
    /** @var UrlHelper */
    protected $urlHelper;

    public function __construct(UrlHelper $urlHelper)
    {
        $this->urlHelper = $urlHelper;
    }

    public function getInitialisationUrl(AccountEntity $account, $route, array $routeVariables = [])
    {
        return $this->urlHelper->fromRoute(
            Module::ROUTE . '/' . AccountController::ROUTE_SETUP,
            [],
            ['force_canonical' => true]
        );
    }
}