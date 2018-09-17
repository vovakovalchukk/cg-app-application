define([
    'Common/Reducers/creator'
], function(
    reducerCreator
) {
    "use strict";
    
    var initialState = {
        columnSettings: []
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