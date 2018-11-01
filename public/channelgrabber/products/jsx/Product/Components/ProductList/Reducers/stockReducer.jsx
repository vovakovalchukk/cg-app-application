import reducerCreator from 'Common/Reducers/creator';
import constants from 'Product/Components/ProductList/Config/constants';

let initialState = {
    stockModeOptions: [],
    stockModes: {
        byProductId: {
//            1 : {
//                value: "List up to"
//                valueEdited: "Fixed At"
//                active:true,
//            }
        }
    },
    stockLevels: {
        byProductId: {
//            1 : {
//                value: "43"
//                valueEdited: "10009"
//                active:true,
//            }
        }
    },
};

let stockModeReducer = reducerCreator(initialState, {
    "STOCK_MODE_SELECT_TOGGLE": function(state, action) {
        let {productId, currentStock} = action.payload;
        let stateCopy = Object.assign({}, state);
        let stockModes = Object.assign({}, state.stockModes);

        let stockModeExists = !!state.stockModes.byProductId[productId];
        stockModes = makeAllStockModesInactiveApartFromOneAtSpecifiedProductId(stockModes, productId);

        if (stockModeExists) {
            stockModes.byProductId[productId].value = currentStock.stockMode;
            stockModes.byProductId[productId].active = !stockModes.byProductId[productId].active;
            return applyStockModesToState(stateCopy, stockModes)
        }

        stockModes.byProductId[productId] = {
            value: currentStock.stockMode,
            valueEdited: '',
            active: true
        };
        return applyStockModesToState(stateCopy, stockModes)
    },
    "STOCK_MODE_OPTIONS_STORE": function(state, action) {
        let newState = Object.assign({}, state, {
            stockModeOptions: action.payload.stockModeOptions
        });
        return newState;
    },
    "STOCK_MODE_CHANGE": function(state, action) {
        let { row,
            value,
            propToChange,
            currentStock
        } = action.payload;

        console.log('in STOCK_MODE_CHANGE -R ', {
            row, value, propToChange
        });

        let stockModes = Object.assign({}, state.stockModes);
        let stockLevels = Object.assign({}, state.stockLevels);

        if(propToChange==="stockMode"){
            stockModes.byProductId[row.id].value = currentStock.stockMode;
            stockModes.byProductId[row.id].valueEdited = value;
        }

        let newState = Object.assign({}, state, {
            stockModes,
            stockLevels
        });


        return newState;
    },
    "STOCK_MODE_EDIT_CANCEL": function(state, action) {
        let {rowData} = action.payload;

        let newStockModeEdits = state.stockModeEdits.slice();
        let newPrevValuesBeforeEdits = state.prevValuesBeforeEdits.slice();

        let relevantEditIndex = newStockModeEdits.findIndex(edit => (edit.productId === rowData.id));
        let relevantNewValuesBeforeEditsIndex = newPrevValuesBeforeEdits.findIndex(beforeVals => (beforeVals.productId === rowData.id));
        newStockModeEdits.splice(relevantEditIndex, 1);
        newPrevValuesBeforeEdits.splice(relevantNewValuesBeforeEditsIndex, 1);

        let newState = Object.assign({}, state, {
            stockModeEdits: newStockModeEdits,
            prevValuesBeforeEdits: newPrevValuesBeforeEdits
        });
        return newState;
    },
    "STOCK_MODE_UPDATE_SUCCESS": function(state) {
        n.success('Stock mode updated successfully..');
        return state
    },
    "STOCK_MODE_UPDATE_FAILURE": function(state, action) {
        let {error} = action.payload;
        n.showErrorNotification(error, "There was an error when attempting to update the stock mode.");
        return state;
    }
});

export default stockModeReducer;

function applyStockModesToState(stateCopy, stockModes) {
    return Object.assign({}, stateCopy, {
        stockModes
    });
}

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
    let previousValuesObjectIndex = getExistingPreviousValueObjectIndex(prevValuesBeforeEdits, rowData.id);
    let previousValuesForProduct = prevValuesBeforeEdits[previousValuesObjectIndex];

    let {stockMode, stockLevel} = currentStock;

    let previousValues = {
        productId: rowData.id,
        stockMode,
        stockLevel
    };

    if (!previousValuesForProduct) {
        prevValuesBeforeEdits.push(previousValues);
        return prevValuesBeforeEdits;
    }

    return prevValuesBeforeEdits;
}

function makeAllStockModesInactiveApartFromOneAtSpecifiedProductId(stockModes, productId) {
    Object.keys(stockModes.byProductId).forEach(key => {
        if (key=== productId.toString() ) {
            return;
        }
        stockModes.byProductId[key].active = false;
    });
    return stockModes;
}