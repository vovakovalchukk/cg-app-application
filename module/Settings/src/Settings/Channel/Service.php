<?php
namespace Settings\Channel;

use Zend\Form\Form;
use CG_UI\View\DataTable;

class Service
{
    protected $accountList;

    public function __construct(DataTable $accountList)
    {
        $this->setAccountList($accountList);
    }

    public function setAccountList(DataTable $accountList)
    {
        $this->accountList = $accountList;
        return $this;
    }

    /**
     * @return DataTable
     */
    public function getAccountList()
    {
        return $this->accountList;
    }

    /**
     * @return Form
     */
    public function getNewChannelForm()
    {
        return new Form();
    }
}