import React, {useState, useEffect} from 'react';
import styled from 'styled-components';

import Input from 'Common/Components/Input';
import FieldWithLabel from 'Common/Components/FieldWithLabel';

import AddTemplate from 'ListingTemplates/Components/AddTemplate';
import TemplateSelect from 'ListingTemplates/Components/TemplateSelect';
import FieldMapper from 'DataExchange/StockTemplates/Components/FieldMapper';

import Hooks from 'DataExchange/StockTemplates/Hooks/Hooks';
const {useTemplatesState, useTemplateState, useFormInputState, useCgOptionsState} = Hooks;

const containerWidth = 600;

const InitialFormSection = styled.section`
  padding-top: 1rem;
  max-width: ${containerWidth}px;
`;

const defaultColumn = {
    cgField: '',
    fileField: ''
};
const defaultTemplate = {
    id: null,
    name: '',
    type: 'stock',
    columnMap: [defaultColumn]
};

let initialCgOptions = null;

const App = props => {
    const formattedTemplates = formatTemplates(props.templates);
    let {templates, setTemplates, deleteTemplateInState} = useTemplatesState(formattedTemplates);

    const templateName = useFormInputState('');
    const newTemplateName = useFormInputState('');

    const [templateInitialised, setTemplateInitialised] = useState(false);
    const [templateSelectValue, setTemplateSelectValue] = useState({});

    const templateState = useTemplateState(defaultTemplate);

    const formattedCgFieldOptions = formatCgFieldOptions(props.cgFieldOptions);

    const {cgFieldOptions, availableCgFieldOptions, setCgFieldOptions, updateCgOptionsFromSelections} = useCgOptionsState(formattedCgFieldOptions);

    if(!initialCgOptions) {
        initialCgOptions = cgFieldOptions;
    }

    return (
        <div>
            <InitialFormSection>
                <div className={'u-defloat'}>
                    <TemplateSelect options={templates}
                                    selectedOption={templateSelectValue}
                                    onOptionChange={(chosenTemplate) => {
                                        setTemplateSelectValue(chosenTemplate);
                                        setTemplateInitialised(true);
                                        templateName.setValue(chosenTemplate.name);
                                        // todo - set field mapper component
                                        // templateHTML.setValue(option.template);
//                                      //todo - remove if not useful
                                        let templateToSet = deepCopyObject(chosenTemplate);

                                        templateState.setTemplate(templateToSet);
//                                        templateState.setTemplate({...defaultTemplate});
                                    }}
                                    deleteTemplate={deleteTemplateHandler}
                    />

                    <AddTemplate newTemplateName={newTemplateName} onAddClick={() => {
                        setTemplateInitialised(true);
                        templateName.setValue(newTemplateName.value);
                        templateState.setTemplate(defaultTemplate)
                    }}
                    />

                    {templateInitialised &&
                        <FieldWithLabel label={'Template Name'} className={'u-margin-top-small'}>
                            <Input
                                {...templateName}
                                inputClassNames={'inputbox u-border-box'}
                            />
                        </FieldWithLabel>
                    }
                </div>

                {templateInitialised && <FieldMapper
                    template = {templateState.template}
                    addFieldRow = {templateState.addFieldRow}
                    availableCgFieldOptions={availableCgFieldOptions}
                    allCgFieldOptions={cgFieldOptions}
                    removeFieldRow = {(rowIndex) => {
                        let template = templateState.deleteFieldRow(rowIndex, availableCgFieldOptions.length);
                        updateCgOptionsFromSelections(template, initialCgOptions);
                    }}
                    changeFileField = {(rowIndex, desiredValue) => {
                        changeField(rowIndex, desiredValue, 'fileField');
                        updateCgOptionsFromSelections(templateState.template, initialCgOptions)
                    }}
                    changeCgField = {(rowIndex, desiredValue) => {
                        changeField(rowIndex, desiredValue, 'cgField');
                        updateCgOptionsFromSelections(templateState.template, initialCgOptions)
                    }}
                    containerWidth={containerWidth}
                />}
            </InitialFormSection>
        </div>
    );

    function changeField(rowIndex, desiredValue, propertyName) {
        let column = templateState.template.columnMap[rowIndex];
        let isPreviouslyBlankRow = !column.cgField && !column.fileField;

        if (propertyName === 'cgField') {
            templateState.changeCgField(rowIndex, desiredValue);
        } else {
            templateState.changeFileField(rowIndex, desiredValue);
        }

        if (!shouldAddNewRow(rowIndex, templateState.template, isPreviouslyBlankRow)) {
            return;
        }

        templateState.addFieldRow();
    }

    async function deleteTemplateHandler() {
        if (!templateSelectValue) {
            return;
        }
        let response = await $.ajax({
            url: '/dataExchange/stock/templates/remove',
            type: 'POST',
            dataType: 'json',
            data: {id: templateSelectValue.id}
        });

        if (response.success) {
            n.success(response.success.message);
            deleteTemplateInState(templateSelectValue);
            templateName.setValue('');
            templateHTML.setValue('');
            return;
        }

        if (!response.error || !response.error.message) {
            return;
        }
        n.error(response.error.message);
    }
};

export default App;


function formatCgFieldOptions(cgFieldOptions){
    let options = [];
    for (let key in cgFieldOptions) {
        options.push({
            title: key,
            name: key,

            value: cgFieldOptions[key]
        });
    }
    return options;
}

function addBlankRowToColumnMap(templateColumnMap) {
    templateColumnMap.push({...defaultColumn});
    return templateColumnMap;
}

function formatTemplates(templates, cgFieldOptionsLength) {
    return templates.map((template) => {
        if (template.columnMap.length === cgFieldOptionsLength) {
            return template;
        }
        let newColumnMap = addBlankRowToColumnMap([...template.columnMap]);
        return {
            ...template,
            columnMap: newColumnMap
        };
    })
}

function deepCopyObject(object) {
    let newObject = JSON.stringify(object, null, 1);
    newObject = JSON.parse(newObject);
    return newObject;
}

function shouldAddNewRow(rowIndex, template, isBlankRow) {
    let isNotLastRow = rowIndex === template.columnMap.length - 1;
    let isNotAtMaxRows = template.columnMap.length !== initialCgOptions.length;
    return isNotLastRow && isBlankRow && isNotAtMaxRows;
}