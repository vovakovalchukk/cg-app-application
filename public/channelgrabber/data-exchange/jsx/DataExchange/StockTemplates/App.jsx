import React, {useState, useEffect} from 'react';
import styled from 'styled-components';

import Input from 'Common/Components/Input';
import FieldWithLabel from 'Common/Components/FieldWithLabel';

// todo - move these over to cg-common
import AddTemplate from 'ListingTemplates/Components/AddTemplate';
import TemplateSelect from 'ListingTemplates/Components/TemplateSelect';
import FieldMapper from 'DataExchange/StockTemplates/Components/FieldMapper';

const InitialFormSection = styled.section`
  padding-top: 1rem;
  max-width: 700px
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

let maxNumberOfCgOptions = null;

const App = props => {
    const formattedTemplates = formatTemplates(props.templates);

    let {templates, setTemplates, deleteTemplateInState} = useTemplatesState(formattedTemplates);

    const templateName = useFormInputState('');
    const newTemplateName = useFormInputState('');

    const [templateInitialised, setTemplateInitialised] = useState(false);
    const [templateSelectValue, setTemplateSelectValue] = useState({});

    const templateState = useTemplateState(defaultTemplate);

    const formattedCgFieldOptions = formatCgFieldOptions(props.cgFieldOptions);

    const [cgFieldOptions, setCgFieldOptions] = useState(formattedCgFieldOptions);

    useEffect(() => {
        maxNumberOfCgOptions = cgFieldOptions.length;
    }, []);

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

                <FieldMapper
                    template = {templateState.template}
                    addFieldRow = {templateState.addFieldRow}
                    cgFieldOptions={cgFieldOptions}
                    removeFieldRow = {(rowIndex) => {
                        templateState.deleteFieldRow(rowIndex);
                    }}
                    //todo - extract method
                    changeFileField = {(rowIndex, desiredValue) => {
                        changeField(rowIndex, desiredValue, 'fileField')
                    }}
                    changeCgField = {(rowIndex, desiredValue) => {
                        changeField(rowIndex, desiredValue, 'cgField')
                    }}
                />
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

function useTemplateState(initialTemplate) {
    const [template, setTemplate] = useState(initialTemplate);

    const INPUT_FIELD = 'cgField';
    const SELECT_FIELD = 'fileField';

    const blankRow = {
        [INPUT_FIELD]: "",
        [SELECT_FIELD]: ""
    };

    let columnMap = [...template.columnMap];

    function addFieldRow() {
        let newColumnMap = [...columnMap];
        newColumnMap.push(blankRow);
        setTemplate({
            ...template,
            columnMap: newColumnMap
        });
    }

    function deleteFieldRow(index) {
        let newColumnMap = columnMap.slice();
        newColumnMap.splice(index, 1);
        setTemplate({
            ...template,
            columnMap: newColumnMap
        });
    }

    function changeCgField(fieldIndex, desiredValue) {
        // todo - need to to find out whether the template at this point is referencing templates
        let newColumnMap = [...columnMap];
        newColumnMap[fieldIndex][INPUT_FIELD] = desiredValue;
        setTemplate({
            ...template,
            columnMap: newColumnMap
        });
    }

    function changeFileField(fieldIndex, desiredValue) {
        let newColumnMap = columnMap.slice();
        newColumnMap[fieldIndex][SELECT_FIELD] = desiredValue;
        setTemplate({
            ...template,
            columnMap: newColumnMap
        })
    }

    return {
        template,
        setTemplate,
        addFieldRow,
        deleteFieldRow,
        changeCgField,
        changeFileField
    };
}

function useTemplatesState(initialTemplates) {
    initialTemplates = Array.isArray(initialTemplates) ? initialTemplates : [];
    const formattedTemplates = initialTemplates.map(template => {
        return {
            ...template,
            value: template.name
        };
    });
    const [templates, setTemplates] = useState(formattedTemplates);

    function deleteTemplateInState(template) {
        if (!template) {
            return;
        }
        let newTemplates = templates.slice();
        let templateIndex = newTemplates.findIndex(temp => temp === template);
        newTemplates.splice(templateIndex, 1);
        setTemplates(newTemplates);
    }

    return {
        templates,
        setTemplates,
        deleteTemplateInState
    };
}

function useFormInputState(initialValue) {
    const [value, setValue] = useState(initialValue);
    function onChange(e) {
        setValue(e.target.value);
    }
    return {
        value,
        onChange,
        setValue
    }
}

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
    let isNotAtMaxRows = template.columnMap.length !== maxNumberOfCgOptions;
    return isNotLastRow && isBlankRow && isNotAtMaxRows;
}