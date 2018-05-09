define([
    'Common/Reducers/creator'
], function(
    reducerCreator
) {
    "use strict";

    var initialState = {};

    return reducerCreator(initialState, {
        "LISTING_FORM_SUBMITTED_SUCCESSFUL": function(state, action) {
            console.log(state, action);
            return state;
        },
        "LISTING_FORM_SUBMITTED_ERROR": function(state, action) {
            console.log(state, action);
            return state;
        },
        "LISTING_FORM_SUBMITTED_NOT_ALLOWED": function (state, action) {
            console.log(state, action);
            return state;
        }
    });
});
