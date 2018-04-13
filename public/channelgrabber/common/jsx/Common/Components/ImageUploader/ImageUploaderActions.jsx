define([
    'redux-thunk',
    'Common/Components/ImageUploader/ImageUploaderFunctions'

], function(
    ReduxThunk,
    ImageUploaderFunctions
) {

    var imageUploaderActions = (function() {

        var self = {
            uploadImageHandler: function(image) {
                return function(dispatch) {
                    dispatch(self.imageUploadRequest);
                    ImageUploaderFunctions.uploadImageHandler(image)
                        .then(
                            function(response) {
                                dispatch(self.imageUploadSuccess(response.imageRequestedForUpload));
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
                        image: image
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