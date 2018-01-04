<?php
namespace Products\Product\Csv;

use DateTime;

class Mapper
{
    public function fromFileAndMetadata(string $fileData, array $metadata, ?string $id = null): Entity
    {
        $id = $id ?: $this->generateIdFromMetadata($metadata);
        return new Entity(
            $id,
            $fileData,
            $metadata['accountId'],
            $metadata['rootOuId']
        );
    }

    public function generateIdFromMetadata(array $metadata): string
    {
        return $metadata['rootOuId'] . '-' . $metadata['accountId'] . '-' . (new DateTime())->format('Ymd_His');
    }

    public function getPartsFromId(string $id): array
    {
        [$rootOuId, $accountId, $datetime] = explode('-', $id);
        return compact('rootOuId', 'accountId', 'datetime');
    }
}