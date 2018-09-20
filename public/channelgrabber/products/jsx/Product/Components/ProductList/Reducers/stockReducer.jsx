define([
    'Common/Reducers/creator',
    'Product/Components/ProductList/Config/constants'
], function(
    reducerCreator,
    constants
) {
    "use strict";
    
    let initialState = {
        stockModeOptions: [],
        stockModeEdits: [],
        prevValuesBeforeEdits: []
    };
    
    let stockModeReducer = reducerCreator(initialState, {
        "STOCK_MODE_OPTIONS_STORE": function(state, action) {
            let newState = Object.assign({}, state, {
                stockModeOptions: action.payload.stockModeOptions
            });
            return newState;
        },
        "STOCK_MODE_CHANGE": function(state, action) {
            console.log('in STOCK_MODE_CHANGE stockReducer -R state: ', {
                state,
                action
            });
            let {rowData, previousValue, propToChange} = action.payload;
            
            let newStockModeEdits = state.stockModeEdits.slice();
            if (hasEditBeenRecordedAlready(newStockModeEdits, rowData)) {
                return state;
            }
            
            newStockModeEdits.push({
                productId: rowData.id,
                status: constants.STOCK_MODE_EDITING_STATUSES.editing
            });
            
            let prevValuesBeforeEdits = state.prevValuesBeforeEdits.slice();
            prevValuesBeforeEdits.push({
                productId: rowData.id,
                [propToChange]: previousValue
            });
            
            let newState = Object.assign({}, state, {
                stockModeEdits: newStockModeEdits,
                prevValuesBeforeEdits
            });
            return newState;
        }
    });
    
    return stockModeReducer;
    
    function hasEditBeenRecordedAlready(newStockModeEdits, rowData) {
        return !!newStockModeEdits.find(edit => {
            return edit.productId === rowData.id;
        });
    }
});