import reducerCreator from 'Common/Reducers/creator';
    let initialState = {
        isFetching: false,
        products: {}
    };

    export default reducerCreator(initialState, {
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

