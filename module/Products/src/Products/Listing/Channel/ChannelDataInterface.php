<?php
namespace Products\Listing\Channel;

interface ChannelDataInterface
{
    public function formatExternalChannelData(array $data, string $processGuid): array;
}
