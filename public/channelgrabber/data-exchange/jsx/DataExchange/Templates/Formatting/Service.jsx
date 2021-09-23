const FormattingService = {
    formatTemplateForSave: function(template, templateName) {
        let {columnMap, type} = template;

        const formattedColumnMap = formatColumnMap(columnMap, type);

        const formattedTemplate = {
            columnMap: formattedColumnMap,
            name: templateName || template.name,
            type
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
            const columnMap = [...template.columnMap];
            const newColumnMap = addBlankRowToColumnMap(
                columnMap,
                FormattingService.getDefaultColumn(columnMap.length)
            );
            return {
                ...template,
                columnMap: newColumnMap
            };
        });
    },
    formatCgFieldOptions: function(cgFieldOptions) {
        let options = [];
        for (let key in cgFieldOptions) {
            let title = key;
            let name = key;
            let value = cgFieldOptions[key];
            if (typeof cgFieldOptions[key] === 'object' && cgFieldOptions[key] !== null) {
                name = cgFieldOptions[key].displayName;
                value = cgFieldOptions[key].field;
            }

            options.push({
                title: title,
                name: name,
                value: value
            });
        }
        return options;
    },
    getDefaultColumn: function(order) {
        return {
            cgField: '',
            fileField: '',
            userValue: null,
            order: order
        };
    },
    getDefaultTemplate: function(templateType) {
        return {
            id: null,
            name: '',
            type: templateType,
            columnMap: [FormattingService.getDefaultColumn(0)]
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

function formatColumnMap(columnMap, type) {
    const validColumnMap = columnMap.filter((column) => {
        if (type == 'order') {
            return !!(column.fileField);
        }
        return !!(column.fileField) && !!(column.cgField);
    });

    return validColumnMap.map((column, index) => {
        column.order = index;
        return column;
    });
}
