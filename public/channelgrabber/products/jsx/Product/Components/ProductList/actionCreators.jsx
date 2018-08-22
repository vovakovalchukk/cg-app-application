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
        let updateStockLevelsRequestSuccess = (response) =>{
            return {
                type: "STOCK_LEVELS_UPDATE_REQUEST_SUCCESS",
                payload: {
                    response
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
                return function(dispatch, getState) {
                    pageNumber = pageNumber || 1;
                    searchTerm = searchTerm || '';
                    skuList = skuList || [];
                    $('#products-loading-message').show();
                    var filter = new ProductFilter(searchTerm, null, null, skuList);
                    filter.setPage(pageNumber);
                    
                    dispatch(getProductsRequest());
                    
                    fetchProducts(filter, successCallback, errorCallback);
                    
                    function successCallback(data) {
                        console.log('Provider -in successCallback of performProductsRequest');
                        
                        dispatch(getProductsSuccess(data));
                        $('#products-loading-message').hide();
                        
                        var allDefaultVariationIds = [];
                        data.products.forEach((product) => {
                            var defaultVariationIds = product.variationIds.slice(0, INITIAL_VARIATION_COUNT);
                            allDefaultVariationIds = allDefaultVariationIds.concat(defaultVariationIds);
                        })
                        
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
                    console.log('in getLinkedPRoducts getState: ', getState());
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
                            dispatch(getProductLinksSuccess(response.productLinks))
                            window.triggerEvent('fetchingProductLinksStop')
                        },
                        error: function(error) {
                            console.warn(error);
                        }
                    });
                }
            },
            getUpdatedStockLevels(productSku) {
                return function(dispatch, getState) {
    
                    console.log('in getUpdatedStockLevels with productSku: ', productSku);
                    // var fetchingStockLevelsForSkuState = this.state.fetchingUpdatedStockLevelsForSkus;
                    // fetchingStockLevelsForSkuState[productSku] = true;
                    //
                    // dispatch fetching stocklevels for sku[productSku] <- hold this information in a reducer
    
                    var updateStockLevelsRequest = function() {
                        $.ajax({
                            url: '/products/stock/ajax/' + productSku,
                            type: 'GET',
                            success: function(response) {
                                
                                dispatch(updateStockLevelsRequestSuccess(response));
                               //
                               //  let stateCopy = getState();
                               //  stateCopy.products.simpleAndParentProducts.forEach((product)=>{
                               //          if (product.variationCount == 0) {
                               //              if (!response.stock[product.sku]) {
                               //                  return;
                               //              }
                               //              product.stock = response.stock[product.sku];
                               //              return;
                               //          }
                               //          stateCopy.products.variations[product.id].forEach(function(product) {
                               //              if (!response.stock[product.sku]) {
                               //                  return;
                               //              }
                               //              product.stock = response.stock[product.sku];
                               //              return;
                               //          });
                               //  });
                               // stateCopy.fetchingUpdatedStockLevelsForSkus[productSku] = false;
                               // //todo send back new state
                               //
                               // // this.setState(newState);
                                
                                
                                
                                // var newState = this.state;
                                // newState.products.forEach(function(product) {
                                //     if (product.variationCount == 0) {
                                //         if (!response.stock[product.sku]) {
                                //             return;
                                //         }
                                //         product.stock = response.stock[product.sku];
                                //         return;
                                //     }
                                //     newState.variations[product.id].forEach(function(product) {
                                //         if (!response.stock[product.sku]) {
                                //             return;
                                //         }
                                //         product.stock = response.stock[product.sku];
                                //         return;
                                //     });
                                // });
                                // newState.fetchingUpdatedStockLevelsForSkus[productSku] = false;
                                // this.setState(newState);
                            }.bind(this),
                            error: function(error) {
                                console.error(error);
                            }
                        });
                    }.bind(this);
                    // this.setState(
                    //     fetchingStockLevelsForSkuState,
                    //     updateStockLevelsRequest
                    // );
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
                        // set variations to state --- same effect as setting it to products.variationsByParent
                        dispatch(getProductVariationsRequestSuccess(variationsByParent))
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
    
    function getSkusToFindLinkedProductsFor (products){
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
});