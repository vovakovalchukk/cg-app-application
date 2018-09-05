define([
    'Common/Reducers/creator',
    'Product/Components/ProductList/ColumnCreator/columns'
], function(
    reducerCreator,
    columns
) {
    "use strict";
    
    var initialState = {
        columnSettings:[]
    };
    
    var ColumnsReducer = reducerCreator(initialState, {
        "COLUMNS_GENERATE_SETTINGS": function(state, action) {
            let newState = Object.assign({}, state, {
                columnSettings: action.payload.columnSettings
            });
            return newState;
        }
    });
    
    return ColumnsReducer
});