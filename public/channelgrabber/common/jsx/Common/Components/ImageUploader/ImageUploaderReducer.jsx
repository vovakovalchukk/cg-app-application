define([
    'Common/Reducers/creator'

], function(
    reducerCreator
) {
    "use strict";

    var initialState={
        images:[]
    };

    var imageUploaderReducer = reducerCreator(initialState, {
        "IMAGE_UPLOAD_START": function(state, action) {
            return state;
        },
        "IMAGE_UPLOAD_SUCCESS": function(state, action) {
            var newImage = {
                id:action.payload.id,
                url: action.payload.uploadedImageUrl
            };
            var newImages = [].concat(state.images, newImage);
            return Object.assign({images:newImages},{})
        },
        "IMAGE_UPLOAD_FAILURE": function(state, action) {
            console.log('in Image Upload Failure IN reducer')
            return state;
        },
    });



    return imageUploaderReducer;


});