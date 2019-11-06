<?php
namespace Settings\Controller;

use CG\Account\Shared\Entity as Account;
use CG\Channel\Type as ChannelType;
use CG\Ekm\Account\Connector\Rest as RestAccountConnector;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use Settings\Module;

class EkmController extends ChannelControllerAbstract implements LoggerAwareInterface
{
    use LogTrait;

    public const ROUTE_AJAX = 'ajax';
    protected const LOG_CODE = 'EkmController';
    protected const ERROR_DENIED = 'access_denied';

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

    /**
     * EKM redirects the user back here after an authentication attempt
     * either with a `code` or an `error` and always with 'state'
     */
    public function connectRestAccountAction(): void
    {
        $params = $this->params()->fromQuery();
        if (isset($params['error']) && $params['error'] != '') {
            $this->logConnectRestAccountError($params['error']);
            $this->redirectToSalesAccountsPage();
            return;
        }
        $currentUri = $this->getRequest()->getUri()->setQuery([]);
        $params['redirectUri'] = (string)$currentUri;
        $accountId = RestAccountConnector::getAccountIdFromState($params['state'] ?? '');
        $account = $this->getAccountCreationService()->connectAccount(
            $this->getActiveUserContainer()->getActiveUser()->getOrganisationUnitId(),
            $accountId,
            $params
        );
        $this->redirectToAccountPage($account);
    }

    protected function redirectToSalesAccountsPage(): void
    {
        $route = implode('/', [Module::ROUTE, ChannelController::ROUTE, ChannelController::ROUTE_CHANNELS]);
        $this->redirect()->toRoute($route, ['type' => ChannelType::SALES]);
    }

    protected function redirectToAccountPage(Account $account): void
    {
        $route = implode('/', [Module::ROUTE, ChannelController::ROUTE, ChannelController::ROUTE_CHANNELS, ChannelController::ROUTE_ACCOUNT]);
        $this->redirect()->toRoute($route, ['type' => ChannelType::SALES, 'account' => $account->getId()]);
    }

    protected function logConnectRestAccountError(string $error): void
    {
        if ($error == static::ERROR_DENIED) {
            $this->logNotice('EKM auth attempt was denied by the user', [], [static::LOG_CODE, 'Auth', 'Denied']);
        } else {
            $this->logWarning('EKM auth attempt returned an error: %s', [$error], [static::LOG_CODE, 'Auth', 'Error']);
        }
    }
}