define([
    'Product/Storage/Ajax',
    'Product/Filter/Entity',
    'Product/Components/ProductList/Config/constants'
], function(
    AjaxHandler,
    ProductFilter,
    constants
) {
    "use strict";
    
    let actionCreators = (function() {
        return {
            changeStockMode: (rowData, stockModeValue, propToChange) => {
                return function(dispatch, getState) {
                    // console.log('in saveStockMode AC rowData: ', {rowData, stockModeValue});
                    if (rowData === null) {
                        return;
                    }
            
                    let previousValue = getState.customGetters.getStock(rowData.id)[propToChange];
            
                    dispatch({
                        type: "STOCK_MODE_CHANGE",
                        payload: {
                            rowData,
                            stockModeValue,
                            propToChange,
                            previousValue
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
                        console.log('stock mode has changed so will save stockmode');
                        updateStockModeWithBoundArguments = updateStockMode.bind(
                            this,
                            rowData.id,
                            productStock.stockMode
                        );
                    }
            
                    if (stockLevelHasBeenEdited(productStock, stock, rowData)) {
                        console.log('stockLevel has changed so will save stockMode');
                        //todo - save stockLevel
                        updateStockLevelWithBoundArguments = updateStockLevel.bind(
                            this,
                            rowData.id,
                            productStock.stockLevel
                        );
                    }
            
            
                    console.log('about to call saved promise updateStockModeWithBoundArguments: ', updateStockModeWithBoundArguments);
            
            
                    let saveStockModePromise = updateStockModeWithBoundArguments();
                    let saveStockLevelsPromise = updateStockLevelWithBoundArguments();
                    // console.log('after promise call saveStockModePromise: ' , saveStockModePromise);
            
                    Promise.all([saveStockModePromise, saveStockLevelsPromise]).then((resp) => {
                        console.log('in .then after savingStockLevel resp: ', resp);
                        n.success('Stock mode updated successfully..');
                    }, err => {
                        n.showErrorNotification(err, "There was an error when attempting to update the stock mode.");
                        console.error("There was an error saving stock mode values");
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
            success: function(resp) {
                // n.success('Stock mode updated successfully..');
                console.log('Stock mode updated successfully. resp: ', resp);
                // window.triggerEvent('mode-' + this.props.variation.sku, response);
            },
            error: function(error) {
                console.error("There was an error when attempting to update the stock mode.");
            }
        });
    };
    
    async function updateStockLevel(id, value) {
        console.log('in updateStockLeve with id: ' , id, 'and value : ' , value);
        
        
        $.ajax({
            url: 'products/stockLevel',
            type: 'POST',
            dataType : 'json',
            data: {
                id,
                stockLevel: value
            },
            success: function(resp) {
                console.log('Stock level updated successfully. resp: ', resp);
                return resp;
            }.bind(this),
            error: function(error) {
                console.error('There was an error when attempting to update the stock level.')
                return error;
            }
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