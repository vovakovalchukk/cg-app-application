<?php
namespace DataExchange\Controller;

use CG\DataExchangeTemplate\Entity as Template;
use CG\Http\Exception\Exception3xx\NotModified;
use CG\Stdlib\Exception\Runtime\Conflict;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG_UI\View\Prototyper\ViewModelFactory;
use DataExchange\Template\Fields\Factory as FieldsFactory;
use DataExchange\Template\Service as TemplateService;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

class TemplateController extends AbstractActionController implements LoggerAwareInterface
{
    use LogTrait;

    const ROUTE = 'DataExchangeTemplate';
    const ROUTE_SAVE = 'DataExchangeTemplateSave';
    const ROUTE_REMOVE = 'DataExchangeTemplateRemove';

    const LOG_CODE = 'DataExchangeTemplateController';

    const ROUTE_ALLOWED_TYPES_MAP = [
        Template::TYPE_STOCK => Template::TYPE_STOCK,
        'orders' => Template::TYPE_ORDER,
        'orderTracking' => Template::TYPE_ORDER_TRACKING,
    ];

    const TEMPLATE_TYPE_TO_ROUTE_TYPE_MAP = [
        Template::TYPE_STOCK => Template::TYPE_STOCK,
        Template::TYPE_ORDER => 'orders',
        Template::TYPE_ORDER_TRACKING => 'orderTracking',
    ];

    /** @var ViewModelFactory */
    protected $viewModelFactory;
    /** @var JsonModelFactory */
    protected $jsonModelFactory;
    /** @var TemplateService */
    protected $templateService;

    public function __construct(
        ViewModelFactory $viewModelFactory,
        JsonModelFactory $jsonModelFactory,
        TemplateService $templateService
    ) {
        $this->viewModelFactory = $viewModelFactory;
        $this->jsonModelFactory = $jsonModelFactory;
        $this->templateService = $templateService;
    }

    public static function getAllowedRouteTypes(): array
    {
        return array_keys(static::ROUTE_ALLOWED_TYPES_MAP);
    }

    public static function getRouteTypeForTemplateType(string $type): string
    {
        return static::TEMPLATE_TYPE_TO_ROUTE_TYPE_MAP[$type] ?? Template::TYPE_STOCK;
    }

    protected function fetchTypeFromRoute(): string
    {
        $type = $this->params()->fromRoute('type', null);
        return static::ROUTE_ALLOWED_TYPES_MAP[$type];
    }

    public function indexAction()
    {
        $type = $this->fetchTypeFromRoute();
        $viewModel = $this->viewModelFactory->newInstance();
        $viewModel->setTemplate('data-exchange/' . $type . '/template.phtml');

        $cgFieldOptions = FieldsFactory::fetchFieldsForType($type);
        $templates = $this->templateService->fetchAllTemplatesForActiveUser($type);

        $viewModel->setVariables([
            'isHeaderBarVisible' => false,
            'subHeaderHide' => true,
            'templates' => $templates,
            'cgFieldOptions' => $cgFieldOptions
        ]);

        return $viewModel;
    }

    public function saveAction()
    {
        $templateArray = $this->params()->fromPost('template', []);
        $templateId = isset($templateArray['id']) ? intval($templateArray['id']) : 0;
        $type = $this->fetchTypeFromRoute();
        $templateArray['type'] = $type;

        $success = false;
        try {
            $template = $this->templateService->saveForActiveUser($type, $templateArray, $templateId > 0 ? $templateId : null);
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
        $type = $this->fetchTypeFromRoute();

        $templateId = $this->params()->fromPost('id', 0);
        $success = true;
        $response = [];

        try {
            $this->templateService->remove($type, $templateId);
        } catch (NotFound $e) {
            $response = ['message' => 'The template you are trying to delete no longer exists.'];
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
