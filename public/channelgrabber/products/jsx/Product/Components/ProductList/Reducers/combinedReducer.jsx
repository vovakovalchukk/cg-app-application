define([
    'redux',
    'Product/Components/ProductList/Reducers/productsReducer',
    'Product/Components/ProductList/Reducers/tabsReducer',
    'Product/Components/ProductList/Reducers/columnsReducer',
    'Product/Components/ProductList/Reducers/listReducer',
    'Product/Components/ProductList/Reducers/accountReducer'
], function(
    Redux,
    productsReducer,
    tabsReducer,
    columnsReducer,
    listReducer,
    accountReducer
) {
    "use strict";
    
    var appReducer = Redux.combineReducers({
        products: productsReducer,
        tabs: tabsReducer,
        columns: columnsReducer,
        list: listReducer,
        account: accountReducer
    });
    
    const combinedReducer = (state, action) => {
        return appReducer(state, action);
    };
    
    return combinedReducer;
});