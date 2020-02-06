import React, {useState} from 'react';

const useItemsState = (initialItems) => {

    const buildDefaultItemsById = (items) => {
        const itemsById = {};

        items.forEach((item) => {
            itemsById[item.id] = {
                ...item,
                selectedAmount: item.amount,
                selected: false
            }
        });

        return itemsById;
    };

    const updateItemPropertyInItems = (items, productId, property, newValue) => {
        return {
            ...items,
            [productId]: {
                ...items[productId],
                [property]: newValue
            }
        }
    };

    const [items, setItems] = useState(buildDefaultItemsById(initialItems));

    return {
        items,
        updateItemAmount: (productId, newAmount) => {
            setItems(updateItemPropertyInItems(items, productId, 'selectedAmount', newAmount));
        },
        toggleItem: (productId) => {
            setItems(updateItemPropertyInItems(items, productId, 'selected', !(items[productId].selected)));
        }
    }
};

export default useItemsState;
