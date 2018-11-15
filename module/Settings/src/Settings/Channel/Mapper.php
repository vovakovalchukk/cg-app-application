<?php
namespace Settings\Channel;

use CG\Account\DataTableMapper;
use CG\Account\Shared\Entity;
use CG\NetDespatch\Account\CreationService\RoyalMail as NDRMAccountCreationService;
use CG\OrganisationUnit\StorageInterface as OUStorage;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\User\ActiveUserInterface as ActiveUser;
use DateTime;
use Settings\Controller\ChannelController;
use Settings\Module;
use Zend\Mvc\Controller\Plugin\Url;

class Mapper extends DataTableMapper
{
    const STATUS_ACTIVE = 'active';
    const STATUS_EXPIRED = 'expired';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_DELETED = 'deleted';

    /** @var OUStorage $ouStorage */
    protected $ouStorage;
    /** @var ActiveUser $activeUser */
    protected $activeUser;

    public function __construct(OUStorage $ouStorage, ActiveUser $activeUser)
    {
        $this->setOuStorage($ouStorage)->setActiveUser($activeUser);
    }

    public function toDataTableArray(Entity $accountEntity, Url $urlPlugin = null, $type = null, DateTime $now = null)
    {
        if (!($now instanceof DateTime)) {
            $now = new DateTime();
        }

        $dataTableArray = parent::toDataTableArray($accountEntity);
        $dataTableArray['manageLinks'] = $this->getManageLinks($accountEntity->getId(), $type, $urlPlugin);
        $dataTableArray['organisationUnit'] = $this->getOrganisationUnitCompanyName($accountEntity->getOrganisationUnitId());
        $dataTableArray['status'] = $accountEntity->getStatus($now);
        // Don't allow users to enable pending OBA accounts, we enable them once we get the credentials
        if ($accountEntity->getChannel() == NDRMAccountCreationService::CHANNEL_NAME
            && !$this->activeUser->isAdmin()
            && $accountEntity->getPending()
        ) {
            $dataTableArray['disabled'] = true;
        }

        $dataTableArray['expiryDate'] = 'N/A';
        $expiryDate = $accountEntity->getExpiryDateAsDateTime();
        if ($expiryDate instanceof DateTime) {
            $timeToExpiry = $expiryDate->getTimestamp() - $now->getTimestamp();
            $dataTableArray['expiryDate'] = ($timeToExpiry > 0) ? $expiryDate->format('jS F Y') : 'Expired';
        }

        return $dataTableArray;
    }

    protected function getManageLinks($id, $type, Url $urlPlugin)
    {
        $links = [
            'manage' => ChannelController::ROUTE_CHANNELS . '/' . ChannelController::ROUTE_ACCOUNT
        ];

        $manageLinks = [];
        foreach ($links as $class => $link) {
            $route = Module::ROUTE . '/' . ChannelController::ROUTE . '/' . $link;
            $routeMap = explode('/', $route);
            $manageLinks[] = [
                'name' => end($routeMap),
                'class' => $class,
                'href' => $urlPlugin->fromRoute($route, ['account' => $id, 'type' => $type])
            ];
        }

        return $manageLinks;
    }

    /**
     * @return self
     */
    protected function setOuStorage(OUStorage $ouStorage)
    {
        $this->ouStorage = $ouStorage;
        return $this;
    }

    protected function getOrganisationUnitCompanyName($ouId)
    {
        try {
            $ou = $this->ouStorage->fetch($ouId);
            return $ou->getAddressCompanyName();
        } catch (NotFound $exception) {
            return '';
        }
    }

    /**
     * @return self
     */
    protected function setActiveUser(ActiveUser $activeUser)
    {
        $this->activeUser = $activeUser;
        return $this;
    }
}
