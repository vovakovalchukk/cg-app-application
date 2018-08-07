define([
    './ResponseActions'
], function(
    ResponseActions
) {
    "use strict";

    return {
        fetchSearchResults: function(accountId, query, dispatch) {
            $.ajax({
                context: this,
                url: '/products/create-listings/' + accountId + '/search',
                type: 'POST',
                data: {
                    'query': query
                },
                success: function(response) {
                    dispatch(ResponseActions.searchResultsFetched(response));
                }
            });

            return {
                type: "FETCH_SEARCH_RESULTS",
                payload: {}
            };
        }
    };
});
