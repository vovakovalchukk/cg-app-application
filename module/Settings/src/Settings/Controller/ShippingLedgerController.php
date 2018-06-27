<?php
namespace Settings\Controller;

use CG_UI\View\Prototyper\JsonModelFactory;
use Zend\Mvc\Controller\AbstractActionController;

class ShippingLedgerController extends AbstractActionController
{
    const ROUTE = 'Ledger';
    const ROUTE_TOPUP = 'Topup';
    const ROUTE_SAVE = 'Save';

    /** @var JsonModelFactory */
    protected $jsonModelFactory;

    public function __construct(JsonModelFactory $jsonModelFactory)
    {
        $this->jsonModelFactory = $jsonModelFactory;
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
        $accountId = $this->params()->fromRoute('account');
        $input = $this->params()->fromPost();
        // Dummy data to be replaced in TAC-121
        return $this->jsonModelFactory->newInstance([
            'success' => true,
            'error' => '',
        ]);
    }
}