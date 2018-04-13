define([
    'Common/Reducers/creator'

], function(
    reducerCreator
) {
    "use strict";

    var initialState={};

    var imageUploaderReducer = reducerCreator(initialState, {
        "IMAGE_UPLOAD_START": function(state, action) {
            console.log('in Image Upload StART IN reducer action: ',action)
            return state;
        },
        "IMAGE_UPLOAD_SUCCESS": function(state, action) {
            console.log('in Image Upload Success IN reducer action: ', action)
            return state;
        },
        "IMAGE_UPLOAD_FAILURE": function(state, action) {
            console.log('in Image Upload Failure IN reducer')
            return state;
        },
    });



    return imageUploaderReducer;

});