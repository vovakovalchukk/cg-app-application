define([], function() {
    "use strict";
    var functions = {

        filterFields: function(tableIdToFilterFor, variationsTable) {
            console.log('in filterFields with tableIdToFilterFor: ',tableIdToFilterFor);
            var fieldsToAllow = variationsTable.tablesFields.filter(function(tableField) {
                if (tableField.tableId == tableIdToFilterFor) {
                    return tableField.fieldId;
                }
            });
            var fieldIdsToAllow = [];
            for (var i = 0; i < fieldsToAllow.length; i++) {
                fieldIdsToAllow.push(fieldsToAllow[i].fieldId)
            }
            console.log('fieldIdsToAllowIds ', fieldIdsToAllow);
            var filteredFields = variationsTable.fields.filter(function(field) {
                console.log('in filter and field.id =', field.id, ' and fieldIdsToAllow: ', fieldIdsToAllow)
                return fieldIdsToAllow.indexOf(field.id) > -1;
            });
            console.log('filteredFIelds: ', filteredFields)
            var newState = Object.assign({}, variationsTable, {
                fields: filteredFields
            });
            return newState;
        }


    };
    return functions;

});