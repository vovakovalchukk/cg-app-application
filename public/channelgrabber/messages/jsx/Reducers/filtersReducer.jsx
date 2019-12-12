import reducerCreator from 'Common/Reducers/creator';

'use strict';

/**
"byId": {
    "organisationUnitId": {
      "count": <int>
    },
    "unassigned": {
      "count": <string>
    },
    "resolved": {
      "count": <string>
    },
    "assigned": {
      "count": <string>
    },
    "myMessages": {
      "count": <int>
    }
}
*/
const initialState = {
    byId: {}
};

const filtersReducer = reducerCreator(initialState, {
    'FILTERS_FETCH_SUCCESS': (state, action) => {
        let filters = {...state};
        Object.keys(action.payload).forEach(filterId => {
            let filterCount = action.payload[filterId];
            filters.byId[filterId] = {
                count: filterCount,
            };
        });
        return {...state, filters};
    }
});

export default filtersReducer;