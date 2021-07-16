<?php
namespace CG\UkMail\Credentials;

use Zend\Form\Element;
use Zend\Form\Element\Password;
use Zend\Form\Form;
use Zend\Form\Element\Time;
use Zend\Form\Element\Checkbox;

class FormFactory
{
    public function getCredentialsForm(): Form
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

        $form->add(new Checkbox("live", [
            "label" => "Are these live credentials?",
            "checked_value" => 1,
            'unchecked_value' => 0
        ]));

        return $form;
    }

    public function getConfigForm(): Form
    {
        $form = new Form();

        $form->add(
            (new Checkbox("closedForLunch", [
                "label" => "Is the address to be collected from closed for lunch?",
                "checked_value" => 1,
                "unchecked_value" => 0
            ]))
        );

        //UK Mail accepts times between 9:00 and 17:00
        $form->add(
            (new Time("earliestTime", [
                "label" => "Earliest Time the parcels will be ready for collection",
                "format" => "H:i",
                "min" => "09:00",
                "max" => "16:30"
            ]))
                ->setAttributes([
                    'value' => '09:00',
                    'class' => 'uk-mail-ca-time-inputbox',
                    "min" => "09:00", //@todo min and max attributes are not set in html
                    "max" => "16:30",
                ])
        );

        //UK Mail accepts times between 9:00 and 17:00
        $form->add(
            (new Time("latestTime", [
                "label" => "Latest Time the parcels will be ready for collection",
                "format" => "H:i",
            ]))
                ->setAttributes([
                    'value' => '17:00',
                    'class' => 'uk-mail-ca-time-inputbox',
                    "min" => "09:30", //@todo min and max attributes are not set in html
                    "max" => "17:00",
                ])

        );

        $form->add(
            (new Element("specialInstructions", [
                "label" => "Special instructions for the driver who will be collecting the parcels"
            ]))
        );

        return $form;
    }
}