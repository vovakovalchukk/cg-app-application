import React, {useContext, useState} from "react";

import Icon from 'zf2-v4-ui/img/icons/star.svg';
import service from 'InvoiceOverview/service';
import {RootContext} from "InvoiceOverview/Root";

let FavouriteTemplate = function(props) {
    const rootContext = useContext(RootContext);
    let {className, trimmedName, templateId} = props;

    let favouriteState = useFavourites(rootContext.favourites);

    let iconClassName = '';

    if (favouriteState.isFavourite(templateId)) {
        iconClassName = '-active-favourite';
    }

    return (
        <a
            className={className}
            onClick={favouriteState.toggleFavourite}
        >
            <Icon
                className={`template-overview-${trimmedName}-icon ${iconClassName}`}
            />
        </a>
    );

    function useFavourites(initialFavourites) {
        let [favourites, setFavourites] = useState(initialFavourites);

        async function toggleFavourite() {
            let indexOfTemplateId = favourites.indexOf(templateId);

            let newFavourites = favourites.slice();

            if (indexOfTemplateId > -1) {
                let response = await service.removeFavourite(templateId);
                if (!response.success) {
                    return;
                }
                newFavourites.splice(indexOfTemplateId, 1);
                setFavourites(newFavourites);
                return;
            }
            let response = await service.addFavourite(templateId);
            if (!response.success) {
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

export default FavouriteTemplate;