define([
    'Common/Reducers/creator'
], function(
    reducerCreator
) {
    "use strict";

    var initialState = {
        isVisible: false
    };

    return reducerCreator(initialState, {
        "ADD_NEW_CATEGORY_MAP": function() {
            return {
                isVisible: false
            };
        },
        "SHOW_ADD_NEW_CATEGORY_MAP": function() {
            return {
                isVisible: true
            };
        }
    });
});
