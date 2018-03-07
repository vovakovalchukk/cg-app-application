<?php
namespace Products\Listing\Channel\Ebay;

use CG\Account\Credentials\Cryptor;
use CG\Account\Shared\Entity as Account;
use CG\Ebay\Category\ExternalData\Data;
use CG\Ebay\Category\ExternalData\FeatureHelper;
use CG\Ebay\Credentials;
use CG\Ebay\Site\CurrencyMap;
use CG\Ebay\Site\Map as SiteMap;
use CG\Order\Client\Shipping\Method\Storage\Api as ShippingMethodService;
use CG\Order\Shared\Shipping\Method\Collection as ShippingMethodCollection;
use CG\Order\Shared\Shipping\Method\Entity as ShippingMethod;
use CG\Order\Shared\Shipping\Method\Filter as ShippingMethodFilter;
use CG\Product\Category\ExternalData\Entity as CategoryExternal;
use CG\Product\Category\ExternalData\Service as CategoryExternalService;
use CG\Stdlib\Exception\Runtime\NotFound;
use Products\Listing\Category\Service as CategoryService;
use Products\Listing\Channel\CategoryChildrenInterface;
use Products\Listing\Channel\CategoryDependentServiceInterface;
use Products\Listing\Channel\ChannelSpecificValuesInterface;
use Products\Listing\Channel\DefaultAccountSettingsInterface;

class Service implements
    CategoryDependentServiceInterface,
    DefaultAccountSettingsInterface,
    ChannelSpecificValuesInterface,
    CategoryChildrenInterface
{
    const ALLOWED_SETTINGS_KEYS = [
        'listingLocation' => 'listingLocation',
        'listingCurrency' => 'listingCurrency',
        'paypalEmail' => 'paypalEmail',
        'listingDuration' => 'listingDuration',
        'listingDispatchTime' => 'listingDispatchTime',
        'listingPaymentMethods' => 'listingPaymentMethods'
    ];

    const TYPE_TEXT = 'text';
    const TYPE_SELECT = 'select';
    const TYPE_TEXTSELECT = 'textselect';

    /** @var CategoryService */
    protected $categoryService;
    /** @var Cryptor */
    protected $cryptor;
    /** @var ShippingMethodService */
    protected $shippingMethodService;
    /** @var CategoryExternalService */
    protected $categoryExternalService;
    /** @var array */
    protected $postData;

    protected $selectionModesToInputTypes = [
        'FreeText' => self::TYPE_TEXT,
        'SelectionOnly' => self::TYPE_SELECT,
    ];

    public function __construct(
        CategoryService $categoryService,
        Cryptor $cryptor,
        ShippingMethodService $shippingMethodService,
        CategoryExternalService $categoryExternalService,
        array $postData = []
    ) {
        $this->categoryService = $categoryService;
        $this->cryptor = $cryptor;
        $this->shippingMethodService = $shippingMethodService;
        $this->categoryExternalService = $categoryExternalService;
        $this->postData = $postData;
    }

    public function getCategoryChildrenForCategoryAndAccount(Account $account, int $categoryId): array
    {
        try {
            return $this->categoryService->fetchCategoryChildrenForParentCategoryId($categoryId);
        } catch (NotFound $e) {
            return [];
        }
    }

    public function getCategoryDependentValues(Account $account, int $categoryId): array
    {
        $ebayData = $this->fetchEbayCategoryData($categoryId);

        return [
            'listingDuration' => $this->getListingDurationsFromEbayCategoryData($ebayData),
            'itemSpecifics' => $this->getItemSpecificsFromEbayCategoryData($ebayData),
        ];
    }

    public function getDefaultSettingsForAccount(Account $account): array
    {
        return $this->filterDefaultSettingsKeys($account->getExternalData());
    }

    public function getChannelSpecificFieldValues(Account $account): array
    {
        return [
            'category' => $this->getCategoryOptionsForAccount($account),
            'shippingService' => $this->getShippingMethodsForAccount($account),
            'currency' => $this->getCurrencySymbolForAccount($account),
            'sites' => SiteMap::getIdToNameMap(),
            'defaultSiteId' => $this->fetchDefaultSiteIdForAccount($account)
        ];
    }

    protected function fetchEbayCategoryData(int $categoryId): ?Data
    {
        try {
            /** @var CategoryExternal $categoryExternal */
            $categoryExternal = $this->categoryExternalService->fetch($categoryId);
            /** @var Data $ebayData */
            return $categoryExternal->getData();
        } catch (NotFound $e) {
            return null;
        }
    }

    protected function getListingDurationsFromEbayCategoryData(?Data $ebayData): array
    {
        if (!$ebayData) {
            return [];
        }
        $listingDurations = (new FeatureHelper($ebayData))->getListingDurationsForType();
        return $this->formatListingDurationsArray($listingDurations);
    }

    protected function getItemSpecificsFromEbayCategoryData(?Data $ebayData): array
    {
        if (!$ebayData || empty($ebayData->getCategorySpecifics())) {
            return [];
        }
        $required = [];
        $optional = [];
        $categorySpecifics = $ebayData->getCategorySpecifics();
        foreach ($categorySpecifics['NameRecommendation'] as $recommendation) {
            $name = $recommendation['Name'];
            $itemSpecifics = $this->buildItemSpecificsDataFromRecommendation($recommendation);
            if ($itemSpecifics['minValues'] == 0) {
                $optional[$name] = $itemSpecifics;
            } else {
                $required[$name] = $itemSpecifics;
            }
        }

        return [
            'required' => $required,
            'optional' => $optional,
        ];
    }

    protected function buildItemSpecificsDataFromRecommendation(array $recommendation): array
    {
        return [
            'type' => $this->getInputTypeForRecommendation($recommendation),
            'options' => $this->getOptionsForRecommendation($recommendation),
            'minValues' => $this->getMinValuesForRecommendation($recommendation),
            'maxValues' => $this->getMaxValuesForRecommendation($recommendation),
        ];
    }

    protected function getInputTypeForRecommendation(array $recommendation): string
    {
        $inputType = $this->getRawInputTypeForRecommendation($recommendation);
        // If its technically free text but there are recommended values then we need to allow both
        if ($inputType == static::TYPE_TEXT && isset($recommendation['ValueRecommendation'])) {
            $inputType = static::TYPE_TEXTSELECT;
        }
        return $inputType;
    }

    protected function getRawInputTypeForRecommendation(array $recommendation): string
    {
        if (!isset($recommendation['ValidationRules'], $recommendation['ValidationRules']['SelectionMode'])) {
            return static::TYPE_TEXT;
        }
        $selectionMode = $recommendation['ValidationRules']['SelectionMode'];
        if (!isset($this->selectionModesToInputTypes[$selectionMode])) {
            return static::TYPE_TEXT;
        }
        return $this->selectionModesToInputTypes[$selectionMode];
    }

    protected function getOptionsForRecommendation(array $recommendation): ?array
    {
        if (!isset($recommendation['ValueRecommendation'])) {
            return null;
        }
        $options = [];
        $valueRecommendations = $recommendation['ValueRecommendation'];
        // When there's only one recommendation it doesn't get stored as an array
        if (!isset($valueRecommendations[0])) {
            $valueRecommendations = [$valueRecommendations];
        }
        foreach ($valueRecommendations as $valueRecommendation) {
            $value = (string)$valueRecommendation['Value'];
            $options[$value] = $value;
        }
        return $options;
    }

    protected function getMinValuesForRecommendation(array $recommendation): int
    {
        return isset($recommendation['ValidationRules'], $recommendation['ValidationRules']['MinValues']) ? (int)$recommendation['ValidationRules']['MinValues'] : 0;
    }

    protected function getMaxValuesForRecommendation(array $recommendation): ?int
    {
        return isset($recommendation['ValidationRules'], $recommendation['ValidationRules']['MaxValues']) ? (int)$recommendation['ValidationRules']['MaxValues'] : null;
    }

    protected function filterDefaultSettingsKeys(array $data)
    {
        return array_filter(
            $data,
            function($key) {
                return isset(static::ALLOWED_SETTINGS_KEYS[$key]);
            },
            ARRAY_FILTER_USE_KEY
        );
    }

    protected function getCategoryOptionsForAccount(Account $account): array
    {
        return $this->categoryService->fetchRootCategoriesForAccount(
            $account,
            false,
            $this->getEbaySiteIdForAccount($account),
            false
        );
    }

    protected function getShippingMethodsForAccount(Account $account): array
    {
        try {
            /** @var ShippingMethodCollection $shippingMethods */
            $shippingMethods = $this->shippingMethodService->fetchCollectionByFilter(
                new ShippingMethodFilter('all', 1, [], ['ebay'], [], [$account->getRootOrganisationUnitId()])
            );
            return $this->formatShippingMethodsArray($shippingMethods);
        } catch (NotFound $e) {
            return [];
        }
    }

    protected function getCurrencySymbolForAccount(Account $account): ?string
    {
        try {
            $siteId = $this->getEbaySiteIdForAccount($account);
            return CurrencyMap::getCurrencySymbolBySiteId($siteId);
        } catch (\InvalidArgumentException $e) {
            return null;
        }
    }

    protected function getEbaySiteIdForAccount(Account $account): int
    {
        if (isset($this->postData['siteId'])) {
            return intval($this->postData['siteId']);
        }
        return $this->fetchDefaultSiteIdForAccount($account);
    }

    protected function fetchDefaultSiteIdForAccount(Account $account): int
    {
        /** @var Credentials $credentials */
        $credentials = $this->cryptor->decrypt($account->getCredentials());
        return $credentials->getSiteId();
    }

    protected function formatShippingMethodsArray(ShippingMethodCollection $shippingMethods): array
    {
        $methods = [];
        /** @var ShippingMethod $shippingMethod */
        foreach ($shippingMethods as $shippingMethod) {
            $methods[$shippingMethod->getMethod()] = $shippingMethod->getMethod();
        }
        return $methods;
    }

    /**
     * A listing duration from the array looks like Days_30 or Days_7, so we can split it out by the underscore
     * to format a human readable display name
     * Also, it can have a value like GTC, so, if this is the case, just return it as it is.
     * @param array $listingDurations
     * @return array
     */
    protected function formatListingDurationsArray(array $listingDurations): array
    {
        $durations = [];
        foreach ($listingDurations as $listingDuration) {
            $durationName = $listingDuration;
            $durationArray = explode('_', $listingDuration);
            if (count($durationArray) === 2) {
                $durationName = array_pop($durationArray) . ' ' . array_pop($durationArray);
            }
            $durations[$listingDuration] = $durationName;
        }
        return $durations;
    }
}
