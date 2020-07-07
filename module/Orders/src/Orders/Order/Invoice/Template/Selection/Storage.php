<?php
namespace Orders\Order\Invoice\Template\Selection;

use Orders\Order\Invoice\Template\Selection;
use Zend\Session\SessionManager;

class Storage
{
    /** @var SessionManager */
    protected $sessionManager;

    public function __construct(SessionManager $sessionManager)
    {
        $this->sessionManager = $sessionManager;
    }

    public function fetch(): Selection
    {
        $this->sessionManager->start();
        $storage = $this->sessionManager->getStorage()->toArray();
        $templateIds = $storage['orders']['template']['ids'] ?? [];
        $orderBy = $storage['orders']['template']['orderBy'] ?? null;
        return new Selection($templateIds, $orderBy);
    }
    
    public function save(Selection $selection): Selection
    {
        $this->sessionManager->start();
        $storage = $this->sessionManager->getStorage();
        if (!isset($storage['orders'])) {
            $storage['orders'] = [];
        }
        if (!isset($storage['orders']['template'])) {
            $storage['orders']['template'] = [];
        }
        $storage['orders']['template'] = [
            'ids' => $selection->getTemplateIds(),
            'orderBy' => $selection->getOrderBy(),
        ];
        return $selection;
    }
}