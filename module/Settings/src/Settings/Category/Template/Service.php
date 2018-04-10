<?php

namespace Settings\Category\Template;

use CG\Account\Client\Service as AccountService;
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
use CG\User\ActiveUserInterface;
use Guzzle\Http\Exception\ClientErrorResponseException;
use Products\Listing\Channel\Service as ChannelService;
use Products\Listing\Exception as ListingException;
use Settings\Category\Template\Exception\CategoryAlreadyMappedException;
use Settings\Category\Template\Exception\NameAlreadyUsedException;

class Service
{
    /** @TODO: revert this to 10! */
    const DEFAULT_TEMPLATE_LIMIT = 2;
    const INDEX_NAME = 'OrganisationUnitIdName';
    const INDEX_CATEGORY = 'OrganisationUnitIdCategoryId';

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
    /** @var  Account[] */
    protected $accountsByChannel = [];

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
            return [];
        }

        $allowedChannels = $this->channelService->getAllowedCreateListingsChannels();
        $result = [];
        /** @var Account $account */
        foreach ($accounts as $account) {
            if (!isset($allowedChannels[$account->getChannel()])) {
                continue;
            }
            $displayName = $account->getDisplayName();
            $refreshable = true;
            if (isset(static::SALES_PLATFORMS[$account->getChannel()])) {
                $displayName = $allowedChannels[$account->getChannel()];
                unset($allowedChannels[$account->getChannel()]);
                $refreshable = false;
            }
            $result[$account->getId()] = [
                'channel' => $account->getChannel(),
                'displayName' => $displayName,
                'refreshable' => $refreshable
            ];
        }
        return $result;
    }

    public function fetchCategoryRoots(OrganisationUnit $ou): array
    {
        try {
            $accounts = $this->fetchActiveAccountsForOu($ou);
        } catch (NotFound $e) {
            return [];
        }

        $allowedChannels = $this->channelService->getAllowedCreateListingsChannels();
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
        $filter = (new AccountFilter())
            ->setLimit('all')
            ->setPage(1)
            ->setOrganisationUnitId([$ou->getId()])
            ->setActive(true)
            ->setDeleted(false);
        return $this->accountService->fetchByFilter($filter);
    }

    public function fetchCategoryTemplates(OrganisationUnit $ou, ?string $search, int $page): array
    {
        try {
            $categoryTemplates = $this->fetchCategoryTemplatesByOu($ou, $search, $page);
        } catch (NotFound $e) {
            return [];
        }

        $result = [];
        $categoryFilterForSiblings = $this->buildCategoryFilter();
        /** @var CategoryTemplate $categoryTemplate */
        foreach ($categoryTemplates as $categoryTemplate) {
            try {
                $categories = $this->fetchCategoriesByIds($categoryTemplate->getCategoryIds());
            } catch (NotFound $e) {
                continue;
            }

            $result[$categoryTemplate->getId()] = $this->formatCategoryTemplateArray(
                $categoryTemplate,
                $this->groupCategoriesByAccount($categories, $ou, $categoryFilterForSiblings)
            );
        }
        return $result;
    }

    protected function groupCategoriesByAccount(
        CategoryCollection $categories,
        OrganisationUnit $ou,
        CategoryFilter $categoryFilter
    ): array{
        $categoriesByAccount = [];
        /** @var Category $category */
        foreach ($categories as $category) {
            try {
                $accountId = $this->fetchAccountIdForCategory($category, $ou);
            } catch (NotFound $e) {
                // If no account is found, we skip the current category
                continue;
            }

            if (!isset($categoriesByAccount[$accountId])) {
                $categoriesByAccount[$accountId] = [];
            }

            $data = [];
            $this->formatCategoryTree($categoryFilter, $category, $data);
            $categoriesByAccount[$accountId][] = $data;
        }

        return $categoriesByAccount;
    }

    protected function formatCategoryTree(CategoryFilter $filter, Category $category, &$data)
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
    }

    public function fetchAccountIdForCategory(Category $category, OrganisationUnit $ou)
    {
        if ($category->getAccountId()) {
            return $category->getAccountId();
        }
        /** @var Account $account */
        $account = $this->fetchAccountByOuAndChannel($ou, $category->getChannel());
        return $account->getId();
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

    protected function fetchAccountByOuAndChannel(OrganisationUnit $ou, string $channel): Account
    {
        if (isset($this->accountsByChannel[$channel])) {
            return $this->accountsByChannel[$channel];
        }

        $accountFilter = $filter = (new AccountFilter())
            ->setLimit(1)
            ->setPage(1)
            ->setOrganisationUnitId([$ou->getId()])
            ->setChannel([$channel])
            ->setActive(true)
            ->setDeleted(false);
        $account = $this->accountService->fetchByFilter($accountFilter)->getFirst();
        $this->accountsByChannel[$channel] = $account;
        return $account;
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
        $categoryFilter
            ->setAccountId([$category->getAccountId() ? $category->getAccountId() : null])
            ->setParentId([$category->getParentId() !== null ? $category->getParentId() : null])
            ->setMarketplace([$category->getMarketplace() ? $category->getMarketplace() : null])
            ->setChannel([$category->getChannel()]);
        return $this->categoryService->fetchCollectionByFilter($categoryFilter);
    }

    protected function formatCategoryArray(Category $category, $selectedCategoryId): array
    {
        return [
            'value' => $category->getId(),
            'name' => $category->getTitle(),
            'selected' => $category->getId() == $selectedCategoryId
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
