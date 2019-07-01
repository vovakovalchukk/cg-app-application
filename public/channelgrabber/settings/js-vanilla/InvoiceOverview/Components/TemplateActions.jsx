import React, {useState} from "react";

import LinkAction from 'InvoiceOverview/Components/LinkAction';

import FavouriteTemplate from 'InvoiceOverview/Components/FavouriteTemplate';
import DeleteTemplate from 'InvoiceOverview/Components/DeleteTemplate';

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
        // pass action props to component
        let trimmedName = action.name.toLowerCase().split(' ')[0];
        
        console.log('trimmedName: ', trimmedName);
        
        
        let ActionIcon = actionIconMap[trimmedName];
        if (!ActionIcon) {
            continue;
        }
        
        console.log('ActionIcon: ', ActionIcon);
        
        
        result.push(<ActionIcon
            className={`template-overview-${trimmedName}-icon`}
            trimmedName={trimmedName}
            templateId={templateId}
            href={action.linkHref}
        />);
    }

    return result;

//
//    function getLinkPropsForFavourite(templateId) {
//        let linkProps = {};
//        linkProps.onClick = function() {
//            console.log('on favourite click ', templateId);
//
//            // todo do something better here.
//            console.log('favouriteState: ', favouriteState);
//
//            //todo seewhats happening
//            favouriteState.toggleFavourite(templateId);
//        };
//
//        if (favouriteState.isFavourite(templateId)) {
//            linkProps.iconClassName = '-active-favourite';
//        }
//
//        return linkProps;
//    }

//    function getLinkPropsForDelete(templateId) {
//        let linkProps = {};
//        linkProps.onClick = function() {
//            console.log('on delete click', templateId);
//        };
//        return linkProps;
//    }


};

export default Actions;