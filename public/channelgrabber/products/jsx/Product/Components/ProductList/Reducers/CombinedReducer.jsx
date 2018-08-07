define([
    'redux',
    // 'redux-form',
    'Product/Components/ProductList/Reducers/ProductsReducer',
], function(
    Redux,
    // ReduxForm,
    productsReducer
) {
    var appReducer = Redux.combineReducers({
        products: productsReducer
        // form: ReduxForm.reducer,
    });
    
    const combinedReducer = (state, action) => {
        return appReducer(state, action);
    };
    
    return combinedReducer;
});