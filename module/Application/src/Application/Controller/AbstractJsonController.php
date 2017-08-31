<?php
namespace Application\Controller;

use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG_UI\View\Prototyper\JsonModelFactory;
use Zend\Mvc\Controller\AbstractActionController;

class AbstractJsonController extends  AbstractActionController implements LoggerAwareInterface
{
    use LogTrait;

    protected $jsonModelFactory;

    public function __construct(JsonModelFactory $jsonModelFactory)
    {
        $this->jsonModelFactory = $jsonModelFactory;
    }

    protected function buildSuccessResponse(array $extraFields = null)
    {
        $result = ['success' => true];
        if (!empty ($extraFields)) {
            $result = array_merge($result, $extraFields);
        }
        return $this->buildResponse($result);
    }

    protected function buildErrorResponse($errorMessage, array $extraFields = [])
    {
        $result = ['error' => $errorMessage];
        if (!empty ($extraFields)) {
            $result = array_merge($result, $extraFields);
        }
        return $this->buildResponse($result);
    }

    protected function buildResponse(array $fields)
    {
        return $this->jsonModelFactory->newInstance($fields);
    }
}
