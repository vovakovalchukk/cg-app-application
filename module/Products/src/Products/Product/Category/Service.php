<?php
namespace Products\Product\Category;

use CG\Account\Client\StorageInterface as AccountStorage;
use CG\Account\Shared\Collection as Accounts;
use CG\Account\Shared\Entity as Account;
use CG\Account\Shared\Filter as AccountFilter;
use CG\OrganisationUnit\Service as OUService;
use CG\Product\Category\Collection as Categories;
use CG\Product\Category\Entity as Category;
use CG\Product\Category\Filter as CategoryFilter;
use CG\Product\Category\StorageInterface as CategoryStorage;
use CG\Product\Category\Template\AccountCategory;
use CG\Product\Category\Template\Collection as CategoryTemplates;
use CG\Product\Category\Template\Entity as CategoryTemplate;
use CG\Product\Category\Template\Filter as CategoryTemplateFilter;
use CG\Product\Category\Template\StorageInterface as CategoryTemplateStorage;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\User\ActiveUserInterface;
use Products\Listing\Channel\CategoryDependentServiceInterface;
use Products\Listing\Channel\Factory as ChannelListingFactory;
use Products\Listing\Exception as ListingException;

class Service
{
    /** @var ActiveUserInterface */
    protected $activeUser;
    /** @var OUService */
    protected $ouService;
    /** @var CategoryTemplateStorage */
    protected $categoryTemplateStorage;
    /** @var CategoryStorage */
    protected $categoryStorage;
    /** @var AccountStorage */
    protected $accountStorage;
    /** @var ChannelListingFactory */
    protected $channelListingFactory;

    public function __construct(
        ActiveUserInterface $activeUser,
        OUService $ouService,
        CategoryTemplateStorage $categoryTemplateStorage,
        CategoryStorage $categoryStorage,
        AccountStorage $accountStorage,
        ChannelListingFactory $channelListingFactory
    ) {
        $this->activeUser = $activeUser;
        $this->ouService = $ouService;
        $this->categoryTemplateStorage = $categoryTemplateStorage;
        $this->categoryStorage = $categoryStorage;
        $this->accountStorage = $accountStorage;
        $this->channelListingFactory = $channelListingFactory;
    }

    public function getTemplateOptions(): array
    {
        try {
            $categoryTemplates = $this->categoryTemplateStorage->fetchCollectionByFilter(
                (new CategoryTemplateFilter('all', 1))
                    ->setOrganisationUnitId(
                        $this->ouService->fetchRelatedOrganisationUnitIds($this->activeUser->getActiveUserRootOrganisationUnitId())
                    )
            );
            return $this->getTemplateOptionsArray($categoryTemplates);
        } catch (NotFound $exception) {
            return [];
        }
    }

    /**
     * @param CategoryTemplate[] $categoryTemplates
     * @return array
     */
    protected function getTemplateOptionsArray(CategoryTemplates $categoryTemplates): array
    {
        $templateOptions = [];
        foreach ($categoryTemplates as $categoryTemplate) {
            $templateOptions[$categoryTemplate->getId()] = [
                'name' => $categoryTemplate->getName(),
                'accounts' => array_map(
                    function (AccountCategory $accountCategory) {
                        return $accountCategory->getAccountId();
                    },
                    $categoryTemplate->getAccountCategories()
                )
            ];
        }
        return $templateOptions;
    }

    public function getTemplateDependentFieldValues(array $categoryTemplateIds): array
    {
        try {
            /** @var CategoryTemplates $categoryTemplates */
            $categoryTemplates = $this->categoryTemplateStorage->fetchCollectionByFilter(
                (new CategoryTemplateFilter('all', 1))->setId($categoryTemplateIds)
            );
            $categories = $this->getCategoriesForCategoryTemplates($categoryTemplates);
            $accounts = $this->getAccountsForCategories($categoryTemplates);
            return $this->getTemplateDependentFieldValuesArray($categoryTemplates, $categories, $accounts);
        } catch (NotFound $exception) {
            return [];
        }
    }

    protected function getCategoriesForCategoryTemplates(CategoryTemplates $categoryTemplates): Categories
    {
        $categoryIds = array_merge(...array_map(
            function(CategoryTemplate $categoryTemplate) {
                return $categoryTemplate->getCategoryIds();
            },
            iterator_to_array($categoryTemplates)
        ));

        $filter = (new CategoryFilter('all', 1))->setId($categoryIds);
        if (empty($categoryIds)) {
            return new Categories(Category::class, 'fetchCollectionByFilter', $filter->toArray());
        }
        return $this->categoryStorage->fetchCollectionByFilter($filter);
    }

    protected function getAccountsForCategories(CategoryTemplates $categoryTemplates): Accounts
    {
        $accountIds = [];
        /** @var CategoryTemplate $categoryTemplate */
        foreach ($categoryTemplates as $categoryTemplate) {
            foreach ($categoryTemplate->getAccountCategories() as $accountCategory) {
                $accountIds[$accountCategory->getAccountId()] = $accountCategory->getAccountId();
            }
        }

        $filter = (new AccountFilter('all', 1))->setId($accountIds);
        if (empty($accountIds)) {
            return new Accounts(Account::class, 'fetchCollectionByFilter', $filter->toArray());
        }
        return $this->accountStorage->fetchCollectionByFilter($filter);
    }

    /**
     * @param CategoryTemplate[] $categoryTemplates
     * @param Category[] $categories
     * @param Account[] $accounts
     */
    protected function getTemplateDependentFieldValuesArray(
        CategoryTemplates $categoryTemplates,
        Categories $categories,
        Accounts $accounts
    ): array {
        $templateDependentFieldValues = [];
        foreach ($categoryTemplates as $categoryTemplate) {
            $templateDependentFieldValues[$categoryTemplate->getId()] = [
                'name' => $categoryTemplate->getName(),
                'categories' => [],
            ];

            /** @var AccountCategory $accountCategory */
            foreach ($categoryTemplate->getAccountCategories() as $accountCategory) {
                $category = $categories->getById($accountCategory->getCategoryId());
                if (!($category instanceof Category)) {
                    continue;
                }

                try {
                    $account = $accounts->getById($accountCategory->getAccountId());
                    $fieldValues = $this
                        ->getCategoryDependentServiceInterface($account)
                        ->getCategoryDependentValues($account, $category->getId());
                } catch (ListingException $exception) {
                    // Field values are not supported on selected category
                }

                $templateDependentFieldValues[$categoryTemplate->getId()]['accounts'][$accountCategory->getAccountId()] = [
                    'title' => $category->getTitle(),
                    'categoryId' => $category->getId(),
                    'accountId' => $accountCategory->getAccountId(),
                    'channel' => $category->getChannel(),
                    'fieldValues' => $fieldValues ?? [],
                ];
            }
        }
        return $templateDependentFieldValues;
    }

    protected function getCategoryDependentServiceInterface($accountOrChannel): CategoryDependentServiceInterface
    {
        return $this->channelListingFactory->fetchAndValidateChannelService(
            $accountOrChannel,
            CategoryDependentServiceInterface::class
        );
    }
}