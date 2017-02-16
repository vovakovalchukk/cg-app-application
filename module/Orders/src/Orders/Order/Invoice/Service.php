<?php
namespace Orders\Order\Invoice;

use CG\Account\Client\Service as AccountService;
use CG\Communication\Message\AccountAddressGeneratorFactory;
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
use CG\Order\Shared\Tax\Service as TaxService;
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
    /** @var TaxService */
    protected $taxService;
    /** @var AccountService */
    protected $accountService;
    /** @var AccountAddressGeneratorFactory */
    protected $accountAddressGeneratorFactory;

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
        PrintedDateGenerator $printedDateGenerator,
        TaxService $taxService,
        AccountService $accountService,
        AccountAddressGeneratorFactory $accountAddressGeneratorFactory
    ) {
        parent::__construct($rendererService, $templateFactory, $invoiceSettingsService);
        $this->orderService = $orderService;
        $this->elementFactory = $elementFactory;
        $this->progressStorage = $progressStorage;
        $this->intercomEventService = $intercomEventService;
        $this->activeUserContainer = $activeUserContainer;
        $this->gearmanClient = $gearmanClient;
        $this->printedDateGenerator = $printedDateGenerator;
        $this->taxService = $taxService;
        $this->accountService = $accountService;
        $this->accountAddressGeneratorFactory = $accountAddressGeneratorFactory;
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
        $this->setVatNumberOnOrderCollection($orderCollection);

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

    protected function setVatNumberOnOrderCollection(Collection $orderCollection)
    {
        foreach ($orderCollection as $order) {
            if ($order->getVatNumber()) {
                continue;
            }
            $order->setVatNumber($this->taxService->getVatNumberForOrder($order));
        }
        // Note: we're not persisting the VAT numbers now so as not to delay invoice generation,
        // that will happen in the SetPrintedDate Gearman job
        return $this;
    }

    public function getInvoiceStats(Collection $orders)
    {
        $stats = ['printed' => 0, 'emailed' => 0, 'total' => 0, 'emailingAllowed' => false];

        /** @var Order $order */
        foreach ($orders as $order) {
            $stats['emailingAllowed'] = $this->isEmailingAllowedForOrder($order);

            if ($order->getInvoiceDate()) {
                $stats['printed']++;
            }
            if ($order->getEmailDate()) {
                $stats['emailed']++;
            }

            $stats['total']++;
        }
        return $stats;
    }

    public function emailInvoicesForCollection(Collection $orders, $includePreviouslySent = false)
    {
        /**
         * @var Order $order
         */
        foreach ($orders as $order) {
            if (!$includePreviouslySent && $order->getEmailDate()) {
                // Skip any orders we have previously emailed
                continue;
            }

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

    public function isEmailingAllowedForOrder(Order $order)
    {
        // Check if there is a channel specific email address we can use first (by passing invoices settings config).
        $account = $this->accountService->fetch($order->getAccountId());
        try {
            $accountAddressGenerator = $this->accountAddressGeneratorFactory->getGeneratorForChannel($order->getChannel());
            $sendFrom = $accountAddressGenerator($account);
            if ($sendFrom) {
                return true;
            }
        } catch (\InvalidArgumentException $exception) {
            // No account address generator for this channel - skip it
        }

        $orderRootOuId = $order->getRootOrganisationUnitId();
        $invoiceSettings = $this->invoiceSettingsService->fetch($orderRootOuId);

        if ($invoiceSettings->isAutoEmailAllowed()) {
            return true;
        }

        return false;
    }
}
