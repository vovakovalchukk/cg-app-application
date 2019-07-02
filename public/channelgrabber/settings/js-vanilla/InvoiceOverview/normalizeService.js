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
                };
                actions['delete'] = {
                    key: 'delete',
                    name: 'delete',
                };
            }
        }

        delete newTemplate.links;
    }


    return {
        normalizeTemplateData : (systemTemplates, userTemplates) => {
            let normalizedTemplates = [];
            let templateActions = {
                byTemplateId: {
                    //1: {href} ...
                }
            };
            let favourites = [];

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
                favourites,
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
                    if(newTemplate.favourite){
                        favourites.push(newTemplate.id);
                    }
                    normalizedTemplates.push(newTemplate);
                }
            }
        }
    }
}())