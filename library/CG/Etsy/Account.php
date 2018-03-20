<?php
namespace CG\Etsy;

use CG\Account\Client\Entity as AccountEntity;
use CG\Channel\AccountInterface;
use CG\Zend\Stdlib\Mvc\Model\Helper\Url as UrlHelper;
use Etsy\Controller\AccountController;

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
            AccountController::ROUTE_INITIALISE,
            ['account' => $account->getId()],
            ['force_canonical' => true]
        );
    }
}