<?php
namespace Settings\Controller;

use CG\Account\Shared\Entity as Account;
use CG\Channel\Type as ChannelType;
use Settings\Module;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class WooCommerceController extends ChannelControllerAbstract
{
    const ROUTE_AJAX = 'ajax';

    public function indexAction()
    {
        /** @var ViewModel $index */
        $index = $this->getViewModelFactory()->newInstance();
        $index->setTemplate('settings/channel/woocommerce');
        $index->setVariable('isHeaderBarVisible', false);
        $index->setVariable('subHeaderHide', true);
        $index->setVariable('isSidebarVisible', false);
        $index->setVariable('accountId', $this->params()->fromQuery('accountId'));
        return $index;
    }

    public function saveAction()
    {
        /** @var JsonModel $save */
        $save = $this->getJsonModelFactory()->newInstance();
        $account = $this->getAccount();
        $route = implode('/', [Module::ROUTE, ChannelController::ROUTE, ChannelController::ROUTE_CHANNELS, ChannelController::ROUTE_ACCOUNT]);
        return $save->setVariable(
            'redirectUrl',
            $this->url()->fromRoute($route, ['type' => ChannelType::SALES, 'account' => $account->getId()])
        );
    }

    /**
     * @return Account
     */
    protected function getAccount()
    {
        return $this->getAccountCreationService()->connectAccount(
            $this->getActiveUser()->getOrganisationUnitId(),
            $this->params()->fromPost('accountId'),
            $this->params()->fromPost()
        );
    }
} 
