<?php
namespace Filters\Options;

use CG_UI\View\Filters\SelectOptionsInterface;
use CG\User\ActiveUserInterface;
use CG\User\Entity as User;
use CG\Account\Client\Service as AccountService;
use CG\Account\Shared\Filter as AccountFilter;
use CG\Channel\Type as ChannelType;
use CG\Stdlib\Exception\Runtime\NotFound;

class Channel implements SelectOptionsInterface
{
    protected $activeUserContainer;
    protected $accountService;

    public function __construct(
        ActiveUserInterface $activeUserContainer,
        AccountService $accountService
    )
    {
        $this->setActiveUserContainer($activeUserContainer)
            ->setAccountService($accountService);
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

    protected function getAccounts(User $user, $includeInvisible = false)
    {
        $filter = (new AccountFilter())
            ->setLimit('all')
            ->setPage(1)
            ->setType(ChannelType::SALES)
            ->setOrganisationUnitId($user->getOuList());
        return $this->accountService->fetchByFilter($filter, $includeInvisible);
    }

    /**
     * {@inherit}
     */
    public function getSelectOptions()
    {
        $options = [];
        try {
            // Include hidden Accounts' channels as we still want to allow filtering by them
            $includeInvisible = true;
            $accounts = $this->getAccounts($this->getActiveUser(), $includeInvisible);
            foreach ($accounts as $account) {
                if (isset($options[$account->getChannel()])) {
                    continue;
                }
                $displayName = ($account->getDisplayChannel() ?: str_replace(' ', '', ucwords(str_replace('-', ' ', $account->getChannel()))));
                $options[$account->getChannel()] = $displayName;
            }
        } catch (NotFound $exception) {
            // No accounts means no channels so ignore
        }
        return $options;
    }
}