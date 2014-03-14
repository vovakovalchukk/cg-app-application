<?php
namespace Orders\Order\Invoice;

use Zend\Di\Di;
use Orders\Order\Service as OrderService;
use CG\Order\Service\Filter;
use CG\Order\Shared\Collection;
use Orders\Order\Invoice\Template\Factory as TemplateFactory;
use Orders\Order\Invoice\Renderer\ServiceInterface as RendererService;
use CG\Http\Exception\Exception3xx\NotModified;

class Service
{
    protected $di;
    protected $orderService;
    protected $templateFactory;
    protected $rendererService;

    public function __construct(
        Di $di,
        OrderService $orderService,
        TemplateFactory $templateFactory,
        RendererService $rendererService
    ) {
        $this
            ->setDi($di)
            ->setOrderService($orderService)
            ->setTemplateFactory($templateFactory)
            ->setRendererService($rendererService);
    }

    public function setDi(Di $di)
    {
        $this->di = $di;
        return $this;
    }

    /**
     * @return Di
     */
    public function getDi()
    {
        return $this->di;
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

    public function setTemplateFactory(TemplateFactory $templateFactory)
    {
        $this->templateFactory = $templateFactory;
        return $this;
    }

    /**
     * @return TemplateFactory
     */
    public function getTemplateFactory()
    {
        return $this->templateFactory;
    }

    public function setRendererService(RendererService $rendererService)
    {
        $this->rendererService = $rendererService;
        return $this;
    }

    /**
     * @return RendererService
     */
    public function getRendererService()
    {
        return $this->rendererService;
    }

    /**
     * @param array $orderIds
     * @return Response
     */
    public function getResponseFromOrderIds(array $orderIds)
    {
        $filter = $this->getDi()->get(Filter::class, ['id' => $orderIds]);
        $collection = $this->getOrderService()->getOrders($filter);
        return $this->getResponseFromOrderCollection($collection);
    }

    /**
     * @param Collection $orderCollection
     * @return Response
     */
    public function getResponseFromOrderCollection(Collection $orderCollection)
    {
        $this->markOrdersAsPrintedFromOrderCollection($orderCollection);
        return $this->getDi()->get(
            Response::class,
            [
                'mimeType' => $this->getRendererService()->getMimeType(),
                'filename' => $this->getRendererService()->getFileName(),
                'content' => $this->generateInvoiceFromOrderCollection($orderCollection)
            ]
        );
    }

    public function markOrdersAsPrintedFromOrderCollection(Collection $orderCollection)
    {
        $now = time();
        foreach ($orderCollection as $order) {
            $this->getOrderService()->saveOrder(
                $order->setPrintedDate(date('Y-m-d H:i:s', $now))
            );
        }
    }

    public function generateInvoiceFromOrderCollection(Collection $orderCollection)
    {
        $renderedContent = [];
        foreach ($orderCollection as $order) {
            $renderedContent[] = $this->getRendererService()->renderOrderTemplate(
                $order,
                $this->getTemplateFactory()->getTemplateForOrderEntity($order)
            );
        }
        return $this->getRendererService()->combine($renderedContent);
    }
}