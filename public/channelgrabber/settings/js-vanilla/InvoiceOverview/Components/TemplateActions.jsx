import React, {useState} from "react";

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
    let {actions, templateId} = props;
    if (!actions) {
        return null;
    }

    let result = [];

    let favouriteState = useFavourites();

    for (let actionKey in actions) {
        let action = actions[actionKey];

        let linkProps = getLinkPropsForAction(action, templateId);

        let trimmedName = action.name.toLowerCase().split(' ')[0];
        let ActionIcon = actionIconMap[trimmedName];
        if (!ActionIcon) {
            continue;
        }

        result.push(
            <a {...linkProps}>
                <ActionIcon
                    className={`template-overview-${trimmedName}-icon ${linkProps.iconClassName}`}
                />
            </a>
        )
    }

    return result;

    function getLinkPropsForAction(action, templateId) {
        let linkProps = {};

        let getLinkPropsMap = {
            'favourite': getLinkPropsForFavourite,
            'deleteTemplate': getLinkPropsForDelete
        };

        if (typeof getLinkPropsMap[action.name] == 'function') {
            return getLinkPropsMap[action.name](templateId);
        }

        linkProps['href'] = action.linkHref;
        return linkProps;
    }

    function getLinkPropsForFavourite(templateId) {
        let linkProps = {};
        linkProps.onClick = function() {
            console.log('on favourite click ', templateId);

            // todo do something better here.
            console.log('favouriteState: ', favouriteState);

            //todo seewhats happening
            favouriteState.toggleFavourite(templateId);
        };

        if(favouriteState.isFavourite(templateId)){
            linkProps.iconClassName = '-active-favourite';
        }

        return linkProps;
    }

    function getLinkPropsForDelete(templateId) {
        let linkProps = {};
        linkProps.onClick = function() {
            console.log('on delete click', templateId);
        };
        return linkProps;
    }

    function useFavourites() {
        let [favourites, setFavourites] = useState([]);

        function toggleFavourite(templateId) {
            let indexOfTemplateId = favourites.indexOf(templateId);

            let newFavourites = favourites.slice();

            if(indexOfTemplateId > -1){
                newFavourites.splice(indexOfTemplateId, 1);
                setFavourites(newFavourites);
                return;
            }

            newFavourites.push(templateId);
            setFavourites(newFavourites);
            return;
        }

        function isFavourite(templateId) {
            return favourites.indexOf(templateId) > -1
        }

        return {
            favourites,
            setFavourites,
            isFavourite,
            toggleFavourite
        }
    }
};

export default Actions;