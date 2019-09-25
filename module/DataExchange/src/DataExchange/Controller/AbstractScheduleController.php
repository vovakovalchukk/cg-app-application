<?php
namespace DataExchange\Controller;

use CG\DataExchangeSchedule\Entity as Schedule;
use CG\Http\Exception\Exception3xx\NotModified;
use CG\Stdlib\Exception\Runtime\Conflict;
use CG\Stdlib\Exception\Runtime\NotFound;
use DataExchange\Schedule\Service;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG_UI\View\Prototyper\ViewModelFactory;
use Zend\Mvc\Controller\AbstractActionController;

abstract class AbstractScheduleController extends AbstractActionController
{
    /** @var ViewModelFactory */
    protected $viewModelFactory;
    /** @var JsonModelFactory */
    protected $jsonModelFactory;
    /** @var Service */
    protected $service;

    public function __construct(
        ViewModelFactory $viewModelFactory,
        JsonModelFactory $jsonModelFactory,
        Service $service
    ) {
        $this->viewModelFactory = $viewModelFactory;
        $this->jsonModelFactory = $jsonModelFactory;
        $this->service = $service;
    }

    public function saveAction()
    {
        $data = $this->sanitisePostData($this->params()->fromPost());
        try {
            $entity = $this->saveForType($data);
            $response = [
                'success' => true,
                'id' => $entity->getId(),
                'etag' => $entity->getStoredETag(),
            ];
        } catch (Conflict $e) {
            $response = [
                'success' => false,
                'message' => 'Someone else has modified that record. Please refresh the page and try again.',
            ];
        } catch (NotModified $e) {
            $response = [
                'success' => true,
                'id' => $data['id'],
                'etag' => $data['etag'],
            ];
        }
        return $this->jsonModelFactory->newInstance($response);
    }

    protected function sanitisePostData(array $data): array
    {
        if (isset($data['active'])) {
            // Booleans sometimes come through as strings
            $data['active'] = filter_var($data['active'], FILTER_VALIDATE_BOOLEAN);
        }
        return array_filter($data, function ($value) {
            // Nulls sometimes come through as the empty string which confuses matters at the mapping stage
            return ($value !== null && $value !== '');
        });
    }

    abstract protected function saveForType(array $data): Schedule;

    public function removeAction()
    {
        $id = $this->params()->fromPost('id');
        try {
            $this->service->remove($id);
        } catch (NotFound $e) {
            // No-op
        }
        return $this->jsonModelFactory->newInstance([
            'success' => true,
            'id' => $id,
        ]);
    }
}