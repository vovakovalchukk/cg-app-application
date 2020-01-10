import reducerCreator from 'Common/Reducers/creator';

'use strict';

const initialState = {
    byId: {},
    allIds: []
};

const messagesReducer = reducerCreator(initialState, {
    'TEMPLATES_ADD': (state, action) => {
        let templates = {...state};

        templates.byId = {};

        templates.allIds = [];



//        action.payload.forEach(thread => {
//            thread.messages.forEach(message => {
//                templates.byId[message.id] = message;
//                templates.allIds.push(message.id);
//            });
//        });

        return {...state, ...templates};
    }
});

export function normalizeTemplate(template) {
    console.log('in normalizeTemplate', template);


}

export function initTemplates(templates) {
    let initializedTemplates = {
        byId: {},
        allIds: []
    };

    for (let template of templates) {
        initializedTemplates.byId[template.id] = {...template};
        initializedTemplates.allIds.push(template.id)
    }

    return initializedTemplates;
}

export default messagesReducer;