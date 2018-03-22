define([
    'redux-form',
    'react-redux',
    'CategoryMapper/Actions/Category',
    'CategoryMapper/Components/CategoryMap'
], function(
    ReduxForm,
    ReactRedux,
    Actions,
    CategoryMap
) {
    var mapStateToProps = function(state, ownProps) {
        var categoryMap = state.categoryMap;
        return {
            accounts: categoryMap
        }
    };

    var mapDispatchToProps = null;

    var CategoryMapConnector = ReactRedux.connect(mapStateToProps, mapDispatchToProps);

    CategoryMap = CategoryMapConnector(CategoryMap);

    var categoryMapFormCreator = ReduxForm.reduxForm({
        form: "categoryMap"
    });

    return categoryMapFormCreator(CategoryMap);
});
