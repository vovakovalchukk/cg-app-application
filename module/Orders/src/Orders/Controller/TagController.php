<?php
namespace Orders\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use CG_UI\View\Prototyper\JsonModelFactory;
use Orders\Order\Service as OrderService;
use Orders\Filter\Service as FilterService;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Etag\Exception\NotModified;

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
        return $this->updateTags(new Tag\Append());
    }

    public function removeAction()
    {
        return $this->updateTags(new Tag\Remove());
    }

    protected function getTagRequest()
    {
        $request = new Tag\Request();

        $tag = $this->params()->fromPost('tag');
        if (!$tag) {
            throw new Tag\Exception('No Tag provided');
        }
        $request->setTag($tag);

        $ids = $this->params()->fromPost('orders');
        if (!is_array($ids) || empty($ids)) {
            throw new Tag\Exception('No Orders provided');
        }
        $request->setOrderIds($ids);

        return $request;
    }

    protected function getOrderFilters(Tag\Request $request)
    {
        return $this->getFilterService()->getFilter()
            ->setLimit('all')
            ->setPage(1)
            ->setOrganisationUnitId($this->getOrderService()->getActiveUser()->getOuList())
            ->setId($request->getOrderIds());
    }

    protected function updateTags(callable $updater)
    {
        $response = $this->getJsonModelFactory()->newInstance(['tagged' => false]);
        try {
            $request = $this->getTagRequest();
        } catch (Tag\Exception $exception) {
            return $response->setVariable('error', $exception->getMessage());
        }
        $filter = $this->getOrderFilters($request);

        try {
            foreach($this->getOrderService()->getOrders($filter) as $order) {
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