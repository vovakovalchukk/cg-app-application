<?php
namespace Settings\Channel;

use CG_UI\View\DataTable;
use CG\Account\Shared\Entity as AccountEntity;
use CG\Account\Client\Service as AccountClient;
use CG\Channel\Service as ChannelService;
use CG\OrganisationUnit\StorageInterface as OUClient;
use CG\Stdlib\Exception\Runtime\NotFound;
use Settings\Controller\ChannelController;
use Zend\Form\Form;

class Service
{
    protected $accountList;
    protected $accountClient;
    protected $ouClient;
    protected $channelService;

    public function __construct(
        DataTable $accountList,
        AccountClient $accountClient,
        OUClient $ouClient,
        ChannelService $channelService
    ) {
        $this->setAccountList($accountList)
            ->setAccountClient($accountClient)
            ->setOuClient($ouClient)
            ->setChannelService($channelService);
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
}