<?php
namespace CG\Test\Stock\Location;

use CG\Stock\Location\Service as StockLocationService;
use \PHPUnit_Framework_TestCase;

class StockLocationTest extends PHPUnit_Framework_TestCase
{
    protected $stockLocationService;
    protected static $di;

    /** @var array Must exist in testing environment */
    const EXCLUDE_LOCATION_IDS = [2];

    public static function setUpBeforeClass()
    {
        require_once TEST_PROJECT_ROOT . DIRECTORY_SEPARATOR . 'tests/unit/unit-bootstrap.php';
        static::$di = $di;
    }

    public function setUp()
    {
        $di = static::$di;
        $this->stockLocationService = new StockLocationService(
            $di->get(\CG\Stock\Location\Storage\Api::class),
            $di->get(\CG\Stock\Location\Mapper::class),
            $di->get(\CG\Stock\Auditor::class),
            $di->get(\CG\Stock\Storage\Api::class),
            $di->get(\CG\Notification\Gearman\Generator\Dispatcher::class)
        );
    }

    public function testConstruction()
    {
        $this->assertInstanceOf(StockLocationService::class, $this->stockLocationService);
    }

    public function testRemovesStockLocationsByLocationId()
    {
        $stockLocationCollection = $this->exampleStockLocationCollection();
        $stockLocationCollection->rewind();
        echo sprintf('There are %d stockLocations in the collection' . "\n", $stockLocationCollection->count());
        echo sprintf('The stockLocation IDs in the collection are: %s' . "\n", json_encode($stockLocationCollection->getIds(), true));
        echo sprintf('Attempting to remove stockLocations with Location IDs matching: %s' . "\n", json_encode(static::EXCLUDE_LOCATION_IDS, true));

        $stockLocationService = clone $this->stockLocationService;
        $newStockLocationCollection = $stockLocationService->excludeFromCollectionByLocationIds($stockLocationCollection, static::EXCLUDE_LOCATION_IDS);

        echo sprintf('There is now only %d stockLocations in the collection' . "\n", $newStockLocationCollection->count());
        echo sprintf('The new stockLocation IDs in the collection are: %s' . "\n", json_encode($newStockLocationCollection->getIds(), true));
    }

    public function exampleStockLocationCollection()
    {
        return $this->stockLocationService->fetchCollectionByPaginationAndFilters('all', 1, [], []);
    }
}