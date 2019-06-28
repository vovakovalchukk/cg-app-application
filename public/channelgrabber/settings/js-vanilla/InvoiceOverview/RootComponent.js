import React from 'react';
import SectionComponent from 'InvoiceOverview/SectionComponent';
import normalize from 'InvoiceOverview/normalizeService';


class RootComponent extends React.Component {
    render() {
        let {system, user} = this.props;
        let {templates, templateActions, favourites, TEMPLATE_SOURCES} = normalize.normalizeTemplateData(system, user);

        return (
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
        );
    }
}

export default RootComponent;
