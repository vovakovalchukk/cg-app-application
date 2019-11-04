<?php
namespace DataExchange\Controller;

use CG_UI\View\Prototyper\JsonModelFactory;
use CG\Stdlib\DateTime as CGDateTime;
use CG\Zend\Stdlib\Http\FileResponse;
use DataExchange\Manual\StockExporter;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

class StockExportManualController extends AbstractActionController
{
    public const ROUTE_DOWNLOAD = 'Download';

    /** @var StockExporter */
    protected $stockExporter;
    /** @var JsonModelFactory */
    protected $jsonModelFactory;

    public function __construct(StockExporter $stockExporter, JsonModelFactory $jsonModelFactory)
    {
        $this->stockExporter = $stockExporter;
        $this->jsonModelFactory = $jsonModelFactory;
    }

    public function downloadAction()
    {
        $templateId = $this->params()->fromPost('templateId');
        $sendViaEmail = filter_var($this->params()->fromPost('sendViaEmail'), FILTER_VALIDATE_BOOLEAN);
        if ($sendViaEmail) {
            return $this->sendViaEmail($templateId);
        }
        return $this->downloadToBrowser($templateId);
    }

    protected function sendViaEmail(int $templateId): JsonModel
    {
        $this->stockExporter->sendViaEmail($templateId);
        return $this->jsonModelFactory->newInstance(['success' => true]);
    }

    protected function downloadToBrowser(int $templateId): FileResponse
    {
        $fileContents = $this->stockExporter->download($templateId);
        $date = new CGDateTime();
        $filename = 'stock-' . $date->stdDateFormat() . '-' . $date->stdTimeFormat() . '.csv';
        return new FileResponse('text/csv', $filename, $fileContents);
    }
}