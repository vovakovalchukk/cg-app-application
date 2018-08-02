define([
    'redux',
    'redux-form',
    './Products'
], function(
    Redux,
    ReduxForm,
    ProductsSearchReducer
) {
    "use strict";

    const CombinedReducer = Redux.combineReducers({
        form: ReduxForm.reducer,
        products: ProductsSearchReducer
    });

    return CombinedReducer;
});
