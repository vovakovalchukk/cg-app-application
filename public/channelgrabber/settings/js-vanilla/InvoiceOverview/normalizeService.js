const TEMPLATE_SOURCES = {
    system: 'system',
    user: 'user'
};

export default (function normalizeService() {
    function getTemplateId(template) {
        if(template.key==='blank'){
            return 'blank';
        }
        return template.invoiceId || template.templateId || template.id;
    }

    function buildActions({newTemplate, templateActions, source}){
        if (!newTemplate.links) {
            return;
        }

        for (let link of newTemplate.links) {
            if (!link.properties.href) {
                continue;
            }

            if(!templateActions.byTemplateId[newTemplate.id]){
                templateActions.byTemplateId[newTemplate.id] = {};
            }

            let actions = templateActions.byTemplateId[newTemplate.id];

            actions[link.name] = {...link};
            actions[link.name].linkHref = link.properties.href;
            actions[link.name].linkTarget = link.properties.target;
            delete actions[link.name].properties;

            if(source===TEMPLATE_SOURCES.user){
                actions['favourite'] = {
                    key: 'favourite',
                    name:'favourite',
                    //todo - do something better here
                    iconUrl: ''
                };
                actions['delete'] = {
                    key: 'delete',
                    name: 'delete',
                    iconUrl: ''
                };
            }
        }

        delete newTemplate.links;
    }


    return {
        normalizeTemplateData : (systemTemplates, userTemplates) => {
            const actionTypes = {
                byId: {
                    'favourite': {
                        name: 'favourite',
                        icon: '...'
                    },
                    'create': {name: 'create'},
                    'edit': {name: 'edit'},
                    'deleteTemplate': {name: 'deleteTemplate'},
                    'duplicate': {name: '...'},
                    'buy label': {name: 'sdfsdf'}
                }
            };

            let normalizedTemplates = [];
            let templateActions = {
                byTemplateId: {
                    //1: {href} ...
                }
            };

            normalizeTemplates({
                source: TEMPLATE_SOURCES.system,
                templates: systemTemplates
            });

            normalizeTemplates({
                source: TEMPLATE_SOURCES.user,
                templates: userTemplates
            });

            return {
                templates: normalizedTemplates,
                templateActions,
                TEMPLATE_SOURCES
            };

            function normalizeTemplates({source, templates}) {
                if (!templates) {
                    console.error(`no templates provided by server for type:${source}.`);
                    return;
                }
                for (let template of templates) {
                    let newTemplate = {
                        source,
                        ...template
                    };

                    newTemplate.id = getTemplateId(template);
                    delete newTemplate.invoiceId;
                    delete newTemplate.templateId;
                    buildActions({newTemplate, templateActions, source});
                    normalizedTemplates.push(newTemplate);
                }
            }
        }
    }
}())