<?php
namespace DataExchange\Controller;

use CG_UI\View\Prototyper\JsonModelFactory;
use CG\Stdlib\DateTime as CGDateTime;
use CG\Zend\Stdlib\Http\FileResponse;
use DataExchange\Manual\OrderExporter;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

class OrderExportManualController extends AbstractActionController
{
    public const ROUTE_DOWNLOAD = 'Download';

    /** @var OrderExporter */
    protected $orderExporter;
    /** @var JsonModelFactory */
    protected $jsonModelFactory;

    public function __construct(OrderExporter $orderExporter, JsonModelFactory $jsonModelFactory)
    {
        $this->orderExporter = $orderExporter;
        $this->jsonModelFactory = $jsonModelFactory;
    }

    public function downloadAction()
    {
        $templateId = $this->params()->fromPost('templateId');
        $savedFilterName = $this->params()->fromPost('savedFilterName');
        $orderBy = $this->params()->fromPost('orderBy');
        $sendViaEmail = filter_var($this->params()->fromPost('sendViaEmail'), FILTER_VALIDATE_BOOLEAN);
        if ($sendViaEmail) {
            return $this->sendViaEmail($templateId, $savedFilterName, $orderBy);
        }
        return $this->downloadToBrowser($templateId, $savedFilterName, $orderBy);
    }

    protected function sendViaEmail(int $templateId, string $savedFilterName, ?string $orderBy = null): JsonModel
    {
        $this->orderExporter->sendViaEmail($templateId, $savedFilterName, $orderBy);
        return $this->jsonModelFactory->newInstance(['success' => true]);
    }

    protected function downloadToBrowser(int $templateId, string $savedFilterName, ?string $orderBy = null): FileResponse
    {
        $fileContents = $this->orderExporter->download($templateId, $savedFilterName, $orderBy);
        $date = new CGDateTime();
        $filename = 'stock-' . $date->stdDateFormat() . '-' . $date->stdTimeFormat() . '.csv';
        return new FileResponse('text/csv', $filename, $fileContents);
    }
}