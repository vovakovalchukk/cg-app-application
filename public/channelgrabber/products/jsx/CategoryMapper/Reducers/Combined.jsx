define([
    'redux',
    'redux-form',
    'CategoryMapper/Reducers/CategoryMaps'
], function(
    Redux,
    ReduxForm,
    CategoryMapsReducer
) {
    var CombinedReducer = Redux.combineReducers({
        accounts: function (state) {
            if (!state) {
                return {};
            }
            return state;
        },
        categories: function (state) {
            if (!state) {
                return {};
            }
            return state;
        },
        form: ReduxForm.reducer,
        categoryMaps: CategoryMapsReducer
    });
    return CombinedReducer;
});
