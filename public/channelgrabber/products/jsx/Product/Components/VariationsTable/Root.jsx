define([
    'redux',
    'react-redux',
    'redux-form',
    'Product/Components/CreateProduct/functions/stateFilters',
    './Component',
    './ActionCreators'
], function(
    Redux,
    ReactRedux,
    ReduxForm,
    stateFilters,
    Component,
    ActionCreators
) {
    "use strict";
    const mapStateToProps = function(state) {
        return {
            variationsTable: stateFilters.filterFields(1, state.variationsTable),
            uploadedImages: state.uploadedImages,
            stockModeOptions: state.account.stockModeOptions
        }
    };

    const mapDispatchToProps = function(dispatch) {
        return Redux.bindActionCreators(ActionCreators, dispatch);
    };

    var Connector = ReactRedux.connect(mapStateToProps, mapDispatchToProps);
    return Connector(Component);
//
//    function filterFields(variationsTable, tablesFields){
//        console.log('in removeDImensionsFields with variationsFields: ', variationsTable , ' and tablesFIelds: ', tablesFields);
//        var fieldsToAllow = variationsTable.tablesFields.filter(function(tableField){
//            if(tableField.tableId == 1) {
//                return tableField.fieldId;
//            }
//        });
//        var fieldIdsToAllow =[];
//        for(var i=0;i<fieldsToAllow.length;i++){
//            fieldIdsToAllow.push(fieldsToAllow[i].fieldId)
//        }
//        console.log('fieldIdsToAllowIds ', fieldIdsToAllow);
//
//        var filteredFields= variationsTable.fields.filter(function(field){
//            console.log('in filter and field.id =', field.id , ' and fieldIdsToAllow: ' , fieldIdsToAllow)
//            return fieldIdsToAllow.indexOf(field.id) > -1;
//        });
//        console.log('filteredFIelds: ' , filteredFields)
//        var newState = Object.assign({},variationsTable,{
//            fields : filteredFields
//        });
//        return newState;
//    }

});