<?php
namespace Settings\Controller;

use CG\Account\Shared\Entity as Account;
use Zend\View\Model\JsonModel;

interface AccountActiveToggledInterface
{
    public function accountActiveToggled(Account $account, JsonModel $response);
}
