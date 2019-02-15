import reducerCreator from 'Common/Reducers/creator';

/*
* the state shape with example entries,
*
*  stockModeOptions: [],
*    stockModes: {
*        byProductId: {
*            1 : {
*                value: "List up to"
*                valueEdited: "Fixed At"
*                active:true
*            }
*        }
*    },
*    stockLevels: {
*        byProductId: {
*            1 : {
*                value: "43"
*                valueEdited: "10009"
*                active:true
*            }
*        }
*    },
*    incPOStockInAvailableOptions: [],
*    incPOStockInAvailable: {
*       byProductId: {
*           1: {
*               productId: 1,
*               selected: "default",
*               active: false
*           }
*       }
*    }
*/

let initialState = {
    stockModeOptions: [],
    stockModes: {
        byProductId: {}
    },
    stockLevels: {
        byProductId: {}
    },
    incPOStockInAvailableOptions: [],
    incPOStockInAvailable: {
        byProductId: {}
    }
};

let stockModeReducer = reducerCreator(initialState, {
    "STOCK_MODE_SELECT_TOGGLE": function(state, action) {
        let {productId, currentStock} = action.payload;
        let stateCopy = Object.assign({}, state);
        let stockModes = Object.assign({}, state.stockModes);

        let stockModeExists = !!state.stockModes.byProductId[productId];
        stockModes = makeAllStockModesInactiveApartFromOneAtSpecifiedProductId(stockModes, productId);

        if (stockModeExists) {
            stockModes.byProductId[productId].value = stockModes.byProductId[productId] ? stockModes.byProductId[productId].value : currentStock.stockMode;
            stockModes.byProductId[productId].active = !stockModes.byProductId[productId].active;
            return applyStockModesToState(stateCopy, stockModes)
        }

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
        let {rowData, response, stockModeDesired, stockLevelDesired} = action.payload;
        let stock = Object.assign({}, state);

        if (stockModeDesired) {
            stock.stockModes.byProductId[rowData.id].value = stockModeDesired;
            delete stock.stockModes.byProductId[rowData.id].valueEdited;
        }
        if (stockLevelDesired) {
            stock.stockLevels.byProductId[rowData.id].value = stockLevelDesired;
            delete stock.stockLevels.byProductId[rowData.id].valueEdited;
        }

        let newState = Object.assign({}, state, stock);
        return newState;
    },
    "STOCK_MODE_UPDATE_FAILURE": function(state, action) {
        let {error} = action.payload;
        console.error(error);
        n.showErrorNotification(error, "There was an error when attempting to update the stock mode.");
        return state;
    },
    "INC_PO_STOCK_IN_AVAIL_STORE": function(state, action) {
        let newState = Object.assign({}, state, {
            incPOStockInAvailableOptions: action.payload.incPOStockInAvailableOptions
        });
        return newState;
    },
    "INC_PO_STOCK_FROM_PRODUCTS_EXTRACT": function(state, action) {
        let {products} = action.payload;
        let newIncPOStockInAvailable = getIncPOStockInAvailableFromProducts(products);

        let incPOStockInAvailable = Object.assign(state.incPOStockInAvailable, newIncPOStockInAvailable);

        let newState = Object.assign({}, state, {
            incPOStockInAvailable
        });
        return newState;
    },
    'INC_PO_STOCK_IN_AVAILABLE_TOGGLE': function(state, action) {
        let {productId} = action.payload;
        let stock = Object.assign({}, state);
        let productIncPOStockInAvailable = stock.incPOStockInAvailable.byProductId[productId]
        let previousActiveProp = productIncPOStockInAvailable.active;
        stock = makeAllIncPoStockInAvailableSelectsInactive(stock);
        if (previousActiveProp) {
            delete productIncPOStockInAvailable.active;
            return stock;
        }
        productIncPOStockInAvailable.active = true;
        return stock;
    },
    "INC_PO_STOCK_UPDATE_SUCCESS": function(state, action) {
        let {productId, desiredVal} = action.payload;
        let newIncPOStockInAvailable = Object.assign({}, state.incPOStockInAvailable);

        newIncPOStockInAvailable.byProductId[productId].selected = desiredVal;
        let newState = Object.assign({}, state, {
            incPOStockInAvailable: newIncPOStockInAvailable
        });
        n.success('Product\'s include purchase order stock setting updated successfully.');
        return newState;
    },
    "INC_PO_STOCK_UPDATE_ERROR": function(state, action) {
        let error = action.payload;
        n.showErrorNotification(error, "There was an error when attempting to update the product\'s include purchase order stock setting.");
        return state;
    }
});

export default stockModeReducer;

function makeAllIncPoStockInAvailableSelectsInactive(stock) {
    let stockCopy = Object.assign({}, stock);
    for (let id of Object.keys(stock.incPOStockInAvailable.byProductId)) {
        delete stock.incPOStockInAvailable.byProductId[id].active;
    }
    return stockCopy;
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

function getIncPOStockInAvailableFromProducts(products) {
    let incPOStockInAvailable = {
        byProductId: {},
        allProductIds: []
    };
    products.forEach(product => {
        if (!product.stock) {
            return;
        }
        let value = (product.stock.includePurchaseOrdersUseDefault ? 'default' : (product.stock.includePurchaseOrders ? 'on' : 'off'));
        incPOStockInAvailable.byProductId[product.id] = {
            productId: product.id,
            selected: value
        };
        incPOStockInAvailable.allProductIds.push(product.id);
    });
    return incPOStockInAvailable;
}