<?php
namespace Orders\Order\PickList;

use CG\Order\Service\Filter;
use CG\Order\Shared\Collection as OrderCollection;
use CG\Order\Shared\Entity as OrderEntity;
use CG\Settings\PickList\Service as PickListSettingsService;
use CG\Stats\StatsAwareInterface;
use CG\Stats\StatsTrait;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\User\ActiveUserInterface as ActiveUserContainer;
use Orders\Order\Service as OrderService;

class Service implements LoggerAwareInterface, StatsAwareInterface
{
    use LogTrait;
    use StatsTrait;

    protected $orderService;
    protected $pickListSettingsService;
    protected $progressStorage;
    protected $activeUserContainer;

    public function __construct(
        OrderService $orderService,
        PickListSettingsService $pickListSettingsService,
        ProgressStorage $progressStorage,
        ActiveUserContainer $activeUserContainer
    ) {
        $this->setOrderService($orderService)
            ->setPickListSettingsService($pickListSettingsService)
            ->setProgressStorage($progressStorage)
            ->setActiveUserContainer($activeUserContainer);
    }

    public function checkPickListGenerationProgress($key)
    {
        return (int) $this->getProgressStorage()->getProgress($key);
    }

    public function getResponseFromOrderCollection(OrderCollection $orderCollection, $progressKey = null)
    {
        //TODO: This is just a mock to simulate the generation of the pick list. Will be implemented in CGIV-4637.
        $count = 0;
        foreach ($orderCollection as $order) {
            sleep(1);
            $this->updatePickListGenerationProgress($progressKey, ++$count);
        }
        throw new NotFound(__FUNCTION__ . ' not implemented');
        return null;
    }

    protected function updatePickListGenerationProgress($key, $count)
    {
        if (!$key) {
            return;
        }
        $this->getProgressStorage()->setProgress($key, $count);
    }

    /**
     * @return OrderService
     */
    protected function getOrderService()
    {
        return $this->orderService;
    }

    /**
     * @param OrderService $orderService
     * @return $this
     */
    public function setOrderService(OrderService $orderService)
    {
        $this->orderService = $orderService;
        return $this;
    }

    /**
     * @return PickListSettingsService
     */
    protected function getPickListSettingsService()
    {
        return $this->pickListSettingsService;
    }

    /**
     * @param PickListSettingsService $pickListSettingsService
     * @return $this
     */
    public function setPickListSettingsService(PickListSettingsService $pickListSettingsService)
    {
        $this->pickListSettingsService = $pickListSettingsService;
        return $this;
    }

    /**
     * @return mixed
     */
    protected function getProgressStorage()
    {
        return $this->progressStorage;
    }

    /**
     * @param ProgressStorage $progressStorage
     * @return $this
     */
    public function setProgressStorage(ProgressStorage $progressStorage)
    {
        $this->progressStorage = $progressStorage;
        return $this;
    }

    /**
     * @return ActiveUserContainer
     */
    protected function getActiveUserContainer()
    {
        return $this->activeUserContainer;
    }

    /**
     * @param ActiveUserContainer $activeUserContainer
     * @return $this
     */
    public function setActiveUserContainer(ActiveUserContainer $activeUserContainer)
    {
        $this->activeUserContainer = $activeUserContainer;
        return $this;
    }
}
