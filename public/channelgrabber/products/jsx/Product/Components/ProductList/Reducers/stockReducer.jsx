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
            // console.log('in STOCK_MODE_CHANGE stockReducer -R state: ', {
            //     state,
            //     action
            // });
            let {rowData, currentStock, propToChange} = action.payload;
            
            let newStockModeEdits = state.stockModeEdits.slice();
            if (!hasEditBeenRecordedAlready(newStockModeEdits, rowData)) {
                newStockModeEdits.push({
                    productId: rowData.id,
                    status: constants.STOCK_MODE_EDITING_STATUSES.editing
                });
            }
            
            
            let prevValuesBeforeEdits = createPrevValuesBeforeEdits(state, rowData, propToChange, currentStock);
            
            // console.log('new prevValuesBeforeEdits: ', prevValuesBeforeEdits);
            
            
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
    
    function getExistingPreviousValueObjectIndex(previousValues, rowId) {
        return previousValues.findIndex(value => {
            return value.productId === rowId
        });
    }
    
    function createPrevValuesBeforeEdits(state, rowData, currentStock) {
        let prevValuesBeforeEdits = state.prevValuesBeforeEdits.slice();
        // console.log('in createPreValuesBeforeEdits prevValuesBeforeEdits on state: ' , prevValuesBeforeEdits);
        let previousValuesObjectIndex = getExistingPreviousValueObjectIndex(prevValuesBeforeEdits, rowData.id);
        let previousValuesForProduct = prevValuesBeforeEdits[previousValuesObjectIndex];
        
        let {stockMode, stockLevel} = currentStock;
        
        // todo apply the value
        let previousValues = {
            productId:rowData.id,
            stockMode,
            stockLevel
        };
    
        if (!previousValuesForProduct) {
            prevValuesBeforeEdits.push(previousValues);
            return prevValuesBeforeEdits;
        }
    
        return prevValuesBeforeEdits;
    }
});