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
use CG\Product\Category\Template\Service as CategoryTemplateService;
use CG\Stdlib\Exception\Runtime\NotFound;
use Products\Listing\Channel\Service as ChannelService;

class Service
{
    /** @var  AccountService */
    protected $accountService;
    /** @var  ChannelService */
    protected $channelService;
    /** @var  CategoryTemplateService */
    protected $categoryTemplateService;
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
        CategoryService $categoryService
    ) {
        $this->accountService = $accountService;
        $this->channelService = $channelService;
        $this->categoryTemplateService = $categoryTemplateService;
        $this->categoryService = $categoryService;
    }

    public function fetchAccounts(OrganisationUnit $ou): array
    {
        try {
            $accounts = $this->fetchActiveAccountsForOu($ou);
        } catch (NotFound $e) {
            return [];
        }

        $allowedChannels = $this->channelService->getAllowedCreateListingsChannels($ou, $accounts);
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
        $categoryFilterForSiblings = $this->buildCategoryFilterForSiblings();
        /** @var CategoryTemplate $categoryTemplate */
        foreach ($categoryTemplates as $categoryTemplate) {
            try {
                $categories = $this->fetchCategoriesByIds($categoryTemplate->getCategoryIds());
            } catch (NotFound $e) {
                continue;
            }

            $categoriesByAccount = [];
            /** @var Category $category */
            foreach ($categories as $category) {
                $accountId = $category->getAccountId();
                $filterByAccountId = true;
                if (isset(static::SALES_PLATFORMS[$category->getChannel()])) {
                    $filterByAccountId = false;
                    try {
                        /** @var Account $account */
                        $account = $this->fetchAccountByOuAndChannel($ou, $category->getChannel());
                        $accountId = $account->getId();
                    } catch (NotFound $e) {
                        // If no account is found, we skip the current category
                        continue;
                    }
                }

                if (!isset($categoriesByAccount[$accountId])) {
                    $categoriesByAccount[$accountId] = [];
                }

                try {
                    $siblings = $this->fetchCategorySiblings($categoryFilterForSiblings, $category, $accountId, $filterByAccountId);
                } catch (NotFound $e) {
                    continue;
                }

                /** @var Category $categorySibling */
                foreach ($siblings as $categorySibling) {
                    $categoriesByAccount[$accountId][] = $this->formatCategoryArray($categorySibling, $category->getId());
                }
            }

            $result[$categoryTemplate->getId()] = $this->formatCategoryTemplateArray($categoryTemplate, $categoriesByAccount);
        }
        return $result;
    }

    protected function fetchCategoryTemplatesByOu(
        OrganisationUnit $ou, ?string $search, int $page
    ): CategoryTemplateCollection {
        $filter = (new CategoryTemplateFilter())
            ->setPage($page)
            ->setLimit(10)
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

    protected function fetchCategoriesByIds(array $categoryIds): CategoryCollection
    {
        $filter = (new CategoryFilter())
            ->setPage(1)
            ->setLimit('all')
            ->setId($categoryIds);
        return $this->categoryService->fetchCollectionByFilter($filter);
    }

    protected function buildCategoryFilterForSiblings(): CategoryFilter
    {
        return (new CategoryFilter())
            ->setPage(1)
            ->setLimit('all');
    }

    protected function fetchCategorySiblings(
        CategoryFilter $categoryFilter,
        Category $category,
        int $accountId,
        bool $filterByAccountId
    ): CategoryCollection {
        $categoryFilter->setAccountId([$filterByAccountId ? $accountId : null]);
        $categoryFilter->setParentId([$category->getParentId() !== null ? $category->getParentId() : null]);
        $categoryFilter->setChannel([$category->getChannel()]);
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
}
