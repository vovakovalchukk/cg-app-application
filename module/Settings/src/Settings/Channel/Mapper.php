<?php
namespace Settings\Channel;

use CG\Account\Shared\Entity;
use CG\OrganisationUnit\StorageInterface as OUStorage;
use CG\Stdlib\Exception\Runtime\NotFound;
use Settings\Controller\ChannelController;
use Settings\Module;
use Zend\Mvc\Controller\Plugin\Url;
use DateTime;

class Mapper
{
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

    public function toDataTableArray(Entity $entity, Url $urlPlugin)
    {
        $dataTableArray = $entity->toArray();

        unset(
            $dataTableArray['credentials'],
            $dataTableArray['organisationUnitId']
        );

        $dataTableArray['organisationUnit'] = $this->getOrganisationUnitCompanyName($entity->getOrganisationUnitId());
        $dataTableArray['manageLinks'] = $this->getManageLinks($entity->getId(), $urlPlugin);

        $dataTableArray['expiryDate'] = $this->parseExpiryDate($entity->getExpiryDate());
        if ($dataTableArray['expiryDate']) {
            $dataTableArray['expiryDate'] -= time();
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
            'manage' => ChannelController::CHANNEL_ROUTE,
            'delete' => ChannelController::CHANNEL_ROUTE . '/' . ChannelController::CHANNEL_DELETE_ROUTE
        ];

        $manageLinks = [];
        foreach ($links as $class => $link) {
            $route = Module::ROUTE . '/' . ChannelController::LIST_ROUTE . '/' . $link;
            $routeMap = explode('/', $route);
            $manageLinks[] = [
                'name' => end($routeMap),
                'class' => $class,
                'href' => $urlPlugin->fromRoute($route, ['channel' => $id])
            ];
        }

        return $manageLinks;
    }

    protected function parseExpiryDate($expiryDate)
    {
        if ($expiryDate instanceof DateTime) {
            return $expiryDate->getTimestamp();
        }

        $time = strtotime($expiryDate);
        if (!$time) {
            return null;
        }
        return $time;
    }
}