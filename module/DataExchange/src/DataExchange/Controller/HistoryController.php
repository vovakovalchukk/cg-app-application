<?php
namespace DataExchange\Controller;

use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG_UI\View\Prototyper\ViewModelFactory;
use DataExchange\History\Service\Service as HistoryService;
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

    }

    public function stopAction()
    {

    }
}
