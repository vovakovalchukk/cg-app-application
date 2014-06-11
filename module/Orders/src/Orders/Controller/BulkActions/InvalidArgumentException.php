<?php
namespace Orders\Controller\BulkActions;

use InvalidArgumentException as PhpException;

class InvalidArgumentException extends PhpException implements ExceptionInterface {}