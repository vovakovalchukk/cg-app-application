<?php
namespace CG\UkMail;

interface RequestInterface
{
    public function getMethod(): string;
    public function getUri(): string;
    public function asJson(): string;
    public function getResponseClass(): string;
}