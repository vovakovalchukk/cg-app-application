<?php
namespace Products\Product\Csv;

use DateTime;

class Entity
{
    /** @var string */
    protected $id;
    /** @var string */
    protected $fileData;
    /** @var int */
    protected $accountId;
    /** @var int */
    protected $rootOuId;

    public function __construct(string $id, string $fileData, int $accountId, int $rootOuId)
    {
        $this->id = $id;
        $this->fileData = $fileData;
        $this->accountId = $accountId;
        $this->rootOuId = $rootOuId;
    }

    public static function fromRaw(string $fileData, array $metaData, ?string $id = null): Entity
    {
        $id = $id ?: static::generateId($metaData);
        return new self($id, $fileData, $metaData['accountId'], $metaData['rootOuId']);
    }

    public static function generateId(array $metaData): string
    {
        return $metaData['rootOuId'] . '-' . $metaData['accountId'] . '-' . (new DateTime())->format('Ymd_His');
    }

    public static function getPartsFromId(string $id): array
    {
        [$rootOuId, $accountId, $datetime] = explode('-', $id);
        return compact('rootOuId', 'accountId', 'datetime');
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): Entity
    {
        $this->id = $id;
        return $this;
    }

    public function getFileData(): string
    {
        return $this->fileData;
    }

    public function setFileData(string $fileData): Entity
    {
        $this->fileData = $fileData;
        return $this;
    }

    public function getAccountId(): int
    {
        return $this->accountId;
    }

    public function setAccountId(int $accountId): Entity
    {
        $this->accountId = $accountId;
        return $this;
    }
}