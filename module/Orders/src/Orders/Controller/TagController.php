<?php
namespace Orders\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use CG_UI\View\Prototyper\JsonModelFactory;
use Orders\Order\Service as OrderService;
use Orders\Filter\Service as FilterService;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Etag\Exception\NotModified;
use CG\Order\Shared\Collection;

class TagController extends AbstractActionController
{
    protected $jsonModelFactory;
    protected $orderService;
    protected $filterService;

    public function __construct(
        JsonModelFactory $jsonModelFactory,
        OrderService $orderService,
        FilterService $filterService
    )
    {
        $this
            ->setJsonModelFactory($jsonModelFactory)
            ->setOrderService($orderService)
            ->setFilterService($filterService);
    }

    public function setJsonModelFactory(JsonModelFactory $jsonModelFactory)
    {
        $this->jsonModelFactory = $jsonModelFactory;
        return $this;
    }

    /**
     * @return JsonModelFactory
     */
    public function getJsonModelFactory()
    {
        return $this->jsonModelFactory;
    }

    public function setOrderService(OrderService $orderService)
    {
        $this->orderService = $orderService;
        return $this;
    }

    /**
     * @return OrderService
     */
    public function getOrderService()
    {
        return $this->orderService;
    }

    public function setFilterService(FilterService $filterService)
    {
        $this->filterService = $filterService;
        return $this;
    }

    /**
     * @return FilterService
     */
    public function getFilterService()
    {
        return $this->filterService;
    }

    public function appendAction()
    {
        return $this->updateTags(
            $this->getOrderService()->getOrders($this->getOrderFilters()),
            new Tag\Append()
        );
    }

    public function removeAction()
    {
        return $this->updateTags(
            $this->getOrderService()->getOrders($this->getOrderFilters()),
            new Tag\Remove()
        );
    }

    protected function getTagRequest(array $ids)
    {
        $request = new Tag\Request();
        $tag = $this->params()->fromPost('tag');

        if (!$tag) {
            throw new Tag\Exception('No Tag provided');
        }

        if (empty($ids)) {
            throw new Tag\Exception('No Orders provided');
        }

        return $request
            ->setTag($tag)
            ->setOrderIds($ids);
    }

    protected function getOrderFilters()
    {
        return $this->getFilterService()->getFilter()
            ->setLimit('all')
            ->setPage(1)
            ->setOrganisationUnitId($this->getOrderService()->getActiveUser()->getOuList())
            ->setOrderIds((array) $this->params()->fromPost('orders', []));
    }

    protected function updateTags(Collection $orders, callable $updater)
    {
        $response = $this->getJsonModelFactory()->newInstance(['tagged' => false]);

        try {
            $request = $this->getTagRequest($orders->getIds());
        } catch (Tag\Exception $exception) {
            return $response->setVariable('error', $exception->getMessage());
        }

        try {
            foreach($orders as $order) {
                try {
                    $tags = call_user_func($updater, $request, $order->getTags());
                    $order->setTags(array_unique($tags));
                    $this->getOrderService()->saveOrder($order);
                } catch (NotModified $exception) {
                    // Not changed so ignore
                }
            }
        } catch (NotFound $exception) {
            return $response->setVariable(
                'error',
                'Order' . (count($request->getOrderIds()) > 1 ? 's' : '') . ' could not be found'
            );
        }

        return $response->setVariable('tagged', true);
    }
}