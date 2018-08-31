<?php
namespace CG\Hermes;

interface RequestInterface
{
    public function getMethod(): string;
    public function getUri(): string;
    public function asXML(): string;
    public function getResponseClass(): string;
}