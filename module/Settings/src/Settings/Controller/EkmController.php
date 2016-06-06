<?php
namespace Settings\Controller;

use Settings\Module;
use CG\Channel\Type as ChannelType;

class EkmController extends ChannelControllerAbstract
{
    const ROUTE_AJAX = 'ajax';

    public function indexAction()
    {
        $index = $this->getViewModelFactory()->newInstance();
        $index->setTemplate('settings/channel/ekm');
        $index->setVariable('isHeaderBarVisible', false);
        $index->setVariable('subHeaderHide', true);
        $index->setVariable('isSidebarVisible', false);
        $index->setVariable('accountId', $this->params()->fromQuery('accountId'));
        return $index;
    }

    public function saveAction()
    {
        $view = $this->getJsonModelFactory()->newInstance();
        $accountEntity = $this->getAccountCreationService()->connectAccount(
            $this->getActiveUserContainer()->getActiveUser()->getOrganisationUnitId(),
            $this->params()->fromPost('accountId'),
            $this->params()->fromPost()
        );
        $routeName = implode('/', [Module::ROUTE, ChannelController::ROUTE, ChannelController::ROUTE_CHANNELS, ChannelController::ROUTE_ACCOUNT]);
        $url = $this->plugin('url')->fromRoute($routeName, ["account" => $accountEntity->getId(), "type" => ChannelType::SALES]);
        $view->setVariable('redirectUrl', $url);
        return $view;
    }
}