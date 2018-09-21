import ResponseActions from './ResponseActions';
    

    export default {
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
                },
                error: function() {
                    n.error("An unknown error has occurred. Please try again or contact support if the problem persists");
                }
            });

            return {
                type: "FETCH_SEARCH_RESULTS",
                payload: {}
            };
        }
    };

