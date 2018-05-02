define([], function() {
    "use strict";
    var functions = {

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
                    if(fields[i].name==variationField){
                        if(fields[i].isDimensionsField){
                            variationToReturn[variationField] = variationRow[variationField];
                        }
                    }
                }
            }
            return variationToReturn;
        }



    };
    return functions;

});