<?php
namespace Settings\Controller;

use Settings\Module;
use CG\Channel\Type as ChannelType;

class AmazonController extends ChannelControllerAbstract
{
    public function saveAction()
    {
        $accountEntity = $this->getAccountCreationService()->connectAccount(
            $this->getActiveUserContainer()->getActiveUser()->getOrganisationUnitId(),
            $this->params()->fromQuery('accountId'),
            array_merge($this->params()->fromQuery(), $this->params()->fromRoute())
        );
        $routeName = implode('/', [Module::ROUTE, ChannelController::ROUTE, ChannelController::ROUTE_CHANNELS, ChannelController::ROUTE_ACCOUNT]);
        $url = $this->plugin('url')->fromRoute($routeName, ["account" => $accountEntity->getId(), "type" => ChannelType::SALES]);
        $this->plugin('redirect')->toUrl($url);
        return false;
    }
}