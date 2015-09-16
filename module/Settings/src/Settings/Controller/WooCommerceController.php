<?php
namespace Settings\Controller;

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
} 
