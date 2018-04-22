define([
    'redux',
    'redux-form',
    'Common/Components/ImageUploader/ImageUploaderReducer',
    'Product/Components/CreateProduct/Reducers/AccountReducer',
    'Product/Components/CreateProduct/Reducers/CreateVariationsTableReducer'
], function(
    Redux,
    ReduxForm,
    imageUploaderReducer,
    AccountReducer,
    CreateVariationsTableReducer
) {
    var CombinedReducer = Redux.combineReducers({
        form: ReduxForm.reducer,
        account: AccountReducer,
        variationsTable: CreateVariationsTableReducer,
        uploadedImages: imageUploaderReducer
    });
    return CombinedReducer;
});