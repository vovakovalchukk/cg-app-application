const messageActions = {
    fetchMessages: (params) => {
        const addFakeDate = false;
        return async function (dispatch, getState) {
            let response = await fetchThreads(params, getState());
            // this is as expected
            if (addFakeDate) {
                response.threads = fakeSomeExtraDataForPagination(response.threads);
            }
            dispatch({
                type: 'THREADS_FETCH_SUCCESS',
                payload: response.threads,
            })
        };
    },
};

export default messageActions;

function fetchThreads(params, state) {
    const {filter} = params;

    const newFilter = {
        ...state.filter,
        ...filter
    };

    const newParams = {...params};
    delete newParams.filter;

    return $.ajax({
        url: '/messages/ajax',
        type: 'POST',
        data: {
            filter: newFilter, // TODO - see below
            page: 1, // TODO - pagination
            sortDescending: true, // TODO - date column sort order
            ...newParams
        }
    });
}

function fakeSomeExtraDataForPagination(threads){
    let hackLength = 100;
    let extraThreads = JSON.parse(JSON.stringify(threads));
    extraThreads.forEach(thread => {
        thread.id = `${thread.id}-Z`;
    });
    let combinedThreads = [...threads, ...extraThreads];
    if (combinedThreads.length > hackLength) {
        combinedThreads.length = hackLength;
    }
    return combinedThreads;
}