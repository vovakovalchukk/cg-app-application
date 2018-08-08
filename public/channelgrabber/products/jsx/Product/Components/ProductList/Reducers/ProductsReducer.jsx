define([
    'Common/Reducers/creator',
    'Product/Components/ProductList/stateFilters'
], function(
    reducerCreator,
    stateFilters
) {
    "use strict";
    var initialState = {
        completeInitialLoads: {
            simpleAndParentProducts: false
        },
        simpleAndParentProducts: [],
        variationsByParent:[]
    };
    
    var ProductsReducer = reducerCreator(initialState, {
        "INITIAL_SIMPLE_AND_PARENT_PRODUCTS_LOAD": function(state, action) {
            // console.log('r-in initial products load with action.payload.products: ', action.payload.products);
            let newState = Object.assign({}, state, {
                completeInitialLoads: {
                    simpleAndParentProducts: true
                },
                simpleAndParentProducts: action.payload.products,
                visibleRows: action.payload.products
            });
            return newState;
        },
        "PRODUCT_VARIATIONS_GET_REQUEST_SUCCESS": function(state,action){
            // console.log('r- PRODUCT_VARIATIONS_GET_REQUEST_SUCCESS action : ' , action , ' state: ' , state);
            let newState = Object.assign({}, state,{
                variationsByParent:action.payload
            });
            return newState;
        },
        "PRODUCT_EXPAND": function(state, action) {
            console.log('r- in product expand with action: ', action, ' state: ', state);
            let currentVisibleProducts = state.visibleRows.slice();
            let productRowIdToExpand = action.payload.productRowIdToExpand;
            
            let parentProductIndex = stateFilters.getProductIndex(currentVisibleProducts, productRowIdToExpand);
            
            let rowsToAdd = state.variationsByParent[action.payload.productRowIdToExpand];
            currentVisibleProducts.splice(
                parentProductIndex + 1,
                0,
                ...rowsToAdd
            );
            
            let newState = Object.assign({}, state, {
               visibleRows: currentVisibleProducts
            });
            return newState;
        },
        "PRODUCT_COLLAPSE": function(state,action){
            console.log('r- in product collapse with action: ', action, ' state: ', state);
            let currentVisibleProducts = state.visibleRows.slice();
            let productRowIdToExpand = action.payload.productRowIdToExpand;
    
            let parentProductIndex = stateFilters.getProductIndex(currentVisibleProducts, productRowIdToExpand);
            
            let numberOfRowsToRemove = state.variationsByParent[action.payload.productRowIdToExpand].length;
    
            currentVisibleProducts.splice(
                parentProductIndex + 1,
                numberOfRowsToRemove
            );
    
            let newState = Object.assign({}, state, {
                visibleRows: currentVisibleProducts
            });
            console.log('newState after collapse: ', newState);
            
            return newState;
        }
        
    });
    
    return ProductsReducer;
});