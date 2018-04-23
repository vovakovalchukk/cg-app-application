define([
    'Common/Reducers/creator'
], function(
    reducerCreator
) {
    var initialState = {};

    return reducerCreator(initialState, {
        "CATEGORY_MAP_SELECTED": function(state, action) {
            var newState = Object.assign({}, state);

            for (var categoryId in newState) {
                newState[categoryId] = Object.assign({}, newState[categoryId], {
                    selected: false
                });
            }

            action.payload.categoryIds.forEach(function(categoryId) {
                if (categoryId in newState) {
                    newState[categoryId] = Object.assign({}, newState[categoryId],{
                        selected: true
                    });
                }
            });

            return newState;
        },
        "ADD_NEW_CATEGORY_MAP": function(state, action) {
            var newState = Object.assign({}, state);
            newState[action.payload.mapId] = {
                name: action.payload.name,
                selected: true
            };
            return newState;
        }
    });
});
