<?php
namespace DataExchange\Controller;

use CG_UI\View\Prototyper\JsonModelFactory;
use DataExchange\Manual\OrderTrackingImporter;
use Zend\Mvc\Controller\AbstractActionController;

class OrderTrackingImportManualController extends AbstractActionController
{
    public const ROUTE_UPLOAD = 'Upload';

    /** @var OrderTrackingImporter */
    protected $orderTrackingImporter;
    /** @var JsonModelFactory */
    protected $jsonModelFactory;

    public function __construct(OrderTrackingImporter $orderTrackingImporter, JsonModelFactory $jsonModelFactory)
    {
        $this->orderTrackingImporter = $orderTrackingImporter;
        $this->jsonModelFactory = $jsonModelFactory;
    }

    public function uploadAction()
    {
        $templateId = $this->params()->fromPost('templateId');
        $uploadFile = $this->params()->fromPost('uploadFile');

        ($this->orderTrackingImporter)($templateId, $uploadFile);

        return $this->jsonModelFactory->newInstance(['success' => true]);
    }
}