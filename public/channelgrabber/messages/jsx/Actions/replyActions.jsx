const replyActions = {
    replyOnChange: (params) => {
        return function (dispatch, getState) {
            dispatch({
                type: 'REPLY_INPUT_CHANGE',
                payload: params.target.value,
            })
        };
    },
    replyOptionSelect: (params) => {
        return function (dispatch, getState) {
            dispatch({
                type: 'REPLY_OPTION_SELECT',
                payload: params,
            })
        };
    },
    replyTemplateSelect: (params) => {
        return function (dispatch, getState) {
            dispatch({
                type: 'REPLY_TEMPLATE_SELECT',
                payload: params,
            })
        };
    },
};

export default replyActions;