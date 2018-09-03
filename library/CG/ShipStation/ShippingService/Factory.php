<?php
namespace CG\ShipStation\ShippingService;

use CG\Account\Shared\Entity as Account;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\ShipStation\GetClassNameForChannelTrait;
use CG\ShipStation\ShippingServiceInterface;
use Zend\Di\Di;

class Factory
{
    use GetClassNameForChannelTrait;

    /** @var Di */
    protected $di;
    /** @var OrganisationUnitService */
    protected $organisationUnitService;

    public function __construct(Di $di, OrganisationUnitService $organisationUnitService)
    {
        $this->di = $di;
        $this->organisationUnitService = $organisationUnitService;
    }

    public function __invoke(Account $account): ShippingServiceInterface
    {
        $className = __NAMESPACE__ . '\\' . $this->getClassNameForChannel($account->getChannel());
        if (!class_exists($className)) {
            $className = Other::class;
        }
        /** @var OrganisationUnit $ou */
        $ou = $this->organisationUnitService->fetch($account->getOrganisationUnitId());
        $class = $this->di->get($className, ['account' => $account, 'domesticCountryCode' => $ou->getAddressCountryCode()]);
        if (!$class instanceof ShippingServiceInterface) {
            throw new \RuntimeException($className . ' does not implement ' . ShippingServiceInterface::class);
        }
        return $class;
    }
}