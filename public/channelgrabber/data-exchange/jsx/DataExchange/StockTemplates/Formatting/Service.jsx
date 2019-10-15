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
    }
}

export default FormattingService;

function removeInvalidRows(columnMap) {
    let newColumnMap = columnMap.filter((column) => {
        return column.cgField && column.fileField;
    });
    return newColumnMap;
}