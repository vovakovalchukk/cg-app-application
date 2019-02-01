import stateUtility from 'Product/Components/ProductList/stateUtility';

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
        lowStockSelectToggle: (productId) => {
            return function(dispatch) {
                dispatch({
                   type: "LOW_STOCK_SELECT_TOGGLE",
                   payload: {
                       productId: productId
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

function stockModeHasBeenEdited(productStock, stock, rowData) {
    return stock.stockModes.byProductId[rowData.id] && stock.stockModes.byProductId[rowData.id].valueEdited;
}

function stockLevelHasBeenEdited(productStock, stock, rowData) {
    return stock.stockLevels.byProductId[rowData.id] && stock.stockLevels.byProductId[rowData.id].valueEdited;
}
