import AjaxHandler from 'Product/Storage/Ajax';
import ProductFilter from 'Product/Filter/Entity'
import constants from 'Product/Components/ProductList/Config/constants'
import productLinkActions from 'Product/Components/ProductList/ActionCreators/productLinkActions'
import vatActions from 'Product/Components/ProductList/ActionCreators/vatActions'
import stateUtility from 'Product/Components/ProductList/stateUtility'

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
        storeStockModeOptions: (stockModeOptions) => {
            return {
                type: "STOCK_MODE_OPTIONS_STORE",
                payload: {
                    stockModeOptions
                }
            }
        },
        getProducts: (pageNumber, searchTerm, skuList) => {
            return async function(dispatch, getState) {
                pageNumber = pageNumber || 1;
                searchTerm = getState.customGetters.getCurrentSearchTerm() || '';
                skuList = skuList || [];
                let filter = new ProductFilter(searchTerm, null, null, skuList);
                filter.setPage(pageNumber);
                filter.setLimit(getState.customGetters.getPaginationLimit());
                let data = {};
                try {
                    dispatch(getProductsRequestStart());
                    data = await fetchProducts(filter);
                } catch (err) {
                    throw 'Unable to load products... error: ' + err;
                }
                dispatch(vatActions.extractVatFromProducts(data.products));

                dispatch(getProductsSuccess(data));
                if (!data.products.length) {
                    return data;
                }
                dispatch(productLinkActions.getLinkedProducts());
                return data;
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
                
                actionCreators.dispatchExpandVariationsWithAjaxRequest(dispatch, productRowIdToExpand);
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
        dispatchExpandVariationsWithAjaxRequest: async (dispatch, productRowId) => {
            let filter = new ProductFilter(null, productRowId);
            AjaxHandler.fetchByFilter(filter, fetchProductVariationsCallback);
            
            function fetchProductVariationsCallback(data) {
                $('#products-loading-message').hide();
                let variationsByParent = stateUtility.sortVariationsByParentId(data.products);
                dispatch(getProductVariationsRequestSuccess(variationsByParent));
                dispatch(expandProductSuccess(productRowId));
                let skusFromData = getSkusFromData(data);
                dispatch(productLinkActions.getLinkedProducts(skusFromData));
            }
        },
        getVariationsByParentProductId: (parentProductId) => {
            return async function(dispatch) {
                let filter = new ProductFilter(null, parentProductId);
                let data = await AjaxHandler.fetchByFilter(filter);
                
                let variationsByParent = stateUtility.sortVariationsByParentId(data.products);
                dispatch(getProductVariationsRequestSuccess(variationsByParent));
                return data;
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
})();

export default actionCreators;

function getVisibleFixedColumns(state) {
    return state.columns.filter((column) => {
        return column.fixed
    });
}

function getSkusFromData(data) {
    return data.products.map((product) => {
        return product.sku;
    });
}

function variationsHaveAlreadyBeenRequested(variationsByParent, productId) {
    return !!variationsByParent[productId]
}