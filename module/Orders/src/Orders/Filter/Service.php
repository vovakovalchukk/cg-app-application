<?php
namespace Orders\Filter;

use CG\Order\Service\Filter\Entity;
use CG\Order\Service\Filter\Mapper;
use Zend\Session\ManagerInterface;

class Service
{
    protected $entity;
    protected $mapper;
    protected $persistentStorage;

    public function __construct(Entity $entity, Mapper $mapper, ManagerInterface $persistentStorage)
    {
        $this
            ->setEntity($entity)
            ->setMapper($mapper)
            ->setPersistentStorage($persistentStorage);
    }

    public function setEntity(Entity $entity)
    {
        $this->entity = $entity;
        return $this;
    }

    public function getEntity()
    {
        return $this->entity;
    }

    public function setMapper(Mapper $mapper)
    {
        $this->mapper = $mapper;
        return $this;
    }

    public function getMapper()
    {
        return $this->mapper;
    }

    public function setPersistentStorage(ManagerInterface $persistentStorage)
    {
        $this->persistentStorage = $persistentStorage;
        return $this;
    }

    public function getPersistentStorage()
    {
        return $this->persistentStorage;
    }

    public function setPersistentEntity(Entity $filter)
    {
        $storage = $this->getPersistentStorage()->getStorage();

        if (!isset($storage['orders'])) {
            $storage['orders'] = [];
        }

        $storage['orders']['filter'] = $filter;

        return $this;
    }

    public function getPersistentEntity()
    {
        $storage = $this->getPersistentStorage()->getStorage();

        if (!isset($storage['orders'])) {
            $storage['orders'] = [];
        }

        if (!isset($storage['orders']['filter']) || !($storage['orders']['filter'] instanceof Entity)) {
            $storage['orders']['filter'] = $this->getEntity();
        }

        return $storage['orders']['filter'];
    }

    public function getEntityFromArray(array $data)
    {
        return $this->getMapper()->fromArray($data);
    }

    public function mergeEntities(Entity $entity1, $entity2)
    {
        return $this->getMapper()->merge($entity1, $entity2);
    }
}