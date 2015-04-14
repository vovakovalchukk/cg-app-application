<?php
namespace Orders\Order\TableService;

use CG\Order\Shared\Entity as OrderEntity;
use CG\User\ActiveUserInterface;
use CG_UI\View\Filters\SelectOptionsInterface;

class OrdersTableFulfilmentChannelColumns implements SelectOptionsInterface
{
    const AMAZON_FBA_OPTION = 'Amazon FBA';

    protected $activeUserContainer;

    public function __construct(ActiveUserInterface $activeUserContainer)
    {
        $this->setActiveUserContainer($activeUserContainer);
    }

    /**
     * TODO CGIV-5335 Options should be a dynamic list
     * {@inherit}
     */
    public function getSelectOptions()
    {
        return [
            OrderEntity::DEFAULT_FULFILMENT_CHANNEL => OrderEntity::DEFAULT_FULFILMENT_CHANNEL,
            static::AMAZON_FBA_OPTION => static::AMAZON_FBA_OPTION
        ];
    }

    public function setActiveUserContainer(ActiveUserInterface $activeUserContainer)
    {
        $this->activeUserContainer = $activeUserContainer;
        return $this;
    }

    /**
     * @return ActiveUserInterface
     */
    public function getActiveUserContainer()
    {
        return $this->activeUserContainer;
    }
}