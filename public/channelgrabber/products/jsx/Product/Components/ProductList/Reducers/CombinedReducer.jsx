define([
    'redux',
    // 'redux-form',
    'Product/Components/ProductList/Reducers/ProductsReducer',
    'Product/Components/ProductList/Reducers/TabsReducer'
], function(
    Redux,
    // ReduxForm,
    productsReducer,
    tabsReducer
) {
    "use strict";
    
    var appReducer = Redux.combineReducers({
        products: productsReducer,
        tabs:tabsReducer
    });
    
    const combinedReducer = (state, action) => {
        return appReducer(state, action);
    };
    
    return combinedReducer;
});