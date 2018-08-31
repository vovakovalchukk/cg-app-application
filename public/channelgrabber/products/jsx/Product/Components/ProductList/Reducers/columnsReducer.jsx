define([
    'Common/Reducers/creator',
    'Product/Components/ProductList/ColumnCreator/columns'
], function(
    reducerCreator,
    columns
) {
    "use strict";
    
    var initialState = columns;
    
    var ColumnsReducer = reducerCreator(initialState, {});
    
    return ColumnsReducer
});