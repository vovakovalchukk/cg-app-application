<?php
namespace Products\Product\Csv;

use CG\StdLib\Storage\FetchInterface;
use CG\StdLib\Storage\RemoveInterface;
use CG\StdLib\Storage\SaveInterface;

interface StorageInterface extends FetchInterface, SaveInterface, RemoveInterface
{

}