define([
    'redux',
    'redux-form'
], function(
    Redux,
    ReduxForm
) {
    "use strict";

    var CombinedReducer = Redux.combineReducers({
        form: ReduxForm.reducer,
        accounts: function (state) {
            return state ? state : {};
        },
        channelBadges: function (state) {
            return state ? state : {};
        }
    });
    return CombinedReducer;
});
