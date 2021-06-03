<?php
namespace Settings\Controller;

use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\User\OrganisationUnit\Service as UserOUService;
use CG_UI\View\Prototyper\ViewModelFactory;
use Settings\Category\Template\Service as CategoryTemplateService;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class CategoryTemplatesController extends AbstractActionController implements LoggerAwareInterface
{
    use LogTrait;

    public const ROUTE_INDEX = 'Index';

    protected const LOG_CODE = 'CategoryTemplatesController';
    protected const LOG_EXCEPTION_MSG = 'Exception when loading Accounts and Categories for OU %d';

    /** @var ViewModelFactory */
    protected $viewModelFactory;
    /** @var CategoryTemplateService */
    protected $categoryTemplateService;
    /** @var UserOUService */
    protected $userOuService;

    public function __construct(
        ViewModelFactory $viewModelFactory,
        CategoryTemplateService $categoryTemplateService,
        UserOUService $userOuService
    ) {
        $this->viewModelFactory = $viewModelFactory;
        $this->categoryTemplateService = $categoryTemplateService;
        $this->userOuService = $userOuService;
    }

    public function indexAction()
    {
        $ou = $this->userOuService->getRootOuByActiveUser();
        try {
            $accounts = $this->categoryTemplateService->fetchAccounts($ou);
            $categories = $this->categoryTemplateService->fetchCategoryRoots($ou);
        } catch (\Throwable $e) {
            $accounts = $categories = [];
            $this->logDebugException($e, static::LOG_EXCEPTION_MSG, ['ou' => $ou], static::LOG_CODE);
        }
        $template = $this->newViewModel()
            ->setVariable('accounts', $accounts)
            ->setVariable('categories', $categories);
        return $template;
    }

    /**
     * @param $variables
     * @param $options
     * @return ViewModel
     */
    protected function newViewModel($variables = null, $options = null)
    {
        return $this->viewModelFactory->newInstance($variables, $options);
    }
}
