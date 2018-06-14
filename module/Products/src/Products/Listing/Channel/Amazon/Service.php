<?php
namespace Products\Listing\Channel\Amazon;

use CG\Account\Shared\Entity as Account;
use CG\Amazon\Category\ExternalData\Data as AmazonCategoryExternalData;
use CG\FeatureFlags\Service as FeatureFlagsService;
use CG\Listing\Client\Service as ListingService;
use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\Product\Category\ExternalData\Entity as CategoryExternalEntity;
use CG\Product\Category\ExternalData\StorageInterface as CategoryExternalStorage;
use CG\Stdlib\Exception\Runtime\NotFound;
use Products\Listing\Category\Service as CategoryService;
use Products\Listing\Channel\CategoryChildrenInterface;
use Products\Listing\Channel\CategoryDependentServiceInterface;
use Products\Listing\Channel\ChannelSpecificValuesInterface;

class Service implements
    ChannelSpecificValuesInterface,
    CategoryChildrenInterface,
    CategoryDependentServiceInterface
{
    /** @var CategoryService */
    protected $categoryService;
    /** @var  CategoryExternalStorage */
    protected $categoryExternalStorage;
    /** @var FeatureFlagsService */
    protected $featureFlagsService;
    /** @var OrganisationUnitService */
    protected $organisationUnitService;

    public function __construct(
        CategoryService $categoryService,
        CategoryExternalStorage $categoryExternalStorage,
        FeatureFlagsService $featureFlagsService,
        OrganisationUnitService $organisationUnitService
    ) {
        $this->categoryService = $categoryService;
        $this->categoryExternalStorage = $categoryExternalStorage;
        $this->featureFlagsService = $featureFlagsService;
        $this->organisationUnitService = $organisationUnitService;
    }

    public function getChannelSpecificFieldValues(Account $account): array
    {
        return [
            'categories' => $this->categoryService->fetchRootCategoriesForAccount($account, true, null, false)
        ];
    }

    public function getCategoryChildrenForCategoryAndAccount(Account $account, int $categoryId)
    {
        try {
            return $this->categoryService->fetchCategoryChildrenForParentCategoryId($categoryId);
        } catch (NotFound $e) {
            return [];
        }
    }

    public function getCategoryDependentValues(?Account $account, int $categoryId): array
    {
        $values = [
            'itemSpecifics' => $this->getItemSpecifics($categoryId),
            'rootCategories' => $this->categoryService->fetchRootCategoriesForAccount($account, true, null, false)
        ];
        $rootOu = $this->organisationUnitService->getRootOuFromOuId($account->getOrganisationUnitId());
        if ($this->featureFlagsService->isActive(ListingService::FEATURE_FLAG_CREATE_LISTINGS_VARIATIONS_AMAZON, $rootOu)) {
            $values['variationThemes'] = $this->getVariationThemes();
        }
        return $values;
    }

    protected function getItemSpecifics(int $categoryId): array
    {
        try {
            /** @var CategoryExternalEntity $categoryExternal */
            $categoryExternal = $this->categoryExternalStorage->fetch($categoryId);
            if ($categoryExternal->getChannel() !== 'amazon') {
                throw new NotFound('The given category ' . $categoryId . ' doesn\'t belong to Amazon');
            }
            /** @var AmazonCategoryExternalData $amazonData */
            $amazonData = $categoryExternal->getData();
            return $amazonData->getAttributes();
        } catch (NotFound $e) {
            return [];
        }
    }

    protected function getVariationThemes(): array
    {
        // To be replaced by LIS-237
        return [
            [
                'name' => 'SizeColor',
                'validValues' => [
                    [
                        'name' => "Size",
                        'options' => [
                            'small' => 'small',
                            'medium' => 'medium',
                            'large' => 'large',
                        ]
                    ],
                    [
                        'name' => "Color",
                        'options' => [
                            'red' => 'red',
                            'green' => 'green',
                            'blue' => 'blue',
                        ]
                    ],
                ]
            ],
            [
                'name' => 'Color',
                'validValues' => [
                    [
                        'name' => "Color",
                        'options' => [
                            'red' => 'red',
                            'green' => 'green',
                            'blue' => 'blue',
                        ]
                    ],
                ]
            ],
        ];
    }
}
