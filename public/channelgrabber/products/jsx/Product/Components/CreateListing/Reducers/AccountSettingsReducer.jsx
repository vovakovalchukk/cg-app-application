define([
    'redux-form',
    'Common/Reducers/creator'
], function(
    ReduxForm,
    reducerCreator
) {
    var initialState = {};

    return reducerCreator(initialState, {
        "ACCOUNT_SETTINGS_FETCHED": function(state, action) {
            return Object.assign({}, state, {
                [action.payload.accountId]: {
                    settings: action.payload.settings
                }
            });
        },
        "NO_ACCOUNT_SETTINGS": function(state, action) {
            return Object.assign({}, state, {
                [action.payload.accountId]: {
                    error: true
                }
            });
        }
    });
});
