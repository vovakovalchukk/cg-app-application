<?php
namespace Products\Listing\Channel\Amazon;

use CG\Account\Credentials\Cryptor;
use CG\Account\Shared\Entity as Account;
use CG\Amazon\BrowseNode\Category\Usage\Storage as BrowseNodeCategoryUsageStorage;
use CG\Amazon\Category\Entity as AmazonCategory;
use CG\Amazon\Category\Filter as AmazonCategoryFilter;
use CG\Amazon\Category\Service as AmazonCategoryService;
use CG\Amazon\Category\VariationTheme\Collection as VariationThemes;
use CG\Amazon\Category\VariationTheme\Entity as VariationTheme;
use CG\Amazon\Category\VariationTheme\Service as VariationThemeService;
use CG\Amazon\Credentials;
use CG\Amazon\RegionAbstract as Region;
use CG\Amazon\RegionFactory;
use CG\Stdlib\Exception\Runtime\NotFound;
use Products\Listing\Category\Service as CategoryService;
use Products\Listing\Channel\CategoryChildrenInterface;
use Products\Listing\Channel\CategoryDependentServiceInterface;
use Products\Listing\Channel\ChannelDataInterface;
use Products\Listing\Channel\ChannelSpecificValuesInterface;

class Service implements
    ChannelSpecificValuesInterface,
    CategoryChildrenInterface,
    CategoryDependentServiceInterface,
    ChannelDataInterface
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
    /** @var Cryptor */
    protected $cryptor;
    /** @var RegionFactory */
    protected $regionFactory;
    /** @var AmazonCategoryService */
    protected $amazonCategoryService;
    /** @var VariationThemeService */
    protected $variationThemeService;
    /** @var BrowseNodeCategoryUsageStorage */
    protected $browseNodeCategoryUsageStorage;

    public function __construct(
        CategoryService $categoryService,
        Cryptor $cryptor,
        RegionFactory $regionFactory,
        AmazonCategoryService $amazonCategoryService,
        VariationThemeService $variationThemeService,
        BrowseNodeCategoryUsageStorage $browseNodeCategoryUsageStorage
    ) {
        $this->categoryService = $categoryService;
        $this->cryptor = $cryptor;
        $this->regionFactory = $regionFactory;
        $this->amazonCategoryService = $amazonCategoryService;
        $this->variationThemeService = $variationThemeService;
        $this->browseNodeCategoryUsageStorage = $browseNodeCategoryUsageStorage;
    }

    public function getChannelSpecificFieldValues(Account $account): array
    {
        return [
            'categories' => $this->categoryService->fetchRootCategoriesForAccount(
                $account,
                null,
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
        $marketplace = $this->getMarketplaceForAccount($account);

        return [
            'amazonCategories' => $this->getAmazonCategoryOptions($categoryId),
            'rootCategories' => $this->categoryService->fetchRootCategoriesForAccount($account, true, $marketplace, false),
        ];
    }

    protected function getAmazonCategoryOptions(int $cgCategoryId): array
    {
        $options = $this->fetchAmazonCategoryOptions();
        $highUsageOptions = $this->extractHighUsageAmazonCategoryOptions($options, $cgCategoryId);
        $options = $this->sortAmazonCategoryOptionsAlphabetically($options);

        return [
            'priorityOptions' => $this->formatAmazonCategoryOptions($highUsageOptions),
            'options' => $this->formatAmazonCategoryOptions($options),
        ];
    }

    protected function fetchAmazonCategoryOptions(): array
    {
        try {
            $filter = new AmazonCategoryFilter('all', 1);
            $amazonCategories = $this->amazonCategoryService->fetchCollectionByFilter($filter);
            $options = [];
            /** @var AmazonCategory $amazonCategory */
            foreach ($amazonCategories as $amazonCategory) {
                $options[$amazonCategory->getId()] = $amazonCategory->getName();
            }
            return $options;
        } catch (NotFound $e) {
            return [];
        }
    }

    protected function sortAmazonCategoryOptionsAlphabetically(array $options): array
    {
        asort($options);
        return $options;
    }

    protected function extractHighUsageAmazonCategoryOptions(array &$options, int $cgCategoryId): array
    {
        $cgCategory = $this->categoryService->fetch($cgCategoryId);
        $browseNodeId = $cgCategory->getExternalId();
        $usage = $this->browseNodeCategoryUsageStorage->getForBrowseNode($browseNodeId);
        if (empty($usage)) {
            return [];
        }
        $usedOptions = [];
        foreach ($usage as $amazonCategoryId) {
            if (!isset($options[$amazonCategoryId])) {
                continue;
            }
            $usedOptions[$amazonCategoryId] = $options[$amazonCategoryId];
            unset($options[$amazonCategoryId]);
        }
        return $usedOptions;
    }

    protected function formatAmazonCategoryOptions(array $options): array
    {
        return array_map(function ($id, $name) {
            return [
                'name' => $name,
                'value' => $id,
            ];
        }, array_keys($options), $options);
    }

    public function formatExternalChannelData(array $data, string $processGuid): array
    {
        $externalData = [];
        foreach (array_values($data['bulletPoint'] ?? []) as $key => $value) {
            $externalData['bulletPoint'.++$key] = $value;
        }
        foreach (array_values($data['searchTerm'] ?? []) as $key => $value) {
            $externalData['searchTerm'.++$key] = $value;
        }
        return array_merge($data, $externalData);
    }

    public function getAmazonCategoryDependentValues(Account $account, int $amazonCategoryId): array
    {
        $amazonCategory = $this->amazonCategoryService->fetch($amazonCategoryId);
        $marketplace = $this->getMarketplaceForAccount($account);
        $variationThemes = $this->getAmazonCategoryVariationThemes($amazonCategory, $marketplace);
        return [
            'itemSpecifics' => $this->getItemSpecifics($amazonCategory),
            'variationThemes' => $this->getVariationThemes($variationThemes),
            'categoryRootVariationThemes' => $this->getVariationThemes($this->filterToCategoryRootVariationThemes($variationThemes)),
            'productTypesFromVariationThemes' => $this->extractProductTypes($variationThemes)
        ];
    }

    protected function getItemSpecifics(AmazonCategory $amazonCategory): array
    {
        $itemSpecifics = $amazonCategory->getAttributes();
        return $this->filterItemSpecifics($itemSpecifics);
    }

    protected function getAmazonCategoryVariationThemes(AmazonCategory $amazonCategory, string $marketplace): VariationThemes
    {
        try {
            return $this->variationThemeService->fetchCollectionByCategoryIdAndMarketplaces($amazonCategory->getId(), [$marketplace]);
        } catch (NotFound $e) {
            return new VariationThemes(VariationTheme::class, __METHOD__, ['amazonCategory' => $amazonCategory->getId(), 'marketplace' => [$marketplace]]);
        }
    }

    protected function getVariationThemes(VariationThemes $variationThemes): array
    {
        $variationThemesOptions = [];
        /** @var VariationTheme $variationTheme */
        foreach ($variationThemes as $variationTheme) {
            $variationThemesOptions[] = [
                'name' => $variationTheme->getName(),
                'attributes' => $variationTheme->getAttributes(),
                'productType' => $variationTheme->getProductType(),
                'validValues' => array_map(function($key, $options){
                    return [
                        'name' => $key,
                        'options' => array_combine($options, $options)
                    ];
                }, array_keys($variationTheme->getValidValues()), $variationTheme->getValidValues())
            ];
        }
        return $variationThemesOptions;
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

    protected function filterToCategoryRootVariationThemes(VariationThemes $variationThemes): VariationThemes
    {
        $categoryRootVariationThemes = new VariationThemes(VariationTheme::class, __METHOD__, $variationThemes->getSourceFilters());
        foreach ($variationThemes as $variationTheme) {
            /** @var VariationTheme $variationTheme */
            if ($variationTheme->getProductType() !== null) {
                continue;
            }
            $categoryRootVariationThemes->attach($variationTheme);
        }
        return $categoryRootVariationThemes;
    }

    protected function extractProductTypes(VariationThemes $variationThemes): array
    {
        return array_filter($variationThemes->getArrayOf('ProductType'));
    }
}
