<?php
namespace CG\CourierAdapter\Provider\Implementation;

use InvalidArgumentException;
use Zend\Form\Element as ZendFormElement;
use Zend\Form\Fieldset as ZendFormFieldset;
use Zend\Form\Form as ZendForm;

trait PrepareAdapterImplementationFieldsTrait
{
    protected function convertAdapterImplementationFieldsToForm(array $fields, array $values = [])
    {
        $form = new ZendForm();

        $this->prepareAdapterImplementationFields($fields, $values);
        foreach ($fields as $field) {
            $form->add($field);
        }

        return $form;
    }

    protected function prepareAdapterImplementationFields(array $fields, array $values = [])
    {
        foreach ($fields as $field) {
            if (!$field instanceof ZendFormElement) {
                throw new InvalidArgumentException('Form elements must be instances of ' . ZendFormElement::class);
            }
            if ($field instanceof ZendFormFieldset) {
                $this->prepareAdapterImplementationFields($field->getElements(), $values);
                continue;
            }
            if ($field->getOption('required')) {
                $class = $field->getAttribute('class') ?: '';
                $field->setAttribute('class', $class . ' required');
            }
            if (isset($values[$field->getName()])) {
                $value = $values[$field->getName()];
                if ($field->getAttribute('type') == 'checkbox' && is_string($value) && $value !== '') {
                    $value = true;
                }
                $field->setValue($value);
            }
        }
    }
}