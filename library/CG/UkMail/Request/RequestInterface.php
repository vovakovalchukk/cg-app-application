<?php
namespace CG\UkMail\Request;

interface RequestInterface
{
    public function getMethod(): string;
    public function getUri(): string;
    public function getResponseClass(): string;
}