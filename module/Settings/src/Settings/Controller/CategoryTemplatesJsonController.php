<?php
namespace Settings\Controller;

use Application\Controller\AbstractJsonController;

class CategoryTemplatesJsonController extends AbstractJsonController
{
    const ROUTE_ACCOUNTS = 'accounts';
    const ROUTE_FETCH = 'fetch';
    const ROUTE_CATEGORY_ROOTS = 'categoryRoots';
    const ROUTE_SAVE = 'save';
    const ROUTE_CATEGORY_CHILDREN = 'categoryChildren';
    const ROUTE_REFRESH_CATEGORIES = 'refreshCategories';
    const ROUTE_TEMPLATE_DELETE = 'templateDelete';

    public function accountsAction()
    {
        return $this->buildResponse([
            'accounts' => [
                12 => [
                    'channel' => 'ebay',
                    'displayName' => 'eBay',
                    'refreshable' => false
                ],
                36 => [
                    'channel' => 'shopify',
                    'displayName' => 'Shopify',
                    'refreshable' => true
                ],
                42 => [
                    'channel' => 'big-commerce',
                    'displayName' => 'BigCommerce',
                    'refreshable' => true
                ],
                115 => [
                    'channel' => 'woo-commerce',
                    'displayName' => 'WooCommerce',
                    'refreshable' => false
                ]
            ]
        ]);
    }

    public function fetchAction()
    {

        return $this->buildResponse([
            'categoryTemplates' => [
                1 => [
                    'etag' => '12321esdfc2342jkda',
                    'name' => 'washing machines test',
                    'accountCategories' => [
                        'accountId' => 12,
                        'categories' => [
                            [
                                'value' => '12345',
                                'name' => 'Washing machines and others',
                                'selected' => false
                            ],
                            [
                                'value' => '34567',
                                'name' => 'Dryers',
                                'selected' => true
                            ],
                            [
                                'value' => '28234',
                                'name' => 'Washer dryers',
                                'selected' => false
                            ]
                        ]
                    ]
                ]
            ]
        ]);
    }

    public function categoryRootsAction()
    {
        return $this->buildResponse([
            'accountCategories' => [
                [
                    'accountId' => 12,
                    'categories' => [
                        '123' => 'Televisions',
                        '345' => 'Clothes',
                        '567' => 'Phones'
                    ]
                ]
            ]
        ]);
    }

    public function saveAction()
    {
        return $this->buildResponse([
            'valid' => true,
            'id' => 726,
            'etag' => '12321esdfc2342jkda',
            'errors' => [
                [
                    'code' => 'existing',
                    'message' => 'You have already mapped this category',
                    'existing' => [
                        'name' => 'TVs',
                        'accountId' => 36,
                        'externalCategoryId' => 12345
                    ]
                ]
            ]
        ]);
    }

    public function categoryChildrenAction()
    {
        return $this->buildResponse([
            'categories' => [
                '1023' => 'Televisions Child',
                '2354' => 'Clothes Child',
                '8721' => 'Phones Child'
            ]
        ]);
    }

    public function refreshCategoriesAction()
    {
        return $this->buildResponse([
            'categories' => [
                '91' => 'Refetched Televisions',
                '103' => 'Refetched Clothes Child',
                '209' => 'Refetched Phones Child'
            ]
        ]);
    }

    public function templateDeleteAction()
    {
        return $this->buildResponse([
            'valid' => true,
            'errors' => []
        ]);
    }
}
