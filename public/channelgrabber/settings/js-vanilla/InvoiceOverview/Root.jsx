import React, {useState} from 'react';
import SectionComponent from 'InvoiceOverview/SectionComponent';
import normalize from 'InvoiceOverview/normalizeService';

const RootContext = React.createContext({});

let Root = props => {
    let {system, user} = props;
    let {templates, templateActions, favourites, TEMPLATE_SOURCES} = normalize.normalizeTemplateData(system, user);

    let templatesState = useTemplates(templates);

    return (
        <RootContext.Provider value={{templatesState, favourites}}>
            <div>
                <SectionComponent
                    className={'invoice-template-section module'}
                    sectionHeader={'Create New Template'}
                    templates={templatesState.templates}
                    templateActions={templateActions}
                    source={TEMPLATE_SOURCES.system}
                />
                <SectionComponent
                    className={'invoice-template-section module'}
                    sectionHeader={'Edit Existing Template'}
                    templates={templatesState.templates}
                    templateActions={templateActions}
                    source={TEMPLATE_SOURCES.user}
                />
            </div>
        </RootContext.Provider>
    );

    function useTemplates(initialTemplates) {
        let [templates, setTemplates] = useState(initialTemplates);

        function deleteTemplate(templateId) {
            if (!templates) {
                return;
            }
            let newTemplates = templates.slice();
            let templateIndex = newTemplates.findIndex(template => (template.id === templateId));
            if (templateIndex < 0) {
                return;
            }
            newTemplates.splice(templateIndex, 1);
            setTemplates(newTemplates);
        }

        return {
            templates,
            setTemplates,
            deleteTemplate
        }
    }
};

export default Root;
export {RootContext};