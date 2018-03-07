<?php
namespace Settings\Controller;

use Application\Controller\AbstractJsonController;
use CG\User\OrganisationUnit\Service as UserOUService;
use CG_UI\View\Prototyper\JsonModelFactory;
use Products\Listing\Exception as ListingException;
use Settings\Category\Template\Service as CategoryTemplateService;

class CategoryTemplatesJsonController extends AbstractJsonController
{
    const ROUTE_ACCOUNTS = 'accounts';
    const ROUTE_FETCH = 'fetch';
    const ROUTE_CATEGORY_ROOTS = 'categoryRoots';
    const ROUTE_SAVE = 'save';
    const ROUTE_CATEGORY_CHILDREN = 'categoryChildren';
    const ROUTE_REFRESH_CATEGORIES = 'refreshCategories';
    const ROUTE_TEMPLATE_DELETE = 'templateDelete';

    /** @var UserOUService */
    protected $userOuService;
    /** @var  CategoryTemplateService */
    protected $categoryTemplateService;

    public function __construct(
        JsonModelFactory $jsonModelFactory,
        UserOUService $userOuService,
        CategoryTemplateService $categoryTemplateService
    ) {
        parent::__construct($jsonModelFactory);
        $this->userOuService = $userOuService;
        $this->categoryTemplateService = $categoryTemplateService;
    }

    public function accountsAction()
    {
        $ou = $this->userOuService->getRootOuByActiveUser();
        return $this->buildResponse([
            'accounts' => $this->categoryTemplateService->fetchAccounts($ou)
        ]);
    }

    public function fetchAction()
    {
        $ou = $this->userOuService->getRootOuByActiveUser();
        $search = $this->params()->fromPost('search', null);
        $page = (int) $this->params()->fromPost('page', 1);
        return $this->buildResponse(
            $this->categoryTemplateService->fetchCategoryTemplates($ou, $search, $page)
        );
    }

    public function categoryRootsAction()
    {
        try {
            return $this->buildResponse([
                'accountCategories' => $this->categoryTemplateService->fetchCategoryRoots(
                    $this->userOuService->getRootOuByActiveUser()
                )
            ]);
        } catch (\Throwable $e) {
            return $this->buildGenericErrorResponse();
        }
    }

    public function saveAction()
    {
        $success = $this->params()->fromPost('success' , false);
        $success = !($success === 'false' || $success === false);

        if ($success) {
            return $this->buildSuccessResponse([
                'valid' => true,
                'id' => 726,
                'etag' => '12321esdfc2342jkda',
                'errors' => false
            ]);
        }

        return $this->buildErrorResponse(
            [
                'code' => 'existing',
                'message' => 'You have already mapped this category',
                'existing' => [
                    'name' => 'Washing machines and others',
                    'accountId' => 36,
                    'externalCategoryId' => 12345
                ]
            ],
            [
                'valid' => false,
                'id' => 1
            ]
        );
    }

    public function categoryChildrenAction()
    {
        try {
            return $this->buildResponse([
                'categories' => $this->categoryTemplateService->fetchCategoryChildrenForAccountAndCategory(
                    $this->getAccountIdFromRoute(),
                    $this->params()->fromRoute('categoryId', -1)
                )
            ]);
        } catch (ListingException $e) {
            return $this->buildErrorResponse($e->getMessage());
        } catch (\Throwable $e) {
            return $this->buildGenericErrorResponse();
        }
    }

    public function refreshCategoriesAction()
    {
        try {
            return $this->buildResponse([
                'categories' => $this->categoryTemplateService->refreshCategories($this->getAccountIdFromRoute())
            ]);
        } catch (ListingException $e) {
            return $this->buildErrorResponse($e->getMessage());
        } catch (\Throwable $e) {
            return $this->buildGenericErrorResponse();
        }
    }

    public function templateDeleteAction()
    {
        return $this->buildResponse([
            'valid' => true,
            'errors' => []
        ]);
    }

    protected function getAccountIdFromRoute()
    {
        return $this->params()->fromRoute('accountId', 0);
    }

    protected function buildGenericErrorResponse()
    {
        return $this->buildErrorResponse('An error has occurred. Please try again');
    }
}
