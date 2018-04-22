define([
    'Common/Reducers/creator'
], function(
    reducerCreator
) {
    var initialState = {};

    return reducerCreator(initialState, {
        "ADD_NEW_CATEGORY_MAP": function(state, action) {
            var newState = Object.assign({}, state);
            newState[action.payload.mapId] = action.payload.name;
            return newState;
        }
    });
});
