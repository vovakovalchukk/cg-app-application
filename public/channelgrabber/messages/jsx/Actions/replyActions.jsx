const replyActions = {
    replyActionsToggleVisibility: (params) => {
        const payload = true; // todo
        return function (dispatch, getState) {
            dispatch({
                type: 'REPLY_ACTIONS_TOGGLE_VISIBILITY',
                payload: payload,
            })
        };
    },
    replyInputType: (params) => {
        const payload = params.target.value;
        return function (dispatch, getState) {
            dispatch({
                type: 'REPLY_INPUT_CHANGED',
                payload,
            })
        };
    },
};

export default replyActions;