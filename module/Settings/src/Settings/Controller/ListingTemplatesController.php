<?php

namespace Settings\Controller;

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
        // todo - replace with non dummy data as part of TAC-433
        $response = $this->newJsonModel();
        $response->setVariable('success', [
            "message" => "You have successfully saved your template."
        ]);
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
        return json_encode($usersTemplates, JSON_UNESCAPED_SLASHES);
    }
}
