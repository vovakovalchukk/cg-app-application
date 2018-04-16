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
                                dispatch(self.imageUploadSuccess(response.url));
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
            imageUploadSuccess: function(uploadedImageUrl) {
                return {
                    type: 'IMAGE_UPLOAD_SUCCESS',
                    payload: {
                        uploadedImageUrl: uploadedImageUrl
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
            }
        }

        return self;

    }());

    return imageUploaderActions;

});