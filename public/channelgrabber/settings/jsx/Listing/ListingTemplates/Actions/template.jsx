const templateActions = {
    storeUserTemplates: (templates) => {
        return {
            type: 'USERS_TEMPLATES_STORE',
            payload: {
                templates
            }
        }
    },
    changeLoadTemplateOption: (option) => {
        return {
            type: 'LOAD_TEMPLATE_OPTION_CHANGE',
            payload: {option}
        }
    },
    changeTemplateName: (e) => {
        console.log('in changeTemplateName AQ',e.target.value );
        
        
        return {
            type: "TEMPLATE_NAME_CHANGE",
            payload: {
                newValue: e.target.value
            }
        }
    },
    changeNewTemplateName: (e) =>{
        return {
            type: "NEW_TEMPLATE_NAME_CHANGE",
            payload: {
                newValue: e.target.value
            }
        }
    },
    addNewTemplate: () => {
        return {
            type: "NEW_TEMPLATE_ADD",
            payload: {}
        }
    }
};

export default templateActions;