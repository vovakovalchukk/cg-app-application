<?php
namespace DataExchange\Controller;

use CG\DataExchangeSchedule\Entity as Schedule;

class OrderExportController extends AbstractScheduleController
{
    public const ROUTE = 'StockExport';
    public const ROUTE_SAVE = 'Save';
    public const ROUTE_REMOVE = 'Remove';

    public function indexAction()
    {
        return $this->viewModelFactory->newInstance([
            'isHeaderBarVisible' => false,
            'subHeaderHide' => true,
            'orderExportSchedules' => $this->service->fetchOrderExportsForActiveUser(),
            'orderTemplateOptions' => $this->service->fetchOrderTemplateOptionsForActiveUser(),
            'fromAccountOptions' => $this->service->fetchEmailFromAccountOptionsForActiveUser(),
            'toAccountOptions' => $this->service->fetchEmailToAndFtpAccountOptionsForActiveUser(),
            'savedFilterOptions' => $this->service->fetchSavedFilterOptionsForActiveUser(),
        ]);
    }

    protected function saveForType(array $data): Schedule
    {
        return $this->service->saveOrderExportForActiveUser($data);
    }
}