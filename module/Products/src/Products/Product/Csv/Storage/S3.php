<?php
namespace Products\Product\Csv\Storage;

use CG\FileStorage\FileStorageException;
use CG\FileStorage\S3\Adapter as S3Adapter;
use CG\Http\StatusCode;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\Stdlib\Exception\Runtime\NotFound;
use Products\Product\Csv\Entity;
use Products\Product\Csv\StorageInterface;

class S3 implements StorageInterface, LoggerAwareInterface
{
    use LogTrait;

    const FILE_TYPE_EXTENSION = 'csv';
    const S3_BUCKET = 'orderhub-productimportdata';
    const LOG_CODE = 'ProductCsvStorageS3';
    const LOG_NOT_FOUND = 'Product CSV not found at S3 key %s';
    const LOG_SAVE_FAILED = 'Product CSV save to S3 failed, key %s';

    /** @var S3Adapter */
    protected $s3Adapter;

    public function __construct(S3Adapter $s3Adapter)
    {
        $this->s3Adapter = $s3Adapter;
    }

    public function fetch($id): Entity
    {
        try {
            $result = $this->s3Adapter->read($this->getS3Key($id));
            $metadata = $result->getExtraFields()['Metadata'] ?? [];
            return Entity::fromRaw($id, $result->getBody(), $metadata);

        } catch (FileStorageException $e) {
            $this->logWarningException($e, static::LOG_NOT_FOUND, [$this->getS3Key($id)], [static::LOG_CODE, 'NotFound']);
            throw new NotFound(vsprintf(static::LOG_NOT_FOUND, $this->getS3Key($id)), StatusCode::NOT_FOUND, $e);
        }
    }

    /**
     * @param Entity $entity
     */
    public function save($entity): Entity
    {
        try {
            $this->s3Adapter->write(
                $this->getS3Key($entity->getId()),
                $entity->getFileData(),
                ['Metadata' => $this->getMetaData($entity)]
            );
            return $entity;

        } catch (FileStorageException $e) {
            $this->logWarningException($e, static::LOG_SAVE_FAILED, [$this->getS3Key($entity->getId())], [static::LOG_CODE, 'SaveFailed']);
            throw $e;
        }
    }

    public function remove($entity)
    {
        try {
            $this->s3Adapter->delete($this->getS3Key($entity->getId()));
        } catch (FileStorageException $e) {
            $this->logWarningException($e, static::LOG_NOT_FOUND, [$this->getS3Key($id)], [static::LOG_CODE, 'RemoveFailed']);
            throw new NotFound(vsprintf(static::LOG_NOT_FOUND, $this->getS3Key($id)), StatusCode::NOT_FOUND, $e);
        }
    }

    protected function getS3Key(string $id): string
    {
        return ENVIRONMENT . '/' . $this->extractOUIDFromId($id) . '/' . $id . '.' . static::FILE_TYPE_EXTENSION;
    }

    protected function extractOUIDFromId(string $id): int
    {
        $idParts = Entity::getPartsFromId($id);
        return (int)$idParts['rootOuId'];
    }

    protected function getMetaData(Entity $entity): array
    {
        return ['accountId' => $entity->getAccountId()];
    }
}