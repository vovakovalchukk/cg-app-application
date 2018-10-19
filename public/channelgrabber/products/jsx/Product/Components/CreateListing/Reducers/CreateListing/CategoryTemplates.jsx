import reducerCreator from 'Common/Reducers/creator';
    const initialState = {
        isFetching: true,
        categories: {}
    };

    export default reducerCreator({}, {
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

