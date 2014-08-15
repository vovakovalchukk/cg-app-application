<?php
namespace Orders\Order\Filter;

use CG_UI\View\Filters\SelectOptionsInterface;
use CG\User\ActiveUserInterface;
use CG\User\Entity as User;
use CG\Account\Client\Service as AccountService;
use CG\Stdlib\Exception\Runtime\NotFound;
use Orders\Order\Batch\Service as BatchService;
use CG\Order\Shared\Batch\Mapper as BatchMapper;

class Channel implements SelectOptionsInterface
{
    protected $activeUserContainer;
    protected $accountService;
    protected $batchService;
    protected $batchMapper;

    public function __construct(
        ActiveUserInterface $activeUserContainer,
        AccountService $accountService,
        BatchService $batchService)
    {
        $this->setActiveUserContainer($activeUserContainer)
            ->setAccountService($accountService)
            ->setBatchService($batchService)
            ->setBatchMapper($batchMapper);
    }

    public function getBatches()
    {
        return $this->getBatchService()->getBatches();
    }

    public function setActiveUserContainer(ActiveUserInterface $activeUserContainer)
    {
        $this->activeUserContainer = $activeUserContainer;
        return $this;
    }

    /**
     * @return ActiveUserInterface
     */
    public function getActiveUserContainer()
    {
        return $this->activeUserContainer;
    }

    /**
     * @return User
     */
    public function getActiveUser()
    {
        return $this->getActiveUserContainer()->getActiveUser();
    }

    public function setAccountService(AccountService $accountService)
    {
        $this->accountService = $accountService;
        return $this;
    }

    public function getBatchService()
    {
        return $this->batchService;
    }

    public function setBatchService(BatchService $batchService)
    {
        $this->batchService = $batchService;
        return $this;
    }

    public function getBatchMapper()
    {
        return $this->batchMapper;
    }

    public function setBatchMapper(BatchMapper $batchMapper)
    {
        $this->batchMapper = $batchMapper;
        return $this;
    }

    /**
     * @return AccountService
     */
    public function getAccountService()
    {
        return $this->accountService;
    }

    protected function getAccounts(User $user)
    {
        return $this->getAccountService()->fetchByOU(
            $user->getOuList(),
            'all'
        );
    }

    /**
     * {@inherit}
     */
    public function getSelectOptions()
    {
        $options = [];
        try {
            $accounts = $this->getAccounts($this->getActiveUser());
            foreach ($accounts as $account) {
                if (isset($options[$account->getChannel()])) {
                    continue;
                }
                $options[$account->getChannel()] = $account->getChannel();
            }
        } catch (NotFound $exception) {
            // No accounts means no channels so ignore
        }
        return $options;
    }
}