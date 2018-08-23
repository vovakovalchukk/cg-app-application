define([
    'Product/Storage/Ajax',
    'Product/Filter/Entity'
], function(
    AjaxHandler,
    ProductFilter
) {
    "use strict";
    
    const PRODUCTS_URL = "/products/ajax";
    const PRODUCT_LINKS_URL = "/products/links/ajax";
    const INITIAL_VARIATION_COUNT = 2;
    
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
            return {
                type: "PRODUCTS_GET_REQUEST_SUCCESS",
                payload: data
                
            }
        };
        let getProductLinksSuccess = (productLinks) => {
            return {
                type: "PRODUCT_LINKS_GET_REQUEST_SUCCESS",
                payload: {
                    productLinks
                }
            }
        };
        let updateStockLevelsRequestSuccess = (response) => {
            return {
                type: "STOCK_LEVELS_UPDATE_REQUEST_SUCCESS",
                payload: {
                    response
                }
            }
        };
        let updateFetchingStockLevelsForSkus = (fetchingStockLevelsForSkus) => {
            return {
                type: "FETCHING_STOCK_LEVELS_FOR_SKUS_UPDATE",
                payload: {
                    fetchingStockLevelsForSkus
                }
            }
        };
        
        return {
            storeAccountFeatures: (features) => {
                return {
                    type: "ACCOUNT_FEATURES_STORE",
                    payload: {
                        features
                    }
                }
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
                return function(dispatch) {
                    pageNumber = pageNumber || 1;
                    searchTerm = searchTerm || '';
                    skuList = skuList || [];
                    var filter = new ProductFilter(searchTerm, null, null, skuList);
                    filter.setPage(pageNumber);
                    
                    dispatch(getProductsRequest());
                    
                    fetchProducts(filter, successCallback, errorCallback);
                    
                    function successCallback(data) {
                        dispatch(getProductsSuccess(data));
                        
                        let allDefaultVariationIds = getAllDefaultVariationIdsFromProducts(data.products);
                        
                        if (allDefaultVariationIds.length == 0) {
                            dispatch(actionCreators.getLinkedProducts())
                            return;
                        }
                        
                        var productFilter = new ProductFilter(null, null, allDefaultVariationIds);
                        dispatch(actionCreators.getVariations(productFilter))
                    }
                    
                    function errorCallback(err) {
                        throw 'Unable to load products';
                    }
                }
            },
            getLinkedProducts: () => {
                return function(dispatch, getState) {
                    let state = getState();
                    if (!state.account.features.linkedProducts) {
                        return;
                    }
                    window.triggerEvent('fetchingProductLinksStart');
                    
                    let skusToFindLinkedProductsFor = getSkusToFindLinkedProductsFor(state.products);
                    
                    $.ajax({
                        url: PRODUCT_LINKS_URL,
                        data: {
                            skus: JSON.stringify(skusToFindLinkedProductsFor)
                        },
                        type: 'POST',
                        success: function(response) {
                            dispatch(getProductLinksSuccess(response.productLinks));
                        },
                        error: function(error) {
                            console.warn(error);
                        }
                    });
                }
            },
            getUpdatedStockLevels(productSku) {
                return function(dispatch, getState) {
                    var fetchingStockLevelsForSkus = getState().list.fetchingUpdatedStockLevelsForSkus;
                    fetchingStockLevelsForSkus[productSku] = true;
                    
                    dispatch(updateFetchingStockLevelsForSkus(fetchingStockLevelsForSkus));
                    updateStockLevelsRequest();
                    
                    function updateStockLevelsRequest() {
                        $.ajax({
                            url: '/products/stock/ajax/' + productSku,
                            type: 'GET',
                            success: function(response) {
                                dispatch(updateStockLevelsRequestSuccess(response));
                            },
                            error: function(error) {
                                console.error(error);
                            }
                        });
                        fetchingStockLevelsForSkus[productSku] = false;
                        dispatch(updateFetchingStockLevelsForSkus(fetchingStockLevelsForSkus));
                    }
                }
            },
            getVariations: (filter) => {
                return function(dispatch, getState) {
                    dispatch(getProductVariationsRequest());
                    AjaxHandler.fetchByFilter(filter, onSuccess.bind(this));
                    $('#products-loading-message').show();
                    
                    function onSuccess(data) {
                        var variationsByParent = sortVariationsByParentId(
                            data.products,
                            filter.getParentProductId()
                        );
                        dispatch(getProductVariationsRequestSuccess(variationsByParent));
                        dispatch(actionCreators.getLinkedProducts());
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
                dispatch(getProductVariationsRequestSuccess(variationsByParent));
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
    
    function getSkusToFindLinkedProductsFor(products) {
        var skusToFindLinkedProductsFor = {};
        for (var productId in products.variations) {
            products.variations[productId].forEach(function(variation) {
                skusToFindLinkedProductsFor[variation.sku] = variation.sku;
            });
        }
        products.visibleRows.forEach(function(product) {
            if (product.variationCount == 0 && product.sku) {
                skusToFindLinkedProductsFor[product.sku] = product.sku;
            }
        });
        return skusToFindLinkedProductsFor;
    }
    
    function getAllDefaultVariationIdsFromProducts(products) {
        var allDefaultVariationIds = [];
        products.forEach((product) => {
            var defaultVariationIds = product.variationIds.slice(0, INITIAL_VARIATION_COUNT);
            allDefaultVariationIds = allDefaultVariationIds.concat(defaultVariationIds);
        });
        return allDefaultVariationIds;
    }
});