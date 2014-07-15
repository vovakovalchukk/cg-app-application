<?php
namespace Orders\Order\Invoice;

use CG\Order\Service\Filter;
use CG\Order\Shared\Collection;
use CG\Order\Shared\Entity as OrderEntity;
use CG\Settings\Invoice\Shared\Entity as InvoiceSettingsEntity;
use CG\Settings\Invoice\Service\Service as InvoiceSettingsService;
use CG\Stdlib\DateTime;
use CG\Template\Element\Factory as ElementFactory;
use CG\Template\PaperPage;
use Orders\Order\Invoice\Renderer\ServiceInterface as RendererService;
use Orders\Order\Invoice\Template\Factory as TemplateFactory;
use Orders\Order\Service as OrderService;
use Zend\Di\Di;

class Service
{
    protected $di;
    protected $orderService;
    protected $templateFactory;
    protected $elementFactory;
    protected $rendererService;
    protected $invoiceSettingsService;
    protected $templates = [];

    public function __construct(
        Di $di,
        OrderService $orderService,
        TemplateFactory $templateFactory,
        ElementFactory $elementFactory,
        RendererService $rendererService,
        InvoiceSettingsService $invoiceSettingsService
    ) {
        $this
            ->setDi($di)
            ->setOrderService($orderService)
            ->setTemplateFactory($templateFactory)
            ->setElementFactory($elementFactory)
            ->setRendererService($rendererService)
            ->setInvoiceSettingsService($invoiceSettingsService);
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

    public function setInvoiceSettingsService(InvoiceSettingsService $invoiceSettingsService)
    {
        $this->invoiceSettingsService = $invoiceSettingsService;
        return $this;
    }

    /**
     * @return InvoiceSettingsService
     */
    public function getInvoiceSettingsService()
    {
        return $this->invoiceSettingsService;
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
        return $this->getResponseFromOrderCollection(
            $orderCollection,
            $this->getInvoiceSettings()
        );
    }

    public function getResponseFromFilterId($filterId)
    {
        return $this->getResponseFromOrderCollection(
            $this->getOrderService()->getOrdersFromFilterId($filterId),
            $this->getInvoiceSettings()
        );
    }

    /**
     * @return InvoiceSettingsEntity
     */
    protected function getInvoiceSettings()
    {
        return $this->getInvoiceSettingsService()->fetch(
            $this->getOrderService()->getActiveUser()->getOrganisationUnitId()
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
                $order->setPrintedDate(date(DateTime::FORMAT, $now))
            );
        }
    }

    protected function getTemplateId(InvoiceSettingsEntity $invoiceSettings, OrderEntity $order)
    {
        $templateId = $invoiceSettings->getDefault();
        $tradingCompanyDefaults = $invoiceSettings->getTradingCompanies();
        $tradingCompanyId = $order->getOrganisationUnitId();

        if (isset($tradingCompanyDefaults[$tradingCompanyId])) {
            $templateId = $tradingCompanyDefaults[$tradingCompanyId];
        }
        return $templateId;
    }

    protected function getTemplate(InvoiceSettingsEntity $invoiceSettings, OrderEntity $order)
    {
        $templateId = $this->getTemplateId($invoiceSettings, $order);

        if (! isset($this->templates[$templateId])) {
            if ($templateId == null) {
                $template = $this->getTemplateFactory()->getDefaultTemplateForOrderEntity($templateId);
            } else {
                $template = $this->getTemplateFactory()->getTemplateById($templateId);
            }
            $this->template[$templateId] = $template;
        }
        return $this->templates[$templateId];
    }

    public function generateInvoiceFromOrderCollection(Collection $orderCollection)
    {
        $invoiceSettings = $this->getInvoiceSettings();
        $renderedContent = [];
        foreach ($orderCollection as $order) {
            $renderedContent[] = $this->getRendererService()->renderOrderTemplate(
                $order,
                $this->getTemplate($invoiceSettings, $order)
            );
        }
        return $this->getRendererService()->combine($renderedContent);
    }
}