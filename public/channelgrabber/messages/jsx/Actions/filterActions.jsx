const filterActions = {
    fetchFilters: () => {
        return async function (dispatch, getState) {
            let response = await fetchHeadline();

            dispatch({
                type: "FILTERS_FETCH_SUCCESS",
                payload: response.headline,
            })
        };
    },
};

export default filterActions;

function fetchHeadline() {

    return $.ajax({
        url: '/messages/ajax/headline',
    });

}