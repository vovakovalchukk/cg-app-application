<?php
namespace Settings\PickList;

use CG\FeatureFlags\Service as FeatureFlags;
use CG\OrganisationUnit\Service as OuService;
use CG\Product\PickingLocation\Entity as PickingLocation;
use CG\Product\PickingLocation\Filter as PickingLocationFilter;
use CG\Product\PickingLocation\Service as PickingLocationService;
use CG\Settings\PickList\Entity as PickList;
use CG\Settings\PickList\Mapper as PickListMapper;
use CG\Settings\PickList\Service as PickListService;
use CG\Settings\PickList\SortValidator;
use CG\Stdlib\Exception\Runtime\NotFound;

class Service
{
    const FEATURE_FLAG_PICK_LOCATIONS = 'PickLocations';

    /** @var PickListService */
    protected $pickListService;
    /** @var PickListMapper */
    protected $pickListMapper;
    /** @var OuService */
    protected $ouService;
    /** @var FeatureFlags */
    protected $featureFlags;
    /** @var PickingLocationService */
    protected $pickingLocationService;

    public function __construct(
        PickListService $pickListService,
        PickListMapper $pickListMapper,
        OuService $ouService,
        FeatureFlags $featureFlags,
        PickingLocationService $pickingLocationService
    ) {
        $this->pickListService = $pickListService;
        $this->pickListMapper = $pickListMapper;
        $this->ouService = $ouService;
        $this->featureFlags = $featureFlags;
        $this->pickingLocationService = $pickingLocationService;
    }

    public function isPickLocationsEnabled(int $organisationUnitId): bool
    {
        return $this->featureFlags->isActive(
            static::FEATURE_FLAG_PICK_LOCATIONS,
            $this->ouService->getRootOuFromOuId($organisationUnitId)
        );
    }

    /**
     * @return PickList
     */
    public function savePickListSettings(array $pickListSettings, int $organisationUnitId)
    {
        $pickListSettings['id'] = $organisationUnitId;
        $pickListSettings['showPictures'] = $this->filterBoolean($pickListSettings['showPictures'] ?? null);
        $pickListSettings['showSkuless'] = $this->filterBoolean($pickListSettings['showSkuless'] ?? null);
        $pickListSettings['showPickingLocations'] = $this->filterBoolean($pickListSettings['showPickingLocations'] ?? null);
        $pickList = $this->getPickListMapper()->fromArray($pickListSettings);
        if (isset($pickListSettings['eTag'])) {
            $pickList->setStoredETag($pickListSettings['eTag']);
        }
        $this->getPickListService()->save($pickList);
        return $pickList;
    }

    protected function filterBoolean($value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }

    /**
     * @return PickList
     */
    public function getPickListSettings(int $organisationUnitId)
    {
        return $this->getPickListService()->fetch($organisationUnitId);
    }

    public function getPickListValues(int $organisationUnitId): array
    {
        try {
            /** @var PickingLocation[] $pickingLocations */
            $pickingLocations = $this->pickingLocationService->fetchCollectionByFilter(
                (new PickingLocationFilter('all'))->setOrganisationUnitId([$organisationUnitId])
            );
        } catch (NotFound $exception) {
            return [];
        }

        $values = [];
        foreach ($pickingLocations as $pickingLocation) {
            $values[$pickingLocation->getLevel()] = $pickingLocation->getNames();
        }
        return $values;
    }

    public function getSortFields(bool $pickLocationsEnabled)
    {
        $sortFields = SortValidator::getSortFieldsNames();
        if (!$pickLocationsEnabled) {
            unset($sortFields[SortValidator::SORT_FIELD_PICKING_LOCATION]);
        }
        return $sortFields;
    }

    public function getSortDirections()
    {
        return SortValidator::getSortDirectionsNames();
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
     * @return PickListMapper
     */
    protected function getPickListMapper()
    {
        return $this->pickListMapper;
    }

    /**
     * @param PickListMapper $pickListMapper
     * @return $this
     */
    public function setPickListMapper(PickListMapper $pickListMapper)
    {
        $this->pickListMapper = $pickListMapper;
        return $this;
    }
}
