<?php
namespace DataExchange\Controller;

use CG\DataExchangeSchedule\Entity as Schedule;

class OrderTrackingImportController extends AbstractScheduleController
{
    public const ROUTE = 'OrderTrackingImport';
    public const ROUTE_SAVE = 'Save';
    public const ROUTE_REMOVE = 'Remove';

    public function indexAction()
    {
        return $this->viewModelFactory->newInstance([
            'isHeaderBarVisible' => false,
            'subHeaderHide' => true,
            'orderTrackingImportSchedules' => $this->service->fetchOrderTrackingImportsForActiveUser(),
            'orderTrackingTemplateOptions' => $this->service->fetchOrderTrackingTemplateOptionsForActiveUser(),
            'fromAccountOptions' => $this->service->fetchFtpAccountOptionsForActiveUser(),
        ]);
    }

    protected function saveForType(array $data): Schedule
    {
        return $this->service->saveOrderTrackingImportForActiveUser($data);
    }
}