define([
    'Product/Components/ProductList/stateUtility',
], function(
    stateUtility
) {
    "use strict";
    
    let actionCreators = (function() {
        return {
            changeStockMode: (rowData, stockModeValue, propToChange) => {
                return function(dispatch, getState) {
                    if (rowData === null) {
                        return;
                    }
                    let currentStock = getState.customGetters.getStock(rowData.id);
                    dispatch({
                        type: "STOCK_MODE_CHANGE",
                        payload: {
                            rowData,
                            stockModeValue,
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
                    
                    let saveStockModePromise = Promise.resolve();
                    let saveStockLevelsPromise = Promise.resolve();
                    
                    if (stockModeHasBeenEdited(productStock, stock, rowData)) {
                        saveStockModePromise = updateStockMode(
                            rowData.id,
                            productStock.stockMode
                        );
                    }
                    
                    if (stockLevelHasBeenEdited(productStock, stock, rowData)) {
                        saveStockLevelsPromise = updateStockLevel(
                            rowData.id,
                            productStock.stockLevel
                        );
                    }
                    n.notice('Updating stock mode value.');
                    try {
                        let response = await Promise.all([saveStockModePromise, saveStockLevelsPromise]);
                        dispatch({
                            type: "STOCK_MODE_UPDATE_SUCCESS",
                            payload: response
                        });
                    } catch (error) {
                        dispatch({
                            type: "STOCK_MODE_UPDATE_FAILURE",
                            payload: {error}
                        });
                    }
                }
            },
            cancelStockModeEdit: (rowData) => {
                return function(dispatch, getState) {
                    let prevValues = getState.customGetters.getStockPrevValuesBeforeEdits();
                    let prevValuesForRow = prevValues.find(values => values.productId === rowData.id);
                    
                    dispatch({
                        type: "STOCK_MODE_EDIT_CANCEL",
                        payload: {
                            rowData,
                            prevValuesForRow
                        }
                    });
                    
                }
            },
            updateAvailable: (productData,field,desiredValue) => {
                return async function(dispatch) {
                    console.log('in updateAvailable AQ with args ' , {productData, field,desiredValue});
                    let response;
                    
                    let totalQuantity = getTotalQuantityFromDesiredAvailable(productData, desiredValue);
                    
                    let dataToSend = {
                        stockLocationId: getStockLocationId(productData),
                        totalQuantity,
                        eTag: getStockEtag(productData)
                    };
                    n.notice('Updating stock level.');
                    try{
                        response = await updateStock(dataToSend);
                        n.success('Stock total updated successfully.');
                    }catch(err){
                        n.showErrorNotification("There was an error when attempting to update the stock total.");
                        console.error(err);
                        dispatch({
                            type:"AVAILABLE_UPDATE_FAIL"
                        });
                        return err;
                    }
                    dispatch({
                        type:"AVAILABLE_UPDATE_SUCCESS"
                    });
                    console.log('after try catch');
                    return response;
                }
            }
        }
    }());
    
    return actionCreators;
    
    function getStockLocationId(rowData) {
        return (rowData.stock ? rowData.stock.locations[0].id : '');
    }
    
    function getStockEtag(rowData){
        return (rowData.stock ? rowData.stock.locations[0].eTag : '');
    }
    
    function getTotalQuantityFromDesiredAvailable(rowData, desiredAvailable){
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
    
    function updateStock(data){
        return $.ajax({
            "url": "products/stock/update",
            type: 'POST',
            dataType: 'json',
            data,
            success: response => (response),
            error: error => (error)
        });
    }
    
    function getPreviousProductStock(prevValuesBeforeEdits, productId) {
        return prevValuesBeforeEdits.find(prevStockValues => {
            return prevStockValues.id === productId
        });
    }
    
    function getPreviousStockMode(previousValuesBeforeEdits, productId) {
        let previousProductStock = getPreviousProductStock(previousValuesBeforeEdits, productId);
        if (!!previousProductStock) {
            return previousProductStock.stockMode
        }
        return null;
    }
    
    function getPreviousStockLevel(previousValuesBeforeEdits, productId) {
        let previousProductStock = getPreviousProductStock(previousValuesBeforeEdits, productId);
        if (!!previousProductStock) {
            return previousProductStock.stockLevel
        }
        return null;
    }
    
    function stockModeHasBeenEdited(productStock, stock, rowData) {
        return productStock.stockMode !== getPreviousStockMode(stock.prevValuesBeforeEdits, rowData.id);
    }
    
    function stockLevelHasBeenEdited(productStock, stock, rowData) {
        return productStock.stockLevel !== getPreviousStockLevel(stock.prevValuesBeforeEdits, rowData.id);
    }
});
