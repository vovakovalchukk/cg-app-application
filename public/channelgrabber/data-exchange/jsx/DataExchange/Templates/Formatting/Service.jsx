const FormattingService = {
    formatTemplateForSave: function(template, templateName) {
        let {columnMap} = template;

        const formattedColumnMap = removeInvalidRows(columnMap);

        const formattedTemplate = {
            columnMap: formattedColumnMap,
            name: templateName,
            type: 'stock'
        };

        if (template.id) {
            formattedTemplate.id = template.id;
        }

        if (template.organisationUnitId) {
            delete template.organisationUnitId;
        }

        if (template.value) {
            delete template.value;
        }

        return formattedTemplate;
    },
    formatTemplates: function(templates, cgFieldOptionsLength) {
        return templates.map((template) => {
            if (template.columnMap.length === cgFieldOptionsLength || blankRowExistsAlreadyInTemplate(template)) {
                return template;
            }
            let newColumnMap = addBlankRowToColumnMap(
                [...template.columnMap],
                FormattingService.getDefaultColumn()
            );
            return {
                ...template,
                columnMap: newColumnMap
            };
        })
    },
    formatCgFieldOptions: function(cgFieldOptions) {
        let options = [];
        for (let key in cgFieldOptions) {
            options.push({
                title: key,
                name: key,
                value: cgFieldOptions[key]
            });
        }
        return options;
    },
    getDefaultColumn: function() {
        return {
            cgField: '',
            fileField: ''
        };
    },
    getDefaultTemplate: function(templateType) {
        return {
            id: null,
            name: '',
            type: templateType,
            columnMap: [FormattingService.getDefaultColumn()]
        }
    }
};

export default FormattingService;

function addBlankRowToColumnMap(templateColumnMap, defaultColumn) {
    templateColumnMap.push({...defaultColumn});
    return templateColumnMap;
}

function blankRowExistsAlreadyInTemplate(template) {
    let columns = template.columnMap;
    if (!columns.length) {
        return false;
    }
    let lastColumn = columns[columns.length - 1];
    return isBlankColumn(lastColumn);
}

function isBlankColumn(column) {
    return !column.fileField && !column.cgField;
}

function removeInvalidRows(columnMap) {
    let newColumnMap = columnMap.filter((column) => {
        return column.cgField && column.fileField;
    });
    return newColumnMap;
}