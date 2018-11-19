import reducerCreator from 'Common/Reducers/creator';

    let initialState = {
        guid: null,
        accounts: {},
        inProgress: false,
        processGuid: null
    };

    export default reducerCreator(initialState, {
        "LISTING_FORM_SUBMITTED_SUCCESSFUL": function(state, action) {
            return Object.assign({}, state, {
                guid: action.payload.guid,
                processGuid: action.payload.processGuid
            });
        },
        "LISTING_FORM_SUBMITTED_ERROR": function(state, action) {
            n.error(action.payload.error);
            return Object.assign({}, state, {
                inProgress: false
            });
        },
        "LISTING_FORM_SUBMITTED_NOT_ALLOWED": function (state, action) {
            n.error("You do not have permission to do this.");
            return Object.assign({}, state, {
                inProgress: false
            });
        },
        "LISTING_PROGRESS_FETCHED": function(state, action) {
            var accounts = action.payload.accounts;
            var newState = Object.assign({}, state, {
                accounts: Object.assign({}, state.accounts)
            });
            var newAccountsState = newState.accounts;
            for (var accountId in accounts) {
                if (!newAccountsState[accountId]) {
                    newAccountsState[accountId] = {};
                }
                for (var categoryId in accounts[accountId].categories) {
                    newAccountsState[accountId][categoryId] = accounts[accountId].categories[categoryId];
                }
            }
            return newState;
        },
        "SUBMIT_LISTING_FORM": function(state, action) {
            return Object.assign({}, state, {
                inProgress: true
            });
        },
        "LISTING_SUBMISSION_FINISHED": function(state, action) {
            return Object.assign({}, state, {
                inProgress: false
            });
        },
        "REVERT_TO_INITIAL_VALUES": function() {
            return Object.assign({}, initialState);
        }
    });

