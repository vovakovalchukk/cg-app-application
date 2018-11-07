
    var formActionCreators = {
        imageUploadStart: function(image) {
            return {
                type: 'IMAGE_UPLOAD_START',
                payload: {
                    image: image
                }
            };
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
        },
        newVariationRowCreate: function() {
            return {
                type: 'NEW_VARIATION_ROW_CREATE'
            }
        },
        newVariationRowCreateRequest: function(variationId) {
            return function(dispatch, getState) {
                var currState = getState();
                if (!variationIsEmpty(currState, variationId)) {
                    dispatch(formActionCreators.newVariationRowCreate());
                }
            }
        }
    };

    export default formActionCreators;

    function variationIsEmpty(currState, variationId) {
        return currState.form.createProductForm.values && currState.form.createProductForm.values.variations["variation-" + variationId];
    }
