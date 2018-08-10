define([
    'redux',
    // 'redux-form',
    'Product/Components/ProductList/Reducers/ProductsReducer',
], function(
    Redux,
    // ReduxForm,
    productsReducer
) {
    "use strict";
    
    var appReducer = Redux.combineReducers({
        products: productsReducer
    });
    
    const combinedReducer = (state, action) => {
        return appReducer(state, action);
    };
    
    return combinedReducer;
});