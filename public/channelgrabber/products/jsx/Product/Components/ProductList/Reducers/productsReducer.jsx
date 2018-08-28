define([
    'Common/Reducers/creator',
    'Product/Components/ProductList/stateUtility'
], function(
    reducerCreator,
    stateUtility
) {
    "use strict";
    
    var initialState = {
        completeInitialLoads: {
            simpleAndParentProducts: false
        },
        simpleAndParentProducts: [],
        variationsByParent: [],
        allProductsLinks: {}
    };
    
    const LINK_STATUSES = {
        fetching: "fetching",
        success: "success"
    }
    
    var ProductsReducer = reducerCreator(initialState, {
        "PRODUCTS_GET_REQUEST_SUCCESS": function(state, action) {
            let newState = Object.assign({}, state, {
                completeInitialLoads: {
                    simpleAndParentProducts: true
                },
                simpleAndParentProducts: action.payload.products,
                visibleRows: action.payload.products
            });
            return newState;
        },
        "PRODUCT_LINKS_GET_REQUEST_SUCCESS": function(state, action) {
            let newState = Object.assign({}, state, {
                allProductsLinks: action.payload.productLinks
            });
            window.triggerEvent('fetchingProductLinksStop');
            return newState;
        },
        "STOCK_LEVELS_UPDATE_REQUEST_SUCCESS": function(state, action) {
            const {response} = action.payload;
            let productsCopy = state.simpleAndParentProducts.slice();
            let visibleRowsCopy = state.visibleRows.slice();
            let variationsCopy = Object.assign({}, state.variationsByParent);
            
            let newProducts = applyStockResponseToProducts(productsCopy, response);
            let newVisibleRows = applyStockResponseToProducts(visibleRowsCopy, response);
            let newVariations = applyStockResponseToVariations(visibleRowsCopy, variationsCopy, response);
            
            let newState = Object.assign({}, state, {
                simpleAndParentProducts: newProducts,
                visibleRows: newVisibleRows,
                variations: newVariations
            });
            
            return newState;
        },
        "PRODUCT_VARIATIONS_GET_REQUEST_SUCCESS": function(state, action) {
            let newVariationsByParent = Object.assign({}, state.variationsByParent, action.payload);
            let newState = Object.assign({}, state, {
                variationsByParent: newVariationsByParent
            });
            return newState;
        },
        "PRODUCT_EXPAND_REQUEST": function(state, action) {
            let currentVisibleProducts = state.visibleRows.slice();
            
            currentVisibleProducts = changeExpandStatus(
                currentVisibleProducts,
                action.payload.productRowIdToExpand,
                'loading'
            );
            
            let newState = Object.assign({}, state, {
                visibleRows: currentVisibleProducts
            });
            return newState;
        },
        "PRODUCT_EXPAND_SUCCESS": function(state, action) {
            let currentVisibleProducts = state.visibleRows.slice();
            let productRowIdToExpand = action.payload.productRowIdToExpand;
            
            let parentProductIndex = stateUtility.getProductIndex(currentVisibleProducts, productRowIdToExpand);
            
            let rowsToAdd = state.variationsByParent[action.payload.productRowIdToExpand];
            currentVisibleProducts.splice(
                parentProductIndex + 1,
                0,
                ...rowsToAdd
            );
            currentVisibleProducts = changeExpandStatus(
                currentVisibleProducts,
                action.payload.productRowIdToExpand,
                'expanded'
            );
            
            let newState = Object.assign({}, state, {
                visibleRows: currentVisibleProducts
            });
            return newState;
        },
        "PRODUCT_COLLAPSE": function(state, action) {
            let currentVisibleProducts = state.visibleRows.slice();
            let productRowId = action.payload.productRowIdToCollapse;
            
            let parentProductIndex = stateUtility.getProductIndex(currentVisibleProducts, productRowId);
            
            let numberOfRowsToRemove = state.variationsByParent[productRowId].length;
            
            currentVisibleProducts.splice(
                parentProductIndex + 1,
                numberOfRowsToRemove
            );
            
            currentVisibleProducts = changeExpandStatus(
                currentVisibleProducts,
                productRowId,
                'collapsed'
            );
            
            let newState = Object.assign({}, state, {
                visibleRows: currentVisibleProducts
            });
            return newState;
        },
        "FETCHING_LINKED_PRODUCTS_START": function(state,action){
            const {skusToFindLinkedProductsFor} = action.payload;
            
            let variationsByParentCopy = Object.assign({}, state.variationsByParent);
            let visibleRowsCopy = state.visibleRows.slice();
            
            let newVariationsByParent = applyFetchingStatusToVariations(
                variationsByParentCopy,
                skusToFindLinkedProductsFor,
                LINK_STATUSES.fetching
            );
            let newVisibleRows = applyFetchingStatusToNewVisibleRows(
                visibleRowsCopy,
                skusToFindLinkedProductsFor,
                LINK_STATUSES.fetching
            );
            
            return Object.assign({}, state, {
                variationsByParent: newVariationsByParent,
                visibleRows: newVisibleRows
            })
        }
    });
    
    return ProductsReducer;
    
    function changeExpandStatus(products, productId, desiredStatus) {
        let productRowIndex = products.findIndex((product) => {
            return product.id === productId;
        });
        products[productRowIndex].expandStatus = desiredStatus;
        return products;
    }
    
    function applyStockResponseToProducts(products, response) {
        products.forEach((product) => {
            if (product.variationCount !== 0) {
                return;
            }
            if (!response.stock[product.sku]) {
                return;
            }
            product.stock = response.stock[product.sku];
        });
        return products;
    }
    
    function applyStockResponseToVariations(products, variations, response) {
        products.forEach((product) => {
            if (product.variationCount == 0 || !variations[product.id]) {
                return;
            }
            variations[product.id].forEach(function(product) {
                if (!response.stock[product.sku]) {
                    return;
                }
                product.stock = response.stock[product.sku];
                return;
            });
        });
        return variations;
    }
    
    function applyFetchingStatusToVariations(variationsByParent, skusToFindLinkedProductsFor, DESIRED_LINK_STATUS){
        Object.keys(variationsByParent).map(parentId => {
            let variations =  variationsByParent[parentId];
            variations.forEach((variation, i)=>{
                if(skusToFindLinkedProductsFor.indexOf(variation.sku) < 0){
                    return;
                }
                variationsByParent[parentId][i]["linkStatus"] = DESIRED_LINK_STATUS
            });
        });
        return variationsByParent;
    }
    
    function applyFetchingStatusToNewVisibleRows(visibleRowsCopy, skusToFindLinkedProductsFor, DESIRED_LINK_STATUS){
        console.log('in applyFetchingStatusToNewVisibleRows visibleRowsCopy: ' , visibleRowsCopy, ' skusToFindLinkedProductsFor: ' , skusToFindLinkedProductsFor);
        visibleRowsCopy.forEach((row,i)=>{
            if(skusToFindLinkedProductsFor.indexOf(row.sku) < 0){
                return;
            }
            visibleRowsCopy[i]["linkStatus"] = DESIRED_LINK_STATUS;
        });
        return visibleRowsCopy;
    }
});