define([], function() {
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
                return function(dispatch, getState) {
                    let state = getState();
                    let productStock = getState.customGetters.getStock(rowData.id);
                    let stock = state.stock;
                    
                    let updateStockModeWithBoundArguments = async () => {
                    };
                    let updateStockLevelWithBoundArguments = async () => {
                    }
                    
                    if (stockModeHasBeenEdited(productStock, stock, rowData)) {
                        updateStockModeWithBoundArguments = updateStockMode.bind(
                            this,
                            rowData.id,
                            productStock.stockMode
                        );
                    }
                    
                    if (stockLevelHasBeenEdited(productStock, stock, rowData)) {
                        updateStockLevelWithBoundArguments = updateStockLevel.bind(
                            this,
                            rowData.id,
                            productStock.stockLevel
                        );
                    }
                    
                    let saveStockModePromise = updateStockModeWithBoundArguments();
                    let saveStockLevelsPromise = updateStockLevelWithBoundArguments();
                    
                    Promise.all([saveStockModePromise, saveStockLevelsPromise]).then((response) => {
                        dispatch({
                            type: "STOCK_MODE_UPDATE_SUCCESS",
                            payload: response
                        });
                    }, error => {
                        dispatch({
                            type: "STOCK_MODE_UPDATE_FAILURE",
                            payload: {error}
                        });
                    });
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
            }
        }
    }());
    
    return actionCreators;
    
    async function updateStockMode(id, value) {
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
    };
    
    async function updateStockLevel(id, value) {
        $.ajax({
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