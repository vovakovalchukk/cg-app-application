import React, {useState} from 'react';
import SectionComponent from 'InvoiceOverview/SectionComponent';
import normalize from 'InvoiceOverview/normalizeService';

const RootContext = React.createContext({});

let RootComponent = function(props) {
    let {system, user} = props;
    let {templates, templateActions, favourites, TEMPLATE_SOURCES} = normalize.normalizeTemplateData(system, user);

    let templatesState = useTemplates(templates);

    //todo handle the deletion of components here
    return (
        <RootContext.Provider value={templatesState}>
            <div>
                <SectionComponent
                    className={'invoice-template-section module'}
                    sectionHeader={'Create New Template'}
                    templates={templates}
                    templateActions={templateActions}
                    source={TEMPLATE_SOURCES.system}
                />
                <SectionComponent
                    className={'invoice-template-section module'}
                    sectionHeader={'Edit Existing Template'}
                    templates={templates}
                    templateActions={templateActions}
                    source={TEMPLATE_SOURCES.user}
                />
            </div>
        </RootContext.Provider>
    );

    function useTemplates(initialTemplates) {
        let [templates, setTemplates] = useState(initialTemplates);

        function deleteTemplate(templateId){
            console.log('in deleteTemplate');
            let newTemplates = templates.slice();
            console.log('newTemplates: ', newTemplates);
        }

        return {
            templates,
            setTemplates,
            deleteTemplate
        }
    }
};

export default RootComponent;
export {RootContext};