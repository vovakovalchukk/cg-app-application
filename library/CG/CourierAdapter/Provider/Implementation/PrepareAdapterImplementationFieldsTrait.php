<?php
namespace CG\CourierAdapter\Provider\Implementation;

use InvalidArgumentException;
use Zend\Form\Element as ZendFormElement;
use Zend\Form\Element\Checkbox as ZendFormCheckbox;
use Zend\Form\Fieldset as ZendFormFieldset;
use Zend\Form\Form as ZendForm;

trait PrepareAdapterImplementationFieldsTrait
{
    protected function prepareAdapterImplementationFormForDisplay(ZendForm $form, array $values = [])
    {
        $fieldsOrSets = array_merge($form->getFieldsets(), $form->getElements());
        $this->prepareAdapterImplementationFieldsForDisplay($fieldsOrSets, $values);

        if (!empty($values)) {
            $form->setData($values);
        }

        $form->prepare();
        // ZendFrom will remove any password values on prepare()
        $this->reAddPasswordFieldValues($fieldsOrSets, $values);
    }

    protected function prepareAdapterImplementationFieldsForDisplay(array $fields, array $values = [])
    {
        foreach ($fields as $field) {
            if (!$field instanceof ZendFormElement) {
                throw new InvalidArgumentException('Form elements must be instances of ' . ZendFormElement::class);
            }
            if ($field instanceof ZendFormFieldset) {
                $this->prepareAdapterImplementationFieldsForDisplay($field->getElements(), $values);
                continue;
            }
            if ($field->getOption('required') || $field->getAttribute('required')) {
                $class = $field->getAttribute('class') ?: '';
                $field->setAttribute('class', $class . ' required');
            }
        }
    }

    protected function reAddPasswordFieldValues(array $fields, $values = [])
    {
        if (empty($values)) {
            return;
        }
        foreach ($fields as $field) {
            if (!$field instanceof ZendFormElement) {
                throw new InvalidArgumentException('Form elements must be instances of ' . ZendFormElement::class);
            }
            if ($field instanceof ZendFormFieldset) {
                $this->reAddPasswordFieldValues($field->getElements(), $values);
                continue;
            }
            if ($field->getAttribute('type') == 'password' && isset($values[$field->getName()])) {
                $field->setValue($values[$field->getName()]);
            }
        }
    }

    protected function prepareAdapterImplementationFormForSubmission(ZendForm $form, array $values)
    {
        $preparedValues = $this->prepareAdapterImplementationFormValuesForSubmission($form, $values);
        $form->setData($preparedValues);
    }

    protected function prepareAdapterImplementationFormValuesForSubmission(ZendFormFieldset $fieldset, array $values)
    {
        // ZF2 replaces spaces with undercores :(
        $normalisedValues = [];
        foreach ($values as $key => $value) {
            $normalisedValues[str_replace('_', ' ', $key)] = $value;
        }
        $fieldsOrSets = array_merge($fieldset->getFieldsets(), $fieldset->getElements());
        foreach ($fieldsOrSets as $fieldsOrSet) {
            if ($fieldsOrSet instanceof ZendFormFieldset) {
                $subSetValues = (isset($normalisedValues[$fieldsOrSet->getName()]) ? $normalisedValues[$fieldsOrSet->getName()] : []);
                $normalisedValues[$fieldsOrSet->getName()] = $this->prepareAdapterImplementationFormValuesForSubmission($fieldsOrSet, $subSetValues);
                continue;
            }
            if (isset($normalisedValues[$fieldsOrSet->getName()])) {
                continue;
            }
            if ($fieldsOrSet instanceof ZendFormCheckbox) {
                $normalisedValues[$fieldsOrSet->getName()] = $fieldsOrSet->getUncheckedValue();
            } else {
                $normalisedValues[$fieldsOrSet->getName()] = null;
            }
        }
        return $normalisedValues;
    }
}