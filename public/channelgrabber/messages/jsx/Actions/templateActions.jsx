const templateActions = {
    addTemplate: (template) => {
        return async function(dispatch, getState) {
            dispatch({
                type: 'TEMPLATE_ADD',
                payload: {
                    template
                }
            })
        };
    },
    removeTemplate: (templateId) => {
        return async function(dispatch, getState) {
            dispatch({
                type: "TEMPLATE_REMOVE",
                payload: {
                    templateId
                }
            });
        }
    }
};

export default templateActions;