<?php
namespace Orders\Order\PickList;

use CG\Order\Shared\Collection as OrderCollection;
use CG\Product\Service\Service as ProductService;
use CG\Product\Collection as ProductCollection;
use CG\Product\Filter as ProductFilter;
use CG\Product\Entity as Product;
use CG\Settings\PickList\Service as PickListSettingsService;
use CG\Settings\PickList\Entity as PickListSettings;
use CG\Settings\Picklist\SortValidator;
use CG\PickList\Service as PickListService;
use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\Stats\StatsAwareInterface;
use CG\Stats\StatsTrait;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\User\ActiveUserInterface as ActiveUserContainer;

class Service implements LoggerAwareInterface, StatsAwareInterface
{
    use LogTrait;
    use StatsTrait;

    protected $productService;
    protected $pickListService;
    protected $pickListSettingsService;
    protected $organisationUnitService;
    protected $mapper;
    protected $progressStorage;
    protected $activeUserContainer;

    public function __construct(
        ProductService $productService,
        PickListService $pickListService,
        PickListSettingsService $pickListSettingsService,
        OrganisationUnitService $organisationUnitService,
        Mapper $mapper,
        ProgressStorage $progressStorage,
        ActiveUserContainer $activeUserContainer
    ) {
        $this->setProductService($productService)
            ->setPickListService($pickListService)
            ->setPickListSettingsService($pickListSettingsService)
            ->setOrganisationUnitService($organisationUnitService)
            ->setMapper($mapper)
            ->setProgressStorage($progressStorage)
            ->setActiveUserContainer($activeUserContainer);
    }

    public function getResponseFromOrderCollection(OrderCollection $orderCollection, $progressKey = null)
    {
        $pickListEntries = $this->getPickListEntries($orderCollection);
        $this->logDebugDump($pickListEntries, 'Pick List', [], 'PICK LIST');

        $rendered = $this->getPickListService()->renderTemplate($pickListEntries, $this->getOrganisationUnit());
        return new Response('application/pdf', 'picklist.pdf', $rendered);
    }

    public function checkPickListGenerationProgress($key)
    {
        return (int) $this->getProgressStorage()->getProgress($key);
    }

    protected function getPickListEntries(OrderCollection $orderCollection)
    {
        $pickListSettings = $this->getPickListSettings();

        $aggregator = new ItemAggregator($orderCollection, $pickListSettings->getShowSkuless());
        $aggregator();

        $products = $this->getProductsForSkus($aggregator->getSkus());

        $pickListEntries = array_merge(
            $this->getMapper()->fromItemsAndProductsBySku(
                $aggregator->getItemsIndexedBySku(),
                $products,
                $pickListSettings->getShowPictures()
            ),
            $this->getMapper()->fromItemsByTitle(
                $aggregator->getItemsIndexedByTitle()
            )
        );

        $pickListEntries = $this->getMapper()->sortEntries(
            $pickListEntries,
            SortValidator::getSortFieldsNames()[$pickListSettings->getSortField()],
            $pickListSettings->getSortDirection() === SortValidator::SORT_DIRECTION_ASC
        );

        return $pickListEntries;
    }

    protected function getProductsForSkus(array $skus)
    {
        $filter = new ProductFilter();
        $filter->setSku($skus);

        try {
            return $this->getProductService()->fetchCollectionByFilter($filter);
        } catch (NotFound $e) {
            return new ProductCollection(Product::class, __FUNCTION__, ['sku' => $skus]);
        }
    }

    protected function updatePickListGenerationProgress($key, $count)
    {
        if (!$key) {
            return;
        }
        $this->getProgressStorage()->setProgress($key, $count);
    }

    /**
     * @return OrganisationUnit
     */
    protected function getOrganisationUnit()
    {
        $organisationUnitId = $this->getActiveUserContainer()->getActiveUserRootOrganisationUnitId();
        return $this->getOrganisationUnitService()->fetch($organisationUnitId);
    }

    /**
     * @return PickListSettings
     */
    protected function getPickListSettings()
    {
        $organisationUnitId = $this->getActiveUserContainer()->getActiveUserRootOrganisationUnitId();
        return $this->getPickListSettingsService()->fetch($organisationUnitId);
    }

    /**
     * @return ProductService
     */
    protected function getProductService()
    {
        return $this->productService;
    }

    /**
     * @param ProductService $productService
     * @return $this
     */
    public function setProductService(ProductService $productService)
    {
        $this->productService = $productService;
        return $this;
    }

    /**
     * @return PickListSettingsService
     */
    protected function getPickListSettingsService()
    {
        return $this->pickListSettingsService;
    }

    /**
     * @param PickListSettingsService $pickListSettingsService
     * @return $this
     */
    public function setPickListSettingsService(PickListSettingsService $pickListSettingsService)
    {
        $this->pickListSettingsService = $pickListSettingsService;
        return $this;
    }

    /**
     * @return PickListService
     */
    protected function getPickListService()
    {
        return $this->pickListService;
    }

    /**
     * @param PickListService $pickListService
     * @return $this
     */
    public function setPickListService(PickListService $pickListService)
    {
        $this->pickListService = $pickListService;
        return $this;
    }

    /**
     * @return OrganisationUnitService
     */
    protected function getOrganisationUnitService()
    {
        return $this->organisationUnitService;
    }

    /**
     * @param OrganisationUnitService $organisationUnitService
     * @return $this
     */
    public function setOrganisationUnitService(OrganisationUnitService $organisationUnitService)
    {
        $this->organisationUnitService = $organisationUnitService;
        return $this;
    }

    /**
     * @return Mapper
     */
    protected function getMapper()
    {
        return $this->mapper;
    }

    /**
     * @param Mapper $mapper
     * @return $this
     */
    public function setMapper(Mapper $mapper)
    {
        $this->mapper = $mapper;
        return $this;
    }

    /**
     * @return mixed
     */
    protected function getProgressStorage()
    {
        return $this->progressStorage;
    }

    /**
     * @param ProgressStorage $progressStorage
     * @return $this
     */
    public function setProgressStorage(ProgressStorage $progressStorage)
    {
        $this->progressStorage = $progressStorage;
        return $this;
    }

    /**
     * @return ActiveUserContainer
     */
    protected function getActiveUserContainer()
    {
        return $this->activeUserContainer;
    }

    /**
     * @param ActiveUserContainer $activeUserContainer
     * @return $this
     */
    public function setActiveUserContainer(ActiveUserContainer $activeUserContainer)
    {
        $this->activeUserContainer = $activeUserContainer;
        return $this;
    }
}
