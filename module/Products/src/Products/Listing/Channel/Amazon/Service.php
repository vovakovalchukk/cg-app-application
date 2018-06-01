<?php
namespace Products\Listing\Channel\Amazon;

use CG\Account\Shared\Entity as Account;
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

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
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
            'itemSpecifics' => $this->getItemSpecifics(),
            'rootCategories' => $this->categoryService->fetchRootCategoriesForAccount($account, true, null, false)
        ];
    }

    protected function getItemSpecifics(): array
    {
        $itemSpecifics = json_decode(file_get_contents('home.json'), true);
        $attributes = $itemSpecifics['attributes'];
        $required = [];
        $optional = [];
        foreach ($attributes as $attribute) {
            if (!empty($attribute['options'])) {
                $attribute['options'] = array_combine($attribute['options'], $attribute['options']);
            }
            if ($attribute['required']) {
                $required[$attribute['name']] = $attribute;
            } else {
                $optional[$attribute['name']] = $attribute;
            }
        }

        return [
            'required' => $required,
            'optional' => $optional
        ];

        $fieldNames = ['Brand', 'Size', 'Type', 'Color', 'Material', 'Composition', 'MultiPack', 'Number in a pack', 'Length', 'Style', 'Collar', 'Test one'];
        $values = [
            [
                'type' => 'select',
                'options' => $this->getOptionsForSelect($fieldNames),
                'minValues' => 1,
                'maxValues' => 1
            ],
            [
                'type' => 'select',
                'options' => $this->getOptionsForSelect($fieldNames),
                'minValues' => 0,
                'maxValues' => mt_rand(1, 10)
            ],
            [
                'type' => 'select',
                'options' => $this->getOptionsForSelect($fieldNames),
                'minValues' => 0,
                'maxValues' => mt_rand(1, 10)
            ],
            [
                'type' => 'text',
                'minValues' => 0,
                'maxValues' => 1
            ],
            [
                'type' => 'text',
                'minValues' => 0,
                'maxValues' => 10
            ]
        ];

        $required = [];
        for ($i = 0; $i < mt_rand(5, 6); $i++) {
            shuffle($fieldNames);
            shuffle($values);
            $required[array_pop($fieldNames)] = $values[0];
        }

        $optional = [];
        for ($i = 0; $i < mt_rand(5, 6); $i++) {
            shuffle($fieldNames);
            shuffle($values);
            $optional[array_pop($fieldNames)] = $values[0];
        }

        return [
            'required' => $required,
            'optional' => $optional
        ];
    }

    protected function getOptionsForSelect(array $fieldNames): array
    {
        $options = array_slice($fieldNames, mt_rand(0, 5), mt_rand(6, 11));
        return array_combine($options, $options);
    }
}
