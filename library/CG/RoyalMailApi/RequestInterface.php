<?php
namespace CG\RoyalMailApi;

use JsonSerializable;

interface RequestInterface extends JsonSerializable
{
    public function getMethod(): string;
    public function getUri(): string;
    public function getAdditionalHeaders(): array;
    public function getResponseClass(): string;
}