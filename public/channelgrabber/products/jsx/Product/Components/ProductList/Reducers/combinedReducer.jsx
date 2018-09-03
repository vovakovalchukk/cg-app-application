define([
    'redux',
    'Product/Components/ProductList/Reducers/productsReducer',
    'Product/Components/ProductList/Reducers/tabsReducer',
    'Product/Components/ProductList/Reducers/columnsReducer',
    'Product/Components/ProductList/Reducers/listReducer',
    'Product/Components/ProductList/Reducers/accountReducer',
    'Product/Components/ProductList/Reducers/paginationReducer',
    'Product/Components/ProductList/Reducers/searchReducer'
], function(
    Redux,
    productsReducer,
    tabsReducer,
    columnsReducer,
    listReducer,
    accountReducer,
    paginationReducer,
    searchReducer
) {
    "use strict";
    
    var appReducer = Redux.combineReducers({
        products: productsReducer,
        tabs: tabsReducer,
        columns: columnsReducer,
        list: listReducer,
        account: accountReducer,
        pagination: paginationReducer,
        search: searchReducer
    });
    
    const combinedReducer = (state, action) => {
        return appReducer(state, action);
    };
    
    return combinedReducer;
});