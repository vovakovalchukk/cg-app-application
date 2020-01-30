import reducerCreator from 'Common/Reducers/creator';
import stateUtility from "../stateUtility";

/*
* the state shape with example entries,
*
*  stockModeOptions: [],
*    stockModes: {
*        byProductId: {
*            1 : {
*                value: "List up to"
*                valueEdited: "Fixed At"
*            }
*        }
*    },
*    stockLevels: {
*        byProductId: {
*            1 : {
*                value: "43"
*                valueEdited: "10009"
*            }
*        }
*    },
*    incPOStockInAvailableOptions: [],
*    incPOStockInAvailable: {
*       byProductId: {
*           1: {
*               productId: 1,
*               selected: "default",
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
    },
    lowStockThresholdToggle: {},
    lowStockThresholdValue: {},
    reorderQuantity: {}
};

let stockModeReducer = reducerCreator(initialState, {
    "STOCK_MODE_SELECT_TOGGLE": function(state, action) {
        let {productId, currentStock} = action.payload;
        let stateCopy = Object.assign({}, state);
        let stockModes = Object.assign({}, state.stockModes);

        let stockModeExists = !!state.stockModes.byProductId[productId];

        if (stockModeExists) {
            stockModes.byProductId[productId].value = stockModes.byProductId[productId] ? stockModes.byProductId[productId].value : currentStock.stockMode;
            return applyStockModesToState(stateCopy, stockModes)
        }

        stockModes.byProductId[productId] = {
            value: stockModes.byProductId[productId] ? stockModes.byProductId[productId] : currentStock.stockMode,
            valueEdited: '',
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
            stockModes = applyStockModes(stockModes, row, currentStock, value);
        }

        if (propToChange === "stockLevel") {
            stockLevels = applyStockLevels(stockLevels, row, currentStock, value);
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
    "STORE_LOW_STOCK_THRESHOLD": function(state, action) {
        let products = action.payload.products;

        let lowStockThresholdToggle = Object.assign({}, state.lowStockThresholdToggle);
        let lowStockThresholdValue = Object.assign({}, state.lowStockThresholdValue);
        let reorderQuantity = Object.assign({}, state.reorderQuantity);

        products.forEach((product) => {
            if (stateUtility.isParentProduct(product) || !product.stock) {
                return;
            }

            lowStockThresholdToggle[product.id] = {
                value: product.stock.lowStockThresholdOn,
                editedValue: product.stock.lowStockThresholdOn,
                active: false
            };
            lowStockThresholdValue[product.id] = {
                value: product.stock.lowStockThresholdValue,
                editedValue: product.stock.lowStockThresholdValue
            };
            reorderQuantity[product.id] = {
                value: product.stock.reorderQuantity,
                editedValue: product.stock.reorderQuantity
            }
        });

        return Object.assign({}, state, {
            lowStockThresholdToggle,
            lowStockThresholdValue,
            reorderQuantity
        });
    },
    "LOW_STOCK_CHANGE": function(state, action) {
        let {productId, newValue, type} = action.payload;

        return Object.assign({}, state, {
            [type]: Object.assign({}, state[type], {
                [productId]: Object.assign({}, state[type][productId], {
                    editedValue: newValue
                })
            })
        });
    },
    "LOW_STOCK_RESET": function(state, action) {
        let {productId} = action.payload;

        return Object.assign({}, state, {
            lowStockThresholdToggle: Object.assign({}, state.lowStockThresholdToggle, {
                [productId]: Object.assign({}, state.lowStockThresholdToggle[productId], {
                    editedValue: state.lowStockThresholdToggle[productId].value,
                    active: false
                })
            }),
            lowStockThresholdValue: Object.assign({}, state.lowStockThresholdValue, {
                [productId]: Object.assign({}, state.lowStockThresholdValue[productId], {
                    editedValue: state.lowStockThresholdValue[productId].value
                })
            })
        });
    },
    "LOW_STOCK_UPDATE_SUCCESSFUL": function(state, action) {
        let {productId, toggle, value} = action.payload;

        return Object.assign({}, state, {
            lowStockThresholdToggle: Object.assign({}, state.lowStockThresholdToggle, {
                [productId]: Object.assign({}, state.lowStockThresholdToggle[productId], {
                    value: toggle,
                    editedValue: toggle,
                    active: false
                })
            }),
            lowStockThresholdValue: Object.assign({}, state.lowStockThresholdValue, {
                [productId]: Object.assign({}, state.lowStockThresholdValue[productId], {
                    value: value,
                    editedValue: value
                })
            })
        });
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

        let newAllProductIds = state.incPOStockInAvailable.allProductIds ? state.incPOStockInAvailable.allProductIds.slice() : [];
        let newPObyProductId = Object.assign({}, state.incPOStockInAvailable.byProductId);

        newIncPOStockInAvailable.allProductIds.forEach(productId => {
            newAllProductIds.push(productId);
        });

        for (let productId in newIncPOStockInAvailable.byProductId) {
            newPObyProductId[productId] = newIncPOStockInAvailable.byProductId[productId];
        }

        let incPOStockInAvailable = {
            byProductId: newPObyProductId,
            allProductIds: newAllProductIds
        };


        let newState = Object.assign({}, state, {
            incPOStockInAvailable
        });
        return newState;
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
    },
    "REORDER_QUANTITY_CHANGE": function(state, action) {
        let {productId, newValue} = action.payload;

        return Object.assign({}, state, {
            reorderQuantity: Object.assign({}, state.reorderQuantity, {
                [productId]: Object.assign({}, state.reorderQuantity[productId], {
                    editedValue: newValue
                })
            })
        });
    },
    "REORDER_QUANTITY_RESET": function(state, action) {
        let {productId} = action.payload;

        return Object.assign({}, state, {
            reorderQuantity: Object.assign({}, state.reorderQuantity, {
                [productId]: Object.assign({}, state.reorderQuantity[productId], {
                    editedValue: state.reorderQuantity[productId].value
                })
            })
        });
    },
    "REORDER_QUANTITY_UPDATE_SUCCESSFUL": function(state, action) {
        let {productId, value} = action.payload;

        return Object.assign({}, state, {
            reorderQuantity: Object.assign({}, state.reorderQuantity, {
                [productId]: Object.assign({}, state.reorderQuantity[productId], {
                    value: value,
                    editedValue: value,
                    active: false
                })
            })
        });
    }
});

export default stockModeReducer;

function applyStockModesToState(stateCopy, stockModes) {
    return Object.assign({}, stateCopy, {
        stockModes
    });
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

function applyStockModes(stockModes, row, currentStock, value) {
    if (!stockModes.byProductId[row.id]) {
        stockModes.byProductId[row.id] = {};
    }
    stockModes.byProductId[row.id].value = currentStock.stockMode;
    stockModes.byProductId[row.id].valueEdited = value;
    return stockModes;
}

function applyStockLevels(stockLevels, row, currentStock, value) {
    if (!stockLevels.byProductId[row.id]) {
        stockLevels.byProductId[row.id] = {}
    }
    stockLevels.byProductId[row.id].value = currentStock.stockLevel;
    stockLevels.byProductId[row.id].valueEdited = value;
    return stockLevels;
}