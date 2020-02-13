<?php
namespace Orders\Order\Csv;

use CG\Intercom\Event\Request as IntercomEvent;
use CG\Intercom\Event\Service as IntercomEventService;
use CG\Order\Client\Csv\Generator as CsvGenerator;
use CG\Order\Service\Filter as OrderFilter;
use CG\Order\Shared\Collection as OrderCollection;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\User\OrganisationUnit\Service as UserOuService;
use Orders\Order\Csv\Fields\Orders as OrdersFields;
use Orders\Order\Csv\Fields\OrdersItems as OrdersItemsFields;

class Service implements LoggerAwareInterface
{
    use LogTrait;

    const EVENT_CSV_GENERATED = 'Orders CSV Generated';

    const MIME_TYPE = 'text/csv';
    const FILENAME = 'orders.csv';

    /** @var CsvGenerator */
    protected $csvGenerator;
    /** @var UserOuService */
    protected $userOuService;
    /** @var IntercomEventService */
    protected $intercomEventService;

    public function __construct(
        CsvGenerator $csvGenerator,
        UserOuService $userOuService,
        IntercomEventService $intercomEventService
    ) {
        $this->csvGenerator = $csvGenerator;
        $this->userOuService = $userOuService;
        $this->intercomEventService = $intercomEventService;
    }

    public function generateCsvForOrders(OrderCollection $orders, ?string $progressKey = null): string
    {
        $csv = $this->csvGenerator->generateCsvForOrders(
            $orders,
            $this->userOuService->getRootOuByActiveUser(),
            $progressKey,
            OrdersFields::getFields()
        );
        $this->notifyOfGeneration();
        return $csv;
    }

    public function generateCsvFromFilterForOrders(OrderFilter $filter, ?string $progressKey = null): string
    {
        $csv = $this->csvGenerator->generateCsvFromFilterForOrders(
            $filter,
            $this->userOuService->getRootOuByActiveUser(),
            $progressKey,
            OrdersFields::getFields()
        );
        $this->notifyOfGeneration();
        return $csv;
    }

    public function generateCsvForOrdersAndItems(OrderCollection $orders, ?string $progressKey = null): string
    {
        $csv = $this->csvGenerator->generateCsvForOrdersAndItems(
            $orders,
            $this->userOuService->getRootOuByActiveUser(),
            $progressKey,
            OrdersItemsFields::getFields()
        );
        $this->notifyOfGeneration();
        return $csv;
    }

    public function generateCsvFromFilterForOrdersAndItems(OrderFilter $filter, ?string $progressKey = null): string
    {
        $csv = $this->csvGenerator->generateCsvFromFilterForOrdersAndItems(
            $filter,
            $this->userOuService->getRootOuByActiveUser(),
            $progressKey,
            OrdersItemsFields::getFields()
        );
        $this->notifyOfGeneration();
        return $csv;
    }

    public function generateCsvForAllOrders(array $ouIds, ?string $progressKey = null, ?array $fields = null): string
    {
        $csv = $this->csvGenerator->generateCsvForAllOrders($ouIds, $this->userOuService->getRootOuByActiveUser(), $progressKey, $fields);
        $this->notifyOfGeneration();
        return $csv;
    }

    public function generateCsvForAllOrdersAndItems(array $ouIds, ?string $progressKey = null, ?array $fields = null): string
    {
        $csv = $this->csvGenerator->generateCsvForAllOrdersAndItems($ouIds, $this->userOuService->getRootOuByActiveUser(), $progressKey, $fields);
        $this->notifyOfGeneration();
        return $csv;
    }

    public function checkToCsvGenerationProgress(string $progressKey): ?int
    {
        return $this->csvGenerator->checkToCsvGenerationProgress($progressKey);
    }

    public function startProgress(string $progressKey): void
    {
        $this->csvGenerator->startProgress($progressKey);
    }

    protected function notifyOfGeneration(): void
    {
        $event = new IntercomEvent(static::EVENT_CSV_GENERATED, $this->userOuService->getActiveUser()->getId());
        $this->intercomEventService->save($event);
    }
}
