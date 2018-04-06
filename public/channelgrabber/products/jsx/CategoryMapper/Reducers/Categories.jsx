define([
    'Common/Reducers/creator'
], function(
    reducerCreator
) {
    var initialState = {};

    return reducerCreator(initialState, {
        "CATEGORY_CHILDREN_FETCHED": function (state, action) {
            var newState = Object.assign({}, state),
                accountId = action.payload.accountId,
                categoryId = action.payload.categoryId,
                childCategories = action.payload.categories,
                selectedCategories = action.payload.selectedCategories;

            newState[accountId] = Object.assign({}, newState[accountId]);

            var accountCategories = JSON.parse(JSON.stringify(newState[accountId])),
                categories = accountCategories;

            for (var i = 0; i < selectedCategories.length; i++) {
                categories = categories[selectedCategories[i]].categoryChildren;
            }

            if (categoryId in categories && 'categoryChildren' in categories[categoryId] && Object.keys(categories[categoryId].categoryChildren).length > 0) {
                return state;
            }

            categories[categoryId] = Object.assign({}, categories[categoryId], {
                categoryChildren: childCategories
            });

            newState[accountId] = accountCategories;

            return newState;
        },
        "REFRESH_CATEGORIES_FETCHED": function (state, action) {
            var newState = Object.assign({}, state, {
                [action.payload.accountId]: Object.assign({}, action.payload.categories)
            });

            return newState;
        },
        "CATEGORY_MAPS_FETCHED": function (state, action) {
            // console.log(state, action);
            return state;
        }
    });
});
