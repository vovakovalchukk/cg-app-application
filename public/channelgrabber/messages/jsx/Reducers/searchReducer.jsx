import reducerCreator from 'Common/Reducers/creator';

'use strict';

const initialState = {
    query: '',
};

const searchReducer = reducerCreator(initialState, {
    'SEARCH_INPUT_CHANGED': (state, action) => {
        let search = {...state};

        search.query = action.payload;

        return {...state, ...search};
    },
});

export default searchReducer;
