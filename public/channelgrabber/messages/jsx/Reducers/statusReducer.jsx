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
},
active: 'Unassigned'
*/
const initialState = {
    byId: {},
    active: 'Unassigned',
    default: 'Unassigned'
};

const statusReducer = reducerCreator(initialState, {
    'STATUS_FETCH_SUCCESS': (state, action) => {
        let status = {...state};
        Object.keys(action.payload).forEach(statusId => {
            let statusCount = action.payload[statusId];
            status.byId[statusId] = {
                count: statusCount,
            };
        });
        return {...state, status};
    }
});

export default statusReducer;