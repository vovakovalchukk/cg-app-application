import React from 'react';
import TemplateActions from 'InvoiceOverview/TemplateActions/TemplateActions';

const THUMBNAIL_RELEVANT_ACTION_NAMES = ['create', 'edit'];

const Thumbnail = props => {
    let {imageUrl, actions} = props;
    if (!imageUrl) {
        return null;
    }

    let linkAttributes = getThumbnailLinkAttributesFromActions();

    return <a {...linkAttributes}>
        <img src={imageUrl} alt='thumbnail'/>
    </a>;

    function getThumbnailLinkAttributesFromActions() {
        let attributes = {};
        attributes['src'] = imageUrl;
        for (let actionName in actions) {
            let currentAction = actions[actionName];
            attributes['href'] = currentAction.linkHref;
            if (currentAction.linkTarget) {
                attributes['linkTarget'] = currentAction.linkTarget;
            }
        }
        return attributes;
    }
};

const TemplateComponent = props => {
    let {id, imageUrl, templateActions} = props;

    let thumbnailActions = getThumbnailActions(templateActions);

    return (<div className={"invoice-template-element"}>
        <div className={'invoice-template-thumb'}>
            <Thumbnail imageUrl={imageUrl} actions={thumbnailActions}/>
            <div className={'template-overview-actions-container'}>
                <TemplateActions actions={templateActions} templateId={id}/>
            </div>
        </div>
        <div className={'invoice-template-name'}>
            {props.name}
        </div>
    </div>);

    function getThumbnailActions(actions) {
        return Object.keys(actions).filter(actionKey => {
            return THUMBNAIL_RELEVANT_ACTION_NAMES.includes(actions[actionKey].name.toLowerCase());
        }).map(actionKey => {
            return actions[actionKey];
        });
    }
};

export default TemplateComponent;