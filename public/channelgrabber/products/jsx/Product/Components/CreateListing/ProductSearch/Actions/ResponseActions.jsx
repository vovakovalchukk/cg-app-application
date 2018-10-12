
    

    export default {
        searchResultsFetched: function (response) {
            const products = response.products ? response.products : {};
            return {
                type: "SEARCH_RESULTS_FETCHED",
                payload: {
                    products: products
                }
            };
        }
    };

