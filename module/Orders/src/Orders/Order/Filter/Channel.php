<?php
namespace Orders\Order\Filter;

use CG_UI\View\Filters\SelectOptionsInterface;
use CG\User\ActiveUserInterface;
use CG\User\Entity as User;
use CG\Account\Client\Service as AccountService;
use CG\Stdlib\Exception\Runtime\NotFound;

class Channel implements SelectOptionsInterface
{
    protected $activeUserContainer;
    protected $accountService;

    public function __construct(
        ActiveUserInterface $activeUserContainer,
        AccountService $accountService,
        BatchMapper $batchMapper)
    {
        $this->setActiveUserContainer($activeUserContainer)
            ->setAccountService($accountService)
            ->setBatchMapper($batchMapper);
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