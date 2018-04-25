<?php
namespace Settings\Controller;

use Application\Controller\AbstractJsonController;
use CG\Http\Exception\Exception3xx\NotModified;
use CG\Product\Category\Entity as Category;
use CG\Product\Category\Template\Collection as CategoryTemplateCollection;
use CG\Product\Category\Template\Entity as CategoryTemplate;
use CG\Stdlib\Exception\Runtime\Conflict;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\User\OrganisationUnit\Service as UserOUService;
use CG_UI\View\Prototyper\JsonModelFactory;
use Products\Listing\Exception as ListingException;
use Settings\Category\Template\Exception\CategoryAlreadyMappedException;
use Settings\Category\Template\Exception\NameAlreadyUsedException;
use Settings\Category\Template\Service as CategoryTemplateService;
use Zend\View\Model\JsonModel;

class CategoryTemplatesJsonController extends AbstractJsonController
{
    const ROUTE_ACCOUNTS = 'accounts';
    const ROUTE_FETCH = 'fetch';
    const ROUTE_CATEGORY_ROOTS = 'categoryRoots';
    const ROUTE_SAVE = 'save';
    const ROUTE_CATEGORY_CHILDREN = 'categoryChildren';
    const ROUTE_REFRESH_CATEGORIES = 'refreshCategories';
    const ROUTE_TEMPLATE_DELETE = 'templateDelete';

    const SAVE_ERROR_EXISTING_NAME = 'existing name';
    const SAVE_ERROR_EXISTING_CATEGORY = 'existing category';
    const SAVE_ERROR_ETAG = 'conflict';

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
        try {
            $ou = $this->userOuService->getRootOuByActiveUser();
            return $this->buildResponse([
                'accounts' => $this->categoryTemplateService->fetchAccounts($ou),
                'categories' => $this->categoryTemplateService->fetchCategoryRoots($ou)
            ]);
        } catch (\Throwable $e) {
            return $this->buildGenericErrorResponse();
        }
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
        $postData = $this->params()->fromPost();
        try {
            /** @var CategoryTemplate $entity */
            $entity = $this->categoryTemplateService->saveCategoryTemplateFromRaw($postData);
            return $this->buildSuccessResponse([
                'valid' => true,
                'id' => $entity->getId(),
                'etag' => $entity->getStoredETag(),
                'error' => false
            ]);
        } catch (NameAlreadyUsedException $e) {
            return $this->buildNameAlreadyExistsError($e, $postData['name']);
        } catch (CategoryAlreadyMappedException $e) {
            return $this->buildCategoryAlreadyMappedError($e, $postData['categoryIds'], $postData['id'] ?? null);
        } catch (Conflict $e) {
            return $this->buildEtagError();
        } catch (NotModified $e) {
            return $this->buildSuccessResponse([
                'valid' => true
            ]);
        }
    }

    protected function buildNameAlreadyExistsError(Conflict $e, string $name): JsonModel
    {
        return $this->buildErrorResponse(
            [
                'code' => static::SAVE_ERROR_EXISTING_NAME,
                'message' => $e->getMessage(),
                'existing' => [
                    'name' => $name
                ]
            ],
            [
                'valid' => false,
            ]
        );
    }

    protected function buildCategoryAlreadyMappedError(Conflict $e, array $requestedCategoryIds, $currentTemplateId = null): JsonModel
    {
        return $this->buildErrorResponse(
            [
                'code' => static::SAVE_ERROR_EXISTING_CATEGORY,
                'message' => $e->getMessage(),
                'existing' => $this->buildDetailsOfAlreadyMappedCategories($requestedCategoryIds, $currentTemplateId)
            ],
            [
                'valid' => false,
                'id' => $currentTemplateId
            ]
        );
    }

    protected function buildDetailsOfAlreadyMappedCategories(array $requestedCategoryIds, $currentTemplateId = null): array
    {
        $existingTemplates = $this->fetchExistingByCategoryIds($requestedCategoryIds, $currentTemplateId);
        $existingDetails = [];
        /** @var CategoryTemplate $existingTemplate */
        foreach ($existingTemplates as $existingTemplate) {
            $overlapCategoryIds = array_intersect($existingTemplate->getCategoryIds(), $requestedCategoryIds);
            /** @var Category[] $overlapCategories */
            $overlapCategories = $this->categoryTemplateService->fetchCategoriesByIds($overlapCategoryIds);
            foreach ($overlapCategories as $category) {
                $existingDetails[] = [
                    'name' => $existingTemplate->getName(),
                    'accountId' => $existingTemplate->getAccountIdForCategory($category->getId()),
                    'categoryId' => $category->getId(),
                ];
            }
        }
        return $existingDetails;
    }

    protected function buildEtagError(): JsonModel
    {
        return $this->buildErrorResponse(
            [
                'code' => static::SAVE_ERROR_ETAG,
                'message' => 'Someone else may have updated this template in the meantime. Please refresh and try again.',
                'existing' => false
            ],
            [
                'valid' => false,
            ]
        );
    }

    protected function fetchExistingByCategoryIds(array $requestedCategoryIds, $currentTemplateId = null): CategoryTemplateCollection
    {
        $existingTemplates = $this->categoryTemplateService->fetchByCategoryIds($requestedCategoryIds);
        if ($currentTemplateId && $existingTemplates->containsId($currentTemplateId)) {
            // Don't mark it as conflicting with itself
            $currentTemplate = $existingTemplates->getById($currentTemplateId);
            $existingTemplates->detach($currentTemplate);
        }
        return $existingTemplates;
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
        try {
            $id = $this->params()->fromRoute('templateId');
            $this->categoryTemplateService->deleteById($id);
            return $this->buildSuccessResponse(['valid' => true]);

        } catch (NotFound $e) {
            // Nothing to delete
            return $this->buildSuccessResponse(['valid' => true]);
        } catch (\Exception $e) {
            return $this->buildErrorResponse('There was a problem while deleting the template. Please try again. Contact support if the problem persists.', ['valid' => false]);
        }
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
