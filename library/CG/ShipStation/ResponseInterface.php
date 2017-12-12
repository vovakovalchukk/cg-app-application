<?php
namespace CG\ShipStation;

interface ResponseInterface
{
    public function createFromJson(string $json);
    public function getJsonResponse(): ?string;
}
