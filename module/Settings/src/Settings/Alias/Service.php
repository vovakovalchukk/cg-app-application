<?php
namespace Settings\Alias;

use CG\Settings\Alias\Mapper as AliasMapper;
use CG\Settings\Alias\StorageInterface as AliasStorage;
use CG\User\ActiveUserInterface;

class Service {

    public function __construct(AliasStorage $repository, AliasMapper $mapper, ActiveUserInterface $activeUser)
    {
        $this->setRepository($repository)
            ->setMapper($mapper)
            ->setActiveUser($activeUser);
    }

    public function saveFromJson($alias)
    {
        $decodedAlias = json_decode($alias, true);
        $convertedAlias = $this->getMapper()->fromArrays([$decodedAlias]);
        $decodedAlias = $convertedAlias[0];
        $decodedAlias['organisationUnitId'] = $this->getActiveUser()->getActiveUserRootOrganisationUnitId();
        $entity = $this->getMapper()->fromArray($decodedAlias);
        $this->getRepository()->save($entity);
        return $entity;
    }
} 