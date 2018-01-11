<?php
namespace CG\ShipStation;

interface RequestInterface
{
    public function getUri(): string;
    public function getMethod(): string;
    public function toJson(): string;
    public function getResponseClass(): string;
}
