import FilterItem from './FilterItem';
import Item from './Item';

import React from 'react';

const navItems = [
    {
        id: 'unassigned',
        filterId: 'unassigned',
        displayText: 'Unassinged',
        to: '/list/unassigned',
        shouldDisplay: areNumberOfOusAbove0,
        component: FilterItem
    },
    {
        id: 'assigned',
        filterId: 'assigned',
        displayText: 'Assigned',
        to: '/list/assigned',
        shouldDisplay: areNumberOfOusAbove0,
        component: FilterItem
    },
    {
        id: 'my-messages',
        filterId: 'my-messages',
        displayText: 'My Messages',
        to: '/list/my-messages',
        shouldDisplay: areNumberOfOusAbove0,
        component: FilterItem
    },
    {
        id: 'resolved',
        filterId: 'resolved',
        displayText: 'Resolved',
        to: '/list/resolved',
        component: FilterItem
    },
    {
        id: 'open',
        filterId: 'resolved',
        displayText: 'Open',
        to: '/list/open',
        component: FilterItem
    },
    {
        id: 'templates',
        displayText: 'Templates',
        to: '/templates',
        component: Item
    }
];

export default navItems;

function areNumberOfOusAbove0({ous}) {
    return Array.isArray(ous) && ous.length > 1;
}