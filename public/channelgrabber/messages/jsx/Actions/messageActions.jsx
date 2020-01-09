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
    addMessage: (params) => {
        return async function (dispatch, getState) {
            let response = await addMessage(params, getState());
            dispatch({
                type: 'ADD_MESSAGE_SUCCESS',
                payload: response,
            })
        }
    },
    saveStatus: (params) => {
        return async function (dispatch, getState) {
            let response = await saveStatus(params, getState());
            dispatch({
                type: 'SAVE_STATUS_SUCCESS',
                payload: response,
            })
        }
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

function addMessage(params, state) {
    // threadId: the ID of the current Thread
    const threadId = '1-6d5f9a764ed0e67c196d2cdc3498a0d8aea56f32'; // todo

    // body: the text the user entered
    const body = 'testing add message ajax'; // todo

    return $.ajax({
        url: '/messages/ajax/addMessage',
        type: 'POST',
        data: {
            threadId: threadId,
            body: body
        }
    });
}

function saveStatus(params, state) {
    // threadId: the ID of the current Thread
    const threadId = '1-6d5f9a764ed0e67c196d2cdc3498a0d8aea56f32'; // todo

    // status: resolved/awaiting reply/new
    const status = 'resolved'; // todo

    return $.ajax({
        url: '/messages/ajax/save',
        type: 'POST',
        data: {
            threadId: threadId,
            status: status
        }
    });
}
