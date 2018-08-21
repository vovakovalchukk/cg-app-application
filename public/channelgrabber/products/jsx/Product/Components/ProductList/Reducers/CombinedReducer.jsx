define([
    'redux',
    'Product/Components/ProductList/Reducers/ProductsReducer',
    'Product/Components/ProductList/Reducers/TabsReducer'
], function(
    Redux,
    productsReducer,
    tabsReducer
) {
    "use strict";
    
    var appReducer = Redux.combineReducers({
        products: productsReducer,
        tabs: tabsReducer
    });
    
    const combinedReducer = (state, action) => {
        return appReducer(state, action);
    };
    
    return combinedReducer;
});