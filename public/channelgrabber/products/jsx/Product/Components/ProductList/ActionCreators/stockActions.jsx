import stateUtility from 'Product/Components/ProductList/stateUtility';
import LowStockInputs from "../Components/LowStockInputs";

"use strict";

let actionCreators = (function() {
    return {
        toggleStockModeSelect: (productId) => {
            return function(dispatch, getState) {
                let currentStock = getState.customGetters.getStock(productId);
                dispatch({
                    type: 'STOCK_MODE_SELECT_TOGGLE',
                    payload: {
                        productId,
                        currentStock
                    }
                });
            }
        },
        changeStockMode: (row, value, propToChange) => {
            return function(dispatch, getState) {
                if (row === null) {
                    return;
                }
                let currentStock = getState.customGetters.getStock(row.id);
                dispatch({
                    type: "STOCK_MODE_CHANGE",
                    payload: {
                        row,
                        value,
                        propToChange,
                        currentStock
                    }
                });
            };
        },
        saveStockModeToBackend: (rowData) => {
            return async function(dispatch, getState) {
                let state = getState();
                let productStock = getState.customGetters.getStock(rowData.id);
                let stock = state.stock;

                let stockModeDesired, stockLevelDesired;

                n.notice('Updating stock mode.');
                try {
                    let response = {};

                    if (stockModeHasBeenEdited(productStock, stock, rowData)) {
                        stockModeDesired = stock.stockModes.byProductId[rowData.id].valueEdited;
                        response.updateStockMode = await updateStockMode(
                            rowData.id,
                            stockModeDesired
                        );
                    }

                    if (stockLevelHasBeenEdited(productStock, stock, rowData)) {
                        stockLevelDesired = stock.stockLevels.byProductId[rowData.id].valueEdited;
                        response.updateStockLevel = await updateStockLevel(
                            rowData.id,
                            stockLevelDesired
                        );
                    }

                    dispatch({
                        type: "STOCK_MODE_UPDATE_SUCCESS",
                        payload: {
                            response,
                            rowData,
                            stockLevelDesired,
                            stockModeDesired
                        }
                    });
                } catch (error) {
                    dispatch({
                        type: "STOCK_MODE_UPDATE_FAILURE",
                        payload: {
                            error,
                            rowData
                        }
                    });
                }
            }
        },
        cancelStockModeEdit: (rowData) => {
            return function(dispatch) {
                dispatch({
                    type: "STOCK_MODE_EDIT_CANCEL",
                    payload: {
                        rowData
                    }
                });
            }
        },
        updateAvailable: (productData, field, desiredValue) => {
            return async function(dispatch) {
                let response;
                let totalQuantity = getTotalQuantityFromDesiredAvailable(productData, desiredValue);
                let dataToSend = {
                    stockLocationId: getStockLocationId(productData),
                    totalQuantity,
                    eTag: getStockEtag(productData)
                };
                n.notice('Updating stock level.');
                try {
                    response = await updateStock(dataToSend);
                    n.success('Stock total updated successfully.');
                } catch (err) {
                    n.error("There was an error when attempting to update the stock total.");
                    console.error(err);
                    dispatch({
                        type: "AVAILABLE_UPDATE_FAIL"
                    });
                    return err;
                }
                dispatch({
                    type: "AVAILABLE_UPDATE_SUCCESS",
                    payload: {
                        productId: productData.id,
                        desiredStock: totalQuantity
                    }
                });
                return response;
            }
        },
        extractIncPOStockInAvailableFromProducts: (products) => {
            return function(dispatch) {
                dispatch({
                    type: "INC_PO_STOCK_FROM_PRODUCTS_EXTRACT",
                    payload: {
                        products
                    }
                });
            }
        },
        storeLowStockThreshold: (products) => {
            return function(dispatch) {
                dispatch({
                    type: "STORE_LOW_STOCK_THRESHOLD",
                    payload: {
                        products
                    }
                });
            }
        },
        lowStockChange: (productId, type, newValue) => {
            return function(dispatch) {
                dispatch({
                    type: "LOW_STOCK_CHANGE",
                    payload: {
                        productId, newValue, type
                    }
                });
            }
        },
        lowStockReset: (productId) => {
            return function(dispatch) {
                dispatch({
                    type: "LOW_STOCK_RESET",
                    payload: {
                        productId
                    }
                });
            }
        },
        saveLowStockToBackend: (productId, toggle, value) => {
            return async function (dispatch) {
                n.notice('Updating low stock threshold...', true);
                try {
                    let response = await updateLowStock(
                        productId,
                        toggle,
                        formatLowStockValue(toggle, value)
                    );
                    let responseForProduct = {};

                    n.success('The low stock threshold was updated successfully');

                    Object.keys(response.products).forEach((productId) => {
                        responseForProduct = response.products[productId];
                        dispatch({
                            type: "LOW_STOCK_UPDATE_SUCCESSFUL",
                            payload: {
                                productId: productId,
                                toggle: responseForProduct.lowStockThresholdToggle,
                                value: responseForProduct.lowStockThresholdValue
                            }
                        });
                    });
                } catch (error) {
                    console.error(error);
                    n.error('There was an error while saving the low stock threshold. Please try again or contact support if the problem persists');
                    actionCreators.lowStockReset(productId);
                }
            }
        },
        reorderQuantityChange: (productId, newValue) => {
            return function(dispatch) {
                dispatch({
                    type: "REORDER_QUANTITY_CHANGE",
                    payload: {
                        productId, newValue
                    }
                });
            }
        },
        reorderQuantityReset: (productId) => {
            return function(dispatch) {
                dispatch({
                    type: "REORDER_QUANTITY_RESET",
                    payload: {
                        productId
                    }
                });
            }
        },
        saveReorderQuantityToBackend: (productId, value) => {
            return async function (dispatch) {
                n.notice('Updating the reorder quantity...', true);
                try {
                    let response = await updateReorderQuantity(productId, value);
                    let responseForProduct = {};

                    n.success('The reorder quantity was updated successfully');

                    Object.keys(response.products).forEach((productId) => {
                        responseForProduct = response.products[productId];
                        dispatch({
                            type: "REORDER_QUANTITY_UPDATE_SUCCESSFUL",
                            payload: {
                                productId,
                                reorderQuantity: responseForProduct.reorderQuantity
                            }
                        });
                    });
                } catch (error) {
                    console.error(error);
                    n.error('There was an error while saving the reorder quantity. Please try again or contact support if the problem persists');
                    actionCreators.reorderQuantityReset(productId);
                }
            }
        },
        updateIncPOStockInAvailable: (productId, e) => {
            let desiredVal = e.value;
            return async function(dispatch) {
                try {
                    n.notice('Updating Purchase Order stock preference.');
                    let response = await updateIncPOStockInAvailable(productId, desiredVal);
                    dispatch({
                        type: "INC_PO_STOCK_UPDATE_SUCCESS",
                        payload: {
                            productId,
                            desiredVal,
                            response
                        }
                    });
                } catch (error) {
                    dispatch({
                        type: "INC_PO_STOCK_UPDATE_FAILURE",
                        payload: {
                            error
                        },
                    })
                }
            }
        }
    }
}());

export default actionCreators;

function getStockLocationId(rowData) {
    return (rowData.stock ? rowData.stock.locations[0].id : '');
}

function getStockEtag(rowData) {
    return (rowData.stock ? rowData.stock.locations[0].eTag : '');
}

function getTotalQuantityFromDesiredAvailable(rowData, desiredAvailable) {
    return parseInt(desiredAvailable) + parseInt(stateUtility.getAllocatedStock(rowData));
}

async function updateStockLevel(id, value) {
    return $.ajax({
        url: 'products/stockLevel',
        type: 'POST',
        dataType: 'json',
        data: {
            id,
            stockLevel: value
        },
        success: response => (response),
        error: error => (error)
    });
}

function updateStockMode(id, value) {
    return $.ajax({
        url: '/products/stockMode',
        data: {
            id,
            stockMode: value
        },
        method: 'POST',
        dataType: 'json',
        success: response => (response),
        error: error => (error)
    });
}

function updateStock(data) {
    return $.ajax({
        "url": "products/stock/update",
        type: 'POST',
        dataType: 'json',
        data,
        success: response => (response),
        error: error => (error)
    });
}

function updateLowStock(productId, toggle, value) {
    return $.ajax({
        url: "products/lowStockThreshold",
        type: 'POST',
        dataType: 'json',
        data: {
            productId: productId,
            lowStockThresholdToggle: toggle,
            lowStockThresholdValue: value
        },
        success: response => (response),
        error: error => (error)
    });
}

function updateReorderQuantity(productId, reorderQuantity) {
    return $.ajax({
        url: "products/reorderQuantity",
        type: 'POST',
        dataType: 'json',
        data: {
            productId,
            reorderQuantity
        },
        success: response => (response),
        error: error => (error)
    });
}

async function updateIncPOStockInAvailable(productId, includePurchaseOrders) {
    return $.ajax({
        url: '/products/includePurchaseOrders',
        data: {productId, includePurchaseOrders},
        method: 'POST',
        dataType: 'json',
        success: function(response) {
            return response;
        },
        error: function(error) {
            return error;
        }
    });
}

function stockModeHasBeenEdited(productStock, stock, rowData) {
    return stock.stockModes.byProductId[rowData.id] && stock.stockModes.byProductId[rowData.id].valueEdited;
}

function stockLevelHasBeenEdited(productStock, stock, rowData) {
    return stock.stockLevels.byProductId[rowData.id] && stock.stockLevels.byProductId[rowData.id].valueEdited;
}

function formatLowStockValue(toggle, value) {
    if (toggle !== LowStockInputs.optionValueOn) {
        return null;
    }

    return value;
}
