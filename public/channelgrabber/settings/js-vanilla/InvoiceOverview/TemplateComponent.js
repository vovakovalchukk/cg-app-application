import React from 'react';
import TemplateActions from 'InvoiceOverview/Components/TemplateActions';

const Thumbnail = props => {
    let {imageUrl, actions} = props;
    if (!imageUrl) {
        return null;
    }

    //todo - this is no good this shouldnt be read from properties
    let thumbnailProps = {};
    thumbnailProps['src'] = imageUrl;
    for (let actionName in actions) {
        let currentAction = actions[actionName];
        if (currentAction.name !== 'create' || currentAction.name !== 'edit') {
            continue;
        }
        thumbnailProps['href'] = currentAction.linkHref;
        if (currentAction.linkTarget) {
            thumbnailProps['linkTarget'] = currentAction.linkTarget;
        }
    }

    return <img {...thumbnailProps}/>
};

class TemplateComponent extends React.Component {
    render() {
        let {id, imageUrl, templateActions} = this.props;
        return (<div className={"invoice-template-element"}>
            <div className={'invoice-template-thumb'}>
                <Thumbnail imageUrl={imageUrl} actions={templateActions}/>
                <div className={'template-overview-actions-container'}>
                    <TemplateActions actions={templateActions} templateId={id}/>
                </div>
            </div>
            <div className={'invoice-template-name'}>
                {this.props.name}
            </div>
        </div>);
    }
}

export default TemplateComponent;