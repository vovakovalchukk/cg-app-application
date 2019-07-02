import React, {useState} from "react";

import LinkAction from 'InvoiceOverview/TemplateActions/LinkAction';

import FavouriteTemplate from 'InvoiceOverview/TemplateActions/FavouriteTemplate';
import DeleteTemplate from 'InvoiceOverview/TemplateActions/DeleteTemplate';

const actionIconMap = {
    'favourite': FavouriteTemplate,
    'edit': LinkAction,
    'create': LinkAction,
    'duplicate': LinkAction,
    'delete': DeleteTemplate,
    'buy': LinkAction
};

const Actions = props => {
    let {actions, templateId} = props;
    if (!actions) {
        return null;
    }

    let result = [];

    for (let actionKey in actions) {
        let action = actions[actionKey];

        let trimmedName = action.name.toLowerCase().split(' ')[0];

        let ActionIcon = actionIconMap[trimmedName];
        if (!ActionIcon) {
            continue;
        }
        
        result.push(<ActionIcon
            className={`template-overview-${trimmedName}-icon`}
            trimmedName={trimmedName}
            templateId={templateId}
            href={action.linkHref}
        />);
    }

    return result;
};

export default Actions;