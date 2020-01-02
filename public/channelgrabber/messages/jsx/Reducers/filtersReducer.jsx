import reducerCreator from 'Common/Reducers/creator';

'use strict';

const ajaxFilter = {
   'organisationUnitId': {},
   'unassigned': {ajaxProp: 'assignee'},
   'assigned': {ajaxProp: 'assignee'},
   'myMessages': {ajaxProp: 'assignee', ajaxValue: 'active-user'},
   'resolved': {ajaxProp: 'status'},
   'open': {ajaxProp: 'open', ajaxValue: ['new', 'awaiting reply']}
};

/**
"byId": {
    "organisationUnitId": {
      "count": 2
    },
    "unassigned": {
      "id": <int>
      "count": <string>,
      "ajaxFilterProperty": <string>,
      "ajaxFilterValue": <string / array>
    },
    ...
},
default: 'unassigned'
*/
const initialState = {
    byId: {
        'unassigned': {
            id: 'unassigned',
            ajaxFilterProperty: ajaxFilter['unassigned'].ajaxProp
        }
    },
    default: 'unassigned',
    getById: function (id) {
        return this.byId[id] && this.byId[id];
    }
};

const filtersReducer = reducerCreator(initialState, {
    'FILTERS_FETCH_SUCCESS': (state, action) => {
        let filters = {...state};
        Object.keys(action.payload).forEach((filterId) => {
            let filterCount = action.payload[filterId];
            filters.byId[filterId] = createFilterObject({filterId, filterCount});
        });

        filters.byId['open'] = createFilterObject({
            filterId: 'open',
            filterCount: (Number(getFilterCount(filters,'myMessages')) +
                Number(getFilterCount(filters, 'unassigned')) +
                Number(getFilterCount(filters, 'assigned'))).toString()
        });

        return {...state, filters};
    }
});

export default filtersReducer;

function getFilterCount(filters, id) {
    if (!filters.byId[id]) {
        return null;
    }
    return filters.byId[id].count.toString();
}

function createFilterObject({filterId, filterCount}) {
    return{
        id: filterId,
        count: filterCount,
        ajaxFilterProperty: ajaxFilter[filterId].ajaxProp,
        ajaxFilterValue: ajaxFilter[filterId].ajaxValue || filterId
    };
}