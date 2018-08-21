define([
    'Product/Storage/Ajax',
    'Product/Filter/Entity'
], function(
    AjaxHandler,
    ProductFilter
) {
    "use strict";
    
    var actionCreators = (function(){
        
        let getProductVariationsRequest = ()=> {
            return {
                type: 'PRODUCT_VARIATIONS_GET_REQUEST'
            };
        };
        let getProductVariationsRequestSuccess = (variationsByParent) => {
            return {
                type: 'PRODUCT_VARIATIONS_GET_REQUEST_SUCCESS',
                payload: variationsByParent
            };
        };
        let expandProductSuccess = (productRowIdToExpand) => {
            return {
                type: 'PRODUCT_EXPAND_SUCCESS',
                payload:
                    {
                        productRowIdToExpand
                    }
            }
        };
        
        
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
            //todo - make this do something....
            getLinkedProducts:()=>{
                return {
                    type: 'LINKED_PRODUCTS_REQUEST',
                    payload: {}
                }
            },
            fetchVariations: (filter) => {
                return function(dispatch, getState) {
                    console.log('in fetchVariations aq');
                    dispatch(getProductVariationsRequest());
                    AjaxHandler.fetchByFilter(filter, onSuccess.bind(this));
                    $('#products-loading-message').show();
            
                    function onSuccess(data) {
                        var variationsByParent = sortVariationsByParentId(
                            data.products,
                            filter.getParentProductId()
                        );
                        console.log('new variationsByParent in aqß: ', variationsByParent);
                
                        // set variations to state --- same effect as setting it to products.variationsByParent
                        dispatch(getProductVariationsRequestSuccess(variationsByParent))
                        dispatch(actionCreators.getLinkedProducts())
                
                        $('#products-loading-message').hide()
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
                return function(dispatch, getState) {
                    let state = getState();
                    let numberOfVisibleFixedColumns = getVisibleFixedColumns(state).length
                    dispatch({
                        type: "TAB_CHANGE",
                        payload: {
                            desiredTabKey,
                            numberOfVisibleFixedColumns
                        }
                    });
                }
            },
            resetHorizontalScrollbarIndex: () => {
                return {
                    type: "HORIZONTAL_SCROLLBAR_INDEX_RESET",
                    payload: {}
                }
        
            }
        };
    
        function dispatchExpandVariationWithoutAjaxRequest(dispatch, variationsByParent, productRowIdToExpand) {
            dispatch(getProductVariationsRequestSuccess(variationsByParent));
            dispatch(expandProductSuccess(productRowIdToExpand))
        }
    
        function dispatchExpandVariationWithAjaxRequest(dispatch, productRowIdToExpand) {
            let filter = new ProductFilter(null, productRowIdToExpand);
        
            dispatch(getProductVariationsRequest());
            AjaxHandler.fetchByFilter(filter, fetchProductVariationsCallback);
        
            function fetchProductVariationsCallback(data) {
                let variationsByParent = sortVariationsByParentId(data.products, filter.getParentProductId());
                dispatch(productVariationsGetRequestSuccess(variationsByParent));
                dispatch(expandProductSuccess(productRowIdToExpand))
            }
        }
    
    })();
    
    return actionCreators;
    
    
    function getVisibleFixedColumns(state) {
        return state.columns.filter((column) => {
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
    
   
    
});