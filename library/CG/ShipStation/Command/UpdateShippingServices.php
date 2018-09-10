<?php
namespace CG\ShipStation\Command;

use CG\Account\Client\Service as AccountService;
use CG\ShipStation\Carrier\AccountDecider\Factory as AccountDeciderFactory;
use CG\ShipStation\Carrier\AccountDeciderInterface;
use CG\ShipStation\ShippingService\Service as ShippingServiceService;

class UpdateShippingServices
{
    /** @var AccountService */
    protected $accountService;
    /** @var AccountDeciderFactory */
    protected $accountDeciderFactory;
    /** @var ShippingServiceService */
    protected $shippingServiceService;

    public function __construct(
        AccountService $accountService,
        AccountDeciderFactory $accountDeciderFactory,
        ShippingServiceService $shippingServiceService
    ) {
        $this->accountService = $accountService;
        $this->accountDeciderFactory = $accountDeciderFactory;
        $this->shippingServiceService = $shippingServiceService;
    }

    public function __invoke(int $accountId)
    {
        $shippingAccount = $this->accountService->fetch($accountId);
        /** @var AccountDeciderInterface $accountDecider */
        $accountDecider = ($this->accountDeciderFactory)($shippingAccount->getChannel());
        $shipStationAccount = $accountDecider->getShipStationAccountForRequests($shippingAccount);
        $shippingAccount = $accountDecider->getShippingAccountForRequests($shippingAccount);

        $this->shippingServiceService->fetchShippingServicesAndSaveToAccount($shippingAccount, $shipStationAccount);
    }
}