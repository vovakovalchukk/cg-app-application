import reducerCreator from 'Common/Reducers/creator';
    var initialState = {};

    export default reducerCreator(initialState, {
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
        "CATEGORY_MAP_SELECTED_BY_NAME": function(state, action) {
            var newState = Object.assign({}, state),
                name = action.payload.name;

            for (var categoryId in newState) {
                if (newState[categoryId].name == name) {
                    newState[categoryId] = Object.assign({}, newState[categoryId], {
                        selected: true
                    });
                }
            }

            return newState;
        },
        "ADD_NEW_CATEGORY_MAP": function(state, action) {
            var newState = Object.assign({}, state),
                accounts = {};

            action.payload.categories.forEach(function(categoryId, accountId) {
                if (categoryId) {
                    accounts[accountId] = accountId;
                }
            });

            newState[action.payload.mapId] = {
                name: action.payload.name,
                accounts: accounts,
                selected: true
            };

            return newState;
        },
    });

