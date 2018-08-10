define([
    'Common/Reducers/creator'
], function(
    reducerCreator
) {
    let initialState = {
        isFetching: false,
        products: {}
    };

    return reducerCreator(initialState, {
        "FETCH_SEARCH_RESULTS": function() {
            return {
                isFetching: true,
                products: {}
            };
        },
        "SEARCH_RESULTS_FETCHED": function(state, action) {
            if (action.payload.products.length === 0) {
                n.notice('No products found.');
            }
            return {
                isFetching: false,
                products: action.payload.products
            };
        },
        "SEARCH_RESULTS_ERROR": function() {
            return {
                isFetching: false,
                products: {}
            };
        }
    });
});
