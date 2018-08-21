define([
    'Product/Storage/Ajax',
    'Product/Filter/Entity'
], function(
    AjaxHandler,
    ProductFilter
) {
    "use strict";
    
    const PRODUCTS_URL = "/products/ajax";
    
    var actionCreators = (function() {
        
        let self = {};
        
        let getProductsRequest = () => {
            return {
                type: 'PRODUCTS_GET_REQUEST'
            }
        };
        let getProductVariationsRequest = () => {
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
        let fetchProducts = function(filter, successCallback, errorCallback) {
            self.productsRequest = $.ajax({
                'url': PRODUCTS_URL,
                'data': {'filter': filter.toObject()},
                'method': 'POST',
                'dataType': 'json',
                'success': successCallback.bind(this),
                'error': errorCallback.bind(this)
            });
        };
        let getProductsSuccess = function(data) {
            console.log('in AQ getProductsSuccess with Data: ', data );
            return {
                type: "PRODUCTS_GET_REQUEST_SUCCESS",
                payload: data
                
            }
        };
        
        return {
            initialSimpleAndParentProductsLoad: (products) => {
            
            },
            productsLinksLoad: (allProductsLinks) => {
                return {
                    type: "PRODUCTS_LINKS_LOAD",
                    payload: {
                        allProductsLinks
                    }
                }
            },
            getProducts: (pageNumber, searchTerm, skuList) => {
                return function(dispatch, getState) {
                    pageNumber = pageNumber || 1;
                    searchTerm = searchTerm || '';
                    skuList = skuList || [];
                    $('#products-loading-message').show();
                    var filter = new ProductFilter(searchTerm, null, null, skuList);
                    filter.setPage(pageNumber);
                    
                    console.log('Provider - about to fetch with filter: ', filter);
                    dispatch(getProductsRequest());
                    
                    fetchProducts(filter, successCallback, errorCallback);
                    
                    function successCallback(data) {
                        console.log('Provider -in successCallback of performProductsRequest');
                        
                        dispatch(getProductsSuccess(data))
                        // var self = this;
                        // this.setState({
                        //     products: result.products,
                        //     maxListingsPerAccount: result.maxListingsPerAccount,
                        //     pagination: result.pagination,
                        //     initialLoadOccurred: true,
                        //     searchTerm: searchTerm,
                        //     skuList: skuList,
                        //     accounts: result.accounts,
                        //     createListingsAllowedChannels: result.createListingsAllowedChannels,
                        //     createListingsAllowedVariationChannels: result.createListingsAllowedVariationChannels,
                        //     productSearchActive: result.productSearchActive
                        // }, function() {
                        //     $('#products-loading-message').hide();
                        //     self.onNewProductsReceived();
                        // });
                    }
                    
                    function errorCallback(err) {
                        console.log('in Provider error callback with err: ', err);
                        
                        
                        throw 'Unable to load products';
                    }
                    
                    
                }
            },
            //todo - make this do something....
            getLinkedProducts: () => {
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