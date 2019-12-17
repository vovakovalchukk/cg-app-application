import reducerCreator from 'Common/Reducers/creator';

'use strict';

const initialState = {
    byId: {},
    sortBy: '',
};

const columnReducer = reducerCreator(initialState, {
    'SORT_BY_REQUEST': (state, action) => {
        let {key} = action.payload;
        console.log('reducer sort by');
        return {...state, sortBy: key};
    }
});

export default columnReducer;