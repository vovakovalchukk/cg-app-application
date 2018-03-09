<?php
namespace Settings\Controller;

use Application\Controller\AbstractJsonController;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG\User\OrganisationUnit\Service as UserOUService;
use \Settings\Category\Template\Service as CategoryTemplateService;

class CategoryTemplatesJsonController extends AbstractJsonController
{
    const ROUTE_ACCOUNTS = 'accounts';
    const ROUTE_FETCH = 'fetch';
    const ROUTE_CATEGORY_ROOTS = 'categoryRoots';
    const ROUTE_SAVE = 'save';
    const ROUTE_CATEGORY_CHILDREN = 'categoryChildren';
    const ROUTE_REFRESH_CATEGORIES = 'refreshCategories';
    const ROUTE_TEMPLATE_DELETE = 'templateDelete';

    /** @var UserOUService */
    protected $userOuService;
    /** @var  CategoryTemplateService */
    protected $categoryTemplateService;

    public function __construct(JsonModelFactory $jsonModelFactory,
        UserOUService $userOuService,
        CategoryTemplateService $categoryTemplateService
    ) {
        parent::__construct($jsonModelFactory);
        $this->userOuService = $userOuService;
        $this->categoryTemplateService = $categoryTemplateService;
    }

    public function accountsAction()
    {
        $ou = $this->userOuService->getRootOuByActiveUser();
        return $this->buildResponse([
            'accounts' => $this->categoryTemplateService->fetchAccounts($ou)
        ]);
    }

    public function fetchAction()
    {
        $data =  [
            1 => [
                'etag' => '12321esdfc2342jkda',
                'name' => 'washing machines test',
                'accountCategories' => [
                    [
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
                    ],
                    [
                        'accountId' => 36,
                        'categories' => [
                            [
                                'value' => '12345',
                                'name' => 'Washing machines shopify',
                                'selected' => false
                            ],
                            [
                                'value' => '34567',
                                'name' => 'Dryers shopify',
                                'selected' => true
                            ],
                            [
                                'value' => '28234',
                                'name' => 'Washer dryers shopify',
                                'selected' => false
                            ]
                        ]
                    ],
                    [
                        'accountId' => 42,
                        'categories' => [
                            [
                                'value' => '12345',
                                'name' => 'Washing machines and others BigCommerce',
                                'selected' => false
                            ],
                            [
                                'value' => '34567',
                                'name' => 'Dryers BigCommerce',
                                'selected' => true
                            ],
                            [
                                'value' => '28234',
                                'name' => 'Washer dryers BigCommerce',
                                'selected' => false
                            ]
                        ]
                    ]
                ]
            ],
            2 => [
                'etag' => '12321esdfc2342jkda',
                'name' => 'watches',
                'accountCategories' => [
                    [
                        'accountId' => 12,
                        'categories' => [
                            [
                                'value' => '1444',
                                'name' => 'expensive watches',
                                'selected' => true
                            ],
                            [
                                'value' => '98721',
                                'name' => 'good price watches',
                                'selected' => false
                            ],
                            [
                                'value' => '45172',
                                'name' => 'cheap watches',
                                'selected' => false
                            ]
                        ]
                    ],
                    [
                        'accountId' => 36,
                        'categories' => [
                            [
                                'value' => '34231',
                                'name' => 'just watches',
                                'selected' => false
                            ],
                            [
                                'value' => '76314',
                                'name' => 'smart watch',
                                'selected' => false
                            ],
                            [
                                'value' => '94324',
                                'name' => 'no watches haha',
                                'selected' => true
                            ]
                        ]
                    ],
                    [
                        'accountId' => 115,
                        'categories' => [
                            [
                                'value' => '9278341',
                                'name' => 'Washing machines and others',
                                'selected' => true
                            ],
                            [
                                'value' => '987312',
                                'name' => 'Dryers',
                                'selected' => false
                            ],
                            [
                                'value' => '123543',
                                'name' => 'Washer dryers',
                                'selected' => false
                            ]
                        ]
                    ]
                ]
            ],
            3 => [
                'etag' => '12321esdfc2342jkda',
                'name' => 'computers',
                'accountCategories' => [
                    [
                        'accountId' => 36,
                        'categories' => [
                            [
                                'value' => '455662',
                                'name' => 'computers 1 ',
                                'selected' => false
                            ],
                            [
                                'value' => '92831',
                                'name' => 'computers 2',
                                'selected' => true
                            ],
                            [
                                'value' => '1235217',
                                'name' => 'computers 3',
                                'selected' => false
                            ]
                        ]
                    ],
                    [
                        'accountId' => 42,
                        'categories' => [
                            [
                                'value' => '12313123',
                                'name' => 'monitors',
                                'selected' => true
                            ],
                            [
                                'value' => '345627',
                                'name' => 'keyboards',
                                'selected' => false
                            ],
                            [
                                'value' => '221823',
                                'name' => 'laptops',
                                'selected' => false
                            ]
                        ]
                    ],
                    [
                        'accountId' => 115,
                        'categories' => [
                            [
                                'value' => '12123345',
                                'name' => 'cables',
                                'selected' => false
                            ],
                            [
                                'value' => '8921789',
                                'name' => 'tablets',
                                'selected' => true
                            ],
                            [
                                'value' => '89213',
                                'name' => 'nothing here',
                                'selected' => false
                            ]
                        ]
                    ]
                ]
            ]
        ];
        $search = $this->params()->fromPost('search', '');
        $page = $this->params()->fromPost('page', 1);

        if (!empty($search)) {
            $data = array_filter($data, function($value) use ($search) {
                return strpos($value['name'], $search) !== false;
            });
        }

        $data = array_slice($data, ($page - 1) * 2, 2, true);

        return $this->buildResponse(['categoryTemplates' => $data]);
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
                ],
                [
                    'accountId' => 36,
                    'categories' => [
                        '123' => 'Televisions 2',
                        '345' => 'Clothes 2',
                        '567' => 'Phones 2'
                    ]
                ],
                [
                    'accountId' => 42,
                    'categories' => [
                        '123' => 'Televisions 3',
                        '345' => 'Clothes 3',
                        '567' => 'Phones 3'
                    ]
                ],
                [
                    'accountId' => 115,
                    'categories' => [
                        '123' => 'Televisions 4',
                        '345' => 'Clothes 4',
                        '567' => 'Phones 4'
                    ]
                ]
            ]
        ]);
    }

    public function saveAction()
    {
        $success = $this->params()->fromPost('success' , false);
        $success = !($success === 'false' || $success === false);

        if ($success) {
            return $this->buildSuccessResponse([
                'valid' => true,
                'id' => 726,
                'etag' => '12321esdfc2342jkda',
                'errors' => false
            ]);
        }

        return $this->buildErrorResponse(
            [
                'code' => 'existing',
                'message' => 'You have already mapped this category',
                'existing' => [
                    'name' => 'Washing machines and others',
                    'accountId' => 36,
                    'externalCategoryId' => 12345
                ]
            ],
            [
                'valid' => false,
                'id' => 1
            ]
        );
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
                '103' => 'Refetched Clothes',
                '209' => 'Refetched Phones'
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