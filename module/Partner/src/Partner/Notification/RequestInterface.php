<?php
namespace Partner\Notification;

interface RequestInterface
{
    public function getUrl(): string;
    public function getMethod(): string;
    public function toArray(): array;
}
