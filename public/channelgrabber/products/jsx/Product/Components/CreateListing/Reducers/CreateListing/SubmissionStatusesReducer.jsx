define([
    'Common/Reducers/creator'
], function(
    reducerCreator
) {
    "use strict";

    var initialState = {};

    return reducerCreator(initialState, {
        "LISTING_FORM_SUBMITTED_SUCCESSFUL": function(state, action) {
            return Object.assign({}, state, {
                guid: action.payload.guid
            });
        },
        "LISTING_FORM_SUBMITTED_ERROR": function(state, action) {
            n.error(action.payload.error);
            return state;
        },
        "LISTING_FORM_SUBMITTED_NOT_ALLOWED": function (state, action) {
            n.error("You do not have permission to do this.");
            return state;
        }
    });
});
