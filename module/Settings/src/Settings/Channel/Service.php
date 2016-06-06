<?php
namespace Settings\Channel;

use CG_UI\View\DataTable;
use CG\Account\Client\Mapper as AccountMapper;
use CG\Account\Client\Service as AccountClient;
use CG\Account\Shared\Entity as AccountEntity;
use CG\Channel\AccountFactory;
use CG\Channel\Service as ChannelService;
use CG\Intercom\Event\Request as IntercomEvent;
use CG\Intercom\Event\Service as IntercomEventService;
use CG\OrganisationUnit\StorageInterface as OUClient;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\User\ActiveUserInterface;
use Settings\Module;
use Settings\Controller\ChannelController;
use Zend\Form\Form;
use Zend\ServiceManager\ServiceManager;
use Zend\Session\ManagerInterface as SessionManager;

class Service
{
    const EVENT_ACCOUNT_ADDED = 'Account Added';
    const SESSION_ADD_CHANNEL_RETURN_ROUTE = 'addChannelReturnRoute';
    const ADD_CHANNEL_RETURN_ROUTE_TIMEOUT_SEC = 600;

    protected $accountList;
    protected $accountClient;
    protected $ouClient;
    protected $channelService;
    protected $serviceManager;
    /** @var AccountMapper */
    protected $accountMapper;
    /** @var ActiveUserInterface */
    protected $activeUserContainer;
    /** @var AccountFactory */
    protected $accountFactory;
    /** @var IntercomEventService */
    protected $intercomEventService;
    /** @var SessionManager */
    protected $sessionManager;

    public function __construct(
        AccountClient $accountClient,
        OUClient $ouClient,
        ChannelService $channelService,
        ServiceManager $serviceManager,
        AccountMapper $accountMapper,
        ActiveUserInterface $activeUserContainer,
        AccountFactory $accountFactory,
        IntercomEventService $intercomEventService,
        SessionManager $sessionManager
    ) {
        $this->setAccountClient($accountClient)
            ->setOuClient($ouClient)
            ->setChannelService($channelService)
            ->setServiceManager($serviceManager)
            ->setAccountMapper($accountMapper)
            ->setActiveUserContainer($activeUserContainer)
            ->setAccountFactory($accountFactory)
            ->setIntercomEventService($intercomEventService)
            ->setSessionManager($sessionManager);
    }

    public function setAccountList(DataTable $accountList)
    {
        $this->accountList = $accountList;
        return $this;
    }

    /**
     * @return DataTable
     */
    public function getAccountList()
    {
        return $this->accountList;
    }

    public function setupAccountList($type)
    {
        $accountList = $this->getServiceManager()->get($type . 'AccountList');
        $this->setAccountList($accountList);
    }

    /**
     * @return Form
     */
    public function getNewChannelForm()
    {
        return new Form();
    }

    public function getChannelSpecificTemplateNameForAccount(AccountEntity $account)
    {
        return $this->getChannelService()->getChannelSpecificTemplateNameForAccount($account);
    }

    public function getChannelSpecificFormNameForAccount(AccountEntity $account)
    {
        return $this->getChannelService()->getChannelSpecificFormNameForAccount($account);
    }

    public function getTradingCompanyOptionsForAccount(AccountEntity $account)
    {
        try {
            $ou = $this->getOuClient()->fetch($account->getOrganisationUnitId());
            if ($ou->getRoot()) {
                $rootOu = $this->getOuClient()->fetch($ou->getRoot());
            } else {
                $rootOu = $ou;
            }
            $limit = 'all';
            $page = 1;
            $rootOuChildren = $this->getOuClient()->fetchFiltered($limit, $page, $rootOu->getId());
            $tradingCompanies = [];
            foreach ($rootOuChildren as $rootOuChild) {
                if ($rootOuChild->getParent() != $rootOu->getId()) {
                    continue;
                }
                $tradingCompanies[] = $rootOuChild;
            }
            return $tradingCompanies;
        } catch (NotFound $e) {
            return [];
        }
    }

    public function getAccount($id)
    {
        return $this->getAccountClient()->fetch($id);
    }

    public function updateAccount($id, $data)
    {
        $account = $this->getAccount($id);
        foreach ($data as $key => $value) {
            $setter = 'set'.ucfirst($key);
            $account->$setter($value);
        }
        $this->getAccountClient()->save($account);
        return $account;
    }

    public function createAccount($type, $channel, $region)
    {
        $accountEntity = $this->accountMapper->fromArray([
            "channel" => $channel,
            "organisationUnitId" => $this->activeUserContainer->getActiveUser()->getOrganisationUnitId(),
            "displayName" => "",
            "credentials" => "",
            "active" => false,
            "deleted" => false,
            "expiryDate" => null,
            "type" => $type,
            "stockManagement" => false,
        ]);
        $baseRoute = Module::ROUTE . '/' . ChannelController::ROUTE . '/' . ChannelController::ROUTE_CHANNELS;
        $url = $this->accountFactory->createRedirect(
            $accountEntity, $baseRoute, ["type" => $type], $region
        );
        $this->notifyOfChange(static::EVENT_ACCOUNT_ADDED, $accountEntity);
        return $url;
    }

    protected function notifyOfChange($change, AccountEntity $accountEntity)
    {
        $event = new IntercomEvent(
            $change,
            $this->activeUserContainer->getActiveUser()->getId(),
            [
                'id' => $accountEntity->getId(),
                'channel' => $accountEntity->getChannel(),
                'status' => $accountEntity->getStatus(),
                'stockManagement' => $accountEntity->getStockManagement(),
            ]
        );
        $this->intercomEventService->save($event);
    }

    public function getAddChannelRedirectRoute()
    {
        $session = $this->sessionManager->getStorage();
        if (!isset($session[Module::SESSION_KEY], $session[Module::SESSION_KEY][static::SESSION_ADD_CHANNEL_RETURN_ROUTE])) {
            return null;
        }
        $redirectRouteDetails = $session[Module::SESSION_KEY][static::SESSION_ADD_CHANNEL_RETURN_ROUTE];
        unset($session[Module::SESSION_KEY][static::SESSION_ADD_CHANNEL_RETURN_ROUTE]);
        $timeElapsed = time() - $redirectRouteDetails['timestamp'];
        if ($timeElapsed > static::ADD_CHANNEL_RETURN_ROUTE_TIMEOUT_SEC) {
            return null;
        }
        return $redirectRouteDetails['route'];
    }

    public function getAccountClient()
    {
        return $this->accountClient;
    }

    public function setAccountClient(AccountClient $accountClient)
    {
        $this->accountClient = $accountClient;
        return $this;
    }

    public function getOuClient()
    {
        return $this->ouClient;
    }

    public function setOuClient(OuClient $ouClient)
    {
        $this->ouClient = $ouClient;
        return $this;
    }

    public function getChannelService()
    {
        return $this->channelService;
    }

    public function setChannelService(ChannelService $channelService)
    {
        $this->channelService = $channelService;
        return $this;
    }

    protected function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
        return $this;
    }

    protected function getServiceManager()
    {
        return $this->serviceManager;
    }

    protected function setAccountMapper(AccountMapper $accountMapper)
    {
        $this->accountMapper = $accountMapper;
        return $this;
    }

    protected function setActiveUserContainer(ActiveUserInterface $activeUserContainer)
    {
        $this->activeUserContainer = $activeUserContainer;
        return $this;
    }

    protected function setAccountFactory(AccountFactory $accountFactory)
    {
        $this->accountFactory = $accountFactory;
        return $this;
    }

    protected function setIntercomEventService(IntercomEventService $intercomEventService)
    {
        $this->intercomEventService = $intercomEventService;
        return $this;
    }

    protected function setSessionManager(SessionManager $sessionManager)
    {
        $this->sessionManager = $sessionManager;
        return $this;
    }
}