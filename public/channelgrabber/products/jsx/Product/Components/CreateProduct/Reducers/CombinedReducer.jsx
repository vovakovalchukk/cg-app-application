define([
    'redux',
    'redux-form',
    'Common/Components/ImageUploader/ImageUploaderReducer'
], function(
    Redux,
    ReduxForm,
    imageUploaderReducer
) {
    var CombinedReducer = Redux.combineReducers({
        form: ReduxForm.reducer,
        uploadedImages: imageUploaderReducer
    });
    return CombinedReducer;
});