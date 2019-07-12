import React from 'react';
import TemplateComponent from 'InvoiceOverview/TemplateComponent';

class SectionComponent extends React.Component {
    render() {
        let {templates, templateActions, source} = this.props;
        if (templates.length === 0) {
            return null;
        }
        return (<div className={'invoice-template-section module'}>
            <div className={'heading-large'}>
                {this.props.sectionHeader}
            </div>
            <div>
                {templates.map(template => {
                    if (source !== template.source) {
                        return;
                    }
                    return <TemplateComponent
                        {...template}
                        templateActions={templateActions.byTemplateId[template.id]}
                    />
                })}
            </div>
        </div>);
    }
}

export default SectionComponent;