<?php
namespace Orders\Courier;

use CG\Account\Client\Filter as AccountFilter;
use CG\Channel\Type as ChannelType;

trait GetShippingAccountsTrait
{
    protected $shippingAccounts;

    public function getShippingAccounts()
    {
        if ($this->shippingAccounts) {
            return $this->shippingAccounts;
        }
        $ouIds = $this->getUserOuService()->getAncestorOrganisationUnitIdsByActiveUser();
        $filter = (new AccountFilter())
            ->setLimit('all')
            ->setPage(1)
            ->setOrganisationUnitId($ouIds)
            ->setType(ChannelType::SHIPPING)
            ->setDeleted(false);
        $this->shippingAccounts =  $this->getAccountService()->fetchByFilter($filter);
        return $this->shippingAccounts;
    }

    /**
     * @return \CG\Account\Client\Service
     */
    abstract protected function getAccountService();

    /**
     * @return \CG\User\OrganisationUnit\Service
     */
    abstract protected function getUserOuService();
}