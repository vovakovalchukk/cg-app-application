<?php
namespace Orders\Order\Filter;

use CG_UI\View\Filters\FilterOptionsInterface;
use CG\User\ActiveUserInterface;
use CG\User\Entity as User;
use CG\Account\Client\Service as AccountService;
use Zend\Di\Di;
use CG_UI\View\Filters\Options\Select;
use CG\Stdlib\Exception\Runtime\NotFound;

class Channel implements FilterOptionsInterface
{
    protected $activeUserContainer;
    protected $accountService;
    protected $di;

    public function __construct(ActiveUserInterface $activeUserContainer, AccountService $accountService, Di $di)
    {
        $this->setActiveUserContainer($activeUserContainer)->setAccountService($accountService)->setDi($di);
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

    public function setDi(Di $di)
    {
        $this->di = $di;
        return $this;
    }

    /**
     * @return Di
     */
    public function getDi()
    {
        return $this->di;
    }

    /**
     * return Select[] array of options to be added to filter
     */
    public function getOptions()
    {
        $options = [];
        try {
            $accounts = $this->getAccountService()->fetchByOU(
                $this->getActiveUser()->getOuList(),
                'all'
            );

            $channels = [];
            foreach ($accounts as $account) {
                if (isset($channels[$account->getChannel()])) {
                    continue;
                }

                $channels[$account->getChannel()] = true;
                $options[] = $this->getDi()->get(
                    Select::class,
                    [
                        'title' => htmlentities($account->getChannel(), ENT_QUOTES),
                    ]
                );
            }
        } catch (NotFound $exception) {
            // No accounts means no channels so ignore
        }
        return $options;
    }
}