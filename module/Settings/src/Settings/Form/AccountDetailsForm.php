<?php
namespace Settings\Form;

use CG\Account\Shared\Entity as AccountEntity;
use Zend\Form\Element\Csrf as CsrfElement;
use Zend\Form\Element\Hidden as HiddenElement;
use Zend\Form\Element\Text as TextElement;
use Zend\Form\Form;

class AccountDetailsForm extends Form
{
    protected $account;

    public function __construct(AccountEntity $account = null)
    {
        $this->setAccount($account);
        parent::__construct();
        $this->setAttribute('id', 'account_details_form');
        //$filter = $this->getInputFilter();

        $this->add(array(
            'type' => CsrfElement::class,
            'name' => 'csrf',
            'options' => array(
                'csrf_options' => array(
                    'timeout' => 43200
                )
            )
        ));

        $this->add(array(
            'type' => TextElement::class,
            'required' => 'true',
            'name' => 'displayName',
            'options' => array(
                'label' => 'Display Name'
            ),
            'attributes' => array(
                'required' => 'required',
                'value' => ($account ? $account->getDisplayName() : '')
            )
        ));

        $this->add(array(
            'type' => HiddenElement::class,
            'required' => 'true',
            'name' => 'organisationUnitId',
            'attributes' => array(
                'required' => 'required',
                'value' => ($account ? $account->getOrganisationUnitId() : '')
            )
        ));

        $this->add(array(
            'name' => 'save',
            'options'=>array('label' => 'Save'),
            'type'  => 'Submit',
            'attributes' => array(
                'value' => 'Submit',
                'class' => 'button'
            ),
        ));
    }

    public function getAccount()
    {
        return $this->account;
    }

    public function setAccount(AccountEntity $account)
    {
        $this->account = $account;
        return $this;
    }
}
