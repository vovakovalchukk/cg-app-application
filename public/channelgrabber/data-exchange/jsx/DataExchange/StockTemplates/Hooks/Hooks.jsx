import React, {useState} from "react";

const stockTemplatesHooks = {
    useTemplateState: function(initialTemplate) {
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
    },

    useTemplatesState: function(initialTemplates) {
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
    },

    useFormInputState: function(initialValue) {
        const [value, setValue] = useState(initialValue);
        function onChange(e) {
            setValue(e.target.value);
        }
        return {
            value,
            onChange,
            setValue
        }
    },

    useCgOptionsState: function(cgOptions) {
        const [cgFieldOptions, setCgFieldOptions] = useState(cgOptions);

        const availableCgFieldOptions = cgFieldOptions.filter((option) => {
            return option.available || typeof option.available === 'undefined'
        });

        function updateCgOptionsFromSelections(template, initialCgOptions) {
            let selectedCgFields = [];
            template.columnMap.forEach((column) => {
                selectedCgFields.push(column.cgField);
            });
            let fieldsWithAvailableSet = [...cgFieldOptions].map((option) => {
                option.available = !selectedCgFields.includes(option.value);
                return option;
            });
            setCgFieldOptions(fieldsWithAvailableSet);
        }

        return {
            cgFieldOptions,
            availableCgFieldOptions,
            setCgFieldOptions,
            updateCgOptionsFromSelections
        }
    }
};

export default stockTemplatesHooks;

