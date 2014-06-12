<?php
namespace Orders\Order\Invoice;

use Zend\Di\Di;
use Orders\Order\Service as OrderService;
use CG\Order\Service\Filter;
use CG\Order\Shared\Collection;
use CG\Stdlib\DateTime;
use Orders\Order\Invoice\Template\Factory as TemplateFactory;
use CG\Template\Element\Factory as ElementFactory;
use Orders\Order\Invoice\Renderer\ServiceInterface as RendererService;
use CG\Template\PaperPage;

class Service
{
    protected $di;
    protected $orderService;
    protected $templateFactory;
    protected $elementFactory;
    protected $rendererService;

    public function __construct(
        Di $di,
        OrderService $orderService,
        TemplateFactory $templateFactory,
        ElementFactory $elementFactory,
        RendererService $rendererService
    ) {
        $this
            ->setDi($di)
            ->setOrderService($orderService)
            ->setTemplateFactory($templateFactory)
            ->setElementFactory($elementFactory)
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

    public function setElementFactory(ElementFactory $elementFactory)
    {
        $this->elementFactory = $elementFactory;
        return $this;
    }

    /**
     * @return ElementFactory
     */
    public function getElementFactory()
    {
        return $this->elementFactory;
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
        $filter = $this->getDi()->get(Filter::class, ['orderIds' => $orderIds]);
        $orderCollection = $this->getOrderService()->getOrders($filter);
        return $this->getResponseFromOrderCollection($orderCollection);
    }

    public function getResponseFromFilterId($filterId)
    {
        return $this->getResponseFromOrderCollection(
            $this->getOrderService()->getOrdersFromFilterId($filterId)
        );
    }

    public function createTemplate(array $config)
    {
        $config['elements'] = $this->createElements($config['elements']);
        $config['paperPage'] = $this->createPaperPage($config['paperPage']);
        return $this->getTemplateFactory()->getTemplateForOrderEntity($config);
    }

    protected function createElements(array $elementConfigs)
    {
        $elements = [];
        foreach ($elementConfigs as $elementConfig) {
            $elements[] = $this->createElement($elementConfig);
        }
        return $elements;
    }

    protected function createElement(array $config)
    {
        return $this->getElementFactory()->createElement($config);
    }

    protected function createPaperPage(array $config)
    {
        return $this->getDi()->newInstance(PaperPage::class, $config);
    }

    /**
     * @param Collection $orderCollection
     * @return Response
     */
    public function getResponseFromOrderCollection(Collection $orderCollection, $template = null)
    {
        $this->markOrdersAsPrintedFromOrderCollection($orderCollection);
        return $this->getDi()->get(
            Response::class,
            [
                'mimeType' => $this->getRendererService()->getMimeType(),
                'filename' => $this->getRendererService()->getFileName(),
                'content' => $this->generateInvoiceFromOrderCollection($orderCollection, $template)
            ]
        );
    }

    public function markOrdersAsPrintedFromOrderCollection(Collection $orderCollection)
    {
        $now = time();
        foreach ($orderCollection as $order) {
            $this->getOrderService()->saveOrder(
                $order->setPrintedDate(date(DateTime::FORMAT, $now))
            );
        }
    }

    public function generateInvoiceFromOrderCollection(Collection $orderCollection, $template = null)
    {
        $renderedContent = [];
        if (! isset($template)) {
            $template = $this->getTemplateFactory()->getDefaultTemplateForOrderEntity(
                $this->getOrderService()->getActiveUser()->getOrganisationUnitId()
            );
        }

        foreach ($orderCollection as $order) {
            $renderedContent[] = $this->getRendererService()->renderOrderTemplate(
                $order,
                $template
            );
        }
        return $this->getRendererService()->combine($renderedContent);
    }
}