const messageActions = {
    fetchMessages: (params) => {
        return async function (dispatch, getState) {
            let response = await fetchThreads(params, getState());
            dispatch({
                type: 'THREADS_FETCH_SUCCESS',
                payload: response.threads,
            })
        };
    },
    threadChangeStatus: (params) => {
        return async function (dispatch, getState) {
            let response = await threadChangeStatus(params, getState());
            dispatch({
                type: 'THREAD_CHANGE_STATUS_SUCCESS',
                payload: response.threads,
            })
        }
    },
};

export default messageActions;

function threadChangeStatus(params, state) {
    // todo - ajax stuff here
}

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
