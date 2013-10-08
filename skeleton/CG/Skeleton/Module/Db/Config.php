<?php
namespace CG\Skeleton\Module\Db;

use CG\Skeleton\Module\BaseConfig;
use Zend\Config\Config as ZendConfig;

class Config extends BaseConfig
{
    const STORAGE_NODE = 'StorageNode';
    const DATABASE_NAME = 'DatabaseName';
    const DATABASE_ADAPTERS = 'DatabaseAdapters';

    public function getStorageNode()
    {
        return $this->get(static::STORAGE_NODE, '');
    }

    public function setStorageNode($storageNode)
    {
        $this->offsetSet(static::STORAGE_NODE, $storageNode);
        return $this;
    }

    public function getDatabaseName()
    {
        return $this->get(static::DATABASE_NAME, '');
    }

    public function setDatabaseName($databaseName)
    {
        $this->offsetSet(static::DATABASE_NAME, $databaseName);
        return $this;
    }

    public function getDatabaseAdapters()
    {
        $databaseAdapters = $this->get(static::DATABASE_ADAPTERS, array());
        if (is_string($databaseAdapters)) {
            $databaseAdapters = array($databaseAdapters);
        } else if ($databaseAdapters instanceof ZendConfig) {
            $databaseAdapters = $databaseAdapters->toArray();
        }
        return $databaseAdapters;
    }

    public function setDatabaseAdapters(array $databaseAdapters)
    {
        $this->offsetSet(static::DATABASE_ADAPTERS, $databaseAdapters);
        return $this;
    }
}