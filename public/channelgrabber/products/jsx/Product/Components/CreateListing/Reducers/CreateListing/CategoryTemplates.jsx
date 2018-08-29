define([
    'Common/Reducers/creator'
], function(
    reducerCreator
) {
    return reducerCreator({}, {
        "FETCH_CATEGORY_TEMPLATE_DEPENDANT_FIELD_VALUES": function() {
            return {};
        },
        "CATEGORY_TEMPLATE_DEPENDANT_FIELD_VALUES_FETCHED": function(state, action) {
            return action.payload.categoryTemplates;
        }
    });
});
