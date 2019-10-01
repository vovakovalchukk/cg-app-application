<?php
namespace DataExchange\Controller;

use CG\DataExchangeSchedule\Entity as Schedule;

class StockExportController extends AbstractScheduleController
{
    public const ROUTE = 'StockExport';
    public const ROUTE_SAVE = 'Save';
    public const ROUTE_REMOVE = 'Remove';

    public function indexAction()
    {
        return $this->viewModelFactory->newInstance([
            'isHeaderBarVisible' => false,
            'subHeaderHide' => true,
            'stockExportSchedules' => $this->service->fetchStockExportsForActiveUser(),
            'stockTemplateOptions' => $this->service->fetchStockTemplateOptionsForActiveUser(),
            'fromAccountOptions' => $this->service->fetchEmailFromAccountOptionsForActiveUser(),
            'toAccountOptions' => $this->service->fetchEmailToAndFtpAccountOptionsForActiveUser(),
        ]);
    }

    protected function saveForType(array $data): Schedule
    {
        return $this->service->saveStockExportForActiveUser($data);
    }
}