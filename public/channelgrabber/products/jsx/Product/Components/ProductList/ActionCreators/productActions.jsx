define([
    'Product/Storage/Ajax',
    'Product/Filter/Entity',
    'Product/Components/ProductList/Config/constants',
    'Product/Components/ProductList/ActionCreators/productLinkActions',
    'Product/Components/ProductList/stateUtility'
], function(
    AjaxHandler,
    ProductFilter,
    constants,
    productLinkActions,
    stateUtility
) {
    "use strict";
    
    const {PRODUCTS_URL} = constants;
    
    var actionCreators = (function() {
        let self = {};
        
        const getProductsRequestStart = () => {
            return {
                type: 'PRODUCTS_GET_REQUEST_START'
            }
        };
        const getProductVariationsRequestSuccess = (variationsByParent) => {
            return {
                type: 'PRODUCT_VARIATIONS_GET_REQUEST_SUCCESS',
                payload: variationsByParent
            };
        };
        const expandProductSuccess = (productRowIdToExpand) => {
            return {
                type: 'PRODUCT_EXPAND_SUCCESS',
                payload:
                    {
                        productRowIdToExpand
                    }
            }
        };
        const fetchProducts = function(filter) {
            return self.productsRequest = $.ajax({
                'url': PRODUCTS_URL,
                'data': {'filter': filter.toObject()},
                'method': 'POST',
                'dataType': 'json'
            });
        };
        const getProductsSuccess = function(data) {
            return {
                type: "PRODUCTS_GET_REQUEST_SUCCESS",
                payload: data
            }
        };
        const updateStockLevelsRequestSuccess = (response) => {
            return {
                type: "STOCK_LEVELS_UPDATE_REQUEST_SUCCESS",
                payload: {
                    response
                }
            }
        };
        const updateFetchingStockLevelsForSkus = (fetchingStockLevelsForSkus) => {
            return {
                type: "FETCHING_STOCK_LEVELS_FOR_SKUS_UPDATE",
                payload: {
                    fetchingStockLevelsForSkus
                }
            }
        };
        const updateStockLevelsRequest = (productSku) => {
            return $.ajax({
                url: '/products/stock/ajax/' + productSku,
                type: 'GET'
            });
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
            getProducts: (pageNumber, searchTerm, skuList) => {
                return async function(dispatch, getState) {
                    const state = getState();
                    pageNumber = pageNumber || 1;
                    searchTerm = stateUtility.getCurrentSearchTerm(state) || '';
                    skuList = skuList || [];
                    let filter = new ProductFilter(searchTerm, null, null, skuList);
                    filter.setPage(pageNumber);
                    filter.setLimit(stateUtility.getPaginationLimit(state));
                    try {
                        dispatch(getProductsRequestStart());
                        let data = await fetchProducts(filter);
                        dispatch(getProductsSuccess(data));
                        dispatch(productLinkActions.getLinkedProducts());
                        return data;
                    } catch (err) {
                        throw 'Unable to load products... error: ' + err;
                    }
                }
            },
            getUpdatedStockLevels(productSku) {
                return async function(dispatch, getState) {
                    var fetchingStockLevelsForSkus = getState().list.fetchingUpdatedStockLevelsForSkus;
                    fetchingStockLevelsForSkus[productSku] = true;
                    dispatch(updateFetchingStockLevelsForSkus(fetchingStockLevelsForSkus));
                    try {
                        let response = await updateStockLevelsRequest(productSku);
                        dispatch(updateStockLevelsRequestSuccess(response));
                    } catch (err) {
                        console.error(error);
                    }
                    fetchingStockLevelsForSkus[productSku] = false;
                    dispatch(updateFetchingStockLevelsForSkus(fetchingStockLevelsForSkus));
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
                    let numberOfVisibleFixedColumns = getVisibleFixedColumns(state).length;
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
            },
            moveVerticalScrollbarToTop: () => {
                return function(dispatch) {
                    dispatch({
                        type: "VERTICAL_SCROLLBAR_SET_TO_0",
                        payload: {}
                    });
                    dispatch({
                        type: "HORIZONTAL_SCROLLBAR_INDEX_RESET",
                        payload: {}
                    })
                };
            }
        };
        
        function dispatchExpandVariationWithoutAjaxRequest(dispatch, variationsByParent, productRowIdToExpand) {
            dispatch(getProductVariationsRequestSuccess(variationsByParent));
            dispatch(expandProductSuccess(productRowIdToExpand))
        }
        
        function dispatchExpandVariationWithAjaxRequest(dispatch, productRowIdToExpand) {
            let filter = new ProductFilter(null, productRowIdToExpand);
            AjaxHandler.fetchByFilter(filter, fetchProductVariationsCallback);
            
            function fetchProductVariationsCallback(data) {
                $('#products-loading-message').hide();
                let variationsByParent = sortVariationsByParentId(data.products, filter.getParentProductId());
                dispatch(getProductVariationsRequestSuccess(variationsByParent));
                dispatch(expandProductSuccess(productRowIdToExpand));
                
                let newVariationSkus = data.products.map((product) => {
                    return product.sku;
                });
                
                dispatch(actionCreators.getLinkedProducts(newVariationSkus));
            }
        }
    })();
    
    return actionCreators;
    
    function getVisibleFixedColumns(state) {
        console.log('in getVisibleFixedColumns with state: ' , state);
        // /
        
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