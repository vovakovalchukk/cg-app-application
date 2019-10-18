import React, {useState} from "react";

const TemplatesHooks = {
    useTemplateState: function(initialTemplate) {
        const [template, setTemplate] = useState(initialTemplate);

        const INPUT_FIELD = 'cgField';
        const SELECT_FIELD = 'fileField';

        const blankRow = {
            [INPUT_FIELD]: "",
            [SELECT_FIELD]: ""
        };

        function getColumnMap() {
            return [...template.columnMap]
        }

        function getBlankRow() {
            return {...blankRow};
        }

        function applyIdToTemplate(id) {
            const newTemplate = {
                ...template,
                id
            };
            setTemplate(newTemplate);
            return newTemplate;
        }

        function addFieldRow() {
            let newColumnMap = getColumnMap();
            newColumnMap.push(blankRow);
            const newTemplate = {
                ...template,
                columnMap: newColumnMap
            };
            setTemplate(newTemplate);
        }

        function deleteFieldRow(rowIndex, availableOptionsLength) {
            const columnMap = getColumnMap();
            const shouldAddBlankRow = availableOptionsLength === 0;
            if (shouldAddBlankRow) {
                columnMap.push(getBlankRow());
            }
            columnMap.splice(rowIndex, 1);
            const newTemplate = {
                ...template,
                columnMap
            };
            setTemplate(newTemplate);
            return newTemplate;
        }

        function changeCgField(fieldIndex, desiredValue) {
            let newColumnMap = getColumnMap();
            newColumnMap[fieldIndex][INPUT_FIELD] = desiredValue;
            setTemplate({
                ...template,
                columnMap: newColumnMap
            });
        }

        function changeFileField(fieldIndex, desiredValue) {
            let newColumnMap = getColumnMap();
            newColumnMap[fieldIndex][SELECT_FIELD] = desiredValue;
            setTemplate({
                ...template,
                columnMap: newColumnMap
            })
        }

        return {
            template,
            setTemplate,
            getBlankRow,
            getColumnMap,
            applyIdToTemplate,
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

        return {
            templates,
            setTemplates
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

        function updateCgOptionsFromSelections(template) {
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

export default TemplatesHooks;