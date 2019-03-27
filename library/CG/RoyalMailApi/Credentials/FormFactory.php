<?php
namespace CG\RoyalMailApi\Credentials;

use Zend\Form\Element;
use Zend\Form\Element\Checkbox;
use Zend\Form\Element\Password;
use Zend\Form\Form;

class FormFactory
{
    public function __invoke()
    {
        $form = new Form();
        $form->add((new Element("clientId", [
            "label" => "Client ID"
        ]))->setAttribute('required', true));
        $form->add((new Password("clientSecret", [
            "label" => "Client Secret"
        ]))->setAttribute('required', true));
        $form->add((new Element("username", [
            "label" => "API Username"
        ]))->setAttribute('required', true));
        $form->add((new Password("password", [
            "label" => "API Password"
        ]))->setAttribute('required', true));
        $form->add(new Checkbox("live", [
            "label" => "Approved for Live by RM?",
            "checked_value" => 1,
            'unchecked_value' => 0,
            'label_attributes' => [
                'title' => 'Royal Mail will first connect you to their onboarding platform for testing and approval. Once approved they will connect you to their live platform. Please indicate if they have approved you for live.'
            ]
        ]));
        return $form;
    }
}