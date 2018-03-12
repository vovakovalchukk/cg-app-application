<?php
namespace CG\CourierExport\RoyalMailClickDrop;

use CG\Account\Shared\Entity as Account;
use CG\Account\Shared\Mapper as AccountMapper;
use CG\Account\Shipping\GenericAccountProviderInterface;
use CG\Channel\Type;
use CG\FeatureFlags\Service as FeatureFlagService;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\User\OrganisationUnit\Service as OUService;

class GenericAccountProvider implements GenericAccountProviderInterface
{
    const CHANNEL = 'royal-mail-click-drop';
    const FEATURE_FLAG = 'RM Click and Drop';

    /** @var FeatureFlagService */
    protected $featureFlagService;
    /** @var OuService */
    protected $ouService;
    /** @var AccountMapper */
    protected $accountMapper;

    public function __construct(
        FeatureFlagService $featureFlagService,
        OuService $ouService,
        AccountMapper $accountMapper
    ) {
        $this->featureFlagService = $featureFlagService;
        $this->ouService = $ouService;
        $this->accountMapper = $accountMapper;
    }

    public function __invoke(): ?Account
    {
        /** @var OrganisationUnit $ou */
        $ou = $this->ouService->getRootOuByActiveUser();
        if (!$this->featureFlagService->isActive(static::FEATURE_FLAG, $ou)) {
            return null;
        }

        return $this->accountMapper->fromArray([
            'id' => PHP_INT_MAX,
            'channel' => static::CHANNEL,
            'organisationUnitId' => $ou->getId(),
            'rootOrganisationUnitId' => $ou->getId(),
            'displayName' => 'Royal Mail Click & Drop',
            'credentials' => null,
            'active' => true,
            'deleted' => false,
            'type' => [Type::SHIPPING],
        ]);
    }
}