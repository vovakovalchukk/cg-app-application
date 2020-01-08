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
};

export default replyActions;