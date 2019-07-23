<?php

namespace Settings\Controller;

use CG\Http\Exception\Exception4xx\Conflict;
use CG_UI\View\Prototyper\ViewModelFactory;
use CG_UI\View\Prototyper\JsonModelFactory;
use Zend\Mvc\Controller\AbstractActionController;
use Settings\ListingTemplate\Service as ListingTemplateService;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

class ListingTemplatesController extends AbstractActionController
{
    const ROOT_INDEX = 'Templates';
    const SAVE_INDEX = 'Save';
    const DELETE_INDEX = 'Delete';
    const PREVIEW_INDEX = 'Preview';

    /** @var ViewModelFactory */
    protected $viewModelFactory;
    /** @var JsonModelFactory */
    protected $jsonModelFactory;
    /** @var ListingTemplateService */
    protected $listingTemplateService;

    public function __construct(
        ViewModelFactory $viewModelFactory,
        JsonModelFactory $jsonModelFactory,
        ListingTemplateService $listingTemplateService
    ) {
        $this->viewModelFactory = $viewModelFactory;
        $this->jsonModelFactory = $jsonModelFactory;
        $this->listingTemplateService = $listingTemplateService;
    }

    public function indexAction(): ViewModel
    {
        $view = $this->newViewModel();
        $view->setTemplate('settings/listing/index');
        $view->setVariable('listingTemplateTags', $this->getListingTemplateTags());
        $view->setVariable('templates', $this->getUsersTemplates());
        $view->setVariable('isHeaderBarVisible', true);
        $view->setVariable('subHeaderHide', true);

        return $view;
    }

    public function deleteAction(): JsonModel
    {
        $response = $this->newJsonModel();
        $response->setVariable('success', [
            "message" => "You have successfully deleted your template."
        ]);
        return $response;
    }

    public function saveAction(): JsonModel
    {
        $response = $this->newJsonModel();
        try {
            $template = $this->listingTemplateService->saveFromPostData($this->params()->fromPost());
            $response->setVariable('success', [
                'message' => 'Template saved successfully.',
                'id' => $template->getId(),
                'etag' => $template->getStoredETag(),
            ]);
        } catch (Conflict $e) {
            $response->setVariable('error', [
                'message' => 'Someone else has changed this template. Please refresh the page and try again.'
            ]);
        }
        return $response;
    }

    public function previewAction(): JsonModel
    {
        // todo - replace with non dummy data as part of TAC-433
        $response = $this->newJsonModel();
        $html = "<h2>Perfect Product</h2><p>This is the description of your perfect product</p>";

        $response->setVariable('success', [
            "message" => "You have successfully received your preview data.",
            "data" =>
                [
                    "html" => $html
                ]
        ]);
        return $response;
    }

    protected function newJsonModel(): JsonModel
    {
        return $this->jsonModelFactory->newInstance();
    }

    protected function newViewModel($variables = null,  $options = null): ViewModel
    {
        return $this->viewModelFactory->newInstance($variables, $options);
    }

    protected function getListingTemplateTags(): string
    {
        $templateTags = $this->listingTemplateService->getListingTemplateTags();
        return json_encode($templateTags);
    }

    protected function getUsersTemplates(): string
    {
        $usersTemplates = $this->listingTemplateService->getUsersTemplates();
        $userTemplatesArray = [];
        foreach ($usersTemplates as $template) {
            $templateArray = $template->toArray();
            $templateArray['etag'] = $template->getStoredETag();
            $userTemplatesArray[] = $templateArray;
        }
        return json_encode($userTemplatesArray, JSON_UNESCAPED_SLASHES);
    }
}
