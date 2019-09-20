<?php
namespace DataExchange\Controller;

use CG\Http\Exception\Exception3xx\NotModified;
use CG\Stdlib\Exception\Runtime\Conflict;
use DataExchange\FtpAccount\Service;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG_UI\View\Prototyper\ViewModelFactory;
use Zend\Mvc\Controller\AbstractActionController;

class FtpAccountController extends AbstractActionController
{
    public const ROUTE = 'FtpAccount';
    public const ROUTE_SAVE = 'Save';

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

    public function indexAction()
    {
        return $this->viewModelFactory->newInstance([
            'isHeaderBarVisible' => false,
            'subHeaderHide' => true,
            'ftpAccounts' => $this->service->fetchAllForActiveUser(),
            'ftpAccountTypeOptions' => $this->service->getTypeOptions(),
            'ftpDefaultPorts' => $this->service->getDefaultPorts(),
        ]);
    }

    public function saveAction()
    {
        $data = $this->params()->fromPost();
        try {
            $entity = $this->service->saveForActiveUser($data);
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
}