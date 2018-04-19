define([
    'Common/Reducers/creator'

], function(
    reducerCreator
) {
    "use strict";
    var initialState = {
        images: []
    };
    var imageUploaderReducer = reducerCreator(initialState, {
        "IMAGE_UPLOAD_START": function(state, action) {
            return state;
        },
        "IMAGE_UPLOAD_SUCCESS": function(state, action) {
            var newImage = {
                id: action.payload.id,
                url: action.payload.uploadedImageUrl
            };
            var newImages = [].concat(state.images, newImage);
            var newState = Object.assign({images: newImages}, {})
            return newState;
        },
        "IMAGE_UPLOAD_FAILURE": function(state, action) {
            return state;
        }
    });

    return imageUploaderReducer;
});