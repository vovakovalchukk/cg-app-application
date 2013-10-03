<?php
namespace CG\Skeleton\Module\Db;

use CG\Skeleton\Module\BaseConfig;
use Zend\Config\Config as ZendConfig;

class Config extends BaseConfig
{
    const STORAGE_NODE = 'StorageNode';
    const DATABASE_NAME = 'DatabaseName';
    const DATABASE_USERS = 'DatabaseUsers';

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

    public function getDatabaseUsers()
    {
        $databaseUsers = $this->get(static::DATABASE_USERS, array());
        if (is_string($databaseUsers)) {
            $databaseUsers = array($databaseUsers);
        } else if ($databaseUsers instanceof ZendConfig) {
            $databaseUsers = $databaseUsers->toArray();
        }
        return $databaseUsers;
    }

    public function setDatabaseUsers(array $databaseUsers)
    {
        $this->offsetSet(static::DATABASE_USERS, $databaseUsers);
        return $this;
    }
}