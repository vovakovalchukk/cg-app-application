<?php
namespace CG\ShipStation;

interface ResponseInterface
{
    public static function createFromJson(string $json);
    public function getJsonResponse(): ?string;
}
