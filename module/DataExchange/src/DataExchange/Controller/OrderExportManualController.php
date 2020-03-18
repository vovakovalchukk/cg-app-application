<?php
namespace DataExchange\Controller;

use Application\Controller\AbstractJsonController;
use CG\Stdlib\DateTime as CGDateTime;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\Zend\Stdlib\Http\FileResponse;
use CG_UI\View\Prototyper\JsonModelFactory;
use DataExchange\Manual\OrderExporter;
use Zend\View\Model\JsonModel;

class OrderExportManualController extends AbstractJsonController implements LoggerAwareInterface
{
    use LogTrait;

    public const ROUTE_DOWNLOAD = 'Download';

    protected const LOG_CODE = 'OrderExportManualController';
    protected const LOG_MESSAGE_NO_ORDERS_FOUND = 'No orders found for filter %s';
    protected const LOG_MESSAGE_EXCEPTION = 'Caught unexpected exception while exporting orders for template ID %s and saved filter %s';

    /** @var OrderExporter */
    protected $orderExporter;

    public function __construct(OrderExporter $orderExporter, JsonModelFactory $jsonModelFactory)
    {
        parent::__construct($jsonModelFactory);
        $this->orderExporter = $orderExporter;
    }

    public function downloadAction()
    {
        $templateId = $this->params()->fromPost('templateId');
        $savedFilterName = $this->params()->fromPost('savedFilterName');
        $sendViaEmail = filter_var($this->params()->fromPost('sendViaEmail'), FILTER_VALIDATE_BOOLEAN);

        try {
            if ($sendViaEmail) {
                return $this->sendViaEmail($templateId, $savedFilterName);
            }

            return $this->downloadToBrowser($templateId, $savedFilterName);
        } catch (NotFound $exception) {
            $this->logWarningException($exception, static::LOG_MESSAGE_NO_ORDERS_FOUND, [$savedFilterName], static::LOG_CODE);
            return $this->buildErrorResponse('No orders found for the selected filter.');
        } catch (\Throwable $exception) {
            $this->logWarningException($exception, static::LOG_MESSAGE_EXCEPTION, [$templateId, $savedFilterName], static::LOG_CODE);
            return $this->buildErrorResponse('An error has occurred. Please try again or contact support if the problem persists.');
        }
    }

    protected function sendViaEmail(int $templateId, string $savedFilterName): JsonModel
    {
        $this->orderExporter->sendViaEmail($templateId, $savedFilterName);
        return $this->buildSuccessResponse();
    }

    protected function downloadToBrowser(int $templateId, string $savedFilterName): FileResponse
    {
        $fileContents = $this->orderExporter->download($templateId, $savedFilterName);
        $date = new CGDateTime();
        $filename = 'stock-' . $date->stdDateFormat() . '-' . $date->stdTimeFormat() . '.csv';
        return new FileResponse('text/csv', $filename, $fileContents);
    }
}