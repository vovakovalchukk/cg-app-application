define([
    'redux',
    'redux-form',
    'Common/Components/ImageUploader/ImageUploaderReducer',
    'Product/Components/CreateProduct/Reducers/AccountReducer',
    'Product/Components/CreateProduct/Reducers/VariationRowPropertiesReducer'
], function(
    Redux,
    ReduxForm,
    imageUploaderReducer,
    AccountReducer,
    VariationRowPropertiesReducer
) {
    var CombinedReducer = Redux.combineReducers({
        form: ReduxForm.reducer,
        account: AccountReducer,
        variationRowProperties: VariationRowPropertiesReducer,
        uploadedImages: imageUploaderReducer
    });
    return CombinedReducer;
});