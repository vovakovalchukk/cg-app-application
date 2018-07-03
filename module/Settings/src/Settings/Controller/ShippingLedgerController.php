<?php
namespace Settings\Controller;

use CG\Http\Exception\Exception3xx\NotModified;
use CG_UI\View\Prototyper\JsonModelFactory;
use Zend\Mvc\Controller\AbstractActionController;
Use CG\Billing\Shipping\Ledger\Service as ShippingLedgerService;
use Settings\Channel\Service as ChannelService;

class ShippingLedgerController extends AbstractActionController
{
    const ROUTE = 'Ledger';
    const ROUTE_TOPUP = 'Topup';
    const ROUTE_SAVE = 'Save';

    /** @var JsonModelFactory */
    protected $jsonModelFactory;
    /** @var ShippingLedgerService */
    protected $shippingLedgerService;
    /** @var ChannelService */
    protected $channelService;

    public function __construct(JsonModelFactory $jsonModelFactory, ShippingLedgerService $shippingLedgerService, ChannelService $channelService)
    {
        $this->jsonModelFactory = $jsonModelFactory;
        $this->shippingLedgerService = $shippingLedgerService;
        $this->channelService = $channelService;
    }

    public function topupAction()
    {
        $accountId = $this->params()->fromRoute('account');
        // Dummy data to be replaced in TAC-121
        return $this->jsonModelFactory->newInstance([
            'success' => true,
            'balance' => 10,
            'error' => '',
        ]);
    }

    public function saveAction()
    {
        $account = $this->channelService->getAccount($this->params()->fromRoute('account'));
        $shippingLedger = $this->shippingLedgerService->fetch($account->getId());

        $input = $this->params()->fromPost();
        $shippingLedger->setAutoTopUp($input['autoTopUp']);

        try {
            $this->shippingLedgerService->save($shippingLedger);
        } catch (NotModified $exception) {
            // Nothing to see here
        }

        // Dummy data to be replaced in TAC-121
        return $this->jsonModelFactory->newInstance([
            'success' => true,
            'error' => '',
        ]);
    }
}