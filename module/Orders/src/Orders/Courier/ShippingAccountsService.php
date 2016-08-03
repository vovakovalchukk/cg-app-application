<?php
namespace Orders\Courier;

use CG\Account\Client\Service as AccountService;
use CG\Account\Shared\Collection as AccountCollection;
use CG\Account\Shared\Entity as Account;
use CG\Account\Shared\Filter as AccountFilter;
use CG\Channel\Type as ChannelType;
use CG\Channel\ShippChannelsProvider\AccountDisplayNameInterface;
use CG\Channel\ShippingChannelsProviderRepository;
use CG\User\OrganisationUnit\Service as UserOuService;

class ShippingAccountsService
{
    /** @var AccountService */
    protected $accountService;
    /** @var UserOuService */
    protected $userOuService;
    /** @var ShippingChannelsProviderRepository */
    protected $shippingChannelsProviderRepository;

    /** @var AccountCollection */
    protected $shippingAccounts;

    public function __construct(
        AccountService $accountService,
        UserOuService $userOuService,
        ShippingChannelsProviderRepository $shippingChannelsProviderRepository
    ) {
        $this->setAccountService($accountService)
            ->setUserOuService($userOuService)
            ->setShippingChannelsProviderRepository($shippingChannelsProviderRepository);
    }

    /**
     * @return AccountCollection
     */
    public function getShippingAccounts()
    {
        if ($this->shippingAccounts) {
            return $this->shippingAccounts;
        }
        $ouIds = $this->userOuService->getAncestorOrganisationUnitIdsByActiveUser();
        $filter = (new AccountFilter())
            ->setLimit('all')
            ->setPage(1)
            ->setOrganisationUnitId($ouIds)
            ->setType(ChannelType::SHIPPING)
            ->setDeleted(false);
        $this->shippingAccounts =  $this->accountService->fetchByFilter($filter);
        return $this->shippingAccounts;
    }

    /**
     * @return AccountCollection
     */
    public function getProvidedShippingAccounts()
    {
        $accounts = $this->getShippingAccounts();
        $providedAccounts = new AccountCollection(Account::class, __FUNCTION__);

        foreach ($accounts as $account)
        {
            if (!$this->shippingChannelsProviderRepository->isProvidedAccount($account)) {
                continue;
            }

            $providedAccounts->attach($account);
        }

        return $providedAccounts;
    }

    /**
     * @return array
     */
    public function convertShippingAccountsToOptions(AccountCollection $shippingAccounts, $selectedAccountId = null)
    {
        $courierOptions = [];
        foreach ($shippingAccounts as $shippingAccount) {
            $displayName = $this->getDisplayNameForAccount($shippingAccount);
            $courierOptions[] = [
                'value' => $shippingAccount->getId(),
                'title' => $displayName,
                'selected' => ($shippingAccount->getId() == $selectedAccountId),
            ];
        }
        return $courierOptions;
    }

    protected function getDisplayNameForAccount(Account $account)
    {
        $displayName = $account->getDisplayname();
        if (!$this->shippingChannelsProviderRepository->isProvidedAccount($account)) {
            return $displayName;
        }
        $provider = $this->shippingChannelsProviderRepository->getProviderForAccount($account);
        if ($provider instanceof AccountDisplayNameInterface) {
            $displayName = $provider->getDisplayNameForAccount($account);
        }
        return $displayName;
    }

    protected function setAccountService(AccountService $accountService)
    {
        $this->accountService = $accountService;
        return $this;
    }

    protected function setUserOuService(UserOuService $userOuService)
    {
        $this->userOuService = $userOuService;
        return $this;
    }

    protected function setShippingChannelsProviderRepository(ShippingChannelsProviderRepository $shippingChannelsProviderRepository)
    {
        $this->shippingChannelsProviderRepository = $shippingChannelsProviderRepository;
        return $this;
    }
}