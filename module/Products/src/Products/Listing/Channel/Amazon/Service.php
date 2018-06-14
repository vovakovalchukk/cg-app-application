<?php
namespace Products\Listing\Channel\Amazon;

use CG\Account\Shared\Entity as Account;
use CG\FeatureFlags\Service as FeatureFlagsService;
use CG\Listing\Client\Service as ListingService;
use CG\OrganisationUnit\Service as OrganisationUnitService;
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
    /** @var FeatureFlagsService */
    protected $featureFlagsService;
    /** @var OrganisationUnitService */
    protected $organisationUnitService;

    public function __construct(
        CategoryService $categoryService,
        FeatureFlagsService $featureFlagsService,
        OrganisationUnitService $organisationUnitService
    ) {
        $this->categoryService = $categoryService;
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
            'itemSpecifics' => $this->getItemSpecifics(),
            'rootCategories' => $this->categoryService->fetchRootCategoriesForAccount($account, true, null, false),
        ];
        $rootOu = $this->organisationUnitService->getRootOuFromOuId($account->getOrganisationUnitId());
        if ($this->featureFlagsService->isActive(ListingService::FEATURE_FLAG_CREATE_LISTINGS_VARIATIONS_AMAZON, $rootOu)) {
            $values['variationThemes'] = $this->getVariationThemes();
        }
        return $values;
    }

    protected function getItemSpecifics(): array
    {
        $itemSpecifics = json_decode(file_get_contents('home.json'), true);
        return $itemSpecifics['attributes'];
    }

    protected function getOptionsForSelect(array $fieldNames): array
    {
        $options = array_slice($fieldNames, mt_rand(0, 5), mt_rand(6, 11));
        return array_combine($options, $options);
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
