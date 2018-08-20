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
        productsLinksLoad: (allProductsLinks) => {
            return {
                type: "PRODUCTS_LINKS_LOAD",
                payload: {
                    allProductsLinks
                }
            }
        },
        expandProduct: (productRowIdToExpand) => {
            return function(dispatch, getState) {
                dispatch({
                    type: 'PRODUCT_EXPAND_REQUEST',
                    payload: {
                        productRowIdToExpand: productRowIdToExpand
                    }
                });
                let variationsByParent = getState().products.variationsByParent;
                
                if (variationsHaveAlreadyBeenRequested(variationsByParent, productRowIdToExpand)) {
                    dispatchExpandVariationWithoutAjaxRequest(dispatch, variationsByParent, productRowIdToExpand);
                    return;
                }
                
                dispatchExpandVariationWithAjaxRequest(dispatch, productRowIdToExpand);
            }
        },
        collapseProduct: (productRowIdToCollapse) => {
            return {
                type: "PRODUCT_COLLAPSE",
                payload: {
                    productRowIdToCollapse
                }
            }
        },
        changeTab: (desiredTabKey) => {
            return function(dispatch,getState){
                let state = getState();
                let numberOfVisibleFixedColumns = getVisibleFixedColumns(state).length
                
                //todo - remove this as it just for testing a theory
                // numberOfVisibleFixedColumns = Math.floor(Math.random() * 2) + 6;
                // console.log('in changeTab.. AQ thunk numberOfVisibleFixedColumns: ', numberOfVisibleFixedColumns);
                dispatch({
                    type: "TAB_CHANGE",
                    payload: {
                        desiredTabKey,
                        numberOfVisibleFixedColumns
                    }
                });
            }
        },
        resetScrollbarIndex: ()=>{
            return {
                type:"SCROLLBAR_INDEX_RESET",
                payload:{}
            }
            
        }
    };
    
    function getVisibleFixedColumns(state){
        return state.columns.filter((column)=>{
            return column.fixed
        });
    }
    
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
    
    function variationsHaveAlreadyBeenRequested(variationsByParent, productId) {
        if (variationsByParent[productId]) {
            return true;
        }
    }
    
    function dispatchExpandVariationWithoutAjaxRequest(dispatch, variationsByParent, productRowIdToExpand) {
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
    
    function dispatchExpandVariationWithAjaxRequest(dispatch, productRowIdToExpand) {
        let filter = new ProductFilter(null, productRowIdToExpand);
        AjaxHandler.fetchByFilter(filter, fetchProductVariationsCallback);
        dispatch({
            type: 'PRODUCT_VARIATIONS_GET_REQUEST'
        });
        
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
});
