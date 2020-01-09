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
};

export default replyActions;