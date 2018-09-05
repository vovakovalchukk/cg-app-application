define([
    'Common/Reducers/creator'
], function(
    reducerCreator
) {
    const initialState = {
        isFetching: true,
        categories: {}
    };

    return reducerCreator({}, {
        "FETCH_CATEGORY_TEMPLATE_DEPENDANT_FIELD_VALUES": function() {
            return initialState;
        },
        "CATEGORY_TEMPLATE_DEPENDANT_FIELD_VALUES_FETCHED": function(state, action) {
            return {
                isFetching: false,
                categories: action.payload.categoryTemplates
            };
        }
    });
});
