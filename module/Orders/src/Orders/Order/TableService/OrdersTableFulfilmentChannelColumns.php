<?php
namespace Orders\Order\TableService;

use CG\Amazon\Order\FulfilmentChannel\Mapper as AmazonFulfilmentMapper;
use CG\Order\Shared\Entity as OrderEntity;
use CG\User\ActiveUserInterface;
use CG_UI\View\Filters\SelectOptionsInterface;

class OrdersTableFulfilmentChannelColumns implements SelectOptionsInterface
{
    protected $activeUserContainer;

    public function __construct(ActiveUserInterface $activeUserContainer)
    {
        $this->setActiveUserContainer($activeUserContainer);
    }

    /**
     * {@inherit}
     */
    public function getSelectOptions()
    {
        return [
            OrderEntity::DEFAULT_FULFILMENT_CHANNEL => OrderEntity::DEFAULT_FULFILMENT_CHANNEL,
            AmazonFulfilmentMapper::CG_FBA          => AmazonFulfilmentMapper::CG_FBA
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