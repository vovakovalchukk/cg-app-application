<?php
namespace CG\UkMail\Request;

interface RequestInterface
{
    public function getMethod(): string;
    public function getUri(): string;
    public function getOptions(array $defaultOptions = []): array;
    public function getResponseClass(): string;
}