<?php
namespace Settings\CreateListings\Csv;

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