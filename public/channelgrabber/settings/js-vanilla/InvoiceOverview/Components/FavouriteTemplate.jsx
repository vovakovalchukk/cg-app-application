import React, {useState} from "react";

import Icon from 'zf2-v4-ui/img/icons/star.svg';

let FavouriteTemplate = function(props) {
    let {className, trimmedName, templateId} = props;
    let favouriteState = useFavourites();

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

    function useFavourites() {
        let [favourites, setFavourites] = useState([]);

        function toggleFavourite() {
            let indexOfTemplateId = favourites.indexOf(templateId);

            let newFavourites = favourites.slice();

            if (indexOfTemplateId > -1) {
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

export default FavouriteTemplate;