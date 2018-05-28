define([], function() {
    "use strict";
    var stateFilters = {
        filterFields: function(tableIdToFilterFor, variationsTable) {
            var fieldsToAllow = variationsTable.tablesFields.filter(function(tableField) {
                if (tableField.tableId == tableIdToFilterFor) {
                    return tableField.fieldId;
                }
            });
            var fieldIdsToAllow = [];
            for (var i = 0; i < fieldsToAllow.length; i++) {
                fieldIdsToAllow.push(fieldsToAllow[i].fieldId)
            }
            var filteredFields = variationsTable.fields.filter(function(field) {
                return fieldIdsToAllow.indexOf(field.id) > -1;
            });
            var newState = Object.assign({}, variationsTable, {
                fields: filteredFields
            });
            return newState;
        },
        getDimensionOnlyFieldsFromVariationRow: function(variationRow, fields) {
            var variationToReturn = {};
            for (var variationField in variationRow) {
                for (var i = 0; i < fields.length; i++) {
                    if ((fields[i].name == variationField) && fields[i].isDimensionsField) {
                            variationToReturn[variationField] = variationRow[variationField];
                    }
                }
            }
            return variationToReturn;
        },
        getCell(cells, variationId, fieldId) {
            for (var i = 0; i < cells.length; i++) {
                if ((cells[i].variationId == variationId) && (cells[i].fieldId == fieldId)) {
                    return cells[i];
                }
            }
            return null;
        },
        findFieldByName(fieldName, fields) {
            var indexOfField = fields.map(function(field) {
                return field.name;
            }).indexOf(fieldName);
            return fields[indexOfField];
        },
        getCustomFields(fields){
            return fields.filter(function(field){
                return field.isCustomAttribute;
            })
        },
        onlyOneCustomFieldExists(fields){
            return stateFilters.getCustomFields(fields).length == 1
        }
    };

    return stateFilters;
});