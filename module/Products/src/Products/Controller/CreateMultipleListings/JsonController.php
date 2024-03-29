<?php
namespace Products\Controller\CreateMultipleListings;

use Application\Controller\AbstractJsonController;
use CG\Channel\Listing\CreationService\StatusService;
use CG_UI\View\Prototyper\JsonModelFactory;
use Products\Listing\MultiCreationService;

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
        $guid = $this->multiCreationService->generateUniqueId();
        $processGuid = $this->buildProcessGuid();

        return $this->buildResponse([
            'allowed' => $this->multiCreationService->createListings(
                $this->params()->fromPost('accountIds', []),
                $this->params()->fromPost('categoryTemplateIds', []),
                $this->params()->fromPost('siteId', ''),
                $this->params()->fromPost('product', []),
                $guid,
                $this->formatAccountCategoriesFromPost(),
                $processGuid
            ),
            'guid' => $guid,
            'processGuid' => $processGuid
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
    }

    protected function formatAccountCategoriesFromPost(): array
    {
        $accountCategories = [];
        foreach ($this->params()->fromPost('accountCategories', []) as $accountCategory) {
            if (!isset($accountCategories[$accountCategory['accountId']])) {
                $accountCategories[$accountCategory['accountId']] = [];
            }
            $accountCategories[$accountCategory['accountId']][$accountCategory['categoryId']] = $accountCategory['categoryId'];
        }
        return $accountCategories;
    }

    protected function buildProcessGuid(): string
    {
        $processGuid = trim($this->params()->fromPost('processGuid', ''));
        return !empty($processGuid) ? $processGuid :  $this->multiCreationService->generateUniqueId();
    }
}
