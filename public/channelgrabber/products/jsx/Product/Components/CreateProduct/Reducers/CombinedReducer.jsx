define([
    'redux',
    'redux-form',
    'Common/Components/ImageUploader/ImageUploaderReducer',
    'Product/Components/CreateProduct/Reducers/AccountReducer'
], function(
    Redux,
    ReduxForm,
    imageUploaderReducer,
    AccountReducer
) {
    var CombinedReducer = Redux.combineReducers({
        form: ReduxForm.reducer,
        account: AccountReducer,
        uploadedImages: imageUploaderReducer
    });
    return CombinedReducer;
});