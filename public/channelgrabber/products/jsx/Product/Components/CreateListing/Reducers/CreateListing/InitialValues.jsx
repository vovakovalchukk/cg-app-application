define([
    'Common/Reducers/creator'
], function(
    reducerCreator
) {
    var initialState = {};

    return reducerCreator(initialState, {
        "LOAD_INITIAL_VALUES": function(state, action) {
            var product = action.payload.product;
            return {
                title: product.name
            };
        }
    });
});
