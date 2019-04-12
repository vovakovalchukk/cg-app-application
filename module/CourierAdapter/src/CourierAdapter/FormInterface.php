<?php
namespace CourierAdapter;

use Zend\View\Model\ViewModel;

interface FormInterface
{
    public function getFormView(string $channelName, int $accountId, string $goBackUrl, string $saveUrl): ViewModel;
}