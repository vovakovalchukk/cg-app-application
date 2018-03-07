define(['Redux/Reducers/creator'], function (reducerCreator) {
    var initialState = "SHOW_ALL";
    var FilterLink = reducerCreator(initialState, {
        "SET_VISIBILITY": function (state, action) {
            return action.payload.filter;
        }
    });

    return FilterLink;
});
