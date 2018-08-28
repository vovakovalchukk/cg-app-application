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
        const getProductLinksSuccess = (productLinks) => {
            return {
                type: "PRODUCT_LINKS_GET_REQUEST_SUCCESS",
                payload: {
                    productLinks
                }
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
        const getProductLinksRequest = (skusToFindLinkedProductsFor) => {
            console.log('!!! in getProductLinksRequest - AC with skusToFindLinkedProductsFor: ', skusToFindLinkedProductsFor);
            return $.ajax({
                url: PRODUCT_LINKS_URL,
                data: {
                    skus: JSON.stringify(skusToFindLinkedProductsFor)
                },
                type: 'POST'
            });
        };
        const updateStockLevelsRequest = (productSku) => {
            return $.ajax({
                url: '/products/stock/ajax/' + productSku,
                type: 'GET'
            });
        };
        const fetchingProductLinksStart = (skusToFindLinkedProductsFor) => {
            return {
                type: "FETCHING_LINKED_PRODUCTS_START",
                payload: {
                    skusToFindLinkedProductsFor
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
                return async function(dispatch) {
                    pageNumber = pageNumber || 1;
                    searchTerm = searchTerm || '';
                    skuList = skuList || [];
                    let filter = new ProductFilter(searchTerm, null, null, skuList);
                    filter.setPage(pageNumber);
                    try {
                        dispatch(getProductsRequestStart());
                        let data = await fetchProducts(filter);
                        dispatch(getProductsSuccess(data));
                        dispatch(actionCreators.getLinkedProducts());
                    } catch (err) {
                        throw 'Unable to load products';
                    }
                }
            },
            getLinkedProducts: (productSkus) => {
                return async function(dispatch, getState) {
                    let state = getState();
                    if (!state.account.features.linkedProducts) {
                        return;
                    }
                    
                    //todo need to trigger an action to set the linkStatus to 'loading' or something
                    
                    window.triggerEvent('fetchingProductLinksStart');
                    
                    
                    let skusToFindLinkedProductsFor = [];
                    if (!productSkus) {
                        skusToFindLinkedProductsFor = getSkusToFindLinkedProductsFor(state.products);
                    } else {
                        skusToFindLinkedProductsFor = productSkus;
                    }
                    
                    // console.log('skusToFindLinkedProductsFor before dispatch : '  , skusToFindLinkedProductsFor);
                    dispatch(fetchingProductLinksStart(skusToFindLinkedProductsFor));
                    
                    let formattedSkus = formatSkusForLinkApi(skusToFindLinkedProductsFor);
                    
                    try {
                        let response = await getProductLinksRequest(formattedSkus);
                        dispatch(getProductLinksSuccess(response.productLinks));
                    } catch (error) {
                        console.warn(error);
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
                
                console.log('in fetchProductVariationsCallbackw with data.products: ', data.products);
                
                
                //todo - change this to - getLinkedProductsForSpecificIds
                dispatch(actionCreators.getLinkedProducts(newVariationSkus));
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
        var skusToFindLinkedProductsFor = [];
        products.visibleRows.forEach((product) => {
            if (product.sku) {
                skusToFindLinkedProductsFor.push(product.sku);
            }
        });
        return skusToFindLinkedProductsFor;
    }
    
    function formatSkusForLinkApi(skusToFindLinkedProductsFor) {
        let linkObj = {};
        skusToFindLinkedProductsFor.forEach((sku) => {
            linkObj[sku] = sku;
        });
        return linkObj;
    }
});