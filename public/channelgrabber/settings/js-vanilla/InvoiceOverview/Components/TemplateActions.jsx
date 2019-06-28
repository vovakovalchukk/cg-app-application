import React from "react";

import FavouriteIcon from 'zf2-v4-ui/img/icons/star.svg';
import EditIcon from 'zf2-v4-ui/img/icons/edit.svg';
import CreateIcon from 'zf2-v4-ui/img/icons/plus.svg';
import DuplicateIcon from 'zf2-v4-ui/img/icons/copy.svg';
import DeleteIcon from 'zf2-v4-ui/img/icons/delete.svg';
import BuyLabelIcon from 'zf2-v4-ui/img/icons/shopping-cart.svg';

const actionIconMap = {
    'favourite': FavouriteIcon,
    'edit': EditIcon,
    'create': CreateIcon,
    'duplicate': DuplicateIcon,
    'delete': DeleteIcon,
    'buy': BuyLabelIcon
};

const Actions = props => {
    let {actions} = props;
    if (!actions) {
        return null;
    }

    let result = [];

    for (let actionKey in actions) {
        let action = actions[actionKey];

        let linkProps = getLinkPropsForAction(action);

        let trimmedName = action.name.toLowerCase().split(' ')[0];
        let ActionIcon = actionIconMap[trimmedName];
        if (!ActionIcon) {
            continue;
        }
        
        console.log('actions: ', actions);


        result.push(
            <a {...linkProps}>
                <ActionIcon
                    className={`template-overview-${trimmedName}-icon`}
                />
            </a>
        )
    }

    return result;

    function getLinkPropsForAction(action) {
        let linkProps = {};

        let getLinkPropsMap = {
            'favourite': getLinkPropsForFavourite,
            'deleteTemplate': getLinkPropsForDelete
        };

        //todo do something different for favourite
        if (typeof getLinkPropsMap[action.name] == 'function') {
            return getLinkPropsMap[action.name]();
        }

        linkProps['href'] = action.linkHref;
        return linkProps;
    }

    function getLinkPropsForFavourite() {
        let linkProps = {};
        linkProps.onClick = function() {
            console.log('on favourite click');
        };
        return linkProps;
    }

    function getLinkPropsForDelete() {
        let linkProps = {};
        linkProps.onClick = function() {
            console.log('on delete click');
        };
        return linkProps;
    }
};

export default Actions;