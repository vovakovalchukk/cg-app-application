define([
            'redux',
            // 'redux-form',
            'Product/Components/CreateProduct/Reducers/CreateProductReducer'

], function(
        Redux,
        // ReduxForm,
        CreateProductReducer
    ) {
        var CombinedReducer = Redux.combineReducers({
            createProduct: CreateProductReducer
        });

        return CombinedReducer;
    });