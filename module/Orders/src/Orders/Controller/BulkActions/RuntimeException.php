<?php
namespace Orders\Controller\BulkActions;

use RuntimeException as PhpException;

class RuntimeException extends PhpException implements ExceptionInterface {}