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
                    let state= getState();
                    let productStock = getState.customGetters.getStock(rowData.id);
                    let stockState = state.stock;
                    
                    if(productStock.stockMode !== getPreviousStockMode(stockState,rowData.id)){
                        // console.log('stock mode has changed');
                        
                        
                    }else{
                        // console.log('stock mode has not changed from what is stored on prev');
                        
                        
                    }
                    // if previous value of stockMode has changed to what it is now save it.
                    // $.ajax({
                    //     url: '/products/stockMode',
                    //     data: {id: this.props.variation.id, stockMode: stockMode.value},
                    //     method: 'POST',
                    //     dataType: 'json',
                    //     success: function(response) {
                    //         n.success('Stock mode updated successfully..');
                    //         window.triggerEvent('mode-' + this.props.variation.sku, response);
                    //     }.bind(this),
                    //     error: function(error) {
                    //         n.showErrorNotification(error, "There was an error when attempting to update the stock mode.");
                    //     }
                    // });
                    
                    
                    // type: "STOCK_MODE_SAVE_TO_BACKEND",
                    // payload: {
                    //     rowData,
                    //     stock
                    // }
                }
            }
        };
    })();
    
    return actionCreators;
    
    function getPreviousStockMode(stockState,productId){
        let previousStockMode = stockState.prevValuesBeforeEdits.find( stock =>{
            return (stock.id === productId) && (stock.stockMode === constants.STOCK_MODE_EDITING_STATUSES.editing);
        });
        return previousStockMode;
    }
});