import reducerCreator from 'Common/Reducers/creator';
    

    var initialState = {};

    export default reducerCreator(initialState, {
        "REFRESH_CATEGORIES": function(state, action) {
            var newState = Object.assign({}, state),
                account = newState[action.payload.accountId];

            account = Object.assign({}, account, {
                refreshing: true
            });
            newState[action.payload.accountId] = account;

            return newState;
        },
        "REFRESH_CATEGORIES_FETCHED": function (state, action) {
            var newState = Object.assign({}, state),
                account = newState[action.payload.accountId];

            account = Object.assign({}, account, {
                refreshing: false
            });
            newState[action.payload.accountId] = account;

            return newState;
        },
    });

