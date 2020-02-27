import React from 'react';
import FilterItem from './FilterItem';
import Item from './Item';

const navItems = [
    {
        id: 'unassigned',
        filterId: 'unassigned',
        displayText: 'Unassigned',
        to: '/messages/list/unassigned',
        shouldDisplay: areNumberOfOusAbove0,
        component: FilterItem
    },
    {
        id: 'assigned',
        filterId: 'assigned',
        displayText: 'Assigned',
        to: '/messages/list/assigned',
        shouldDisplay: areNumberOfOusAbove0,
        component: FilterItem,
        className: 'statusCountPillBox awaiting-payment',
    },
    {
        id: 'myMessages',
        filterId: 'myMessages',
        displayText: 'My Messages',
        to: '/messages/list/myMessages',
        shouldDisplay: areNumberOfOusAbove0,
        component: FilterItem,
        className: 'statusCountPillBox dispatched',
    },
    {
        id: 'resolved',
        filterId: 'resolved',
        displayText: 'Resolved',
        to: '/messages/list/resolved',
        component: FilterItem,
        className: 'statusCountPillBox new',
    },
    {
        id: 'open',
        filterId: 'open',
        displayText: 'Open',
        to: '/messages/list/open',
        component: FilterItem,
        className: 'statusCountPillBox processing',
    },
    {
        id: 'templates',
        displayText: 'Templates',
        to: '/messages/templates',
        component: Item,
    }
];

export default navItems;

function areNumberOfOusAbove0({ous}) {
    return Array.isArray(ous) && ous.length > 1;
}
