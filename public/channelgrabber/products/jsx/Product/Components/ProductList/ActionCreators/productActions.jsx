import AjaxHandler from 'Product/Storage/Ajax';
import ProductFilter from 'Product/Filter/Entity'
import constants from 'Product/Components/ProductList/Config/constants'
import productLinkActions from 'Product/Components/ProductList/ActionCreators/productLinkActions'
import vatActions from 'Product/Components/ProductList/ActionCreators/vatActions'
import nameActions from 'Product/Components/ProductList/ActionCreators/nameActions'
import stateUtility from 'Product/Components/ProductList/stateUtility'
import stockActions from '../ActionCreators/stockActions';

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
    const expandProductSuccess = (productIdToExpand) => {
        return {
            type: 'PRODUCT_EXPAND_SUCCESS',
            payload:
                {
                    productIdToExpand
                }
        }
    };
    const expandProductsSuccess = (productIdsToExpand) => {
        return {
            type: 'PRODUCTS_EXPAND_SUCCESS',
            payload:
                {
                    productIdsToExpand
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
    const getVariationsFromProducts = function(data) {
        return {
            type: "GET_VARIATIONS_FROM_PRODUCTS",
            payload: data
        }
    };
    const getProductsError = function() {
        return {
            type: "PRODUCTS_GET_REQUEST_ERROR"
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

    const expandHandler = (shouldNotExpand, isMultipleProducts, dispatch, productIds) => {
        if (shouldNotExpand) {
            return;
        }
        if (isMultipleProducts) {
            dispatch(expandProductsSuccess(productIds));
            return;
        }
        dispatch(expandProductSuccess(productIds));
    };

    const handleNewVariations = (data, productIds, dispatch, isMultipleProducts, shouldNotExpand) => {
        $('#products-loading-message').hide();
        let variationsByParent = stateUtility.sortVariationsByParentId(data.products);
        dispatch(getProductVariationsRequestSuccess(variationsByParent));

        expandHandler(shouldNotExpand, isMultipleProducts, dispatch, productIds);

        let skusFromData = getSkusFromData(data);
        dispatch(productLinkActions.getLinkedProducts(skusFromData));

        dispatch(vatActions.extractVatFromProducts(data.products));
        dispatch(stockActions.extractIncPOStockInAvailableFromProducts(data.products));
        dispatch(stockActions.storeLowStockThreshold(data.products));
    };

    const handleSkuSpecificSearch = (data, searchTerm, dispatch) => {
        for (let product of data.products) {
            if(!product.variationCount){
                continue;
            }
            for (let productVariation of product.variations) {
                if (productVariation.sku === searchTerm) {
                    dispatch(actionCreators.expandProduct(product.id));
                }
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
        storeStockModeOptions: (stockModeOptions) => {
            return {
                type: "STOCK_MODE_OPTIONS_STORE",
                payload: {
                    stockModeOptions
                }
            }
        },
        storeIncPOStockInAvailableOptions: (incPOStockInAvailableOptions) => {
            return {
                type: "INC_PO_STOCK_IN_AVAIL_STORE",
                payload: {
                    incPOStockInAvailableOptions
                }
            }
        },
        getProducts: (pageNumber, searchTerm, skuList) => {
            return async function(dispatch, getState) {
                let state = getState();
                pageNumber = pageNumber || 1;
                searchTerm = getState.customGetters.getCurrentSearchTerm() || '';
                skuList = skuList || [];
                let filter = new ProductFilter(
                    searchTerm,
                    null,
                    null,
                    skuList
                );
                filter.setPage(pageNumber);
                filter.setLimit(getState.customGetters.getPaginationLimit());
                filter.setEmbedVariationsAsLinks(false);

                if (searchTerm) {
                    filter.setEmbedVariationsAsLinks(false);
                }

                let data = {};
                try {
                    dispatch(getProductsRequestStart());
                    data = await fetchProducts(filter);
                } catch (err) {
                    dispatch(getProductsError(err));
                    throw 'Unable to load products... error: ' + err;
                }

                dispatch(vatActions.extractVatFromProducts(data.products));
                dispatch(stockActions.extractIncPOStockInAvailableFromProducts(data.products));
                dispatch(nameActions.extractNamesFromProducts(data.products));

                dispatch(getProductsSuccess(data));
                dispatch(getVariationsFromProducts(data));

                if (isExpandableSkuSearch(data, searchTerm)) {
                    handleSkuSpecificSearch(data, searchTerm, dispatch);
                }

                if (!data.products.length) {
                    return data;
                }
                dispatch(productLinkActions.getLinkedProducts());

                if (state.accounts.features.preFetchVariations) {
                    dispatch(actionCreators.dispatchGetAllVariations());
                }

                dispatch(stockActions.storeLowStockThreshold(data.products));
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
        expandAllProducts(haveFetchedAlready) {
            return async function(dispatch, getState) {
                let productIdsToExpand = stateUtility.getAllParentProductIds(getState().products);

                if (!haveFetchedAlready) {
                    return await actionCreators.dispatchExpandAllVariationsWithAjaxRequest(dispatch, productIdsToExpand);
                }
                actionCreators.dispatchExpandAllVariationsWithoutAjaxRequest(dispatch, productIdsToExpand);
            }
        },
        collapseAllProducts() {
            return {
                type: "ALL_PRODUCTS_COLLAPSE"
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
                    actionCreators.dispatchExpandVariationWithoutAjaxRequest(dispatch, variationsByParent, productRowIdToExpand);
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
        dispatchGetAllVariations: () => {
            return async function(dispatch, getState) {
                let productIds = stateUtility.getAllParentProductIds(getState().products);
                let filter = new ProductFilter(null, productIds);
                await AjaxHandler.fetchByFilter(filter, data => {
                    handleNewVariations(data, productIds, dispatch, true, true);
                });
            }
        },
        dispatchExpandAllVariationsWithAjaxRequest: async (dispatch, productIds) => {
            let filter = new ProductFilter(null, productIds);

            await AjaxHandler.fetchByFilter(filter, data => {
                handleNewVariations(data, productIds, dispatch, true);
            });
        },
        dispatchExpandAllVariationsWithoutAjaxRequest: (dispatch, productIds) => {
            dispatch(expandProductsSuccess(productIds));
        },
        dispatchExpandVariationsWithAjaxRequest: (dispatch, productId) => {
            let filter = new ProductFilter(null, productId);

            AjaxHandler.fetchByFilter(filter, data => {
                handleNewVariations(data, productId, dispatch, false);
            });
        },
        dispatchExpandVariationWithoutAjaxRequest: async (dispatch, variationsByParent, productRowIdToExpand) => {
            dispatch(getProductVariationsRequestSuccess(variationsByParent));
            dispatch(expandProductSuccess(productRowIdToExpand));
            let data = {
                products: variationsByParent[productRowIdToExpand]
            };
            let skusFromData = getSkusFromData(data);
            dispatch(productLinkActions.getLinkedProducts(skusFromData));
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

function isExpandableSkuSearch(data, searchTerm) {
    return data.products.length < 5 && searchTerm;
}