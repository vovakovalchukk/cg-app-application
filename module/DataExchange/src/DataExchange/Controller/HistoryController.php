<?php
namespace DataExchange\Controller;

use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\Zend\Stdlib\Http\FileResponse;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG_UI\View\Prototyper\ViewModelFactory;
use DataExchange\History\Service as HistoryService;
use Zend\Mvc\Controller\AbstractActionController;

class HistoryController extends AbstractActionController implements LoggerAwareInterface
{
    use LogTrait;

    /** @var ViewModelFactory */
    protected $viewModelFactory;
    /** @var JsonModelFactory */
    protected $jsonModelFactory;
    /** @var HistoryService */
    protected $historyService;

    public const ROUTE = 'History';
    public const ROUTE_FETCH = 'HistoryFetch';
    public const ROUTE_FILES = 'HistoryFiles';
    public const ROUTE_STOP = 'HistoryStop';

    private const MIME_TYPE = 'text/csv';

    public function __construct(
        ViewModelFactory $viewModelFactory,
        JsonModelFactory $jsonModelFactory,
        HistoryService $historyService
    ) {
        $this->viewModelFactory = $viewModelFactory;
        $this->jsonModelFactory = $jsonModelFactory;
        $this->historyService = $historyService;
    }

    public function indexAction()
    {
        return $this->viewModelFactory->newInstance([
            'isHeaderBarVisible' => false,
            'subHeaderHide' => true,
        ]);
    }

    public function fetchAction()
    {
        $limit = $this->params()->fromPost('limit', HistoryService::DEFAULT_LIMIT);
        $page = $this->params()->fromPost('page', HistoryService::DEFAULT_PAGE);

        try {
            $histories = $this->historyService->fetchForActiveUser($limit, $page);
        } catch (\Throwable $e) {
            $this->logError($e);
            $histories = [];
        }

        return $this->jsonModelFactory->newInstance([
            'histories' => $histories
        ]);
    }

    public function filesAction()
    {
        $historyId = $this->params()->fromRoute('historyId');
        $fileType = $this->params()->fromRoute('fileType');

        try {
            [$fileName, $fileContents] = $this->historyService->fetchFile($historyId, $fileType);
            return new FileResponse(static::MIME_TYPE, $fileName, $fileContents);
        } catch (NotFound $e) {
            return $this->jsonModelFactory->newInstance([
                'success' => false,
                'message' => 'The requested file couldn\'t be found.'
            ]);
        } catch (\Throwable $e) {
            $this->logError($e);
            return $this->jsonModelFactory->newInstance([
                'success' => false
            ]);
        }
    }

    public function stopAction()
    {
        $historyId = (int) $this->params()->fromPost('historyId', 0);
        try {
            $historyArray = $this->historyService->stopSchedule($historyId);
            return $this->jsonModelFactory->newInstance([
                'success' => true,
                'history' => $historyArray
            ]);
        } catch (\Throwable $e) {
            $this->logError($e);
            return $this->jsonModelFactory->newInstance([
                'success' => false
            ]);
        }
    }
}
