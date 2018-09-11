define([
    'redux',
    'Product/Components/ProductList/Reducers/productsReducer',
    'Product/Components/ProductList/Reducers/tabsReducer',
    'Product/Components/ProductList/Reducers/columnsReducer',
    'Product/Components/ProductList/Reducers/listReducer',
    'Product/Components/ProductList/Reducers/accountsReducer',
    'Product/Components/ProductList/Reducers/paginationReducer',
    'Product/Components/ProductList/Reducers/searchReducer',
    'Product/Components/ProductList/Reducers/createListingReducer'
], function(
    Redux,
    productsReducer,
    tabsReducer,
    columnsReducer,
    listReducer,
    accountsReducer,
    paginationReducer,
    searchReducer,
    createListingReducer
) {
    "use strict";
    
    var appReducer = Redux.combineReducers({
        products: productsReducer,
        tabs: tabsReducer,
        columns: columnsReducer,
        list: listReducer,
        accounts: accountsReducer,
        pagination: paginationReducer,
        search: searchReducer,
        createListing: createListingReducer
    });
    
    const combinedReducer = (state, action) => {
        return appReducer(state, action);
    };
    
    return combinedReducer;
});