<?php
namespace Settings\Channel;

use CG\Account\Shared\Entity;
use CG\OrganisationUnit\StorageInterface as OUStorage;
use CG\Stdlib\Exception\Runtime\NotFound;
use Settings\Controller\ChannelController;
use Settings\Module;
use Zend\Mvc\Controller\Plugin\Url;
use DateTime;
use Exception;

class Mapper
{
    const STATUS_ACTIVE = 'active';
    const STATUS_EXPIRED = 'expired';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_DELETED = 'deleted';

    protected $ouStorage;

    public function __construct(OUStorage $ouStorage)
    {
        $this->setOuStorage($ouStorage);
    }

    public function setOuStorage(OUStorage $ouStorage)
    {
        $this->ouStorage = $ouStorage;
        return $this;
    }

    /**
     * @return OUStorage
     */
    public function getOuStorage()
    {
        return $this->ouStorage;
    }

    public function toDataTableArray(Entity $entity, Url $urlPlugin, $type, DateTime $now = null)
    {
        if (!($now instanceof DateTime)) {
            $now = new DateTime();
        }

        $dataTableArray = $entity->toArray();

        unset(
            $dataTableArray['credentials'],
            $dataTableArray['organisationUnitId'],
            $dataTableArray['expiryDate']
        );

        $dataTableArray['enabled'] = $entity->getActive() && !$entity->getDeleted();
        $dataTableArray['status'] = $entity->getStatus($now);
        $dataTableArray['organisationUnit'] = $this->getOrganisationUnitCompanyName($entity->getOrganisationUnitId());
        $dataTableArray['manageLinks'] = $this->getManageLinks($entity->getId(), $type, $urlPlugin);
        $dataTableArray['channelImgUrl'] = $entity->getImageUrl();

        $dataTableArray['expiryDate'] = 'N/A';
        $expiryDate = $entity->getExpiryDateAsDateTime();
        if ($expiryDate instanceof DateTime) {
            $timeToExpiry = $expiryDate->getTimestamp() - $now->getTimestamp();
            $dataTableArray['expiryDate'] = ($timeToExpiry > 0) ? $expiryDate->format('jS F Y') : 'Expired';
        }

        return $dataTableArray;
    }

    protected function getOrganisationUnitCompanyName($ouId)
    {
        try {
            $ou = $this->getOuStorage()->fetch($ouId);
            return $ou->getAddressCompanyName();
        } catch (NotFound $exception) {
            return '';
        }
    }

    protected function getManageLinks($id, $type, Url $urlPlugin)
    {
        $links = [
            'manage' => ChannelController::ROUTE_CHANNELS . '/' . ChannelController::ROUTE_ACCOUNT,
            'delete' => ChannelController::ROUTE_CHANNELS . '/' . ChannelController::ROUTE_ACCOUNT . '/' . ChannelController::ROUTE_ACCOUNT_DELETE
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
}