define([
    'redux',
    'Product/Components/ProductList/Reducers/productsReducer',
    'Product/Components/ProductList/Reducers/tabsReducer',
    'Product/Components/ProductList/Reducers/columnsReducer'
], function(
    Redux,
    productsReducer,
    tabsReducer,
    columnsReducer
) {
    "use strict";
    
    var appReducer = Redux.combineReducers({
        products: productsReducer,
        tabs: tabsReducer,
        columns:columnsReducer
    });
    
    const combinedReducer = (state, action) => {
        return appReducer(state, action);
    };
    
    return combinedReducer;
});