import messageActions from 'MessageCentre/Actions/messageActions';

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
    searchSubmit: () => {
        return async function (dispatch, getState) {
            const state = getState();

            const filter = {
                searchTerm: state.threads.searchBy
            };

            dispatch(messageActions.fetchMessages({
                filter
            }));
        };
    }
};

export default searchActions;