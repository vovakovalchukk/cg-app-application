<?php
namespace Products\Controller\CreateMultipleListings;

use Application\Controller\AbstractJsonController;
use CG_UI\View\Prototyper\JsonModelFactory;
use Products\Listing\MultiCreationService;
use CG\Channel\Listing\CreationService\StatusService;

class JsonController extends AbstractJsonController
{
    const ROUTE_SUBMIT_MULTIPLE_LISTINGS = 'SubmitMultipleListings';
    const ROUTE_SUBMIT_MULTIPLE_LISTINGS_PROGRESS = 'SubmitMultipleListingsProgress';

    /** @var MultiCreationService */
    protected $multiCreationService;
    /** @var StatusService */
    protected $statusService;

    public function __construct(
        JsonModelFactory $jsonModelFactory,
        MultiCreationService $multiCreationService,
        StatusService $statusService
    ) {
        parent::__construct($jsonModelFactory);
        $this->multiCreationService = $multiCreationService;
        $this->statusService = $statusService;
    }

    public function submitMultipleAction()
    {
        return $this->buildResponse([
            'allowed' => $this->multiCreationService->createListings(
                $this->params()->fromPost('accountIds', []),
                $this->params()->fromPost('categoryTemplateIds', []),
                $this->params()->fromPost('siteId', ''),
                $this->params()->fromPost('product', []),
                $guid
            ),
            'guid' => $guid,
        ]);
    }

    public function submitMultipleProgressAction()
    {
        $response = [];
        $statuses = $this->statusService->fetchStatusForGuid($this->params()->fromRoute('key'));
        foreach ($statuses as $accountCategory => $status) {
            [$accountId, $categoryId] = explode('-', $accountCategory);
            if (empty($response[$accountId])) {
                $response[$accountId] = ['categories' => []];
            }
            $response[$accountId]['categories'][$categoryId] = $status;
        }
        return $this->buildResponse([
            'accounts' => $response
        ]);

        // Dummy Data
        $statuses = ['started', 'complete', 'error'];
        $warnings = ['This is a warning', 'This is a different warning', 'This is also a warning'];
        $errors = ['This is an error', 'This is a different error', 'ERROR!!!!'];
        $dummyData = [];
        foreach ($this->params()->fromPost('accountId', []) as $accountId) {
            $dummyData[$accountId] = ['categories' => []];
            foreach ($this->params()->fromPost('categoryTemplateId', []) as $categoryTemplateId) {
                $status = $statuses[rand(0, count($statuses) - 1)];
                shuffle($warnings);
                shuffle($errors);
                $dummyData[$accountId]['categories'][$categoryTemplateId] = [
                    'status' => $status,
                    'warnings' => array_slice($warnings, 0, rand(0, count($warnings) - 1)),
                    'errors' => $status == 'error' ? array_slice($errors, 0, rand(1, count($errors) - 1)) : [],
                ];
            }
        }
        return $this->buildResponse($dummyData);
    }
}