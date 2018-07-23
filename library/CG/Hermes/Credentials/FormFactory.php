<?php
namespace CG\Hermes\Credentials;

use Zend\Form\Element;
use Zend\Form\Element\Checkbox;
use Zend\Form\Element\Password;
use Zend\Form\Form;

class FormFactory
{
    public function __invoke()
    {
        $form = new Form();
        $form->add((new Element("clientName", [
            "label" => "Client Name"
        ]))->setAttribute('required', true));
        $form->add((new Element("clientId", [
            "label" => "Client ID"
        ]))->setAttribute('required', true));
        $form->add((new Element("username", [
            "label" => "Username"
        ]))->setAttribute('required', true));
        $form->add((new Password("password", [
            "label" => "Password"
        ]))->setAttribute('required', true));
//        $form->add(new Checkbox("live", [
//            "label" => "Are these live credentials?",
//            "checked_value" => 1,
//            'unchecked_value' => 0
//        ]));
        return $form;
    }
}