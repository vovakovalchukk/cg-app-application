<?php
namespace Products\Listing\Channel\Amazon;

use CG\Account\Shared\Entity as Account;
use CG\Amazon\Category\ExternalData\Data as AmazonCategoryExternalData;
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
        return $categoryData->getAttributes();
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
}
