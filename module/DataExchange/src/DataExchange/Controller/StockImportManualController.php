<?php
namespace DataExchange\Controller;

use CG_UI\View\Prototyper\JsonModelFactory;
use DataExchange\Manual\StockImporter;
use Zend\Mvc\Controller\AbstractActionController;

class StockImportManualController extends AbstractActionController
{
    public const ROUTE_UPLOAD = 'Upload';

    /** @var StockImporter */
    protected $stockImporter;
    /** @var JsonModelFactory */
    protected $jsonModelFactory;

    public function __construct(StockImporter $stockImporter, JsonModelFactory $jsonModelFactory)
    {
        $this->stockImporter = $stockImporter;
        $this->jsonModelFactory = $jsonModelFactory;
    }

    public function uploadAction()
    {
        $templateId = $this->params()->fromPost('templateId');
        $action = $this->params()->fromPost('action');
        $uploadFile = $this->params()->fromPost('uploadFile');

        ($this->stockImporter)($templateId, $action, $uploadFile);

        return $this->jsonModelFactory->newInstance(['success' => true]);
    }
}