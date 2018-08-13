<?php
namespace CG\Hermes\Credentials;

use Zend\Form\Element;
use Zend\Form\Element\Checkbox;
use Zend\Form\Element\Email;
use Zend\Form\Element\Password;
use Zend\Form\Fieldset;
use Zend\Form\Form;

class FormFactory
{
    public function getCredentialsForm()
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
        $form->add(new Checkbox("liveCredentials", [
            "label" => "Are these live credentials?",
            "checked_value" => 1,
            'unchecked_value' => 0,
            'label_attributes' => [
                'title' => 'Hermes will first give you test credentials then, after approval, live credentials. Please specify which these are.'
            ]
        ]));
        $form->add(new Checkbox("testPackApproved", [
            "label" => "Has your live test pack been approved by Hermes?",
            "checked_value" => 1,
            'unchecked_value' => 0,
            'label_attributes' => [
                'title' => 'Only check this after your test pack generated with your LIVE credentials has been approved by Hermes. This fully activates your Hermes account.'
            ]
        ]));
        return $form;
    }

    public function getCredentialsRequestForm()
    {
        $form = new Form();
        $form->add((new Element('Company Registration Number', [
            'label' => 'Company Registration Number'
        ]))->setAttribute('required', true));
        $companyAddress = $this->getAddressFieldset('Company Address');
        $form->add($companyAddress);
        $collectionAddress = $this->getAddressFieldset('Collection Address', 'Collection Address (if different)');
        $form->add($collectionAddress);
        $form->add((new Element('Preferred Collection Time Slot', [
            'label' => 'Preferred Collection Time Slot'
        ]))->setAttribute('required', true));
        $form->add((new Element('Average Weekly Volumes', [
            'label' => 'Average Weekly Volumes (via Hermes)'
        ]))->setAttribute('required', true));
        $form->add($this->getVolumeSplitPerWeightFieldset());
        $form->add($this->getVolumeSplitPerServiceFieldset());
        // TODO: rest of fields

/*
1) Company Registration Number and Registered Address:
2) Collection Address (If Different):
3) Preferred collection time slot:
4) Average weekly volumes (suitable for our network):
5) % Volume Split per weight band:
0-1Kg:
1-2Kg:
2-5Kg:
5-10Kg:
10-15Kg:
6) % Volume Split per Service (UK48, Next Day, International):
7) Average Parcel dimensions:
8) Parcel presentation (i.e. Pallets / Cages / Sacks):
9) Contents of the parcels you are wishing to send:
 */

        return $form;
    }

    protected function getAddressFieldset(string $name, ?string $label = null)
    {
        $address = new Fieldset($name, [
            'label' => $label ?? $name
        ]);
        $address->add((new Element('Line 1', [
            'label' => 'Line 1'
        ]))->setAttribute('required', true));
        $address->add(new Element('Line 2', [
            'label' => 'Line 2'
        ]));
        $address->add(new Element('Line 3', [
            'label' => 'Line 3'
        ]));
        $address->add((new Element('City', [
            'label' => 'City'
        ]))->setAttribute('required', true));
        $address->add(new Element('County', [
            'label' => 'County'
        ]));
        $address->add((new Element('Post code', [
            'label' => 'Post code'
        ]))->setAttribute('required', true));
        return $address;
    }

    protected function getVolumeSplitPerWeightFieldset()
    {
        $weightSplit = new Fieldset('Percentage Volume Split per Weight Band', [
            'label' => '% Volume Split per Weight Band'
        ]);
        $weightSplit->add((new Element('0-1kg', [
            'label' => '0 - 1kg'
        ]))->setAttribute('required', true));
        $weightSplit->add((new Element('1-2kg', [
            'label' => '1 - 2kg'
        ]))->setAttribute('required', true));
        $weightSplit->add((new Element('2-5kg', [
            'label' => '2 - 5kg'
        ]))->setAttribute('required', true));
        $weightSplit->add((new Element('5-10kg', [
            'label' => '5 - 10kg'
        ]))->setAttribute('required', true));
        $weightSplit->add((new Element('10-15kg', [
            'label' => '10 - 15kg'
        ]))->setAttribute('required', true));
        return $weightSplit;
    }

    protected function getVolumeSplitPerServiceFieldset()
    {
        $serviceSplit = new Fieldset('Percentage Volume Split per Service', [
            'label' => '% Volume Split per Service'
        ]);
        $serviceSplit->add((new Element('UK48', [
            'label' => 'UK 48'
        ]))->setAttribute('required', true));
        $serviceSplit->add((new Element('Next Day', [
            'label' => 'Next Day'
        ]))->setAttribute('required', true));
        $serviceSplit->add((new Element('International', [
            'label' => 'International'
        ]))->setAttribute('required', true));
    }
}