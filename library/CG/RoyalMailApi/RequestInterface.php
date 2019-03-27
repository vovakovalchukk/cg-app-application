<?php
namespace CG\RoyalMailApi;

use CG\CourierAdapter\Account;
use JsonSerializable;

interface RequestInterface extends JsonSerializable
{
    public function getMethod(): string;
    public function getUri(): string;
    public function getAdditionalHeaders(Account $account, Credentials $credentials): array;
    public function getResponseClass(): string;
}