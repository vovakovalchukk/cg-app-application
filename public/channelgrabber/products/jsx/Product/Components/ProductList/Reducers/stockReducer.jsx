import reducerCreator from 'Common/Reducers/creator';

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
    }
};

let stockModeReducer = reducerCreator(initialState, {
    "STOCK_MODE_SELECT_TOGGLE": function(state, action) {
        console.log('in STOCK_MODE_SELECT_TOGGLE -R');
        
        
        let {productId, currentStock} = action.payload;
        let stateCopy = Object.assign({}, state);
        let stockModes = Object.assign({}, state.stockModes);

        let stockModeExists = !!state.stockModes.byProductId[productId];
        stockModes = makeAllStockModesInactiveApartFromOneAtSpecifiedProductId(stockModes, productId);

        if (stockModeExists) {
            console.log('currentStockModevalue: ' , stockModes.byProductId[productId]);
            console.log('newStockModeValue: ' , stockModes.byProductId[productId] ? stockModes.byProductId[productId].value : currentStock.stockMode)

            stockModes.byProductId[productId].value = stockModes.byProductId[productId] ? stockModes.byProductId[productId].value : currentStock.stockMode;
            stockModes.byProductId[productId].active = !stockModes.byProductId[productId].active;
            return applyStockModesToState(stateCopy, stockModes)
        }


        console.log('currentStockModevalue: ' , stockModes.byProductId[productId]);
        console.log('newStockModeValue: ' , stockModes.byProductId[productId] ? stockModes.byProductId[productId].value : currentStock.stockMode);



        stockModes.byProductId[productId] = {
            value: stockModes.byProductId[productId] ? stockModes.byProductId[productId] : currentStock.stockMode,
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
        let {
            row,
            value,
            propToChange,
            currentStock
        } = action.payload;

        let stockModes = Object.assign({}, state.stockModes);
        let stockLevels = Object.assign({}, state.stockLevels);

        if (propToChange === "stockMode") {
            stockModes.byProductId[row.id].value = currentStock.stockMode;
            stockModes.byProductId[row.id].valueEdited = value;
        }
        if (propToChange === "stockLevel") {
            if (!stockLevels.byProductId[row.id]) {
                stockLevels.byProductId[row.id] = {}
            }
            stockLevels.byProductId[row.id].value = currentStock.stockLevel;
            stockLevels.byProductId[row.id].valueEdited = value;
        }
        let newState = Object.assign({}, state, {
            stockModes,
            stockLevels
        });
        return newState;
    },
    "STOCK_MODE_EDIT_CANCEL": function(state, action) {
        let {rowData} = action.payload;
        let stockLevels = Object.assign({}, state.stockLevels);
        let stockModes = Object.assign({}, state.stockModes);

        stockModes = resetEditsForRow(stockModes, rowData);
        stockLevels = resetEditsForRow(stockLevels, rowData);

        let newState = Object.assign({}, state, {
            stockLevels,
            stockModes
        });
        return newState;
    },
    "STOCK_MODE_UPDATE_SUCCESS": function(state, action) {
        n.success('Stock mode updated successfully..');
        let {rowData, stockModeDesired, stockLevelDesired} = action.payload;
        console.log('in STOCK_MODE_UPDATE_SUCCESS with action ', action);

        let stock = Object.assign({}, state);

        let stockModes = applyStockValue(rowData.id, stock, stockModeDesired, 'stockModes').stockModes;
        let stockLevels = applyStockValue(rowData.id, stock, stockLevelDesired, 'stockLevels').stockLevels;

        console.log('stockModes after applyingStockValue: ', stockModes);
        console.log('stockLevels after applyStock' , stockLevels );


        stockModes = resetEditsForRow(stockModes, rowData);
        stockLevels = resetEditsForRow(stockLevels, rowData);

        let newState = Object.assign({}, state, {
            stockLevels,
            stockModes
        });
        return newState;
    },
    "STOCK_MODE_UPDATE_FAILURE": function(state, action) {
        let {error} = action.payload;
        console.error(error);
        n.showErrorNotification(error, "There was an error when attempting to update the stock mode.");
        return state;
    }
});

export default stockModeReducer;

function applyStockValue(productId, stock, stockModeDesired, stockProp){
    console.log('in applyStockValue {productId,stock,stockModeDesired,stockProp}: ', {productId,stock,stockModeDesired,stockProp});


    if(!stock[stockProp] || !stock[stockProp].byProductId[productId]){
        console.log('stock[stockProp] does not exist ', stock[stockProp]);
        return stock;
    }
    stock[stockProp].byProductId[productId].value = stockModeDesired;

    console.log('new prop   stock[stockProp].byProductId[productId].value: ', stock[stockProp].byProductId[productId].value);

    console.log('stock to be returned: ', stock);


    return stock
}

function applyStockModesToState(stateCopy, stockModes) {
    return Object.assign({}, stateCopy, {
        stockModes
    });
}

function makeAllStockModesInactiveApartFromOneAtSpecifiedProductId(stockModes, productId) {
    stockModes = Object.assign({}, stockModes)
    Object.keys(stockModes.byProductId).forEach(key => {
        if (key === productId.toString()) {
            return;
        }
        stockModes.byProductId[key].active = false;
    });
    return stockModes;
}

function isNotTheStockAssociatedWithRow(id, rowData) {
    return id.toString() !== rowData.id.toString();
}

function resetEditsForRow(values, rowData) {
    Object.keys(values.byProductId).forEach(id => {
        if (isNotTheStockAssociatedWithRow(id, rowData)) {
            return;
        }
        values.byProductId[id].valueEdited = '';
    });
    return values;
}