<?php
namespace DataExchange\Controller;

use CG\Http\Exception\Exception3xx\NotModified;
use CG\Stdlib\Exception\Runtime\Conflict;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG_UI\View\Prototyper\ViewModelFactory;
use DataExchange\Template\Service as TemplateService;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

class TemplateController extends AbstractActionController implements LoggerAwareInterface
{
    use LogTrait;

    const ROUTE = 'DataExchangeTemplate';
    const ROUTE_SAVE = 'DataExchangeTemplateSave';
    const ROUTE_REMOVE = 'DataExchangeTemplateRemove';

    /** @var ViewModelFactory */
    protected $viewModelFactory;
    /** @var JsonModelFactory */
    protected $jsonModelFactory;
    /** @var TemplateService */
    protected $templateService;

    public function __construct(ViewModelFactory $viewModelFactory)
    {
        $this->viewModelFactory = $viewModelFactory;
    }

    public function indexAction()
    {
        return $this->viewModelFactory->newInstance([
            'isHeaderBarVisible' => false,
            'subHeaderHide' => true,
            'stockTemplates' => $this->templateService->fetchAllTemplatesForActiveUser(),
            'cgFieldOptions' => TemplateService::getCgFieldOptions()
        ]);
    }

    public function saveAction()
    {
        $templateArray = $this->params()->fromPost('template', []);
        $templateId = isset($templateArray['id']) ? intval($templateArray['id']) : 0;

        $success = false;
        try {
            $template = $this->templateService->saveForActiveUser($templateArray, $templateId > 0 ? $templateId : null);
            $success = true;
            $response = [
                'template' => $template->toArray(),
                'id' => $template->getId(),
                'etag' => $template->getStoredETag()
            ];
        } catch (NotFound $e) {
            $response = ['message' => 'The template was already deleted, couldn\'t complete the update. Please refresh the page and try again.'];
        } catch (Conflict $e) {
            $response = ['message' => 'Someone else has modified that record. Please refresh the page and try again'];
        } catch (NotModified $e) {
            $response = [
                'template' => $templateArray,
                'id' => $templateId,
                'etag' => $templateArray['etag'] ?? null
            ];
        } catch (\Throwable $e) {
            $this->logErrorException($e);
            $response = ['message' => 'There was an error while saving the template. Please try again and contact support if the issue persists.'];
        }

        return $this->buildJsonResponse($success, $response);
    }

    public function removeAction()
    {
        $templateId = $this->params()->fromPost('id', 0);
        $success = true;
        $response = [];

        try {
            $this->templateService->remove($templateId);
        } catch (NotFound $e) {
            // No-op
        } catch (\Throwable $e) {
            $this->logErrorException($e);
            $success = false;
            $response = ['message' => 'There was an error while saving the template. Please try again and contact support if the issue persists.'];
        }

        return $this->buildJsonResponse($success, $response);
    }

    protected function buildJsonResponse(bool $success, array $response): JsonModel
    {
        $response = array_merge(['success' => $success], $response);
        return $this->jsonModelFactory->newInstance($response);
    }
}
