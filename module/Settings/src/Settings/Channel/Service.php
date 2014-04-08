<?php
namespace Settings\Channel;

use CG_UI\View\DataTable;
use CG\Account\Shared\Entity as AccountEntity;
use CG\Account\Client\Service as AccountClient;
use CG\OrganisationUnit\StorageInterface as OUClient;
use CG\Stdlib\Exception\Runtime\NotFound;
use Settings\Controller\ChannelController;
use Zend\Form\Form;

class Service
{
    protected $accountList;
    protected $accountClient;
    protected $ouClient;

    public function __construct(
        DataTable $accountList,
        AccountClient $accountClient,
        OUClient $ouClient
    ) {
        $this->setAccountList($accountList)
            ->setAccountClient($accountClient)
            ->setOuClient($ouClient);
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

    public function getChannelSpecificTemplateForAccount(AccountEntity $account)
    {
        /*
         * This is to be implemented by CGIV-1916
         */
        return ChannelController::ACCOUNT_CHANNEL_FORM_BLANK_TEMPLATE;
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
            $rootOuChildren = $this->getOuClient()->fetchFiltered($page, $limit, $rootOu->getId());
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
}