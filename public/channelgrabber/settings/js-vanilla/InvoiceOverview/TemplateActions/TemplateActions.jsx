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

const TemplateActions = props => {
    let {actions, templateId} = props;
    if (!actions) {
        return null;
    }

    let result = [];

    let actionsArray = [];
    for (let actionKey in actions) {
        let action = actions[actionKey];
        actionsArray.push(action)
    }

    actionsArray = actionsArray.sort((a, b) => {
        if (trimName(a.name) === 'delete') {
            return 1
        }
        return -1;
    });

    for (let action of actionsArray) {
        let trimmedName = trimName(action.name);

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

export default TemplateActions;

function trimName(name) {
    return name.toLowerCase().split(' ')[0];
}