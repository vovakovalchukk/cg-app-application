define([], function() {
        var formActionCreators = {
                imageUploadStart: function (image) {
                    console.log('in image upload start AC');
                    return {
                            type: 'IMAGE_UPLOAD_START',
                            payload: {
                                image: image
                            }
                    };
                },
                imageUploadSuccess:  function (image) {
                    console.log('in image upload success AC with image:',image);
                    return {
                        type: 'IMAGE_UPLOAD_SUCCESS',
                        payload: {
                            image: image
                        }
                    };
                },
                imageUploadFailure:  function (image) {
                    console.log('in image upload failure AC');
                    return {
                        type: 'IMAGE_UPLOAD_FAILURE',
                        payload: {
                            image: image
                        }
                    };
                }
        };
    
        return formActionCreators;
    });