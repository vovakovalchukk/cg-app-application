define([
    'Product/Storage/Ajax',
], function(
    AjaxHandler
) {
    "use strict";
    
    return {
        initialSimpleAndParentProductsLoad: (products) => {
            return {
                type: "INITIAL_SIMPLE_AND_PARENT_PRODUCTS_LOAD",
                payload: {
                    products
                }
            };
        },
        expandProduct: (productRowIdToExpand) => {
            // return{
            //     type:"PRODUCT_EXPAND",
            //     payload:{
            //         productRowIdToExpand
            //     }
            // }
            return function(dispatch, getState) {
                dispatch({
                    type: 'PRODUCT_EXPAND_REQUEST'
                });
                // let filter =
                let callback = function(dispatch){
                    dispatch({
                        type: 'PRODUCT_EXPAND_SUCCESS'
                    })
                }
                AjaxHandler.fetchByFilter(filter,callback.bind(dispatch));
            }
    
        },
        collapseProduct:(productRowIdToCollapse )=>{
            return{
                type:"PRODUCT_COLLAPSE",
                payload:{
                    productRowIdToCollapse
                }
            }
        }
    };
});
