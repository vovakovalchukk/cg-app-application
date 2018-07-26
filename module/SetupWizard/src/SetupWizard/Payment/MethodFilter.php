<?php
namespace SetupWizard\Payment;

use CG\Payment\MethodFilterInterface;
use CG\Payment\MethodService as Methods;

class MethodFilter implements MethodFilterInterface
{
    public function __invoke(string $method): bool
    {
        return $method !== Methods::GO_CARDLESS;
    }
}