define([
    'react',
    'react-redux',
    'CategoryMapper/Actions/Category',
    'CategoryMapper/Components/AccountCategorySelect'
], function(
    React,
    ReactRedux,
    Actions,
    AccountCategorySelectComponent
) {
    var mapStateToProps = function(state, ownProps) {
        return state;
    };

    var mapDispatchToProps = function (dispatch) {
        return {
            onOptionChange: function(accountId, categoryId, categoryLevel) {
                dispatch(Actions.categorySelected(dispatch, accountId, categoryId, categoryLevel));
            },
            onRefreshClick: function(accountId) {
                dispatch(Actions.refreshButtonClicked(dispatch, accountId));
            }
        };
    };

    var CategorySelectConnector = ReactRedux.connect(mapStateToProps, mapDispatchToProps);

    return CategorySelectConnector(AccountCategorySelectComponent);
});