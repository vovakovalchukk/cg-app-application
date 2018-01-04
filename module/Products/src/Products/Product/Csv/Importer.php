<?php
namespace Products\Product\Csv;

use CG\User\ActiveUserInterface;
use Products\Product\Csv\Entity;
use Products\Product\Csv\StorageInterface;

class Importer
{
    /** @var ActiveUserInterface */
    protected $activeUserContainer;
    /** @var StorageInterface*/
    protected $storage;

    public function __construct(ActiveUserInterface $activeUserContainer, StorageInterface $storage)
    {
        $this->activeUserContainer = $activeUserContainer;
        $this->storage = $storage;
    }

    public function importFromCsv(string $fileContents, int $accountId)
    {
        $rootOuId = $this->activeUserContainer->getActiveUserRootOrganisationUnitId();
        $this->saveCsv($fileContents, $accountId, $rootOuId);
        // TODO: actually do the import by creating a gearman job
    }

    protected function saveCsv(string $fileContents, int $accountId, int $rootOuId): Entity
    {
        $entity = Entity::fromRaw($fileContents, ['accountId' => $accountId, 'rootOuId' => $rootOuId]);
        return $this->storage->save($entity);
    }
}