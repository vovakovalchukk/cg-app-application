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

    public function toDataTableArray(Entity $entity, Url $urlPlugin, DateTime $now = null)
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
        $dataTableArray['manageLinks'] = $this->getManageLinks($entity->getId(), $urlPlugin);

        $expiryDate = $entity->getExpiryDateAsDateTime();
        if ($expiryDate instanceof DateTime) {
            $dataTableArray['expiryDate'] = $expiryDate->getTimestamp() - $now->getTimestamp();
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

    protected function getManageLinks($id, Url $urlPlugin)
    {
        $links = [
            ChannelController::ACCOUNT_ROUTE,
            ChannelController::ACCOUNT_ROUTE . '/' . ChannelController::ACCOUNT_DELETE_ROUTE
        ];

        $manageLinks = [];
        foreach ($links as $link) {
            $route = Module::ROUTE . '/' . ChannelController::ROUTE . '/' . $link;
            $routeMap = explode('/', $route);
            $manageLinks[] = [
                'name' => end($routeMap),
                'href' => $urlPlugin->fromRoute($route, ['account' => $id])
            ];
        }

        return $manageLinks;
    }
}