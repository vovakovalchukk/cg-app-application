import reducerCreator from 'Common/Reducers/creator';

"use strict";

const initialState = {
    filters: {
        byId: {
            //organisationUnitId: {
            //    count: 3
            // }
        }
    },
};

const filtersReducer = reducerCreator(initialState, {
    "FILTERS_FETCH_SUCCESS": (state, action) => {
        let filters = {...state.filters};
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