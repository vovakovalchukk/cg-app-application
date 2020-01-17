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
    sendAndResolve: (params) => {
        params = params || {
            target: {
                value: 'resolved'
            }
        };
        return async function (dispatch, getState) {
            let message = await addMessage(params, getState());
            let status = await saveStatus(params, getState());
            dispatch({
                type: 'ADD_MESSAGE_SUCCESS',
                payload: message,
            });
            dispatch({
                type: 'SAVE_STATUS_SUCCESS',
                payload: status,
            });
        }
    },
    assignThreadToUser: (params) => {
        console.log('TODO - assignThreadToUser action');
        /*
            When the user chooses a different person from the Assign dropdown then make an AJAX call to /messages/ajax/save and POST the following data:
            - id: the ID of the Thread
            - assignedUserId: the selected assignee User ID, or blank when Unassigned is selected
            In the response you will get the thread details back, again, unless there’s a problem then you will get a message instead which you should show to the user.
            If successful, update anywhere we show the assigned user to be the new assignee.
        */
        return null;
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
    const {threads, reply} = state;

    return $.ajax({
        url: '/messages/ajax/addMessage',
        type: 'POST',
        data: {
            threadId: threads.viewing,
            body: reply.text
        }
    });
}

function saveStatus(params, state) {
    const {threads} = state;

    return $.ajax({
        url: '/messages/ajax/save',
        type: 'POST',
        data: {
            threadId: threads.viewing,
            status: params.target.value
        }
    });
}
