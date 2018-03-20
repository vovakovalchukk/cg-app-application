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
            }
        };
    };

    var CategorySelectConnector = ReactRedux.connect(mapStateToProps, mapDispatchToProps);

    return CategorySelectConnector(AccountCategorySelectComponent);
});