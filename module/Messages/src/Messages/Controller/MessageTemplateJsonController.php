<?php
namespace Messages\Controller;

use CG\CourierAdapter\Exception\NotFound;
use CG\Http\Exception\Exception3xx\NotModified;
use CG\Stdlib\Exception\Runtime\Conflict;
use CG_UI\View\Prototyper\JsonModelFactory;
use Messages\Message\Template\Service;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

class MessageTemplateJsonController extends AbstractActionController
{
    public const ROUTE_TEMPLATES = 'Templates';
    public const ROUTE_SAVE = 'Save';
    public const ROUTE_DELETE = 'Delete';
    public const ROUTE_PREVIEW = 'Preview';

    /** @var JsonModelFactory */
    protected $jsonModelFactory;
    /** @var Service */
    protected $service;

    public function __construct(JsonModelFactory $jsonModelFactory, Service $service)
    {
        $this->jsonModelFactory = $jsonModelFactory;
        $this->service = $service;
    }

    public function saveAction(): JsonModel
    {
        $data = $this->params()->fromPost();

        $jsonModel = $this->jsonModelFactory->newInstance();
        if (!isset($data['name'], $data['template']) || !$data['name'] || !$data['template']) {
            $jsonModel->setVariables(['success' => false, 'message' => 'No name or template body specified']);
            return $jsonModel;
        }
        try {
            $entity = $this->service->saveForActiveUser($data);
            $jsonModel->setVariables(['success' => true, 'id' => $entity->getId(), 'etag' => $entity->getStoredETag()]);
            return $jsonModel;
        } catch (Conflict $e) {
            $jsonModel->setVariables(['success' => false, 'message' => 'Someone else has modified that record. Please refresh the page and try again.']);
            return $jsonModel;
        } catch (NotModified $e) {
            $jsonModel->setVariables(['success' => true, 'id' => $data['id'], 'etag' => $data['etag'] ?? null]);
            return $jsonModel;
        }
    }

    public function deleteAction(): JsonModel
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

    public function previewAction(): JsonModel
    {
        $template = $this->params()->fromPost('template');
        $accountId = $this->params()->fromPost('accountId');
        return $this->jsonModelFactory->newInstance([
            'success' => true,
            'content' => $this->service->renderPreview($template, $accountId),
        ]);
    }
}