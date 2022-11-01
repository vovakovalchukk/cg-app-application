import reducerCreator from 'Common/Reducers/creator';

"use strict";

const initialState = []; // [ { column: 'title', direction: 'asc' }, {...}, ... ]

const sortReducer = reducerCreator(initialState, {
    'SORT_REQUEST': (state, action) => {
        let column = action.payload;

        return applySort(column, state);
    }
});

let applySort = function(column, currentSort) {
    let sort0 = currentSort[0];

    if (sort0 && sort0.column === column) {
        let newDir = changeDirection(sort0.direction);
        if (newDir === '') {
            return [];
        } else {
            return [ { column: column, direction: newDir } ];
        }
    }

    return [ { column: column, direction: 'asc' } ];
}

let changeDirection = function(dir) {
    if (dir === 'asc') {
        return 'desc';
    }
    if (dir === 'desc') {
        return '';
    }
    return 'asc';
}

export default sortReducer;
