import messageActions from 'MessageCentre/Actions/messageActions';

const sortByMethodMap = {
    updatedFuzzy: sortByUpdatedFuzzy,
};

const columnActions = {
    sortBy: (key) => {
        return async function (dispatch, getState) {
            const state = getState();

            if (state.column.sortBy === key) {
                dispatch(messageActions.fetchMessages({sortDescending: true}));
                dispatch({
                    type: 'SORT_BY_REQUEST',
                    payload: {key: ''},
                });
                return;
            }

            dispatch(messageActions.fetchMessages({sortDescending: false}));
            dispatch({
                type: 'SORT_BY_REQUEST',
                payload: {key},
            })
        };
    },
};

export default columnActions;

function sortByUpdatedFuzzy(key) {

}