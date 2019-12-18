import FilterItem from './FilterItem';

const navItems = [
    {
        id: 'unassigned',
        filterId: 'unassigned',
        displayText: 'Unassinged',
        shouldDisplay: areNumberOfOusAbove0,
        component: FilterItem
    },
    {
        id: 'assigned',
        filterId: 'assigned',
        displayText: 'Assigned',
        shouldDisplay: areNumberOfOusAbove0,
        component: FilterItem
    },
    {
        id: 'my-messages',
        filterId: 'my-messages',
        displayText: 'My Messages',
        shouldDisplay: areNumberOfOusAbove0,
        component: FilterItem
    },
    {
        id: 'resolved',
        filterId: 'resolved',
        displayText: 'Resolved',
        component: FilterItem
    },
    {
        id: 'open',
        filterId: 'resolved',
        displayText: 'Open',
        component: FilterItem
    }
];

export default navItems;

function areNumberOfOusAbove0({ous}) {
    return Array.isArray(ous) && ous.length > 1;
}