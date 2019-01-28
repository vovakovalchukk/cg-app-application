<?php
namespace Products\Listing\Channel\Amazon;

use CG\Account\Credentials\Cryptor;
use CG\Account\Shared\Entity as Account;
use CG\Amazon\Category\ExternalData\Data as AmazonCategoryExternalData;
use CG\Amazon\Credentials;
use CG\Product\Category\ExternalData\Entity as CategoryExternalData;
use CG\Product\Category\ExternalData\Service as CategoryExternalService;
use CG\Stdlib\Exception\Runtime\NotFound;
use Products\Listing\Category\Service as CategoryService;
use Products\Listing\Channel\CategoryChildrenInterface;
use Products\Listing\Channel\CategoryDependentServiceInterface;
use Products\Listing\Channel\ChannelSpecificValuesInterface;
use CG\Amazon\RegionFactory;
use CG\Amazon\RegionAbstract as Region;

class Service implements
    ChannelSpecificValuesInterface,
    CategoryChildrenInterface,
    CategoryDependentServiceInterface
{
    const ITEM_SPECIFIC_VARIATION_DATA = 'VariationData';
    const ITEM_SPECIFIC_VARIATION_THEME = 'VariationTheme';
    const ITEM_SPECIFIC_PARENTAGE = 'Parentage';

    const HIDDEN_ITEM_SPECIFICS = [
        self::ITEM_SPECIFIC_VARIATION_DATA => self::ITEM_SPECIFIC_VARIATION_DATA,
        self::ITEM_SPECIFIC_VARIATION_THEME => self::ITEM_SPECIFIC_VARIATION_THEME,
        self::ITEM_SPECIFIC_PARENTAGE => self::ITEM_SPECIFIC_PARENTAGE
    ];

    /** @var CategoryService */
    protected $categoryService;
    /** @var CategoryExternalService */
    protected $categoryExternalService;
    /** @var Cryptor */
    protected $cryptor;
    /** @var RegionFactory */
    protected $regionFactory;

    public function __construct(
        CategoryService $categoryService,
        CategoryExternalService $categoryExternalService,
        Cryptor $cryptor,
        RegionFactory $regionFactory
    ) {
        $this->categoryService = $categoryService;
        $this->categoryExternalService = $categoryExternalService;
        $this->cryptor = $cryptor;
        $this->regionFactory = $regionFactory;
    }

    public function getChannelSpecificFieldValues(Account $account): array
    {
        return [
            'categories' => $this->categoryService->fetchRootCategoriesForAccount(
                $account,
                true,
                $this->getMarketplaceForAccount($account),
                false
            )
        ];
    }

    protected function getMarketplaceForAccount(Account $account): string
    {
        /** @var Credentials $credentials */
        $credentials = $this->cryptor->decrypt($account->getCredentials());
        /** @var Region $region */
        $region = $this->regionFactory->getByRegionCode($credentials->getRegionCode());
        return $region->getCountryCodeForMarketplace($credentials->getDefaultMarketplaceId());
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
        $categoryData = $this->fetchAmazonSpecificCategoryData($categoryId);
        return [
            'itemSpecifics' => $this->getItemSpecifics($categoryData),
            'rootCategories' => $this->categoryService->fetchRootCategoriesForAccount($account, true, null, false),
            'variationThemes' => $this->getVariationThemes($categoryData),
        ];
    }

    protected function getItemSpecifics(?AmazonCategoryExternalData $categoryData): array
    {
        if (!$categoryData) {
            return [];
        }
        $itemSpecifics = $categoryData->getAttributes();
        return $this->filterItemSpecifics($itemSpecifics);
    }

    protected function getVariationThemes(?AmazonCategoryExternalData $categoryData = null): array
    {
        if (!$categoryData || !$categoryData->getVariationThemes()) {
            return [];
        }

        $variationThemes = [];
        foreach ($categoryData->getVariationThemes() as $variationTheme) {
            $variationThemes[] = [
                'name' => $variationTheme['name'],
                'validValues' => array_map(function($key, $options){
                    return [
                        'name' => $key,
                        'options' => array_combine($options, $options)
                    ];
                }, array_keys($variationTheme['validValues']), $variationTheme['validValues'])
            ];
        }
        return $variationThemes;
    }

    protected function fetchAmazonSpecificCategoryData(int $categoryId): ?AmazonCategoryExternalData
    {
        try {
            /** @var CategoryExternalData $categoryExternal */
            $categoryExternal = $this->categoryExternalService->fetch($categoryId);
            $data = $categoryExternal->getData();
            if (!$data instanceof AmazonCategoryExternalData) {
                throw new NotFound('The given category ' . $categoryId . ' doesn\'t belong to Amazon');
            }
            return $data;
        } catch (NotFound $e) {
            return null;
        }
    }

    protected function filterItemSpecifics(array $itemSpecifics): array
    {
        $result = [];
        foreach ($itemSpecifics as $itemSpecific) {
            $name = $itemSpecific['name'];
            if (isset(static::HIDDEN_ITEM_SPECIFICS[$name])) {
                continue;
            }

            $children = $itemSpecific['children'] ?? [];
            if (!empty($children)) {
                $itemSpecific['children'] = $this->filterItemSpecifics($children);
            }
            $result[] = $itemSpecific;
        }

        return $result;
    }
}
