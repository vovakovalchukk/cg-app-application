<?php
namespace Products\Listing\Channel\Amazon;

use CG\Account\Shared\Entity as Account;
use CG\Amazon\Category\ExternalData\Data as AmazonCategoryExternalData;
use CG\Product\Category\ExternalData\Entity as CategoryExternalEntity;
use CG\Product\Category\ExternalData\StorageInterface as CategoryExternalStorage;
use CG\Stdlib\Exception\Runtime\NotFound;
use Products\Listing\Category\Service as CategoryService;
use Products\Listing\Channel\CategoryChildrenInterface;
use Products\Listing\Channel\CategoryDependentServiceInterface;
use Products\Listing\Channel\ChannelSpecificValuesInterface;
use function CG\Stdlib\isArrayAssociative;

class Service implements
    ChannelSpecificValuesInterface,
    CategoryChildrenInterface,
    CategoryDependentServiceInterface
{
    const ITEM_SPECIFIC_VARIATION_DATA = 'VariationData';
    const ITEM_SPECIFIC_VARIATION_THEME = 'VariationTheme';

    const HIDDEN_ITEM_SPECIFICS = [
        self::ITEM_SPECIFIC_VARIATION_DATA => self::ITEM_SPECIFIC_VARIATION_DATA,
        self::ITEM_SPECIFIC_VARIATION_THEME => self::ITEM_SPECIFIC_VARIATION_THEME
    ];

    /** @var CategoryService */
    protected $categoryService;
    /** @var  CategoryExternalStorage */
    protected $categoryExternalStorage;

    public function __construct(
        CategoryService $categoryService,
        CategoryExternalStorage $categoryExternalStorage
    ) {
        $this->categoryService = $categoryService;
        $this->categoryExternalStorage = $categoryExternalStorage;
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
        return [
            'itemSpecifics' => $this->getItemSpecifics($categoryId),
            'rootCategories' => $this->categoryService->fetchRootCategoriesForAccount($account, true, null, false)
        ];
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
            $itemSpecifics = $amazonData->getAttributes();
            $this->filterItemSpecifics($itemSpecifics);
            return $itemSpecifics;
        } catch (NotFound $e) {
            return [];
        }
    }

    protected function filterItemSpecifics(array &$itemSpecifics): void
    {
        foreach ($itemSpecifics as $name => $values) {
            if (isset(static::HIDDEN_ITEM_SPECIFICS[$name])) {
                unset($itemSpecifics[$name]);
                continue;
            }

            if (isArrayAssociative($values)) {
                $this->filterItemSpecifics($values);
            }
        }
    }
}
