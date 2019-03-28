<?php
namespace CG\Intersoft;

interface RequestInterface
{
    public function getMethod(): string;
    public function getUri(): string;
    public function getResponseClass(): string;
    public function asXml(): string;
}