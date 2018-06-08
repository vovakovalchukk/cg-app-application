define([
    'redux',
    'redux-form',
    'Common/Components/ImageUploader/ImageUploaderReducer',
    'Product/Components/CreateProduct/Reducers/AccountReducer',
    'Product/Components/CreateProduct/Reducers/VariationsReducer'
], function(
    Redux,
    ReduxForm,
    imageUploaderReducer,
    AccountReducer,
    VariationsTableReducer
) {
    var AppReducer = Redux.combineReducers({
        form: ReduxForm.reducer,
        account: AccountReducer,
        variationsTable: VariationsTableReducer,
        uploadedImages: imageUploaderReducer
    });

    const CombinedReducer = (state, action) => {
        if (action.type === 'USER_LEAVES_CREATE_PRODUCT') {
            // setting state as undefined triggers Redux to use the initial values of all reducers
            state = undefined;
        }
        return AppReducer(state, action)
    };

    return CombinedReducer;
});