define([
    'Product/Storage/Ajax',
    'Product/Filter/Entity'
], function(
    AjaxHandler,
    ProductFilter
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
            return function(dispatch, getState) {
                dispatch({
                    type: 'PRODUCT_VARIATIONS_GET_REQUEST'
                });
                dispatch({
                    type: 'PRODUCT_EXPAND_REQUEST',
                    payload: {
                        productRowIdToExpand: productRowIdToExpand
                    }
                });
                
                let variationsByParent = getState().products.variationsByParent;
                
                if(variationsHaveAlreadyBeenRequested(variationsByParent, productRowIdToExpand)){
                    dispatch({
                        type: 'PRODUCT_VARIATIONS_GET_REQUEST_SUCCESS',
                        payload: variationsByParent
                    });
                    dispatch({
                        type: 'PRODUCT_EXPAND_SUCCESS',
                        payload: {
                            productRowIdToExpand
                        }
                    });
                    return;
                }
                
                var filter = new ProductFilter(null, productRowIdToExpand);
                AjaxHandler.fetchByFilter(filter, fetchProductVariationsCallback);
                
                function fetchProductVariationsCallback(data) {
                    let variationsByParent = sortVariationsByParentId(data.products, filter.getParentProductId());
                    dispatch({
                        type: 'PRODUCT_VARIATIONS_GET_REQUEST_SUCCESS',
                        payload: variationsByParent
                    });
                    dispatch({
                        type: 'PRODUCT_EXPAND_SUCCESS',
                        payload: {
                            productRowIdToExpand
                        }
                    });
                }
            }
        },
        collapseProduct: (productRowIdToCollapse) => {
            return {
                type: "PRODUCT_COLLAPSE",
                payload: {
                    productRowIdToCollapse
                }
            }
        }
    };
    
    function sortVariationsByParentId(newVariations, parentProductId) {
        var variationsByParent = {};
        for (var index in newVariations) {
            var variation = newVariations[index];
            if (!variationsByParent[variation.parentProductId]) {
                variationsByParent[variation.parentProductId] = [];
            }
            variationsByParent[variation.parentProductId].push(variation);
        }
        return variationsByParent;
    }
    
    function variationsHaveAlreadyBeenRequested(variationsByParent,productId){
        if(variationsByParent[productId]){
            return true;
        }
    }
});
