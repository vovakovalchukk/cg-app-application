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
        $index->addChild($this->getUsernameView(), 'username');
        $index->addChild($this->getPasswordView(), 'password');
        $index->addChild($this->getLinkAccountView(), 'linkAccount');
        $index->addChild($this->getGoBackView(), 'goBack');
        return $index;
    }

    protected function getUsernameView()
    {
        $username = $this->getViewModelFactory()->newInstance([
            'name' => 'ekm-username',
            'id' => 'ekm-username'
        ]);
        $username->setTemplate('elements/text.mustache');
        return $username;
    }

    protected function getPasswordView()
    {
        $password = $this->getViewModelFactory()->newInstance([
            'name' => 'ekm-password',
            'id' => 'ekm-password',
            'type' => 'password'
        ]);
        $password->setTemplate('elements/text.mustache');
        return $password;
    }

    protected function getLinkAccountView()
    {
        $linkAccount = $this->getViewModelFactory()->newInstance([
            'buttons' => true,
            'value' => 'Link Account',
            'id' => 'ekm-link-account'
        ]);
        $linkAccount->setTemplate('elements/buttons.mustache');
        return $linkAccount;
    }

    protected function getGoBackView()
    {
        $linkAccount = $this->getViewModelFactory()->newInstance([
            'buttons' => true,
            'value' => 'Go Back',
            'id' => 'ekm-go-back'
        ]);
        $linkAccount->setTemplate('elements/buttons.mustache');
        return $linkAccount;
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