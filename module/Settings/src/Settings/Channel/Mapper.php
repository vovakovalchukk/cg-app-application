<?php
namespace Settings\Channel;

use CG\Account\Shared\Entity;
use CG\OrganisationUnit\StorageInterface as OUStorage;
use CG\Stdlib\Exception\Runtime\NotFound;

class Mapper
{
    protected $ouStorage;

    public function __construct(OUStorage $ouStorage)
    {
        $this->setOuStorage($ouStorage);
    }

    public function setOuStorage(OUStorage $ouStorage)
    {
        $this->ouStorage = $ouStorage;
        return $this;
    }

    /**
     * @return OUStorage
     */
    public function getOuStorage()
    {
        return $this->ouStorage;
    }

    public function toDataTableArray(Entity $entity)
    {
        $dataTableArray = $entity->toArray();

        unset(
            $dataTableArray['credentials'],
            $dataTableArray['organisationUnitId']
        );

        $dataTableArray['organisationUnit'] = $this->getOrganisationUnitCompanyName($entity->getOrganisationUnitId());

        return $dataTableArray;
    }

    protected function getOrganisationUnitCompanyName($ouId)
    {
        try {
            $ou = $this->getOuStorage()->fetch($ouId);
            return $ou->getAddressCompanyName();
        } catch (NotFound $exception) {
            return '';
        }
    }
}