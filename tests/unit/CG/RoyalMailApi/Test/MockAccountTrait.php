<?php
namespace CG\RoyalMailApi\Test;

use CG\CourierAdapter\Account;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

trait MockAccountTrait
{
    protected function getMockAccount(): MockObject
    {
        $account = $this->getMockBuilder(Account::class)
            ->disableOriginalConstructor()
            ->getMock();
        $account->expects($this->any())
            ->method('getId')
            ->willReturn(1);
        return $account;
    }

    abstract public function getMockBuilder($className);
    abstract public function any();
}