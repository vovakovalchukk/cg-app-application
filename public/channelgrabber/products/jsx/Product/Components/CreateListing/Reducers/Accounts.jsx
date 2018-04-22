define([
    'Common/Reducers/creator'
], function(
    reducerCreator
) {
    var initialState = {};

    return reducerCreator(initialState, {
        "CATEGORY_ROOTS_FETCHED": function(state, action) {
            var newState = Object.assign({}, state),
                accountCategories = action.payload.accountCategories;

            accountCategories.forEach(function(categories) {
                if (!(categories.accountId in newState)) {
                    return;
                }
                newState[categories.accountId] = Object.assign({}, newState[categories.accountId]);
                newState[categories.accountId].categories = categories.categories;
            });

            return newState;
        }
    });
});
