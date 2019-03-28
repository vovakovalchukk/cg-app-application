<?php
namespace CG\Intersoft\Credentials;

use Zend\Form\Element;
use Zend\Form\Element\Checkbox;
use Zend\Form\Element\Password;
use Zend\Form\Form;

class FormFactory
{
    public function __invoke()
    {
        $form = new Form();
        $form->add((new Element("applicationId", [
            "label" => "Application ID"
        ]))->setAttribute('required', true));
        $form->add((new Element("userId", [
            "label" => "User ID"
        ]))->setAttribute('required', true));
        $form->add((new Element("password", [
            "label" => "Password"
        ]))->setAttribute('required', true));
        return $form;
    }
}