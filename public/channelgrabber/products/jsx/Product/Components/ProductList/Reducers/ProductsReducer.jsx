define([
    'Common/Reducers/creator',
], function(
    reducerCreator,
) {
    "use strict";
    var initialState = {
        completeInitialLoads: {
            simpleAndParentProducts: false
        },
        simpleAndParentProducts: []
    };
    
    var ProductsReducer = reducerCreator(initialState, {
        "INITIAL_SIMPLE_AND_PARENT_PRODUCTS_LOAD": function(state, action) {
            console.log('r-in initial products load with action.payload.products: ', action.payload.products);
            let newState = Object.assign({}, state, {
                completeInitialLoads: {
                    simpleAndParentProducts: true
                },
                simpleAndParentProducts: action.payload.products,
                visibleRows: action.payload.products
            });
            return newState;
        },
        "PRODUCT_EXPAND": function(state, action) {
            console.log('in product expand with action: ', action, ' state: ', state);
            let currentVisibleProducts = state.visibleRows.slice();
            let parentProductIndex = null;
            let parentProduct = currentVisibleProducts.find((product, index) => {
                if (product.id === action.payload.productRowIdToExpand) {
                    parentProductIndex = index;
                    return product.id === action.payload.productRowIdToExpand
                }
            });
      
            let rowsToAdd = [];
            parentProduct.variationIds.forEach(variationId => {
                //todo - change this to provide something more meaningful later
                rowsToAdd.push(
                    {
                        name: 'name',
                        sku: 'id - ' + variationId
                    }
                );
            });
            
            currentVisibleProducts.splice(
                parentProductIndex + 1,
                0,
                ...rowsToAdd
            );
            
            let newState = Object.assign({}, state, {
               visibleRows: currentVisibleProducts
            });
            console.log('r-expand newState after splice: ', newState);
    
            return newState;
        }
    });
    
    return ProductsReducer;
});