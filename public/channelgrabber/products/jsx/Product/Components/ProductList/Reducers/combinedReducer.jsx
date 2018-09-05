define([
    'redux',
    'Product/Components/ProductList/Reducers/productsReducer',
    'Product/Components/ProductList/Reducers/tabsReducer',
    'Product/Components/ProductList/Reducers/columnsReducer',
    'Product/Components/ProductList/Reducers/listReducer',
    'Product/Components/ProductList/Accounts/accounts',
    'Product/Components/ProductList/Reducers/paginationReducer',
    'Product/Components/ProductList/Reducers/searchReducer',
    'Product/Components/ProductList/Accounts/accounts'
], function(
    Redux,
    productsReducer,
    tabsReducer,
    columnsReducer,
    listReducer,
    accountsReducer,
    paginationReducer,
    searchReducer,
    accounts
) {
    "use strict";
    
    console.log('combinedReducer accounts: ', accounts.reducer);
    console.log('combinedReducer list reducer: ', listReducer);
    
    
    var appReducer = Redux.combineReducers({
        products: productsReducer,
        tabs: tabsReducer,
        columns: columnsReducer,
        list: listReducer,
        accounts: accounts.reducer,
        pagination: paginationReducer,
        search: searchReducer
    });
    
    const combinedReducer = (state, action) => {
        return appReducer(state, action);
    };
    
    return combinedReducer;
});