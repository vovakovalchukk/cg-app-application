<?php
namespace CG\UkMail\Credentials;

use Zend\Form\Element;
use Zend\Form\Element\Password;
use Zend\Form\Fieldset;
use Zend\Form\Form;

class FormFactory
{
    public function __invoke(): Form
    {
        $form = new Form();
        $form->add((new Element("username", [
            "label" => "Username"
        ]))->setAttribute('required', true));

        $form->add((new Password("password", [
            "label" => "Password"
        ]))->setAttribute('required', true));

        $form->add((new Element("accountNumber", [
            "label" => "Account Number"
        ]))->setAttribute('required', true));

        $form->add((new Element("apiKey", [
            "label" => "API Key"
        ]))->setAttribute('required', true));

        $form->add(new Element\Checkbox("live", [
            "label" => "Are these live credentials?",
            "checked_value" => 1,
            'unchecked_value' => 0
        ]));

        return $form;
    }
}