"use strict";

import productActions from "./productActions";

var actionCreators = (function() {
    return {
        sortBy: (column) => {
            return async (dispatch) => {
                dispatch({
                    type: 'SORT_REQUEST',
                    payload: column,
                });

                await dispatch(productActions.getProducts());
            }
        },
    }
})();

export default actionCreators;
