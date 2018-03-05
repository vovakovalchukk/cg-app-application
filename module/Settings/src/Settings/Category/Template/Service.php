<?php

namespace Settings\Category\Template;

use CG\Account\Client\Service as AccountService;
use CG\Account\Shared\Entity as Account;
use CG\Account\Shared\Filter as AccountFilter;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\Product\Category\Entity as Category;
use CG\Product\Category\Filter as CategoryFilter;
use CG\Product\Category\Service as CategoryService;
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
        $filter = (new CategoryTemplateFilter())
            ->setPage($page)
            ->setLimit(10)
            ->setOrganisationUnitId([$ou->getId()]);
        $search ? $filter->setSearch($search) : null;

        try {
            $categoryTemplates = $this->categoryTemplateService->fetchCollectionByFilter($filter);
        } catch (NotFound $e) {
            return [];
        }

        $result = [];
        $filter = (new CategoryFilter())
            ->setPage(1)
            ->setLimit('all');
        /** @var CategoryTemplate $categoryTemplate */
        foreach ($categoryTemplates as $categoryTemplate) {
            $categories = $this->categoryService->fetchCollectionByFilter($filter->setId($categoryTemplate->getCategoryIds()));
            $categoriesByAccount = [];
            $categoryFilterForSiblings = (new CategoryFilter())
                ->setPage(1)
                ->setLimit('all');

            /** @var Category $category */
            foreach ($categories as $category) {
                $channel = null;
                $accountId = $category->getAccountId();
                $filterByAccountId = true;
                if (isset(static::SALES_PLATFORMS[$category->getChannel()])) {
                    $filterByAccountId = false;
                    $accountFilter = $filter = (new AccountFilter())
                        ->setLimit(1)
                        ->setPage(1)
                        ->setOrganisationUnitId([$ou->getId()])
                        ->setChannel([$category->getChannel()])
                        ->setActive(true)
                        ->setDeleted(false);
                    try {
                        /** @var Account $account */
                        $account = $this->accountService->fetchByFilter($accountFilter)->getFirst();
                    } catch (NotFound $e) {
                        continue;
                    }

                    $channel = $account->getChannel();
                    $accountId = $account->getChannel();
                }

                if (!isset($categoriesByAccount[$accountId])) {
                    $categoriesByAccount[$accountId] = [];
                }

                $filterByAccountId? $categoryFilterForSiblings->setAccountId([$accountId]) : null;
                $categoryFilterForSiblings->setParentId([$category->getParentId() !== null ? $category->getParentId() : null]);
                $categoryFilterForSiblings->setChannel([$channel]);

                try {
                    $siblings = $this->categoryService->fetchCollectionByFilter($categoryFilterForSiblings);
                } catch (NotFound $e) {
                    continue;
                }

                /** @var Category $categorySibling */
                foreach ($siblings as $categorySibling) {
                    $categoriesByAccount[$accountId][] = [
                        'value' => $categorySibling->getId(),
                        'name' => $categorySibling->getTitle(),
                        'selected' => $categorySibling->getId() == $category->getId()
                    ];
                }
            }

            $accountCategories = [];
            foreach ($categoriesByAccount as $accountId => $categories) {
                $accountCategories[] = [
                    'accountId' => $accountId,
                    'categories' => $categories
                ];
            }

            $result[$categoryTemplate->getId()] = [
                'etag' => $categoryTemplate->getETag(),
                'name' => $categoryTemplate->getName(),
                'accountCategories' => $accountCategories
            ];
        }
        return $result;
    }
}
