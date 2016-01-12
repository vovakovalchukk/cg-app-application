<?php
namespace Orders\Order\Invoice;

use CG\Gearman\Client as GearmanClient;
use CG\Intercom\Event\Request as IntercomEvent;
use CG\Intercom\Event\Service as IntercomEventService;
use CG\Order\Client\Gearman\Generator\SetPrintedDate as PrintedDateGenerator;
use CG\Order\Client\Gearman\Workload\EmailInvoice;
use CG\Order\Client\Invoice\Renderer\ServiceInterface as RendererService;
use CG\Order\Client\Invoice\Service as ClientService;
use CG\Order\Client\Invoice\Template\Factory as TemplateFactory;
use CG\Order\Service\Filter;
use CG\Order\Shared\Collection;
use CG\Order\Shared\Entity as Order;
use CG\Settings\Invoice\Service\Service as InvoiceSettingsService;
use CG\Stats\StatsAwareInterface;
use CG\Stats\StatsTrait;
use CG\Stdlib\DateTime;
use CG\Template\Element\Factory as ElementFactory;
use CG\Template\Entity as Template;
use CG\Template\PaperPage;
use CG\User\ActiveUserInterface as ActiveUserContainer;
use CG\Zend\Stdlib\Http\FileResponse as Response;
use Orders\Order\Service as OrderService;

class Service extends ClientService implements StatsAwareInterface
{
    use StatsTrait;

    const STAT_ORDER_ACTION_PRINTED = 'orderAction.printed.%s.%d.%d';
    const EVENT_INVOICES_PRINTED = 'Invoices Printed';

    /** @var OrderService $orderService */
    protected $orderService;
    /** @var ElementFactory $elementFactory */
    protected $elementFactory;
    /** @var ProgressStorage $progressStorage */
    protected $progressStorage;
    /** @var IntercomEventService $intercomEventService */
    protected $intercomEventService;
    /** @var ActiveUserContainer $activeUserContainer */
    protected $activeUserContainer;
    /** @var GearmanClient $gearmanClient */
    protected $gearmanClient;
    /** @var PrintedDateGenerator $printedDateGenerator */
    protected $printedDateGenerator;
    /** @var string $key */
    protected $key;
    /** * @var int $count */
    protected $count = 0;

    public function __construct(
        OrderService $orderService,
        RendererService $rendererService,
        TemplateFactory $templateFactory,
        ElementFactory $elementFactory,
        InvoiceSettingsService $invoiceSettingsService,
        ProgressStorage $progressStorage,
        IntercomEventService $intercomEventService,
        ActiveUserContainer $activeUserContainer,
        GearmanClient $gearmanClient,
        PrintedDateGenerator $printedDateGenerator
    ) {
        parent::__construct($rendererService, $templateFactory, $invoiceSettingsService);
        $this
            ->setOrderService($orderService)
            ->setElementFactory($elementFactory)
            ->setProgressStorage($progressStorage)
            ->setIntercomEventService($intercomEventService)
            ->setActiveUserContainer($activeUserContainer)
            ->setGearmanClient($gearmanClient)
            ->setPrintedDateGenerator($printedDateGenerator);
    }

    public function createTemplate(array $config)
    {
        $config['elements'] = $this->createElements($config['elements']);
        $config['paperPage'] = $this->createPaperPage($config['paperPage']);
        return $this->templateFactory->getTemplateFromConfig($config);
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
        return $this->elementFactory->createElement($config);
    }

    protected function createPaperPage(array $config)
    {
        return new PaperPage(
            $config['height'],
            $config['width'],
            $config['paperType'],
            isset($config['inverse']) ? $config['inverse'] : false
        );
    }

    /**
     * @param array $orderIds
     * @return Response
     */
    public function getResponseFromOrderIds(array $orderIds)
    {
        $filter = (new Filter())->setOrderIds($orderIds);
        return $this->getResponseFromOrderCollection(
            $this->orderService->getOrders($filter)
        );
    }

    public function getResponseFromFilterId($filterId)
    {
        return $this->getResponseFromOrderCollection(
            $this->orderService->getOrdersFromFilterId($filterId)
        );
    }

    /**
     * @param Collection $orderCollection
     * @return Response
     */
    public function getResponseFromOrderCollection(Collection $collection, Template $template = null, $key = null)
    {
        return new Response(
            $this->rendererService->getMimeType(),
            $this->rendererService->getFileName(),
            $this->generateInvoiceForCollection($collection, $template, $key)
        );
    }

    public function markOrdersAsPrintedFromOrderCollection(Collection $orderCollection)
    {
        $this->printedDateGenerator->createJobs($orderCollection, new DateTime());

        /** @var Order $order */
        foreach ($orderCollection as $order) {
            $this->statsIncrement(
                static::STAT_ORDER_ACTION_PRINTED, [
                    $order->getChannel(),
                    $this->activeUserContainer->getActiveUserRootOrganisationUnitId(),
                    $this->activeUserContainer->getActiveUser()->getId()
                ]
            );
        }
    }

    public function emailInvoicesForCollection(Collection $orders)
    {
        /**
         * @var Order $order
         */
        foreach ($orders as $order) {
            $workload = new EmailInvoice($order->getId());
            $this->gearmanClient->doBackground(
                $workload->getWorkerFunctionName(),
                serialize($workload),
                implode('-', [$workload->getWorkerFunctionName(), $order->getId()])
            );
        }
    }

    public function generateInvoiceForCollection(Collection $collection, Template $template = null, $key = null)
    {
        $this->key = $key;
        $this->count = 0;
        $this->updateInvoiceGenerationProgress();
        $result = parent::generateInvoiceForCollection($collection, $template);
        $this->notifyOfGeneration();
        return $result;
    }

    protected function generateInvoiceForOrder(Order $order, Template $template = null)
    {
        parent::generateInvoiceForOrder($order, $template);
        $this->count++;
        $this->updateInvoiceGenerationProgress();
    }

    protected function updateInvoiceGenerationProgress()
    {
        if (!$this->key) {
            return $this;
        }

        $this->progressStorage->setProgress($this->key, $this->count);
        return $this;
    }

    protected function notifyOfGeneration()
    {
        $event = new IntercomEvent(static::EVENT_INVOICES_PRINTED, $this->activeUserContainer->getActiveUser()->getId());
        $this->intercomEventService->save($event);
    }

    public function checkInvoiceGenerationProgress($key)
    {
        return (int) $this->progressStorage->getProgress($key);
    }

    /**
     * @return self
     */
    protected function setOrderService(OrderService $orderService)
    {
        $this->orderService = $orderService;
        return $this;
    }

    /**
     * @return self
     */
    protected function setElementFactory(ElementFactory $elementFactory)
    {
        $this->elementFactory = $elementFactory;
        return $this;
    }

    /**
     * @return self
     */
    protected function setProgressStorage(ProgressStorage $progressStorage)
    {
        $this->progressStorage = $progressStorage;
        return $this;
    }

    /**
     * @return self
     */
    protected function setIntercomEventService(IntercomEventService $intercomEventService)
    {
        $this->intercomEventService = $intercomEventService;
        return $this;
    }

    /**
     * @return self
     */
    protected function setActiveUserContainer(ActiveUserContainer $activeUserContainer)
    {
        $this->activeUserContainer = $activeUserContainer;
        return $this;
    }

    /**
     * @return self
     */
    protected function setGearmanClient(GearmanClient $gearmanClient)
    {
        $this->gearmanClient = $gearmanClient;
        return $this;
    }

    /**
     * @return self
     */
    protected function setPrintedDateGenerator(PrintedDateGenerator $printedDateGenerator)
    {
        $this->printedDateGenerator = $printedDateGenerator;
        return $this;
    }
}
