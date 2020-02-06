import React, {useState} from 'react';

const useItemsState = (initialItems) => {

    const buildDefaultItemsById = (items) => {
        const itemsById = {};

        items.forEach((item) => {
            itemsById[item.id] = {
                ...item,
                selectedAmount: 0
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
        }
    }
};

export default useItemsState;
