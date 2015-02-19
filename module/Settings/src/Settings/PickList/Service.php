<?php
namespace Settings\PickList;

use CG\Settings\PickList\Service as PickListService;
use CG\Settings\PickList\Mapper as PickListMapper;
use CG\Settings\PickList\Entity as PickList;
use CG\Settings\PickList\SortValidator;

class Service
{
    protected $pickListService;
    protected $pickListMapper;

    public function __construct(PickListService $pickListService, PickListMapper $pickListMapper)
    {
        $this->setPickListService($pickListService)
            ->setPickListMapper($pickListMapper);
    }

    /**
     * @return PickList
     */
    public function savePickListSettings(array $pickListSettings, $organisationUnitId)
    {
        $pickListSettings['showPictures'] = $pickListSettings['showPictures'] == 'true';
        $pickListSettings['showSkuless'] = $pickListSettings['showSkuless'] == 'true';
        $pickListSettings['id'] = $organisationUnitId;
        $pickList = $this->getPickListMapper()->fromArray($pickListSettings);
        $pickList->setStoredETag($pickListSettings['eTag']);
        $this->getPickListService()->save($pickList);
        return $pickList;
    }

    /**
     * @return PickList
     */
    public function getPickListSettings($organisationUnitId)
    {
        return $this->getPickListService()->fetch($organisationUnitId);
    }

    public function getSortFields()
    {
        return SortValidator::getSortFieldsNames();
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
