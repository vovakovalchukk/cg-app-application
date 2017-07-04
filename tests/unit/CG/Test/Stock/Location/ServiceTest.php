<?php
namespace CG\Test\Stock\Location;

use CG\Stock\Location\Service as StockLocationService;
use \PHPUnit_Framework_TestCase;

class StockLocationTest extends PHPUnit_Framework_TestCase
{
    protected $stockLocationService;
    protected static $di;

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

    public function testProcess()
    {
        $stockLocationService = clone $this->stockLocationService;
    }
}