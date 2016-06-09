<?php
namespace Settings\Channel;

use CG\Account\DataTableMapper;
use CG\Account\Shared\Entity;
use CG\OrganisationUnit\StorageInterface as OUStorage;
use CG\Stdlib\Exception\Runtime\NotFound;
use Settings\Controller\ChannelController;
use Settings\Module;
use Zend\Mvc\Controller\Plugin\Url;
use DateTime;
use Exception;

class Mapper extends DataTableMapper
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

    public function toDataTableArray(Entity $accountEntity, Url $urlPlugin = null, $type = null, DateTime $now = null)
    {
        if (!($now instanceof DateTime)) {
            $now = new DateTime();
        }

        $dataTableArray = parent::toDataTableArray($accountEntity);
        $dataTableArray['manageLinks'] = $this->getManageLinks($accountEntity->getId(), $type, $urlPlugin);
        $dataTableArray['organisationUnit'] = $this->getOrganisationUnitCompanyName($accountEntity->getOrganisationUnitId());
        $dataTableArray['status'] = $accountEntity->getStatus($now);
        $dataTableArray['disabled'] = $accountEntity->getPending();

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

    public function setOuStorage(OUStorage $ouStorage)
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
}
