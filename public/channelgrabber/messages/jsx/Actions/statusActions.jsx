const statusActions = {
    fetchStatus: () => {
        return async function (dispatch, getState) {
            let response = await fetchHeadline();

            dispatch({
                type: 'STATUS_FETCH_SUCCESS',
                payload: response.headline,
            })
        };
    },
};

export default statusActions;

function fetchHeadline() {

    return $.ajax({
        url: '/messages/ajax/headline',
    });

}