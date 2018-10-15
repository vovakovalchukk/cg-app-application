import reducerCreator from 'Common/Reducers/creator';
    var initialState = {};

    export default reducerCreator(initialState, {
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

