import reducerCreator from 'Common/Reducers/creator';

'use strict';

const initialState = {
    byId: {},
    allIds: new Set()
};

const messagesReducer = reducerCreator(initialState, {
    'TEMPLATE_ADD': (state, action) => {
        const existingTemplates = {...state};
        const newNormalizedTemplates = normalizeTemplates([action.payload.template]);

        const combinedTemplates = {};

        combinedTemplates.byId = {
            ...existingTemplates.byId,
            ...newNormalizedTemplates.byId
        };

        combinedTemplates.allIds = [...new Set([
            ...existingTemplates.allIds,
            ...newNormalizedTemplates.allIds
        ])];

        return combinedTemplates;
    },
    "TEMPLATE_REMOVE": (state, action) => {
        const newTemplates = {...state};

        delete newTemplates.byId[action.payload.templateId];
        newTemplates.allIds.delete(action.payload.templateId);

        return newTemplates;
    }
});

export default messagesReducer;

export function initTemplates(templates) {
    return normalizeTemplates(templates);
}

function normalizeTemplates(templates) {
    const initializedTemplates = {...initialState};

    for (let template of templates) {
        initializedTemplates.byId[template.id] = {...template};
        initializedTemplates.allIds.add(template.id)
    }

    return initializedTemplates;
}