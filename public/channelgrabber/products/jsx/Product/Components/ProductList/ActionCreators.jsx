define([
], function(
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
            return{
                type:"PRODUCT_EXPAND",
                payload:{
                    productRowIdToExpand
                }
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
