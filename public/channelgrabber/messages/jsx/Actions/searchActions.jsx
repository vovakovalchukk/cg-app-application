import messageActions from 'MessageCentre/Actions/messageActions';

const searchActions = {
    searchInputType: (params) => {
        return function (dispatch, getState) {
            dispatch({
                type: 'SEARCH_INPUT_CHANGED',
                payload: params.target.value,
            })
        };
    },
    searchSubmit: () => {
        return async function (dispatch, getState) {
            const state = getState();

            const filter = {
                searchTerm: state.search.query
            };

            dispatch(messageActions.fetchMessages({
                filter
            }));
        };
    }
};

export default searchActions;