<?php
namespace Settings\Channel;

use Zend\Form\Form;

class Service
{
    /**
     * @return Form
     */
    public function getNewChannelForm()
    {
        return new Form();
    }
}