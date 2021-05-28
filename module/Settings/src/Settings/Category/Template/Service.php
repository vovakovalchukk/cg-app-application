<?php

namespace Settings\Category\Template;

use CG\Account\Client\Service as AccountService;
use CG\Account\Shared\Collection as AccountCollection;
use CG\Account\Shared\Entity as Account;
use CG\Account\Shared\Filter as AccountFilter;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\Product\Category\Collection as CategoryCollection;
use CG\Product\Category\Entity as Category;
use CG\Product\Category\Filter as CategoryFilter;
use CG\Product\Category\Service as CategoryService;
use CG\Product\Category\Template\Collection as CategoryTemplateCollection;
use CG\Product\Category\Template\Entity as CategoryTemplate;
use CG\Product\Category\Template\Filter as CategoryTemplateFilter;
use CG\Product\Category\Template\Mapper as CategoryTemplateMapper;
use CG\Product\Category\Template\Service as CategoryTemplateService;
use CG\Stdlib\Exception\Runtime\Conflict;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\User\ActiveUserInterface;
use Guzzle\Http\Exception\ClientErrorResponseException;
use Products\Listing\Channel\Service as ChannelService;
use Products\Listing\Exception as ListingException;
use Settings\Category\Template\Exception\CategoryAlreadyMappedException;
use Settings\Category\Template\Exception\NameAlreadyUsedException;

class Service implements LoggerAwareInterface
{
    use LogTrait;

    /** We should increase this to 10 after the fetch code is re-factored, now it's too slow and it makes users wait too much for 10 maps */
    public const DEFAULT_TEMPLATE_LIMIT = 5;
    public const INDEX_NAME = 'OrganisationUnitIdName';
    public const INDEX_CATEGORY = 'OrganisationUnitIdCategoryId';

    protected const LOG_CODE = 'SettingsCategoryTemplateService';
    protected const LOG_MSG_LISTING_EXCEPTION = 'Exception during fetching categories for account %d';
    protected const LOG_MSG_NOTFOUND_EXCEPTION = 'NotFound Exception during fetching accounts for OU %d';

    /** @var  AccountService */
    protected $accountService;
    /** @var  ChannelService */
    protected $channelService;
    /** @var CategoryTemplateService */
    protected $categoryTemplateService;
    /** @var CategoryTemplateMapper */
    protected $categoryTemplateMapper;
    /** @var ActiveUserInterface */
    protected $activeUserContainer;
    /** @var  CategoryService  */
    protected $categoryService;
    /** @var  AccountCollection */
    protected $accounts;

    const SALES_PLATFORMS = [
        'ebay' => 'ebay',
        'amazon' => 'amazon'
    ];

    public function __construct(
        AccountService $accountService,
        ChannelService $channelService,
        CategoryTemplateService $categoryTemplateService,
        CategoryTemplateMapper $categoryTemplateMapper,
        ActiveUserInterface $activeUserContainer,
        CategoryService $categoryService
    ) {
        $this->accountService = $accountService;
        $this->channelService = $channelService;
        $this->categoryTemplateService = $categoryTemplateService;
        $this->categoryTemplateMapper = $categoryTemplateMapper;
        $this->activeUserContainer = $activeUserContainer;
        $this->categoryService = $categoryService;
    }

    public function fetchAccounts(OrganisationUnit $ou): array
    {
        try {
            $accounts = $this->fetchActiveAccountsForOu($ou);
        } catch (NotFound $e) {
            $this->logDebugException($e, static::LOG_MSG_NOTFOUND_EXCEPTION, [$ou->getId()], static::LOG_CODE);
            return [];
        }

        $allowedChannels = $this->channelService->getAllowedCreateListingsChannels($ou);
        $result = [];
        /** @var Account $account */
        foreach ($accounts as $account) {
            if (!isset($allowedChannels[$account->getChannel()])) {
                continue;
            }

            $result[$account->getId()] = [
                'channel' => $account->getChannel(),
                'displayName' => $account->getDisplayName(),
                'refreshable' => !isset(static::SALES_PLATFORMS[$account->getChannel()])
            ];
        }
        return $result;
    }

    public function fetchCategoryRoots(OrganisationUnit $ou): array
    {
        try {
            $accounts = $this->fetchActiveAccountsForOu($ou);
        } catch (NotFound $e) {
            $this->logDebugException($e, static::LOG_MSG_NOTFOUND_EXCEPTION, [$ou->getId()], static::LOG_CODE);
            return [];
        }

        $allowedChannels = $this->channelService->getAllowedCreateListingsChannels($ou);
        $result = [];
        /** @var Account $account */
        foreach ($accounts as $account) {
            if (!isset($allowedChannels[$account->getChannel()])) {
                continue;
            }

            $result[] = [
                'accountId' => $account->getId(),
                'categories' => $this->fetchCategoriesForAccount($account)
            ];
        }
        return $result;
    }

    public function fetchCategoriesForAccount(Account $account): array
    {
        try {
            $defaultSettings = $this->channelService->getChannelSpecificFieldValues($account);
            return $defaultSettings['categories'] ?? [];
        } catch (ListingException $e) {
            $this->logDebugException($e, static::LOG_MSG_LISTING_EXCEPTION, [$account->getId()], static::LOG_CODE);
            return [];
        }
    }

    public function fetchCategoryChildrenForAccountAndCategory(int $accountId, int $categoryId): array
    {
        try {
            $account = $this->accountService->fetch($accountId);
        } catch (NotFound $e) {
            return [];
        }
        return $this->channelService->getCategoryChildrenForCategoryAndAccount($account, $categoryId);
    }

    public function refreshCategories(int $accountId): array
    {
        try {
            $account = $this->accountService->fetch($accountId);
        } catch (NotFound $e) {
            return [];
        }
        return $this->channelService->refetchAndSaveCategories($account);
    }

    protected function fetchActiveAccountsForOu(OrganisationUnit $ou)
    {
        if (!empty($this->accounts)) {
            return $this->accounts;
        }

        $filter = (new AccountFilter())
            ->setLimit('all')
            ->setPage(1)
            ->setRootOrganisationUnitId([$ou->getId()])
            ->setActive(true)
            ->setDeleted(false);

        $this->accounts = $this->accountService->fetchByFilter($filter);

        return $this->accounts;
    }

    public function fetchCategoryTemplates(OrganisationUnit $ou, ?string $search, int $page): array
    {
        try {
            /** @var CategoryTemplate[] $categoryTemplates */
            $categoryTemplates = $this->fetchCategoryTemplatesByOu($ou, $search, $page);
            $this->accounts = $this->fetchActiveAccountsForOu($ou);
        } catch (NotFound $e) {
            return [];
        }

        $result = [];
        $categoryFilterForSiblings = $this->buildCategoryFilter();
        foreach ($categoryTemplates as $categoryTemplate) {
            try {
                $categories = $this->fetchCategoriesByIds($categoryTemplate->getCategoryIds());
            } catch (NotFound $e) {
                continue;
            }

            $categoriesArray = [];
            foreach ($categoryTemplate->getAccountCategories() as $accountCategory) {
                $category = $categories->getById($accountCategory->getCategoryId());
                if ($category) {
                    $categoriesArray[$accountCategory->getAccountId()] = $categories->getById($accountCategory->getCategoryId());
                }
            }

            $result[$categoryTemplate->getId()] = $this->formatCategoryTemplateArray(
                $categoryTemplate,
                $this->groupCategoriesByAccount($categoriesArray, $categoryFilterForSiblings, $ou)
            );
        }
        return $result;
    }

    /**
     * @param Category[] $categories
     * @param CategoryFilter $categoryFilter
     * @param OrganisationUnit $ou
     * @return array
     */
    protected function groupCategoriesByAccount(
        array $categories,
        CategoryFilter $categoryFilter,
        OrganisationUnit $ou
    ): array {
        $categoriesByAccount = [];
        try {
            $accounts = $this->fetchActiveAccountsForOu($ou);
        } catch (NotFound $e) {
            return $categoriesByAccount;
        }

        foreach ($categories as $accountId => $category) {
            if (!$accounts->getById($accountId)) {
                continue;
            }

            if (!isset($categoriesByAccount[$accountId])) {
                $categoriesByAccount[$accountId] = [];
            }

            $categoriesByAccount[$accountId][] = $this->formatCategoryTree($categoryFilter, $category);
        }

        return $categoriesByAccount;
    }

    protected function formatCategoryTree(CategoryFilter $filter, Category $category, array &$data = [])
    {
        try {
            $siblings = $this->fetchCategorySiblings($filter, $category);
        } catch (NotFound $e) {
            return;
        }

        $array = [];
        /** @var Category $sibling */
        foreach ($siblings as $sibling) {
            $array[] = $this->formatCategoryArray($sibling, $category->getId());
        }

        array_unshift($data, $array);

        if ($category->getParentId()) {
            $this->formatCategoryTree($filter, $this->categoryService->fetch($category->getParentId()), $data);
        }
        return $data;
    }

    protected function fetchCategoryTemplatesByOu(
        OrganisationUnit $ou, ?string $search, int $page
    ): CategoryTemplateCollection {
        $filter = (new CategoryTemplateFilter())
            ->setPage($page)
            ->setLimit(static::DEFAULT_TEMPLATE_LIMIT)
            ->setOrganisationUnitId([$ou->getId()]);
        $search ? $filter->setSearch($search) : null;
        return $this->categoryTemplateService->fetchCollectionByFilter($filter);
    }

    public function fetchCategoriesByIds(array $categoryIds): CategoryCollection
    {
        if (empty($categoryIds)) {
            throw new \InvalidArgumentException('Don\'t attempt to filter on empty categoryId array as it will fetch all categories');
        }
        $filter = (new CategoryFilter())
            ->setPage(1)
            ->setLimit('all')
            ->setId($categoryIds);
        return $this->categoryService->fetchCollectionByFilter($filter);
    }

    protected function buildCategoryFilter(): CategoryFilter
    {
        return (new CategoryFilter())
            ->setPage(1)
            ->setLimit('all');
    }

    protected function fetchCategorySiblings(
        CategoryFilter $categoryFilter,
        Category $category
    ): CategoryCollection {
        $accountIds = $category->getAccountId() ? [$category->getAccountId()] : [];
        $parentIds = $category->getParentId() !== null ? [$category->getParentId()] : [];
        $marketplaces = $category->getMarketplace() ? [$category->getMarketplace()] : [];

        $categoryFilter
            ->setAccountId($accountIds)
            ->setParentId($parentIds)
            ->setMarketplace($marketplaces)
            ->setChannel([$category->getChannel()])
            ->setEnabled(true);
        return $this->categoryService->fetchCollectionByFilter($categoryFilter);
    }

    protected function formatCategoryArray(Category $category, $selectedCategoryId): array
    {
        return [
            'value' => $category->getId(),
            'name' => $category->getTitle(),
            'selected' => $category->getId() == $selectedCategoryId,
            'listable' => $category->isListable()
        ];
    }

    protected function formatCategoryTemplateArray(CategoryTemplate $categoryTemplate, array $categoriesByAccount): array
    {
        $accountCategories = [];
        foreach ($categoriesByAccount as $accountId => $categories) {
            $accountCategories[] = [
                'accountId' => $accountId,
                'categories' => $categories
            ];
        }

        return [
            'etag' => $categoryTemplate->getETag(),
            'name' => $categoryTemplate->getName(),
            'accountCategories' => $accountCategories
        ];
    }

    public function fetchByCategoryIds(array $categoryIds): CategoryTemplateCollection
    {
        $filter = (new CategoryTemplateFilter())
            ->setLimit('all')
            ->setPage(1)
            ->setOrganisationUnitId([$this->activeUserContainer->getActiveUserRootOrganisationUnitId()])
            ->setCategoryId($categoryIds);
        return $this->categoryTemplateService->fetchCollectionByFilter($filter);
    }

    public function saveCategoryTemplateFromRaw(array $rawData): CategoryTemplate
    {
        $rawData['organisationUnitId'] = $this->activeUserContainer->getActiveUserRootOrganisationUnitId();
        $rawData['accounts'] = $this->formatAccountCategoriesForMapper($rawData);
        $entity = $this->categoryTemplateMapper->fromArray($rawData);
        if (isset($rawData['etag'])) {
            $entity->setStoredETag($rawData['etag']);
        }
        try {
            $this->categoryTemplateService->save($entity);
            return $entity;
        } catch (Conflict $e) {
            throw $this->getSpecificConflictException($e);
        }
    }

    protected function formatAccountCategoriesForMapper(array $data): array
    {
        if (empty($data['categoryIds'])) {
            return [];
        }

        $accounts = [];
        foreach ($data['categoryIds'] as $accountId => $categoryId) {
            $accounts[(int) $accountId] = [
                CategoryTemplate::KEY_CATEGORY_ID => (int) $categoryId
            ];
        }

        return $accounts;
    }

    protected function getSpecificConflictException(Conflict $original): Conflict
    {
        $previous = $original->getPrevious();
        if (!$previous || !$previous instanceof ClientErrorResponseException) {
            return $original;
        }
        $body = $previous->getResponse()->getBody(true);
        if (strstr($body, static::INDEX_NAME)) {
            return new NameAlreadyUsedException('You have already used this template name', $original->getCode(), $original);
        }
        if (strstr($body, static::INDEX_CATEGORY)) {
            return new CategoryAlreadyMappedException('You have already mapped this category', $original->getCode(), $original);
        }

        return $original;
    }

    public function deleteById(int $id)
    {
        $entity = $this->categoryTemplateService->fetch($id);
        $this->categoryTemplateService->remove($entity);
    }
}
