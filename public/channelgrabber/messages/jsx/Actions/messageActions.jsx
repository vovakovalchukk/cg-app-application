const messageActions = {
    fetchMessages: () => {
        const addFakeDate = true;
        return async function (dispatch, getState) {
            let response = await fetchThreads();
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

function fetchThreads() {

    return $.ajax({
        url: '/messages/ajax',
        type: 'POST',
        data: {
            filter: [], // TODO - see below
            /*
            Open: send both filter[status][]: new and filter[status][]: awaiting reply
            Resolved: filter[status]: resolved
            Unassigned: filter[assignee]: unassigned
            Assigned: filter[assignee]: assigned
            My Messages: filter[assignee]: active-user
            */
            page: 1, // TODO - pagination
            sortDescending: true, // TODO - date column sort order
        }
    });
}

function fakeSomeExtraDataForPagination(threads){
    let hackLength = 100;
    let extraThreads = JSON.parse(JSON.stringify(threads));
    extraThreads.forEach(thread => {
        thread.id = thread.id + "-Z";
    });
    let combinedThreads = [...threads, ...extraThreads];
    if (combinedThreads.length > hackLength) {
        combinedThreads.length = hackLength;
    }
    return combinedThreads;
}