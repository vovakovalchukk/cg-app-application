<?php
namespace CourierAdapter;

use CG\Account\Client\Service as AccountService;
use CG\CourierAdapter\Provider\Account\CreationService as AccountCreationService;
use CG\CourierAdapter\Provider\Account\Mapper as CAAccountMapper;
use CG\CourierAdapter\Provider\Implementation\Address\Mapper as CAAddressMapper;
use CG\CourierAdapter\Provider\Implementation\Service as AdapterImplementationService;
use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\User\ActiveUserInterface;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG_UI\View\Prototyper\ViewModelFactory;
use CourierAdapter\Account\Service as CAModuleAccountService;
use Zend\View\Model\ViewModel;
use Zend\Mvc\Controller\PluginManager as PluginManager;

abstract class FormAbstract implements FormInterface
{
    /** @var AdapterImplementationService */
    protected $adapterImplementationService;
    /** @var AccountCreationService */
    protected $accountCreationService;
    /** @var ViewModelFactory */
    protected $viewModelFactory;
    /** @var JsonModelFactory */
    protected $jsonModelFactory;
    /** @var ActiveUserInterface */
    protected $activeUserContainer;
    /** @var CAModuleAccountService */
    protected $caModuleAccountService;
    /** @var CAAddressMapper */
    protected $caAddressMapper;
    /** @var OrganisationUnitService */
    protected $organisationUnitService;
    /** @var AccountService */
    protected $accountService;
    /** @var CAAccountMapper */
    protected $caAccountMapper;
    /** @var PluginManager */
    protected $pluginManager;

    public function __construct(
        AdapterImplementationService $adapterImplementationService,
        AccountCreationService $accountCreationService,
        ViewModelFactory $viewModelFactory,
        JsonModelFactory $jsonModelFactory,
        ActiveUserInterface $activeUserContainer,
        CAModuleAccountService $caModuleAccountService,
        CAAddressMapper $caAddressMapper,
        OrganisationUnitService $organisationUnitService,
        AccountService $accountService,
        CAAccountMapper $caAccountMapper,
        PluginManager $pluginManager
    ) {
        $this->adapterImplementationService = $adapterImplementationService;
        $this->accountCreationService = $accountCreationService;
        $this->viewModelFactory = $viewModelFactory;
        $this->jsonModelFactory = $jsonModelFactory;
        $this->activeUserContainer = $activeUserContainer;
        $this->caModuleAccountService = $caModuleAccountService;
        $this->caAddressMapper = $caAddressMapper;
        $this->organisationUnitService = $organisationUnitService;
        $this->accountService = $accountService;
        $this->caAccountMapper = $caAccountMapper;
        $this->pluginManager = $pluginManager;
    }

    abstract public function getFormView(string $shippingChannel, int $accountId,  string $goBackUrl, string $saveUrl): ViewModel;
}