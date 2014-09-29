<?php
namespace Orders\Order\Invoice;

use CG\Order\Service\Filter;
use CG\Order\Shared\Collection;
use CG\Order\Shared\Entity as OrderEntity;
use CG\Settings\Invoice\Shared\Entity as InvoiceSettingsEntity;
use CG\Settings\Invoice\Service\Service as InvoiceSettingsService;
use CG\Stdlib\DateTime;
use CG\Template\Entity as Template;
use CG\Template\Element\Factory as ElementFactory;
use CG\Template\PaperPage;
use Orders\Order\Invoice\Renderer\ServiceInterface as RendererService;
use Orders\Order\Invoice\Template\Factory as TemplateFactory;
use Orders\Order\Invoice\ProgressStorage;
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
    protected $progressStorage;
    protected $templates = [];

    public function __construct(
        Di $di,
        OrderService $orderService,
        TemplateFactory $templateFactory,
        ElementFactory $elementFactory,
        RendererService $rendererService,
        InvoiceSettingsService $invoiceSettingsService,
        ProgressStorage $progressStorage
    ) {
        $this
            ->setDi($di)
            ->setOrderService($orderService)
            ->setTemplateFactory($templateFactory)
            ->setElementFactory($elementFactory)
            ->setRendererService($rendererService)
            ->setInvoiceSettingsService($invoiceSettingsService)
            ->setProgressStorage($progressStorage);
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
    protected function getInvoiceSettingsService()
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

    protected function getProgressStorage()
    {
        return $this->progressStorage;
    }

    protected function setProgressStorage(ProgressStorage $progressStorage)
    {
        $this->progressStorage = $progressStorage;
        return $this;
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
    public function getResponseFromOrderCollection(Collection $orderCollection, Template $template = null, $progressKey = null)
    {
        return $this->getDi()->get(
            Response::class,
            [
                'mimeType' => $this->getRendererService()->getMimeType(),
                'filename' => $this->getRendererService()->getFileName(),
                'content' => $this->generateInvoiceFromOrderCollection($orderCollection, $template, $progressKey)
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

    protected function getTemplateId(OrderEntity $order)
    {
        return $this->getInvoiceSettingsService()->fetchTemplateIdFromOrganisationUnitId(
            $this->getOrderService()->getActiveUser()->getOrganisationUnitId(),
            $order->getOrganisationUnitId()
        );
    }

    protected function getTemplate(OrderEntity $order)
    {
        $templateId = $this->getTemplateId($order);

        if (isset($this->templates[$templateId])) {
            return $this->templates[$templateId];
        }
        $this->templates[$templateId] = $this->getTemplateFactory()->getTemplateById($templateId);
        return $this->templates[$templateId];
    }

    public function generateInvoiceFromOrderCollection(Collection $orderCollection, Template $template = null, $progressKey = null)
    {
        $count = 0;
        $this->updateInvoiceGenerationProgress($progressKey, $count);

        $renderedContent = [];
        foreach ($orderCollection as $order) {
            $renderedContent[] = $this->getRendererService()->renderOrderTemplate(
                $order,
                $template ?: $this->getTemplate($order)
            );
            $this->updateInvoiceGenerationProgress($progressKey, ++$count);
        }
        return $this->getRendererService()->combine($renderedContent);
    }

    protected function updateInvoiceGenerationProgress($key, $count)
    {
        if (!$key) {
            return;
        }
        $this->getProgressStorage()->setProgress($key, $count);
        return $this;
    }

    public function checkInvoiceGenerationProgress($key)
    {
        return $this->getProgressStorage()->getProgress($key);
    }
}