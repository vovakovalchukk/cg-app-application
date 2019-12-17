const searchActions = {
    searchInputType: (params) => {
        const searchTerm = params.target.value;
        return function (dispatch, getState) {
            dispatch({
                type: 'SEARCH_INPUT_CHANGED',
                payload: searchTerm,
            })
        };
    },
    searchSubmit: (params) => {
        console.log('searchSubmit');
        // todo...
        // need to call "fetchThreads" again, but with param
        // filter[searchTerm]: what the user typed
    }
};

export default searchActions;