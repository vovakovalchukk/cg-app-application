<?php
namespace Products\Listing\Channel\Ebay;

use CG\Account\Client\Service as AccountService;
use CG\Account\Credentials\Cryptor;
use CG\Account\Policy\Collection as AccountPolicyCollection;
use CG\Account\Policy\Entity as AccountPolicy;
use CG\Account\Policy\Filter as AccountPolicyFilter;
use CG\Account\Policy\Service as AccountPolicyService;
use CG\Account\Shared\Entity as Account;
use CG\Ebay\CatalogApi\Token\InitialisationService as TokenInitialisationService;
use CG\Ebay\Category\ExternalData\Data;
use CG\Ebay\Category\ExternalData\FeatureHelper;
use CG\Ebay\Credentials;
use CG\Ebay\Listing\Epid\Storage as EpidStorage;
use CG\Ebay\SellerPolicies\Service as EbayPoliciesService;
use CG\Ebay\Site\CurrencyMap;
use CG\Ebay\Site\Map as SiteMap;
use CG\Order\Client\Shipping\Method\Storage\Api as ShippingMethodService;
use CG\Order\Shared\Shipping\Method\Collection as ShippingMethodCollection;
use CG\Order\Shared\Shipping\Method\Entity as ShippingMethod;
use CG\Order\Shared\Shipping\Method\Filter as ShippingMethodFilter;
use CG\Product\Category\ExternalData\Entity as CategoryExternal;
use CG\Product\Category\ExternalData\Filter as CategoryExternalFilter;
use CG\Product\Category\ExternalData\Service as CategoryExternalService;
use CG\Stdlib\DateTime;
use CG\Stdlib\Exception\Runtime\InvalidCredentialsException;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\User\ActiveUserInterface;
use Products\Listing\Category\Service as CategoryService;
use Products\Listing\Channel\AccountDataInterface;
use Products\Listing\Channel\AccountPoliciesInterface;
use Products\Listing\Channel\CategoryChildrenInterface;
use Products\Listing\Channel\CategoryDependentServiceInterface;
use Products\Listing\Channel\ChannelDataInterface;
use Products\Listing\Channel\ChannelSpecificValuesInterface;
use Products\Listing\Channel\DefaultAccountSettingsInterface;
use function CG\Stdlib\isArrayAssociative;

class Service implements
    CategoryDependentServiceInterface,
    DefaultAccountSettingsInterface,
    ChannelSpecificValuesInterface,
    CategoryChildrenInterface,
    AccountPoliciesInterface,
    AccountDataInterface,
    ChannelDataInterface,
    LoggerAwareInterface
{
    use LogTrait;

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
    /** @var ActiveUserInterface */
    protected $activeUser;
    /** @var array */
    protected $postData;
    /** @var AccountPolicyService */
    protected $accountPolicyService;
    /** @var EbayPoliciesService */
    protected $ebayPoliciesService;
    /** @var TokenInitialisationService */
    protected $tokenInitialisationService;
    /** @var AccountService */
    protected $accountService;
    /** @var EpidStorage */
    protected $epidStorage;
    /** @var array */
    protected $credentials = [];

    protected $selectionModesToInputTypes = [
        'FreeText' => self::TYPE_TEXT,
        'SelectionOnly' => self::TYPE_SELECT,
    ];

    public function __construct(
        CategoryService $categoryService,
        Cryptor $cryptor,
        ShippingMethodService $shippingMethodService,
        CategoryExternalService $categoryExternalService,
        ActiveUserInterface $activeUser,
        AccountPolicyService $accountPolicyService,
        EbayPoliciesService $ebayPoliciesService,
        TokenInitialisationService $tokenInitialisationService,
        AccountService $accountService,
        EpidStorage $epidStorage,
        array $postData = []
    ) {
        $this->categoryService = $categoryService;
        $this->cryptor = $cryptor;
        $this->shippingMethodService = $shippingMethodService;
        $this->categoryExternalService = $categoryExternalService;
        $this->activeUser = $activeUser;
        $this->postData = $postData;
        $this->accountPolicyService = $accountPolicyService;
        $this->ebayPoliciesService =  $ebayPoliciesService;
        $this->tokenInitialisationService = $tokenInitialisationService;
        $this->accountService = $accountService;
        $this->epidStorage = $epidStorage;
    }

    public function getCategoryChildrenForCategoryAndAccount(Account $account, int $categoryId): array
    {
        try {
            return $this->appendEbaySpecificFieldsToCategoriesResponse(
                $this->categoryService->fetchCategoryChildrenForParentCategoryId($categoryId)
            );
        } catch (NotFound $e) {
            return [];
        }
    }

    public function getCategoryDependentValues(?Account $account, int $categoryId): array
    {
        $ebayData = $this->fetchEbayCategoryData($categoryId);

        return array_merge(
            [
                'listingDuration' => $this->getListingDurationsFromEbayCategoryData($ebayData),
                'shippingMethods' => $this->getShippingMethodsForAccount(
                    $account ? $account->getRootOrganisationUnitId() : $this->activeUser->getActiveUserRootOrganisationUnitId()
                ),
                'itemSpecifics' => $this->getItemSpecificsFromEbayCategoryData($ebayData),
                'pbse' => $this->getPbseStatusForCategory($ebayData)
            ],
            $this->fetchPoliciesForAccount($account)
        );
    }

    protected function getPbseStatusForCategory(?Data $ebayData): array
    {
        return [
            'required' => $this->getPbseRequiredStatus($ebayData)
        ];
    }

    protected function getPbseRequiredStatus(?Data $ebayData): bool
    {
        try {
            return $ebayData ? (new FeatureHelper($ebayData))->isFeatureEnabled('ProductRequiredEnabled') : false;
        } catch (\InvalidArgumentException $e) {
            $this->logWarningException($e);
            return false;
        }
    }

    protected function fetchPoliciesForAccount(Account $account): array
    {
        try {
            /** @var AccountPolicyCollection $accountPolicies */
            $accountPolicies = $this->accountPolicyService->fetchCollectionByFilter(
                (new AccountPolicyFilter)
                    ->setLimit('all')
                    ->setPage(1)
                    ->setAccountId([$account->getId()])
            );
            $returnPolicies = $this->formatReturnPolicies($accountPolicies->getBy('type', AccountPolicy::TYPE_RETURN));
            $shippingPolicies = $this->formatReturnPolicies($accountPolicies->getBy('type', AccountPolicy::TYPE_SHIPPING));
            $paymentPolicies = $this->formatReturnPolicies($accountPolicies->getBy('type', AccountPolicy::TYPE_PAYMENT));
        } catch (NotFound $exception) {
            $returnPolicies = [];
            $shippingPolicies = [];
            $paymentPolicies = [];
        }

        return [
            'returnPolicies' => $returnPolicies,
            'shippingPolicies' => $shippingPolicies,
            'paymentPolicies' => $paymentPolicies
        ];
    }

    protected function formatReturnPolicies(AccountPolicyCollection $accountPolicies): array
    {
        $policies = [];
        /** @var AccountPolicy $policy */
        foreach ($accountPolicies as $policy) {
            $policies[] = [
                'name' => $policy->getName(),
                'value' => $policy->getExternalId()
            ];
        }
        return $policies;
    }

    public function getDefaultSettingsForAccount(Account $account): array
    {
        return $this->filterDefaultSettingsKeys($account->getExternalData());
    }

    public function getChannelSpecificFieldValues(Account $account): array
    {
        return [
            'categories' => $this->getCategoryOptionsForAccount($account),
            'shippingService' => $this->getShippingMethodsForAccount($account->getRootOrganisationUnitId()),
            'currency' => $this->getCurrencySymbolForAccount($account),
            'sites' => SiteMap::getIdToNameMap(),
            'defaultSiteId' => $this->fetchDefaultSiteIdForAccount($account),

            //todo - to be implemented with no dummy data as part of TAC-433
            'templates' => json_encode([
                [
                    'id' => 1,
                    'name' => 'template1',
                    'html' => "<h1>Template 1 Title</h1>
                    some content in the template
                "
                ],
                [
                    'id' => 2,
                    'name' => 'template2',
                    'html' => "<h1>Template 2 Title</h1>
                    some content in the template
                "
                ],
                [
                    'id' => 3,
                    'name' => 'template3',
                    'html' => "<h1>Template 3 Title</h1>
                    some content in the template
                "
                ]
            ])
        ];
    }

    public function refreshAccountPolicies(Account $account): array
    {
        $this->ebayPoliciesService->fetchAndSaveUserPreferenceForAccount($account);
        return $this->fetchPoliciesForAccount($account);
    }

    public function getAccountData(Account $account): array
    {
        $listingsAuthActive = $this->hasOAuthTokenActive($account);
        $initialisationUrl = !$listingsAuthActive ? $this->tokenInitialisationService->getInitializationUrl($account) : null;
        return array_merge(
            $account->toArray(),
            [
                'listingsAuthActive' => $listingsAuthActive,
                'authTokenInitialisationUrl' => $initialisationUrl,
                'siteId' => $this->fetchDefaultSiteIdForAccount($account)
            ]
        );
    }

    public function formatExternalChannelData(array $data, string $processGuid): array
    {
        $epidAccountId = $data['epidAccountId'] ?? null;
        if (!$epidAccountId) {
            return $data;
        }

        $variationToEpid = $data['variationToEpid'] ?? [];

        try {
            $epidEntity = $this->epidStorage->fetchByGuid($processGuid);
            $variationToEpid = $variationToEpid + $epidEntity->getVariationToEpid();
        } catch (NotFound $e) {
            // No-op
        }

        try {
            /** @var Account $account */
            $account = $this->accountService->fetch($epidAccountId);
        } catch (NotFound $e) {
            unset($data['variationToEpid'], $data['epidAccountId']);
            return $data;
        }

        unset($data['epidAccountId']);

        return array_merge($data, [
            'marketplace' => $this->fetchDefaultSiteIdForAccount($account),
            'variationToEpid' => $variationToEpid
        ]);
    }

    protected function hasOAuthTokenActive(Account $account): bool
    {
        if (!$this->isMarketplaceSupportedByOAuth($account)) {
            return false;
        }
        $tokenExpiryDate = $account->getExternalData()['oAuthExpiryDate'] ?? null;

        try {
            return $tokenExpiryDate && $tokenExpiryDate > (new DateTime())->stdFormat()
                && (bool)$this->fetchCredentialsForAccount($account)->getOAuthToken();
        } catch (InvalidCredentialsException $e) {
            return false;
        }
    }

    protected function isMarketplaceSupportedByOAuth(Account $account): bool
    {
        return SiteMap::isMarketplaceAllowedForCatalogApi($this->fetchDefaultSiteIdForAccount($account));
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

    /**
     * @return Data[]
     */
    protected function fetchEbayCategoriesData(array $categoryIds): array
    {
        $data = [];
        if (empty($categoryIds)) {
            return $data;
        }
        try {
            $filter = (new CategoryExternalFilter('all', 1))->setCategoryId($categoryIds);
            $categoryExternals = $this->categoryExternalService->fetchCollectionByFilter($filter);
            /** @var CategoryExternal $categoryExternal */
            foreach ($categoryExternals as $categoryExternal) {
                $data[$categoryExternal->getCategoryId()] = $categoryExternal->getData();
            }
        } catch (NotFound $exception) {
            // Not Category data for requested categories
        }
        return $data;
    }

    protected function getListingDurationsFromEbayCategoryData(?Data $ebayData): array
    {
        if (!$ebayData) {
            return [];
        }
        $listingDurations = (new FeatureHelper($ebayData))->getListingDurationsForType();
        return $this->formatListingDurationsArray($listingDurations);
    }

    protected function getVariationsEnabledFromEbayCategoryData(?Data $ebayData): bool
    {
        return $ebayData ? (new FeatureHelper($ebayData))->isFeatureEnabled('VariationsEnabled') : true;
    }

    protected function getItemSpecificsFromEbayCategoryData(?Data $ebayData): array
    {
        if (!$ebayData || empty($ebayData->getCategorySpecifics())) {
            return [];
        }
        $required = [];
        $optional = [];
        $categorySpecifics = $ebayData->getCategorySpecifics();
        $recommendations = $categorySpecifics['NameRecommendation'];
        if (is_array($recommendations) && isArrayAssociative($recommendations)) {
            $recommendations = [$recommendations];
        }
        foreach ($recommendations as $recommendation) {
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
        $categories = $this->categoryService->fetchRootCategoriesForAccount(
            $account,
            false,
            $this->getEbaySiteIdForAccount($account),
            false
        );
        return $this->appendEbaySpecificFieldsToCategoriesResponse($categories);
    }

    protected function getShippingMethodsForAccount(int $rootOuId): array
    {
        try {
            /** @var ShippingMethodCollection $shippingMethods */
            $shippingMethods = $this->shippingMethodService->fetchCollectionByFilter(
                new ShippingMethodFilter('all', 1, [], ['ebay'], [], [$rootOuId])
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
        try {
            $credentials = $this->fetchCredentialsForAccount($account);
            return $credentials->getSiteId();
        } catch (InvalidCredentialsException $e) {
            $this->logErrorException($e);
            // We need this to prevent various callers of the current method to break
            return SiteMap::SITE_CODE_UK;
        }
    }

    protected function fetchCredentialsForAccount(Account $account): Credentials
    {
        $credentials = $this->credentials[$account->getId()] ?? null;
        if ($credentials instanceof Credentials) {
            return $credentials;
        }
        /** @var Credentials $credentials */
        $credentials = $this->cryptor->decrypt($account->getCredentials());
        $this->credentials[$account->getId()] = $credentials;
        return $credentials;
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

    protected function appendEbaySpecificFieldsToCategoriesResponse(array $categoriesResponse): array
    {
        $ebayData = $this->fetchEbayCategoriesData(array_keys($categoriesResponse));
        foreach ($categoriesResponse as $categoryId => &$categoryResponse) {
            $categoryResponse['variations'] = $this->getVariationsEnabledFromEbayCategoryData(
                $ebayData[$categoryId] ?? null
            );
        }
        return $categoriesResponse;
    }
}
