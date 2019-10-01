<?php
namespace DataExchange\Controller;

use CG\DataExchangeSchedule\Entity as Schedule;

class StockImportController extends AbstractScheduleController
{
    public const ROUTE = 'StockImport';
    public const ROUTE_SAVE = 'Save';
    public const ROUTE_REMOVE = 'Remove';

    public function indexAction()
    {
        return $this->viewModelFactory->newInstance([
            'isHeaderBarVisible' => false,
            'subHeaderHide' => true,
            'stockImportSchedules' => $this->service->fetchStockImportsForActiveUser(),
            'stockTemplateOptions' => $this->service->fetchStockTemplateOptionsForActiveUser(),
            'actionOptions' => $this->service->getStockImportActionOptions(),
            'fromAccountOptions' => $this->service->fetchFtpAccountOptionsForActiveUser(),
        ]);
    }

    protected function saveForType(array $data): Schedule
    {
        return $this->service->saveStockImportForActiveUser($data);
    }
}