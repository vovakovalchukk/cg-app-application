<?php

namespace Products\Controller\CreateListings;

use Application\Controller\AbstractJsonController;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG_UI\View\Prototyper\JsonModelFactory;
use Products\Listing\Create\Service as CreateListingsService;

class EbayJsonController extends AbstractJsonController
{
    const ROUTE_CREATE_LISTINGS = 'CreateListings';
    const ROUTE = 'EbayListings';
    const ROUTE_DEFAULT_SETTINGS = 'DefaultSettings';
    const ROUTE_CATEGORY_DEPENDENT_FIELD_VALUES = 'CategoryDependentFieldValues';
    const ROUTE_ACCOUNT_SPECIFIC_FIELD_VALUES = 'AccountSpecificFieldValues';

    /** @var JsonModelFactory */
    protected $jsonModelFactory;
    /** @var CreateListingsService */
    protected $createListingsService;

    public function __construct(
        JsonModelFactory $jsonModelFactory,
        CreateListingsService $createListingsService
    ) {
        parent::__construct($jsonModelFactory);
        $this->createListingsService = $createListingsService;
    }

    public function categoryDependentFieldValuesAction()
    {
        $externalCategoryId = $this->params()->fromRoute('externalCategoryId');
        $dummyCategoryIdToFields = [
            1 => [
                'listingDuration' => [
                    '1 days',
                    '2 days',
                    '3 days'
                ]
            ],
            2 => [
                'listingDuration' => [
                    '4 hours',
                    '5 hours',
                    '6 hours'
                ]
            ],
            3 => [
                'listingDuration' => [
                    '4 months',
                    '5 months',
                    '6 months'
                ]
            ]
        ];
        return $this->jsonModelFactory->newInstance($dummyCategoryIdToFields[$externalCategoryId]);
    }

    public function defaultSettingsAjaxAction()
    {
        $accountId = $this->params()->fromRoute('accountId');

        try {
            $defaultSettings = $this->createListingsService->fetchDefaultSettingsForAccount($accountId);
            if (empty($defaultSettings)) {
                return $this->buildErrorResponse('NO_SETTINGS');
            }
            return $this->buildResponse($defaultSettings);
        } catch (NotFound $e) {
            return $this->buildErrorResponse('The account ' . $accountId . ' could not be found.');
        } catch (\Exception $e) {
            return $this->buildErrorResponse('An error has occurred. Please try again');
        }
    }

    public function channelSpecificFieldValuesAction()
    {
        return $this->jsonModelFactory->newInstance([
            'category' => [
                1 => 'spuds',
                2 => 'bananas',
                3 => 'bicycles'
            ],
            'shippingService' => [
                1 => 'Royal Snail',
                2 => 'Parcel Power',
                3 => 'Carrier Pidgeon'
            ],
            'currency' => [
                'BTC',
                'ETH',
                'XRP'
            ]
        ]);
    }
}