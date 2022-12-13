"use strict";

import productActions from "./productActions";

var sortActions = (function() {
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
        storeInitialSort: (initialSort) => {
            return (dispatch) => {
                dispatch({
                    type: "INITIAL_SORT",
                    payload: initialSort,
                });
            };
        },
        saveDefaultSort: (currentUserOnly) => {
            return (dispatch, getState) => {
                const state = getState();
                $.ajax({
                    context: this,
                    url: "/products/sort/save",
                    type: "POST",
                    data: {
                        sortingData: {
                            "sort": state.sort,
                            "limit": state.pagination.limit
                        },
                        currentUserOnly: currentUserOnly
                    },
                    success: function (response) {
                        return response;
                    },
                    error: function (error) {
                        return error;
                    }
                });
            };
        },
    };
})();

export default sortActions;
