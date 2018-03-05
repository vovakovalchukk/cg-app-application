<?php
namespace Settings\Controller;

use Application\Controller\AbstractJsonController;
use CG\Product\Category\Template\Entity as CategoryTemplate;
use CG\Stdlib\Exception\Runtime\Conflict;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG\User\OrganisationUnit\Service as UserOUService;
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

    public function __construct(JsonModelFactory $jsonModelFactory,
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
        return $this->buildResponse([
            'accountCategories' => [
                [
                    'accountId' => 12,
                    'categories' => [
                        '123' => 'Televisions',
                        '345' => 'Clothes',
                        '567' => 'Phones'
                    ]
                ],
                [
                    'accountId' => 36,
                    'categories' => [
                        '123' => 'Televisions 2',
                        '345' => 'Clothes 2',
                        '567' => 'Phones 2'
                    ]
                ],
                [
                    'accountId' => 42,
                    'categories' => [
                        '123' => 'Televisions 3',
                        '345' => 'Clothes 3',
                        '567' => 'Phones 3'
                    ]
                ],
                [
                    'accountId' => 115,
                    'categories' => [
                        '123' => 'Televisions 4',
                        '345' => 'Clothes 4',
                        '567' => 'Phones 4'
                    ]
                ]
            ]
        ]);
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

    protected function buildCategoryAlreadyMappedError(Conflict $e, array $requestedCategoryIds, $templateId = null): JsonModel
    {
        $existing = $this->fetchExistingByCategoryIds($requestedCategoryIds, $templateId);
        $overlapCategoryIds = array_intersect($existing->getCategoryIds(), $requestedCategoryIds);
        $category = $this->categoryTemplateService->fetchCategory($overlapCategoryIds[0]);
        $accountId = $this->categoryTemplateService->fetchAccountIdForCategory($category, $this->userOuService->getRootOuByActiveUser());
        return $this->buildErrorResponse(
            [
                'code' => static::SAVE_ERROR_EXISTING_CATEGORY,
                'message' => $e->getMessage(),
                'existing' => [
                    'name' => $existing->getName(),
                    'accountId' => $accountId,
                    'categoryId' => $category->getId(),
                ]
            ],
            [
                'valid' => false,
                'id' => $existing->getId()
            ]
        );
    }

    protected function buildEtagError(): JsonModel
    {
        return $this->buildErrorResponse(
            [
                'code' => static::SAVE_ERROR_ETAG,
                'message' => 'Someone else may have updated that. Please refresh and try again.',
                'existing' => false
            ],
            [
                'valid' => false,
            ]
        );
    }

    protected function fetchExistingByCategoryIds(array $requestedCategoryIds, $templateId = null): CategoryTemplate
    {
        $existingTemplates = $this->categoryTemplateService->fetchByCategoryIds($requestedCategoryIds);
        foreach ($existingTemplates as $existingTemplate) {
            // Don't conflict with itself
            if ($templateId && $existingTemplate->getId() == $templateId) {
                continue;
            }
            return $existingTemplate;
        }
    }

    public function categoryChildrenAction()
    {
        return $this->buildResponse([
            'categories' => [
                '1023' => 'Televisions Child',
                '2354' => 'Clothes Child',
                '8721' => 'Phones Child'
            ]
        ]);
    }

    public function refreshCategoriesAction()
    {
        return $this->buildResponse([
            'categories' => [
                '91' => 'Refetched Televisions',
                '103' => 'Refetched Clothes',
                '209' => 'Refetched Phones'
            ]
        ]);
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
            return $this->buildErrorResponse('There was a problem with the delete', ['valid' => false]);
        }
    }
}
