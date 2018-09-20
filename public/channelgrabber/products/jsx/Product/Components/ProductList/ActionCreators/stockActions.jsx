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
            changeStockMode: (rowData, stockModeValue,propToChange) => {
                return function(dispatch, getState) {
                    console.log('in saveStockMode AC rowData: ', {rowData, stockModeValue});
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
            }
        };
    })();
    
    return actionCreators;
});