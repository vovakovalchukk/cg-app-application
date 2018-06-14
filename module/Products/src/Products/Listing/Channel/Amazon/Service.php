<?php
namespace Products\Listing\Channel\Amazon;

use CG\Account\Shared\Entity as Account;
use CG\Amazon\Category\ExternalData\Data;
use CG\Listing\Client\Service as ListingService;
use CG\Product\Category\ExternalData\Service as CategoryExternalService;
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
    /** @var CategoryExternalService */
    protected $categoryExternalService;

    public function __construct(
        CategoryService $categoryService,
        CategoryExternalService $categoryExternalService
    ) {
        $this->categoryService = $categoryService;
        $this->categoryExternalService = $categoryExternalService;
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
        $categoryData = $this->fetchAmazonSpecificCategoryData($categoryId);
        return [
            'itemSpecifics' => $this->getItemSpecifics(),
            'rootCategories' => $this->categoryService->fetchRootCategoriesForAccount($account, true, null, false),
            'variationThemes' => $this->getVariationThemes($categoryData),
        ];
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

    protected function getVariationThemes(?Data $categoryData = null): array
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

    protected function fetchAmazonSpecificCategoryData(int $categoryId): ?Data
    {
        try {
            $categoryExternal = $this->categoryExternalService->fetch($categoryId);
            return $categoryExternal->getData();
        } catch (NotFound $e) {
            return null;
        }
    }
}
