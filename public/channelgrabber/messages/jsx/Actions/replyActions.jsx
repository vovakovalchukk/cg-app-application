const replyActions = {
    replyInputType: (params) => {
        const payload = params.target.value;
        return function (dispatch, getState) {
            dispatch({
                type: 'REPLY_INPUT_CHANGED',
                payload,
            })
        };
    },
    replyOptionSelected: (params) => {
        const payload = params;
        return function (dispatch, getState) {
            dispatch({
                type: 'REPLY_OPTION_SELECTED',
                payload,
            })
        };
    },
};

export default replyActions;