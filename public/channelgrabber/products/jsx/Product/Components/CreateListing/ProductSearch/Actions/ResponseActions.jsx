define([], function() {
    "use strict";

    return {
        searchResultsFetched: function (response) {
            return {
                type: "SEARCH_RESULTS_FETCHED",
                payload: {
                    products: response.products
                }
            };
        }
    };
});
