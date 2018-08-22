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
        productLinks: {}
    };
    
    var ProductsReducer = reducerCreator(initialState, {
        "PRODUCTS_GET_REQUEST_SUCCESS": function(state, action) {
            // console.log('in productsReducer -R PRODUCTS_GET_REQUEST_SUCCESS action.payload.products : ' , action.payload.products);
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
            return newState;
        },
        "STOCK_LEVELS_UPDATE_REQUEST_SUCCESS": function(state, action) {
            
            const {response} = action.payload;
            let productsCopy = state.simpleAndParentProducts.slice();
            let visibleRowsCopy = state.visibleRows.slice();
            let variationsCopy = Object.assign({}, state.variationsByParent);
    
    
            console.log('STOCK_LEVELS_UPDATE_REQUEST_SUCCESS in ProductsReducer with stock_levels_update_request_success state : ', state , ' variationsCopy',variationsCopy);
    
    
            let newProducts = applyStockResponseToProducts(productsCopy, response);
            let newVisibleRows = applyStockResponseToProducts(visibleRowsCopy, response);
            let newVariations = applyStockResponseToVariations(productsCopy, variationsCopy, response);
            
            //
            let newState = Object.assign({}, state, {
                simpleAndParentProducts: newProducts,
                visibleRows: newVisibleRows,
                variations: newVariations
            });
            
            return newState;
        },
        "PRODUCT_VARIATIONS_GET_REQUEST_SUCCESS": function(state, action) {
            let newState = Object.assign({}, state, {
                variationsByParent: action.payload
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
            if (product.variationCount == 0) {
                if (!response.stock[product.sku]) {
                    return;
                }
                product.stock = response.stock[product.sku];
                return;
            }
        });
        return products;
    }
    
    function applyStockResponseToVariations(products, variations, response) {
        products.forEach((product) => {
            if (product.variationCount == 0) {
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
});