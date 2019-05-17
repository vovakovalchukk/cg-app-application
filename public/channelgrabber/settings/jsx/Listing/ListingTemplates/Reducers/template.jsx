import React from 'react';
import reducerCreator from 'Common/Reducers/creator';

const templateReducer = {
    'USER_TEMPLATES_STORE': function(state, action){
        return assignState(state, {
            userTemplates: action.payload.templates
        });
    },
    'TEMPLATE_ADD': function(state, action) {
        return assignState(state, {
            selectedLoadTemplateOption: {},
            templateName: '',
            editorHtml: '',
            initialised: true
        });
    },
    'TEMPLATE_NAME_CHANGE': function(state, action) {
        console.log('in templateNameChange');
        return assignState(state, {
            templateName: action.payload.newValue
        });
    },
    'LOAD_TEMPLATE_OPTION_CHANGE': function(state, action) {
        console.log('LOAD_TEMPLATE_OPTION_CHANGE ');
        
        console.log('action: ', action);
        // todo - get the html of the selectedtemplate and assign it to state
        return assignState(state, {
            selectedLoadTemplateOption: action.payload.option,
            editorHtml: ''
        });
    },
    "NEW_TEMPLATE_NAME_CHANGE": function(state, action){
        console.log('NEW_TEMPLATE_NAME_CHANGE');
        return assignState(state,{
            newTemplateName: action.payload.newValue
        })
    }
};

let reducer = reducerCreator({}, templateReducer);

export default reducer;

function assignState(state, additionalState) {
    return Object.assign({}, state, additionalState);
}


