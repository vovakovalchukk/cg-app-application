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

    var mapDispatchToProps = function (dispatch) {
        return {
            onCategorySelected: function(accountId, categoryId, categoryLevel) {
                dispatch(Actions.categorySelected(dispatch, accountId, categoryId, categoryLevel));
            },
            onRefreshClick: function(accountId) {
                dispatch(Actions.refreshButtonClicked(dispatch, accountId));
            },
            onRemoveButtonClick: function (accountId) {
                dispatch(Actions.removeButtonClicked(accountId));
            }
        };
    };

    var CategoryMapConnector = ReactRedux.connect(mapStateToProps, mapDispatchToProps);

    CategoryMap = CategoryMapConnector(CategoryMap);

    var categoryMapFormCreator = ReduxForm.reduxForm({
        form: "categoryMap"
    });

    return categoryMapFormCreator(CategoryMap);
});
