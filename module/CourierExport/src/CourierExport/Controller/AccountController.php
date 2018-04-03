<?php
namespace CourierExport\Controller;

use CG\CourierExport\Provider;
use Settings\Module as Settings;
use Settings\Controller\ChannelController as SettingsChannelController;
use Zend\Mvc\Controller\AbstractActionController;

class AccountController extends AbstractActionController
{
    const ROUTE_SETUP = 'CourierExportAccountSetup';

    /** @var Provider */
    protected $provider;

    public function __construct(Provider $provider)
    {
        $this->provider = $provider;
    }

    public function setupAction()
    {
        try {
            $account = $this->provider->connectAccount(
                $this->params()->fromRoute('channel'),
                $this->params()->fromRoute('account')
            );
        } catch (\InvalidArgumentException $exception) {
            return $this->notFoundAction();
        }

        return $this->redirect()->toRoute(
            implode('/', [
                Settings::ROUTE,
                SettingsChannelController::ROUTE,
                SettingsChannelController::ROUTE_CHANNELS,
                SettingsChannelController::ROUTE_ACCOUNT,
            ]),
            ['type' => $account->getType(), 'account' => $account->getId()]
        );
    }
}