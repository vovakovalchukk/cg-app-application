<?php
namespace CG\ManualOrder\Test\Account;

use CG\Account\Client\Service as AccountService;
use CG\Account\Shared\Collection as AccountCollection;
use CG\Account\Shared\Entity as Account;
use CG\ManualOrder\Account\Service;
use CG\ManualOrder\Account\CreationService;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\Stdlib\Exception\Runtime\NotFound;
use PHPUnit_Framework_TestCase;

class ServiceTest extends PHPUnit_Framework_TestCase
{
    protected $service;
    protected $accountService;
    protected $creationService;

    public function setUp()
    {
        $this->accountService = $this->getMockBuilder(AccountService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->creationService = $this->getMockBuilder(CreationService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->service = new Service($this->accountService, $this->creationService);
    }

    public function testGetAccountForOrganisationUnitCreatesOneWhereNoneExist()
    {
        $this->accountService->expects($this->once())
            ->method('fetchByFilter')
            ->will($this->throwException(new NotFound()));

        $account = $this->getMockAccount();
        $this->creationService->expects($this->once())
            ->method('connectAccount')
            ->will($this->returnValue($account));

        $ou = $this->getMockOrganisationUnit();

        $result = $this->service->getAccountForOrganisationUnit($ou);

        $this->assertInstanceOf(Account::class, $result);
        $this->assertEquals($account->getId(), $result->getId());
    }

    public function testGetAccountForOrganisationUnitFetchesOneWhereItExists()
    {
        $account = $this->getMockAccount();
        $accountCollection = new AccountCollection(Account::class, 'TEST');
        $accountCollection->attach($account);
        $this->accountService->expects($this->once())
            ->method('fetchByFilter')
            ->will($this->returnValue($accountCollection));

        $this->creationService->expects($this->never())
            ->method('connectAccount');

        $ou = $this->getMockOrganisationUnit();

        $result = $this->service->getAccountForOrganisationUnit($ou);

        $this->assertInstanceOf(Account::class, $result);
        $this->assertEquals($account->getId(), $result->getId());
    }

    protected function getMockOrganisationUnit()
    {
        $ou = $this->getMockBuilder(OrganisationUnit::class)
            ->disableOriginalConstructor()
            ->getMock();
        $ou->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));

        return $ou;
    }

    protected function getMockAccount()
    {
        $ou = $this->getMockBuilder(Account::class)
            ->disableOriginalConstructor()
            ->getMock();
        $ou->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));

        return $ou;
    }
}
