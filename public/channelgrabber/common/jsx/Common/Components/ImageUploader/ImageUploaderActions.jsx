define([
    'redux-thunk',
    'Common/Components/ImageUploader/ImageUploaderFunctions'

], function(
    ReduxThunk,
    ImageUploaderFunctions
) {

    var imageUploaderActions = (function() {
        var self = {
            uploadImageHandler: function(event) {
                return function(dispatch) {
                    var image = ImageUploaderFunctions.getImageFromOnChangeEvent(event);
                    dispatch(self.imageUploadRequest(image));
                    ImageUploaderFunctions.uploadImageHandler(image)
                        .then(
                            function(response) {
                                dispatch(self.imageUploadSuccess(response));
                            }
                        )
                        .catch(function(response) {
                            dispatch(self.imageUploadFailure(response.imageRequestedForUpload));
                        })
                }
            },
            imageUploadRequest: function(image) {
                return {
                    type: 'IMAGE_UPLOAD_REQUEST',
                    payload: {
                        image: image
                    }
                }
            },
            imageUploadSuccess: function(image) {
                return {
                    type: 'IMAGE_UPLOAD_SUCCESS',
                    payload: {
                        id: image.id,
                        uploadedImageUrl: image.url
                    }
                };
            },
            imageUploadFailure: function(image) {
                return {
                    type: 'IMAGE_UPLOAD_FAILURE',
                    payload: {
                        image: image
                    }
                };
            },
            newVariationRowRequest:function(){
                return {
                    type: 'NEW_VARIATION_ROW_REQUEST'
                }
            }
        }
        return self;
    }());

    return imageUploaderActions;
});