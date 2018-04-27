define([
    'redux',
    'redux-form',
    'Common/Components/ImageUploader/ImageUploaderReducer',
    'Product/Components/CreateProduct/Reducers/AccountReducer',
    'Product/Components/VariationsTable/Reducer'
], function(
    Redux,
    ReduxForm,
    imageUploaderReducer,
    AccountReducer,
    VariationsTableReducer
) {
    var CombinedReducer = Redux.combineReducers({
        form: ReduxForm.reducer,
        account: AccountReducer,
        variationsTable: VariationsTableReducer,
        uploadedImages: imageUploaderReducer
    });
    return CombinedReducer;
});