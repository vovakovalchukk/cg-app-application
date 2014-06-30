<?php
namespace Settings\Controller;

use CG\Account\Client\Service as AccountService;
use CG\Account\Client\Entity as AccountEntity;
use CG\Account\Credentials\Cryptor;
use CG\Amazon\Credentials;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\User\ActiveUserInterface;
use CG_UI\View\Prototyper\JsonModelFactory;
use Zend\Di\Di;
use Zend\Mvc\Controller\AbstractActionController;
use Settings\Module;
use CG\Http\Exception\Exception3xx\NotModified;

class AmazonController extends AbstractActionController
{
    protected $di;
    protected $jsonModelFactory;
    protected $activeUserContainer;
    protected $accountService;
    protected $cryptor;

    public function __construct(
        Di $di,
        JsonModelFactory $jsonModelFactory,
        ActiveUserInterface $activeUserContainer,
        AccountService $accountService,
        Cryptor $cryptor
    )
    {
        $this->setDi($di)
            ->setJsonModelFactory($jsonModelFactory)
            ->setActiveUserContainer($activeUserContainer)
            ->setAccountService($accountService)
            ->setCryptor($cryptor);
    }

    public function saveAction()
    {
        if ($this->params()->fromQuery('accountId')) {
            $accountEntity = $this->getAccountService()->fetch($this->params()->fromQuery('accountId'));
        } else {
            $accountEntity = $this->getDi()->newInstance(AccountEntity::class, array(
                "channel" => "amazon",
                "organisationUnitId" => $this->getActiveUserContainer()->getActiveUser()->getOrganisationUnitId(),
                "displayName" => "Amazon",
                "credentials" => "",
                "active" => true,
                "deleted" => false,
                "expiryDate" => null
            ));
        }
        $credentials = $this->getDi()->get(Credentials::class, array(
            'awsAccessKeyId'=> $this->params()->fromPost('AWSAccessKeyId'),
            'merchantId' => $this->params()->fromPost('Merchant'),
            'regionCode' => $this->params()->fromRoute('region')
        ));
        $accountEntity->setCredentials($this->getCryptor()->encrypt($credentials));
        try {
            $accountEntity = $this->getAccountService()->save($accountEntity);
        } catch (NotModified $e) {
            //Ignore the account has been reconnected but the credentials remain the same
        }
        $routeName = implode('/', [Module::ROUTE, ChannelController::ROUTE, ChannelController::ROUTE_CHANNELS, ChannelController::ACCOUNT_ROUTE]);
        $url = $this->plugin('url')->fromRoute($routeName, ["account" => $accountEntity->getId()]);
        $this->plugin('redirect')->toUrl($url);
        return false;
    }

    public function setDi(Di $di)
    {
        $this->di = $di;
        return $this;
    }

    public function getDi()
    {
        return $this->di;
    }

    public function setJsonModelFactory(JsonModelFactory $jsonModelFactory)
    {
        $this->jsonModelFactory = $jsonModelFactory;
        return $this;
    }

    public function getJsonModelFactory()
    {
        return $this->jsonModelFactory;
    }

    public function setActiveUserContainer(ActiveUserInterface $activeUserContainer)
    {
        $this->activeUserContainer = $activeUserContainer;
        return $this;
    }

    public function getActiveUserContainer()
    {
        return $this->activeUserContainer;
    }

    public function setAccountService(AccountService $accountService)
    {
        $this->accountService = $accountService;
        return $this;
    }

    public function getAccountService()
    {
        return $this->accountService;
    }

    public function setCryptor(Cryptor $cryptor)
    {
        $this->cryptor = $cryptor;
        return $this;
    }

    public function getCryptor()
    {
        return $this->cryptor;
    }
}