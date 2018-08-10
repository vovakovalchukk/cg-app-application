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
        "PRODUCT_EXPAND_REQUEST": function(state, action) {
            console.log('r- in PRODUCT_EXPAND_REQUEST with action: ', action, ' state: ', state);
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
            console.log('r- in product_expand_success with action: ', action, ' state: ', state);
            let currentVisibleProducts = state.visibleRows.slice();
            let productRowIdToExpand = action.payload.productRowIdToExpand;
            
            let parentProductIndex = stateFilters.getProductIndex(currentVisibleProducts, productRowIdToExpand);
            
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
        "PRODUCT_COLLAPSE": function(state,action){
            // console.log('r- in product collapse with action: ', action, ' state: ', state);
            let currentVisibleProducts = state.visibleRows.slice();
            let productRowId = action.payload.productRowIdToCollapse;
    
            let parentProductIndex = stateFilters.getProductIndex(currentVisibleProducts, productRowId);
            
            console.log('in PRODUCT_COLLPASE action: ', action);
            
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
    
    return ProductsReducer
    
    function changeExpandStatus(products,productId, desiredStatus){
        let productRowIndex = products.findIndex((product)=>{
            return product.id === productId;
        });
        products[productRowIndex].expandStatus = desiredStatus;
        return products;
    }
});